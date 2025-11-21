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
        $vendor = $this->invoice->vendor_name ?? 'Unknown';
        $total = number_format($this->invoice->total_amount ?? 0, 2) . ' €';
        
        return "✅ *Invoice Processed*\n\n" .
               "Invoice: #{$invoiceNumber}\n" .
               "Vendor: {$vendor}\n" .
               "Total: {$total}\n\n" .
               "View details in InvoiceAI.";
    }
}
