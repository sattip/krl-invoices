<?php

namespace App\Notifications\Billing;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSucceeded extends Notification implements ShouldQueue
{
    use Queueable;

    public string $amount;
    public string $planName;
    public string $date;
    public string $invoiceNumber;

    public function __construct(string $amount, string $planName, string $date, string $invoiceNumber)
    {
        $this->amount = $amount;
        $this->planName = $planName;
        $this->date = $date;
        $this->invoiceNumber = $invoiceNumber;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Received - InvoiceAI')
            ->view('emails.billing.payment-succeeded', [
                'amount' => $this->amount,
                'planName' => $this->planName,
                'date' => $this->date,
                'invoiceNumber' => $this->invoiceNumber,
            ]);
    }
}
