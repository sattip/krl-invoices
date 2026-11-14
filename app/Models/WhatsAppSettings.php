<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class WhatsAppSettings extends Model
{
    use HasFactory;

    // The live migration creates `whatsapp_settings`; Eloquent's default would be `whats_app_settings`.
    protected $table = 'whatsapp_settings';

    protected $fillable = [
        'company_id',
        'api_key',
        'webhook_secret',
        'session_id',
        'phone_number',
        'phone_display_name',
        'is_enabled',
        'is_connected',
        'connection_status',
        'last_connected_at',
        'notification_settings',
        'message_templates',
        'account_details',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_connected' => 'boolean',
        'notification_settings' => 'array',
        'message_templates' => 'array',
        'account_details' => 'array',
        'last_connected_at' => 'datetime',
    ];

    protected $hidden = ['api_key', 'webhook_secret'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Encrypt API key
    public function setApiKeyAttribute(?string $value): void
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiKeyAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    // Encrypt webhook secret
    public function setWebhookSecretAttribute(?string $value): void
    {
        $this->attributes['webhook_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getWebhookSecretAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function defaultNotificationSettings(): array
    {
        return [
            'invoice_processed' => true,
            'invoice_failed' => true,
            'payment_received' => true,
            'payment_failed' => true,
            'usage_warning' => true,
        ];
    }

    public static function forCompany(int $companyId): self
    {
        return self::firstOrCreate(
            ['company_id' => $companyId],
            [
                'is_enabled' => false,
                'notification_settings' => self::defaultNotificationSettings(),
            ]
        );
    }

    public function isConfigured(): bool
    {
        return $this->is_enabled && !empty($this->api_key);
    }

    public function shouldNotify(string $type): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }
        
        return $this->notification_settings[$type] ?? false;
    }
}
