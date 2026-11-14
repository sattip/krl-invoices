<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Notifications\WhatsApp\PaymentFailed;
use App\Notifications\WhatsApp\PaymentReceived;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = $this->stripeService->verifyWebhookSignature($payload, $signature);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type, 'id' => $event->id]);

        try {
            match ($event->type) {
                'customer.subscription.created' => $this->handleSubscriptionCreated($event->data->object),
                'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
                'invoice.payment_succeeded' => $this->handlePaymentSucceeded($event->data->object),
                'invoice.payment_failed' => $this->handlePaymentFailed($event->data->object),
                default => Log::info('Unhandled webhook event', ['type' => $event->type]),
            };
        } catch (\Exception $e) {
            Log::error('Stripe webhook handler error', [
                'type' => $event->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response('OK', 200);
    }

    protected function handleSubscriptionCreated($stripeSubscription)
    {
        $companyId = $stripeSubscription->metadata->company_id ?? null;
        $planId = $stripeSubscription->metadata->plan_id ?? null;

        if (! $companyId) {
            $company = $this->findCompanyByCustomerId($stripeSubscription->customer);
            $companyId = $company?->id;
        }

        if (! $companyId) {
            Log::warning('Subscription created but company not found', [
                'stripe_subscription_id' => $stripeSubscription->id,
                'customer_id' => $stripeSubscription->customer,
            ]);

            return;
        }

        if (! $planId) {
            $priceId = $stripeSubscription->items->data[0]->price->id ?? null;
            if ($priceId) {
                $plan = Plan::where('stripe_price_id', $priceId)->first();
                $planId = $plan?->id;
            }
        }

        if (! $planId) {
            Log::warning('Subscription created but plan not found', ['stripe_subscription_id' => $stripeSubscription->id]);

            return;
        }

        $existing = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        if ($existing) {
            Log::info('Subscription already exists, updating', ['subscription_id' => $existing->id]);
            $this->handleSubscriptionUpdated($stripeSubscription);

            return;
        }

        Subscription::create([
            'company_id' => $companyId,
            'plan_id' => $planId,
            'stripe_subscription_id' => $stripeSubscription->id,
            'stripe_customer_id' => $stripeSubscription->customer,
            'status' => $stripeSubscription->status,
            'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
        ]);

        Log::info('Subscription created', [
            'company_id' => $companyId,
            'plan_id' => $planId,
            'stripe_subscription_id' => $stripeSubscription->id,
        ]);
    }

    protected function handleSubscriptionUpdated($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if (! $subscription) {
            Log::warning('Subscription not found for update', ['stripe_subscription_id' => $stripeSubscription->id]);
            $this->handleSubscriptionCreated($stripeSubscription);

            return;
        }

        $priceId = $stripeSubscription->items->data[0]->price->id ?? null;
        if ($priceId) {
            $plan = Plan::where('stripe_price_id', $priceId)->first();
            if ($plan && $plan->id !== $subscription->plan_id) {
                $subscription->plan_id = $plan->id;
            }
        }

        $subscription->update([
            'status' => $stripeSubscription->status,
            'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            'canceled_at' => $stripeSubscription->canceled_at
                ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->canceled_at)
                : null,
        ]);

        Log::info('Subscription updated', ['subscription_id' => $subscription->id, 'status' => $stripeSubscription->status]);
    }

    protected function handleSubscriptionDeleted($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if (! $subscription) {
            Log::warning('Subscription not found for deletion', ['stripe_subscription_id' => $stripeSubscription->id]);

            return;
        }

        $subscription->update([
            'status' => Subscription::STATUS_CANCELED,
            'canceled_at' => now(),
        ]);

        Log::info('Subscription canceled', ['subscription_id' => $subscription->id]);
    }

    protected function handlePaymentSucceeded($invoice)
    {
        if ($invoice->subscription) {
            $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

            if ($subscription) {
                if ($subscription->status !== Subscription::STATUS_ACTIVE) {
                    $subscription->update(['status' => Subscription::STATUS_ACTIVE]);
                }

                $amount = money_el($invoice->amount_paid / 100);
                $description = $invoice->lines->data[0]->description ?? 'Συνδρομή InvoiceAI';

                Log::info('Payment succeeded', [
                    'subscription_id' => $subscription->id,
                    'amount' => $invoice->amount_paid / 100,
                ]);

                // Send WhatsApp notification
                $company = $subscription->company;
                if ($company) {
                    $owner = $company->users()->where('role', 'owner')->first();
                    if ($owner) {
                        try {
                            $owner->notify(new PaymentReceived($amount, $description));
                        } catch (\Exception $e) {
                            Log::warning('WhatsApp payment received notification failed', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }
        }
    }

    protected function handlePaymentFailed($invoice)
    {
        if ($invoice->subscription) {
            $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

            if ($subscription) {
                $subscription->update(['status' => Subscription::STATUS_PAST_DUE]);

                $amount = money_el($invoice->amount_due / 100);
                $reason = $invoice->last_finalization_error->message ?? 'Ο τρόπος πληρωμής απορρίφθηκε';

                Log::warning('Payment failed', [
                    'subscription_id' => $subscription->id,
                    'company_id' => $subscription->company_id,
                ]);

                // Send WhatsApp notification
                $company = $subscription->company;
                if ($company) {
                    $owner = $company->users()->where('role', 'owner')->first();
                    if ($owner) {
                        try {
                            $owner->notify(new PaymentFailed($amount, $reason));
                        } catch (\Exception $e) {
                            Log::warning('WhatsApp payment failed notification failed', ['error' => $e->getMessage()]);
                        }
                    }
                }
            }
        }
    }

    protected function findCompanyByCustomerId(string $customerId): ?Company
    {
        $subscription = Subscription::where('stripe_customer_id', $customerId)->first();

        return $subscription?->company;
    }
}
