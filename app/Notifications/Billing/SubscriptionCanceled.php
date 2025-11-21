<?php

namespace App\Notifications\Billing;

use App\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCanceled extends Notification implements ShouldQueue
{
    use Queueable;

    public Plan $plan;
    public string $endDate;

    public function __construct(Plan $plan, string $endDate)
    {
        $this->plan = $plan;
        $this->endDate = $endDate;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Subscription Canceled - InvoiceAI')
            ->view('emails.billing.subscription-canceled', [
                'plan' => $this->plan,
                'endDate' => $this->endDate,
            ]);
    }
}
