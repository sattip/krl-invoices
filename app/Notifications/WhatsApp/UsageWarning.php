<?php

namespace App\Notifications\WhatsApp;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UsageWarning extends Notification
{
    use Queueable;

    public function __construct(
        public int $used,
        public int $limit,
        public int $percentage
    ) {}

    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): string
    {
        return "📊 *Προειδοποίηση χρήσης*\n\n".
               "Έχετε χρησιμοποιήσει το {$this->percentage}% του μηνιαίου ορίου τιμολογίων.\n\n".
               "Χρήση: {$this->used} / {$this->limit} τιμολόγια\n\n".
               'Αν χρειάζεστε περισσότερα, μπορείτε να αναβαθμίσετε το πακέτο σας.';
    }
}
