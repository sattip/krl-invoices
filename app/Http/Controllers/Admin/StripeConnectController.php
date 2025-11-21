<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\StripeSettings;
use App\Services\StripeService;
use Illuminate\Http\Request;

class StripeConnectController extends Controller
{
    /**
     * Display the Stripe setup page.
     */
    public function setup()
    {
        $settings = StripeSettings::getInstance();
        $stripeService = new StripeService();

        $connectionStatus = null;
        if ($settings->isConfigured()) {
            $connectionStatus = $stripeService->testConnection();
        }

        return view('admin.stripe.setup', compact('settings', 'connectionStatus'));
    }

    /**
     * Save Stripe credentials.
     */
    public function saveCredentials(Request $request)
    {
        $validated = $request->validate([
            'stripe_publishable_key' => 'required|string|starts_with:pk_',
            'stripe_secret_key' => 'required|string|starts_with:sk_',
            'stripe_webhook_secret' => 'nullable|string|starts_with:whsec_',
        ]);

        $settings = StripeSettings::getInstance();
        $settings->update([
            'stripe_publishable_key' => $validated['stripe_publishable_key'],
            'stripe_secret_key' => $validated['stripe_secret_key'],
            'stripe_webhook_secret' => $validated['stripe_webhook_secret'] ?? null,
            'is_connected' => true,
        ]);

        // Test connection
        $stripeService = new StripeService();
        $testResult = $stripeService->testConnection();

        if (!$testResult['success']) {
            $settings->update(['is_connected' => false]);
            return back()->with('error', 'Failed to connect to Stripe: ' . $testResult['message']);
        }

        // Update account details
        $settings->update([
            'account_details' => [
                'account_id' => $testResult['account_id'] ?? null,
                'business_name' => $testResult['business_name'] ?? null,
            ],
        ]);

        return back()->with('success', 'Stripe connected successfully!');
    }

    /**
     * Test the Stripe connection.
     */
    public function testConnection()
    {
        $stripeService = new StripeService();
        $result = $stripeService->testConnection();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Disconnect Stripe.
     */
    public function disconnect()
    {
        $settings = StripeSettings::getInstance();
        $settings->update([
            'stripe_publishable_key' => null,
            'stripe_secret_key' => null,
            'stripe_webhook_secret' => null,
            'is_connected' => false,
            'account_details' => null,
        ]);

        return back()->with('success', 'Stripe disconnected successfully.');
    }

    /**
     * Display plans management page.
     */
    public function plans()
    {
        $plans = Plan::ordered()->get();
        return view('admin.stripe.plans', compact('plans'));
    }

    /**
     * Update plan Stripe price IDs.
     */
    public function updatePlans(Request $request)
    {
        $validated = $request->validate([
            'plans' => 'required|array',
            'plans.*.id' => 'required|exists:plans,id',
            'plans.*.stripe_price_id' => 'nullable|string|starts_with:price_',
            'plans.*.price' => 'required|numeric|min:0',
            'plans.*.invoice_limit' => 'required|integer|min:1',
            'plans.*.is_active' => 'boolean',
        ]);

        foreach ($validated['plans'] as $planData) {
            Plan::where('id', $planData['id'])->update([
                'stripe_price_id' => $planData['stripe_price_id'] ?? null,
                'price' => $planData['price'],
                'invoice_limit' => $planData['invoice_limit'],
                'is_active' => $planData['is_active'] ?? false,
            ]);
        }

        return back()->with('success', 'Plans updated successfully.');
    }

    /**
     * Sync plans with Stripe (create products/prices).
     */
    public function syncPlans()
    {
        $stripeService = new StripeService();

        if (!$stripeService->isConfigured()) {
            return back()->with('error', 'Stripe is not configured. Please add your credentials first.');
        }

        $plans = Plan::whereNull('stripe_price_id')->orWhere('stripe_price_id', '')->get();
        $synced = 0;

        foreach ($plans as $plan) {
            try {
                $priceId = $stripeService->createProductAndPrice($plan);
                $plan->update(['stripe_price_id' => $priceId]);
                $synced++;
            } catch (\Exception $e) {
                return back()->with('error', "Failed to sync plan '{$plan->name}': " . $e->getMessage());
            }
        }

        if ($synced === 0) {
            return back()->with('info', 'All plans are already synced with Stripe.');
        }

        return back()->with('success', "{$synced} plan(s) synced with Stripe successfully.");
    }
}
