<?php

namespace App\Notifications\WhatsApp;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentFailed extends Notification
{
    use Queueable;

    public function __construct(
        public string $amount,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "⚠️ *Payment Failed*\n\n" .
               "Amount: {$this->amount}\n" .
               "Reason: {$this->reason}\n\n" .
               "Please update your payment method in InvoiceAI.";
    }
}
