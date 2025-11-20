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
            abort(403, 'You must be assigned to a company to access billing. Please contact an administrator.');
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
            return back()->with('error', 'Payment system is not configured. Please contact support.');
        }

        // Check if company already has a subscription
        if ($company->hasActiveSubscription()) {
            return redirect()->route('billing.plans')
                ->with('error', 'You already have an active subscription. Please upgrade or downgrade instead.');
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
                ->with('error', 'Invalid checkout session.');
        }

        // The webhook will handle creating the subscription
        // Here we just show a success message

        return redirect()->route('billing.index')
            ->with('success', 'Subscription activated successfully! Welcome aboard.');
    }

    /**
     * Handle canceled checkout.
     */
    public function checkoutCancel()
    {
        return redirect()->route('billing.plans')
            ->with('info', 'Checkout was canceled. You can try again when ready.');
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
            return back()->with('error', 'No active subscription found.');
        }

        $currentPlan = $subscription->plan;

        // Validate it's actually an upgrade
        if ($newPlan->price <= $currentPlan->price) {
            return back()->with('error', 'Please use the downgrade option for lower-priced plans.');
        }

        try {
            $this->stripeService->updateSubscription($subscription, $newPlan, true);

            return redirect()->route('billing.index')
                ->with('success', "Successfully upgraded to {$newPlan->name}! Prorated charges have been applied.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upgrade: ' . $e->getMessage());
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
            return back()->with('error', 'No active subscription found.');
        }

        $currentPlan = $subscription->plan;

        // Validate it's actually a downgrade
        if ($newPlan->price >= $currentPlan->price) {
            return back()->with('error', 'Please use the upgrade option for higher-priced plans.');
        }

        try {
            // Schedule downgrade for end of billing period
            $this->stripeService->updateSubscription($subscription, $newPlan, false);

            return redirect()->route('billing.index')
                ->with('success', "Your plan will change to {$newPlan->name} at the end of your current billing period.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to downgrade: ' . $e->getMessage());
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
            return back()->with('error', 'No active subscription found.');
        }

        try {
            $this->stripeService->cancelSubscription($subscription);

            return redirect()->route('billing.index')
                ->with('success', 'Your subscription will be canceled at the end of the current billing period.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
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
            return back()->with('error', 'No canceled subscription found to resume.');
        }

        try {
            $this->stripeService->resumeSubscription($subscription);

            return redirect()->route('billing.index')
                ->with('success', 'Your subscription has been resumed.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to resume subscription: ' . $e->getMessage());
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
            return back()->with('error', 'No billing account found.');
        }

        try {
            $url = $this->stripeService->getCustomerPortalUrl(
                $customerId,
                route('billing.index')
            );

            return redirect($url);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to access billing portal: ' . $e->getMessage());
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
