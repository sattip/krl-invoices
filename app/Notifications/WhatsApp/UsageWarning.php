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
        return "📊 *Usage Warning*\n\n" .
               "You have used {$this->percentage}% of your monthly invoice limit.\n\n" .
               "Used: {$this->used} / {$this->limit} invoices\n\n" .
               "Consider upgrading your plan for more capacity.";
    }
}
