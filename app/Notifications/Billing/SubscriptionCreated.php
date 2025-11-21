<?php

namespace App\Notifications\Billing;

use App\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public Plan $plan;

    public function __construct(Plan $plan)
    {
        $this->plan = $plan;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to InvoiceAI - Subscription Activated')
            ->view('emails.billing.subscription-created', [
                'plan' => $this->plan,
            ]);
    }
}
