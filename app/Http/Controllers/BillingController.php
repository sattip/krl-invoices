<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\StripeService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Get company or abort with error.
     */
    protected function getCompanyOrFail()
    {
        $company = current_company();

        if (!$company) {
            abort(403, 'Για πρόσβαση στη χρέωση, ο λογαριασμός σας πρέπει να είναι συνδεδεμένος με εταιρεία. Παρακαλούμε επικοινωνήστε με τον διαχειριστή.');
        }

        return $company;
    }

    /**
     * Display the billing dashboard.
     */
    public function index()
    {
        $company = $this->getCompanyOrFail();
        $subscription = $company->subscription;
        $plan = $company->currentPlan();

        $usage = [
            'used' => $company->invoicesUsedThisMonth(),
            'limit' => $company->invoiceLimit(),
            'remaining' => $company->invoicesRemainingThisMonth(),
            'percentage' => $company->usagePercentage(),
        ];

        return view('billing.index', compact('company', 'subscription', 'plan', 'usage'));
    }

    /**
     * Display available plans.
     */
    public function plans()
    {
        $company = current_company();
        $currentPlan = $company ? $company->currentPlan() : null;
        $plans = Plan::active()->ordered()->get();

        return view('billing.plans', compact('plans', 'currentPlan', 'company'));
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $company = $this->getCompanyOrFail();
        $plan = Plan::findOrFail($request->plan_id);

        if (!$this->stripeService->isConfigured()) {
            return back()->with('error', 'Το σύστημα πληρωμών δεν έχει ρυθμιστεί. Παρακαλούμε επικοινωνήστε με την υποστήριξη.');
        }

        // Check if company already has a subscription
        if ($company->hasActiveSubscription()) {
            return redirect()->route('billing.plans')
                ->with('error', 'Έχετε ήδη ενεργή συνδρομή. Χρησιμοποιήστε την αναβάθμιση ή την υποβάθμιση πακέτου.');
        }

        // Get or create Stripe customer
        $customerId = $company->stripeCustomerId();
        if (!$customerId) {
            $owner = $company->owner();
            $customer = $this->stripeService->createCustomer(
                $company,
                $owner->email,
                $company->name
            );
            $customerId = $customer->id;
        }

        // Create Checkout session
        $session = $this->stripeService->createCheckoutSession(
            $customerId,
            $plan,
            route('billing.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            route('billing.checkout.cancel'),
            [
                'company_id' => $company->id,
                'plan_id' => $plan->id,
            ]
        );

        return redirect($session->url);
    }

    /**
     * Handle successful checkout.
     */
    public function checkoutSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('billing.index')
                ->with('error', 'Μη έγκυρη συνεδρία πληρωμής.');
        }

        // The webhook will handle creating the subscription
        // Here we just show a success message

        return redirect()->route('billing.index')
            ->with('success', 'Η πληρωμή ολοκληρώθηκε. Η συνδρομή σας ενεργοποιείται — καλώς ήρθατε στο InvoiceAI!');
    }

    /**
     * Handle canceled checkout.
     */
    public function checkoutCancel()
    {
        return redirect()->route('billing.plans')
            ->with('info', 'Η διαδικασία πληρωμής ακυρώθηκε. Μπορείτε να δοκιμάσετε ξανά όποτε θέλετε.');
    }

    /**
     * Upgrade to a higher plan.
     */
    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $company = $this->getCompanyOrFail();
        $subscription = $company->subscription;
        $newPlan = Plan::findOrFail($request->plan_id);

        if (!$subscription || !$subscription->isActive()) {
            return back()->with('error', 'Δεν βρέθηκε ενεργή συνδρομή.');
        }

        $currentPlan = $subscription->plan;

        // Validate it's actually an upgrade
        if ($newPlan->price <= $currentPlan->price) {
            return back()->with('error', 'Για φθηνότερα πακέτα χρησιμοποιήστε την επιλογή υποβάθμισης.');
        }

        try {
            $this->stripeService->updateSubscription($subscription, $newPlan, true);

            return redirect()->route('billing.index')
                ->with('success', "Η αναβάθμιση σε {$newPlan->name} ολοκληρώθηκε. Εφαρμόστηκε αναλογική χρέωση.");
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Η αναβάθμιση απέτυχε. Δοκιμάστε ξανά ή επικοινωνήστε με την υποστήριξη.');
        }
    }

    /**
     * Downgrade to a lower plan (at period end).
     */
    public function downgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $company = $this->getCompanyOrFail();
        $subscription = $company->subscription;
        $newPlan = Plan::findOrFail($request->plan_id);

        if (!$subscription || !$subscription->isActive()) {
            return back()->with('error', 'Δεν βρέθηκε ενεργή συνδρομή.');
        }

        $currentPlan = $subscription->plan;

        // Validate it's actually a downgrade
        if ($newPlan->price >= $currentPlan->price) {
            return back()->with('error', 'Για ακριβότερα πακέτα χρησιμοποιήστε την επιλογή αναβάθμισης.');
        }

        try {
            // Schedule downgrade for end of billing period
            $this->stripeService->updateSubscription($subscription, $newPlan, false);

            return redirect()->route('billing.index')
                ->with('success', "Το πακέτο σας άλλαξε σε {$newPlan->name}. Η νέα τιμή θα χρεωθεί από την επόμενη περίοδο χρέωσης.");
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Η υποβάθμιση απέτυχε. Δοκιμάστε ξανά ή επικοινωνήστε με την υποστήριξη.');
        }
    }

    /**
     * Cancel subscription.
     */
    public function cancel()
    {
        $company = $this->getCompanyOrFail();
        $subscription = $company->subscription;

        if (!$subscription || !$subscription->isActive()) {
            return back()->with('error', 'Δεν βρέθηκε ενεργή συνδρομή.');
        }

        try {
            $this->stripeService->cancelSubscription($subscription);

            return redirect()->route('billing.index')
                ->with('success', 'Η συνδρομή σας θα ακυρωθεί στο τέλος της τρέχουσας περιόδου χρέωσης.');
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Η ακύρωση της συνδρομής απέτυχε. Δοκιμάστε ξανά ή επικοινωνήστε με την υποστήριξη.');
        }
    }

    /**
     * Resume a canceled subscription.
     */
    public function resume()
    {
        $company = $this->getCompanyOrFail();
        $subscription = $company->subscription;

        if (!$subscription || !$subscription->isCanceled()) {
            return back()->with('error', 'Δεν βρέθηκε ακυρωμένη συνδρομή προς επανενεργοποίηση.');
        }

        try {
            $this->stripeService->resumeSubscription($subscription);

            return redirect()->route('billing.index')
                ->with('success', 'Η συνδρομή σας επανενεργοποιήθηκε.');
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Η επανενεργοποίηση της συνδρομής απέτυχε. Δοκιμάστε ξανά ή επικοινωνήστε με την υποστήριξη.');
        }
    }

    /**
     * Redirect to Stripe Customer Portal.
     */
    public function portal()
    {
        $company = $this->getCompanyOrFail();
        $customerId = $company->stripeCustomerId();

        if (!$customerId) {
            return back()->with('error', 'Δεν βρέθηκε λογαριασμός χρέωσης.');
        }

        try {
            $url = $this->stripeService->getCustomerPortalUrl(
                $customerId,
                route('billing.index')
            );

            return redirect($url);
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Δεν ήταν δυνατή η πρόσβαση στην πύλη χρέωσης. Δοκιμάστε ξανά ή επικοινωνήστε με την υποστήριξη.');
        }
    }

    /**
     * Display billing invoices.
     */
    public function invoices()
    {
        $company = $this->getCompanyOrFail();
        $customerId = $company->stripeCustomerId();

        $stripeInvoices = [];
        if ($customerId && $this->stripeService->isConfigured()) {
            try {
                $stripeInvoices = $this->stripeService->getCustomerInvoices($customerId, 20);
            } catch (\Exception $e) {
                // Log error but don't fail
            }
        }

        return view('billing.invoices', compact('stripeInvoices'));
    }
}
