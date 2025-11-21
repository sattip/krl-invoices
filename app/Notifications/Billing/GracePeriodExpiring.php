<?php

namespace App\Notifications\Billing;

use App\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GracePeriodExpiring extends Notification implements ShouldQueue
{
    use Queueable;

    public Plan $plan;
    public string $expirationDate;
    public int $daysRemaining;

    public function __construct(Plan $plan, string $expirationDate, int $daysRemaining)
    {
        $this->plan = $plan;
        $this->expirationDate = $expirationDate;
        $this->daysRemaining = $daysRemaining;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Grace Period Expiring Soon - InvoiceAI')
            ->view('emails.billing.grace-period-expiring', [
                'plan' => $this->plan,
                'expirationDate' => $this->expirationDate,
                'daysRemaining' => $this->daysRemaining,
            ]);
    }
}
