<?php

namespace App\Notifications\Billing;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public string $amount;
    public string $planName;
    public string $date;

    public function __construct(string $amount, string $planName, string $date)
    {
        $this->amount = $amount;
        $this->planName = $planName;
        $this->date = $date;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Failed - Action Required - InvoiceAI')
            ->view('emails.billing.payment-failed', [
                'amount' => $this->amount,
                'planName' => $this->planName,
                'date' => $this->date,
            ]);
    }
}
