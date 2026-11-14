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
                ->with('error', 'Το Stripe Client ID δεν έχει ρυθμιστεί. Προσθέστε το STRIPE_CLIENT_ID στο αρχείο .env.');
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
                ->with('error', $request->error_description ?? 'Η εξουσιοδότηση ακυρώθηκε ή απέτυχε.');
        }

        $code = $request->code;
        if (!$code) {
            return redirect()->route('admin.stripe.setup')
                ->with('error', 'Δεν ελήφθη κωδικός εξουσιοδότησης από το Stripe.');
        }

        // Exchange code for access token
        $response = Http::asForm()->post('https://connect.stripe.com/oauth/token', [
            'client_secret' => config('services.stripe.secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            $error = $response->json('error_description') ?? 'Η σύνδεση με το Stripe απέτυχε.';
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
            ->with('success', 'Η σύνδεση με το Stripe μέσω OAuth ολοκληρώθηκε επιτυχώς.');
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
            return back()->with('error', 'Η σύνδεση με το Stripe απέτυχε: ' . $testResult['message']);
        }

        // Update account details
        $settings->update([
            'account_details' => [
                'account_id' => $testResult['account_id'] ?? null,
                'business_name' => $testResult['business_name'] ?? null,
            ],
        ]);

        return back()->with('success', 'Η σύνδεση με το Stripe ολοκληρώθηκε επιτυχώς.');
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

        return back()->with('success', 'Η σύνδεση με το Stripe αποσυνδέθηκε επιτυχώς.');
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

        return back()->with('success', 'Τα πακέτα ενημερώθηκαν επιτυχώς.');
    }

    /**
     * Sync plans with Stripe (create products/prices).
     */
    public function syncPlans()
    {
        $stripeService = new StripeService();

        if (!$stripeService->isConfigured()) {
            return back()->with('error', 'Το Stripe δεν έχει ρυθμιστεί. Προσθέστε πρώτα τα διαπιστευτήριά σας.');
        }

        $plans = Plan::whereNull('stripe_price_id')->orWhere('stripe_price_id', '')->get();
        $synced = 0;

        foreach ($plans as $plan) {
            try {
                $priceId = $stripeService->createProductAndPrice($plan);
                $plan->update(['stripe_price_id' => $priceId]);
                $synced++;
            } catch (\Exception $e) {
                return back()->with('error', "Ο συγχρονισμός του πακέτου «{$plan->name}» απέτυχε: " . $e->getMessage());
            }
        }

        if ($synced === 0) {
            return back()->with('info', 'Όλα τα πακέτα είναι ήδη συγχρονισμένα με το Stripe.');
        }

        return back()->with('success', "Συγχρονίστηκαν επιτυχώς με το Stripe {$synced} πακέτα.");
    }
}
