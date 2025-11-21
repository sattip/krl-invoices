<?php

namespace App\Services;

use App\Models\Company;
use App\Models\WhatsAppSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?string $apiKey = null;
    protected ?WhatsAppSettings $settings = null;
    protected string $baseUrl = 'https://api.wasenderapi.com/api/v1';

    public function __construct(?Company $company = null)
    {
        if ($company) {
            $this->settings = WhatsAppSettings::forCompany($company->id);
            $this->apiKey = $this->settings->api_key;
        }
    }

    public function isConfigured(): bool
    {
        return $this->settings && $this->settings->isConfigured();
    }

    public function sendMessage(string $to, string $message): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->baseUrl . '/messages/text', [
                    'to' => $this->formatPhoneNumber($to),
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', [
                    'to' => $to,
                    'company_id' => $this->settings->company_id,
                ]);

                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('WhatsApp message failed', [
                'to' => $to,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Failed to send message',
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp service error', [
                'error' => $e->getMessage(),
                'to' => $to,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendDocument(string $to, string $message, string $documentUrl, string $filename = 'document.pdf'): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp not configured'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->post($this->baseUrl . '/messages/document', [
                    'to' => $this->formatPhoneNumber($to),
                    'document' => $documentUrl,
                    'filename' => $filename,
                    'caption' => $message,
                ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function testConnection(): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'API key not configured'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->get($this->baseUrl . '/account');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Connection failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure it starts with country code (default to Greece +30 if not)
        if (strlen($phone) === 10 && str_starts_with($phone, '6')) {
            $phone = '30' . $phone; // Greek mobile
        } elseif (strlen($phone) === 10 && str_starts_with($phone, '2')) {
            $phone = '30' . $phone; // Greek landline
        }
        
        return $phone;
    }

    public function getSettings(): ?WhatsAppSettings
    {
        return $this->settings;
    }
}
