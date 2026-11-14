<?php

namespace App\Notifications\WhatsApp;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceFailed extends Notification
{
    use Queueable;

    public function __construct(
        public string $filename,
        public string $error
    ) {}

    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "❌ *Αποτυχία επεξεργασίας τιμολογίου*\n\n".
               "Αρχείο: {$this->filename}\n".
               "Σφάλμα: {$this->error}\n\n".
               'Ελέγξτε το αρχείο και δοκιμάστε ξανά.';
    }
}
