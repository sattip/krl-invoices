<?php

namespace App\Notifications\WhatsApp;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(
        public string $amount,
        public string $description
    ) {}

    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "💰 *Payment Received*\n\n" .
               "Amount: {$this->amount}\n" .
               "Description: {$this->description}\n\n" .
               "Thank you for your payment!";
    }
}
