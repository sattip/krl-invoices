<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\StripeSettings;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
     * Redirect to Stripe Connect OAuth.
     */
    public function connect()
    {
        $clientId = config('services.stripe.client_id');
        
        if (!$clientId) {
            return redirect()->route('admin.stripe.setup')
                ->with('error', 'Stripe Client ID is not configured. Please add STRIPE_CLIENT_ID to your .env file.');
        }

        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'scope' => 'read_write',
            'redirect_uri' => route('admin.stripe.callback'),
        ]);

        return redirect("https://connect.stripe.com/oauth/authorize?{$params}");
    }

    /**
     * Handle OAuth callback from Stripe.
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('admin.stripe.setup')
                ->with('error', $request->error_description ?? 'Authorization was cancelled or failed.');
        }

        $code = $request->code;
        if (!$code) {
            return redirect()->route('admin.stripe.setup')
                ->with('error', 'No authorization code received from Stripe.');
        }

        // Exchange code for access token
        $response = Http::asForm()->post('https://connect.stripe.com/oauth/token', [
            'client_secret' => config('services.stripe.secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            $error = $response->json('error_description') ?? 'Failed to connect to Stripe.';
            return redirect()->route('admin.stripe.setup')
                ->with('error', $error);
        }

        $data = $response->json();

        // Save credentials
        $settings = StripeSettings::getInstance();
        $settings->update([
            'stripe_publishable_key' => $data['stripe_publishable_key'] ?? null,
            'stripe_access_token' => $data['access_token'] ?? null,
            'stripe_refresh_token' => $data['refresh_token'] ?? null,
            'stripe_user_id' => $data['stripe_user_id'] ?? null,
            'livemode' => isset($data['livemode']) ? ($data['livemode'] ? 'live' : 'test') : null,
            'is_connected' => true,
        ]);

        // If we got an access token, use it as the secret key
        if (!empty($data['access_token'])) {
            $settings->update([
                'stripe_secret_key' => $data['access_token'],
            ]);
        }

        // Test connection and get account details
        $stripeService = new StripeService();
        $testResult = $stripeService->testConnection();

        if ($testResult['success']) {
            $settings->update([
                'account_details' => [
                    'account_id' => $testResult['account_id'] ?? $data['stripe_user_id'],
                    'business_name' => $testResult['business_name'] ?? null,
                ],
            ]);
        }

        return redirect()->route('admin.stripe.setup')
            ->with('success', 'Stripe connected successfully via OAuth!');
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
            'stripe_access_token' => null,
            'stripe_refresh_token' => null,
            'stripe_user_id' => null,
            'livemode' => null,
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
