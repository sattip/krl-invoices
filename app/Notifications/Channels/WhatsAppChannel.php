<?php

namespace App\Notifications\Channels;

use App\Models\WhatsAppSettings;
use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // Get the company from the notifiable
        $company = $this->getCompany($notifiable);

        if (!$company) {
            Log::warning('WhatsApp notification failed: No company found', [
                'notifiable' => get_class($notifiable),
                'notification' => get_class($notification),
            ]);
            return;
        }

        // Get WhatsApp settings
        $settings = WhatsAppSettings::forCompany($company->id);

        // Check if WhatsApp is enabled
        if (!$settings->is_enabled || !$settings->api_key) {
            return;
        }

        // Get the notification type and check if it's enabled
        $notificationType = $this->getNotificationType($notification);
        if ($notificationType && !($settings->notification_settings[$notificationType] ?? false)) {
            return;
        }

        // Check if recipient phone is configured
        $recipientPhone = $settings->phone_number;
        if (!$recipientPhone) {
            Log::warning('WhatsApp notification failed: No recipient phone configured', [
                'company_id' => $company->id,
            ]);
            return;
        }

        // Get the WhatsApp message from the notification
        if (!method_exists($notification, 'toWhatsApp')) {
            Log::warning('WhatsApp notification failed: toWhatsApp method not found', [
                'notification' => get_class($notification),
            ]);
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        if (empty($message)) {
            return;
        }

        // Send the message
        try {
            $service = new WhatsAppService($company);
            $result = $service->sendMessage($recipientPhone, $message);

            if (!$result['success']) {
                Log::error('WhatsApp notification failed to send', [
                    'company_id' => $company->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp notification exception', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the company from the notifiable.
     */
    protected function getCompany(object $notifiable): ?object
    {
        // If notifiable is a User, get their company
        if (method_exists($notifiable, 'company')) {
            return $notifiable->company;
        }

        // If notifiable has company_id attribute
        if (isset($notifiable->company_id)) {
            return \App\Models\Company::find($notifiable->company_id);
        }

        // If notifiable is a Company
        if ($notifiable instanceof \App\Models\Company) {
            return $notifiable;
        }

        return null;
    }

    /**
     * Get the notification type key from the notification class.
     */
    protected function getNotificationType(Notification $notification): ?string
    {
        // Map notification classes to setting keys
        $typeMap = [
            'InvoiceProcessed' => 'invoice_processed',
            'InvoiceFailed' => 'invoice_failed',
            'PaymentReceived' => 'payment_received',
            'PaymentFailed' => 'payment_failed',
            'UsageWarning' => 'usage_warning',
        ];

        $className = class_basename($notification);

        return $typeMap[$className] ?? null;
    }
}
