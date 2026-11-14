<?php

namespace App\Notifications\WhatsApp;

use App\Models\Invoice;
use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceProcessed extends Notification
{
    use Queueable;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $invoiceNumber = $this->invoice->invoice_number ?? 'N/A';
        $vendor = $this->invoice->issuer_name ?? 'Unknown';
        $total = number_format((float) ($this->invoice->grand_total ?? 0), 2) . ' ' . ($this->invoice->currency ?: '€');
        
        return "✅ *Invoice Processed*\n\n" .
               "Invoice: #{$invoiceNumber}\n" .
               "Vendor: {$vendor}\n" .
               "Total: {$total}\n\n" .
               "View details in InvoiceAI.";
    }
}
