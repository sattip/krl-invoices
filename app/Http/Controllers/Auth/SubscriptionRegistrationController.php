<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class SubscriptionRegistrationController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Display the registration form (Step 1).
     */
    public function create(Request $request)
    {
        $plans = Plan::active()->ordered()->get();
        $selectedPlan = $request->query('plan');

        return view('auth.register', compact('plans', 'selectedPlan'));
    }

    /**
     * Handle registration form submission (Step 1).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'company_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'plan_id' => ['required', 'exists:plans,id'],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'Για να συνεχίσετε πρέπει να αποδεχτείτε τους Όρους Χρήσης.',
        ]);

        // Store registration data in session
        session([
            'registration' => [
                'name' => $request->name,
                'email' => $request->email,
                'company_name' => $request->company_name,
                // Never keep the plaintext password in the session store.
                'password_hash' => Hash::make($request->password),
                'plan_id' => $request->plan_id,
                'terms_accepted_at' => now()->toIso8601String(),
                'terms_version' => config('legal.terms_version'),
            ],
        ]);

        return redirect()->route('register.checkout');
    }

    /**
     * Display checkout page (Step 2).
     */
    public function checkout()
    {
        $registration = session('registration');

        if (!$registration) {
            return redirect()->route('register')
                ->with('error', 'Συμπληρώστε πρώτα τη φόρμα εγγραφής.');
        }

        $plan = Plan::findOrFail($registration['plan_id']);

        return view('auth.register-checkout', compact('registration', 'plan'));
    }

    /**
     * Process checkout and create Stripe session.
     */
    public function processCheckout()
    {
        $registration = session('registration');

        if (!$registration) {
            return redirect()->route('register')
                ->with('error', 'Συμπληρώστε πρώτα τη φόρμα εγγραφής.');
        }

        if (!$this->stripeService->isConfigured()) {
            return back()->with('error', 'Το σύστημα πληρωμών δεν είναι διαθέσιμο αυτή τη στιγμή. Επικοινωνήστε με την υποστήριξη.');
        }

        $plan = Plan::findOrFail($registration['plan_id']);

        // Create a temporary customer in Stripe
        try {
            $customer = $this->stripeService->getClient()->customers->create([
                'email' => $registration['email'],
                'name' => $registration['name'],
                'metadata' => [
                    'company_name' => $registration['company_name'],
                    'registration' => 'pending',
                ],
            ]);

            // Store customer ID in session
            session(['registration.stripe_customer_id' => $customer->id]);

            // Create Checkout session
            $session = $this->stripeService->createCheckoutSession(
                $customer->id,
                $plan,
                route('register.success') . '?session_id={CHECKOUT_SESSION_ID}',
                route('register.cancel'),
                [
                    'registration_email' => $registration['email'],
                    'company_name' => $registration['company_name'],
                    'plan_id' => $plan->id,
                ]
            );

            return redirect($session->url);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Δεν ήταν δυνατή η έναρξη της πληρωμής. Δοκιμάστε ξανά ή επικοινωνήστε με την υποστήριξη.');
        }
    }

    /**
     * Handle successful registration and payment.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        $registration = session('registration');

        if (!$registration || !$sessionId) {
            return redirect()->route('register')
                ->with('error', 'Η συνεδρία εγγραφής δεν είναι έγκυρη. Ξεκινήστε ξανά την εγγραφή.');
        }

        try {
            // Retrieve the checkout session to verify payment
            $session = $this->stripeService->getClient()->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription'],
            ]);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('register.checkout')
                    ->with('error', 'Η πληρωμή δεν ολοκληρώθηκε. Δοκιμάστε ξανά.');
            }

            // Create the company
            $company = Company::create([
                'name' => $registration['company_name'],
                'email' => $registration['email'],
                'is_active' => true,
            ]);

            // Create the user
            $user = User::create([
                'name' => $registration['name'],
                'email' => $registration['email'],
                'password' => $registration['password_hash'] ?? Hash::make($registration['password']),
                'company_id' => $company->id,
                'role' => 'owner',
            ]);

            // Record the explicit Terms acceptance given at step 1.
            $user->forceFill([
                'terms_accepted_at' => isset($registration['terms_accepted_at'])
                    ? \Carbon\Carbon::parse($registration['terms_accepted_at'])
                    : now(),
                'terms_version' => $registration['terms_version'] ?? config('legal.terms_version'),
            ])->save();

            // Create the subscription record
            $plan = Plan::findOrFail($registration['plan_id']);
            $stripeSubscription = $session->subscription;

            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'stripe_subscription_id' => $stripeSubscription->id,
                'stripe_customer_id' => $session->customer,
                'status' => $stripeSubscription->status,
                'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            ]);

            // Update Stripe customer metadata
            $this->stripeService->getClient()->customers->update($session->customer, [
                'metadata' => [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'registration' => 'completed',
                ],
            ]);

            // Clear registration session
            session()->forget('registration');

            // Fire registered event
            event(new Registered($user));

            // Log the user in
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Καλώς ήρθατε στο InvoiceAI! Ο λογαριασμός σας δημιουργήθηκε.');
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('register')
                ->with('error', 'Δεν ήταν δυνατή η ολοκλήρωση της εγγραφής. Αν έχει γίνει χρέωση, επικοινωνήστε με την υποστήριξη στο ' . config('legal.company.support_email') . '.');
        }
    }

    /**
     * Handle canceled checkout.
     */
    public function cancel()
    {
        return redirect()->route('register.checkout')
            ->with('info', 'Η πληρωμή ακυρώθηκε. Μπορείτε να δοκιμάσετε ξανά όποτε θέλετε.');
    }
}
