<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\StripeSettings;
use App\Models\Subscription;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    protected ?StripeClient $client = null;
    protected StripeSettings $settings;

    public function __construct()
    {
        $this->settings = StripeSettings::getInstance();

        if ($this->settings->isConfigured()) {
            $this->client = new StripeClient($this->settings->stripe_secret_key);
        }
    }

    /**
     * Check if Stripe is configured and ready to use.
     */
    public function isConfigured(): bool
    {
        return $this->client !== null;
    }

    /**
     * Get the Stripe client instance.
     */
    public function getClient(): ?StripeClient
    {
        return $this->client;
    }

    /**
     * Get the publishable key.
     */
    public function getPublishableKey(): ?string
    {
        return $this->settings->stripe_publishable_key;
    }

    /**
     * Create a new Stripe customer for a company.
     */
    public function createCustomer(Company $company, string $email, string $name): \Stripe\Customer
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        return $this->client->customers->create([
            'email' => $email,
            'name' => $name,
            'metadata' => [
                'company_id' => $company->id,
                'company_name' => $company->name,
            ],
        ]);
    }

    /**
     * Create a Stripe Checkout session for subscription.
     */
    public function createCheckoutSession(
        string $customerId,
        Plan $plan,
        string $successUrl,
        string $cancelUrl,
        array $metadata = []
    ): \Stripe\Checkout\Session {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        return $this->client->checkout->sessions->create([
            'customer' => $customerId,
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price' => $plan->stripe_price_id,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
            'subscription_data' => [
                'metadata' => $metadata,
            ],
        ]);
    }

    /**
     * Create a subscription directly (for internal use).
     */
    public function createSubscription(
        Company $company,
        Plan $plan,
        string $customerId
    ): Subscription {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        $stripeSubscription = $this->client->subscriptions->create([
            'customer' => $customerId,
            'items' => [
                ['price' => $plan->stripe_price_id],
            ],
            'metadata' => [
                'company_id' => $company->id,
            ],
        ]);

        return Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'stripe_subscription_id' => $stripeSubscription->id,
            'stripe_customer_id' => $customerId,
            'status' => $stripeSubscription->status,
            'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
        ]);
    }

    /**
     * Update a subscription to a new plan.
     */
    public function updateSubscription(
        Subscription $subscription,
        Plan $newPlan,
        bool $immediate = true
    ): Subscription {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        $stripeSubscription = $this->client->subscriptions->retrieve($subscription->stripe_subscription_id);

        $params = [
            'items' => [
                [
                    'id' => $stripeSubscription->items->data[0]->id,
                    'price' => $newPlan->stripe_price_id,
                ],
            ],
            'proration_behavior' => $immediate ? 'create_prorations' : 'none',
        ];

        if (!$immediate) {
            $params['billing_cycle_anchor'] = 'unchanged';
        }

        $updatedSubscription = $this->client->subscriptions->update(
            $subscription->stripe_subscription_id,
            $params
        );

        $subscription->update([
            'plan_id' => $newPlan->id,
            'status' => $updatedSubscription->status,
            'current_period_start' => \Carbon\Carbon::createFromTimestamp($updatedSubscription->current_period_start),
            'current_period_end' => \Carbon\Carbon::createFromTimestamp($updatedSubscription->current_period_end),
        ]);

        return $subscription->fresh();
    }

    /**
     * Cancel a subscription at period end.
     */
    public function cancelSubscription(Subscription $subscription): Subscription
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        $this->client->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        $subscription->update([
            'canceled_at' => now(),
        ]);

        return $subscription->fresh();
    }

    /**
     * Cancel a subscription immediately.
     */
    public function cancelSubscriptionImmediately(Subscription $subscription): void
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        $this->client->subscriptions->cancel($subscription->stripe_subscription_id);

        $subscription->update([
            'status' => Subscription::STATUS_CANCELED,
            'canceled_at' => now(),
        ]);
    }

    /**
     * Resume a canceled subscription.
     */
    public function resumeSubscription(Subscription $subscription): Subscription
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        $this->client->subscriptions->update($subscription->stripe_subscription_id, [
            'cancel_at_period_end' => false,
        ]);

        $subscription->update([
            'canceled_at' => null,
        ]);

        return $subscription->fresh();
    }

    /**
     * Get the customer portal URL.
     */
    public function getCustomerPortalUrl(string $customerId, string $returnUrl): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        $session = $this->client->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);

        return $session->url;
    }

    /**
     * Sync subscription status from Stripe.
     */
    public function syncSubscriptionStatus(Subscription $subscription): Subscription
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        $stripeSubscription = $this->client->subscriptions->retrieve($subscription->stripe_subscription_id);

        $subscription->update([
            'status' => $stripeSubscription->status,
            'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            'canceled_at' => $stripeSubscription->canceled_at
                ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->canceled_at)
                : null,
        ]);

        return $subscription->fresh();
    }

    /**
     * Get Stripe subscription by ID.
     */
    public function getStripeSubscription(string $subscriptionId): \Stripe\Subscription
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        return $this->client->subscriptions->retrieve($subscriptionId);
    }

    /**
     * Get customer invoices from Stripe.
     */
    public function getCustomerInvoices(string $customerId, int $limit = 10): \Stripe\Collection
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        return $this->client->invoices->all([
            'customer' => $customerId,
            'limit' => $limit,
        ]);
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): \Stripe\Event
    {
        if (!$this->settings->stripe_webhook_secret) {
            throw new \Exception('Webhook secret is not configured');
        }

        return \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            $this->settings->stripe_webhook_secret
        );
    }

    /**
     * Test the Stripe connection.
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Stripe is not configured',
            ];
        }

        try {
            $account = $this->client->accounts->retrieve();
            return [
                'success' => true,
                'message' => 'Connected to Stripe successfully',
                'account_id' => $account->id,
                'business_name' => $account->business_profile->name ?? $account->email,
            ];
        } catch (ApiErrorException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a product and price in Stripe for a plan.
     */
    public function createProductAndPrice(Plan $plan): string
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Stripe is not configured');
        }

        // Create product
        $product = $this->client->products->create([
            'name' => $plan->name,
            'description' => $plan->description ?? "Invoice AI {$plan->name} Plan",
        ]);

        // Create price
        $price = $this->client->prices->create([
            'product' => $product->id,
            'unit_amount' => (int) ($plan->price * 100), // Convert to cents
            'currency' => 'usd',
            'recurring' => [
                'interval' => 'month',
            ],
        ]);

        return $price->id;
    }
}
