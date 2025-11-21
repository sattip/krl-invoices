<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class StripeSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_account_id',
        'stripe_publishable_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
        'stripe_user_id',
        'stripe_access_token',
        'stripe_refresh_token',
        'livemode',
        'is_connected',
        'account_details',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'account_details' => 'array',
    ];

    /**
     * Hidden attributes for security.
     */
    protected $hidden = [
        'stripe_secret_key',
        'stripe_webhook_secret',
        'stripe_access_token',
        'stripe_refresh_token',
    ];

    /**
     * Get the singleton instance of stripe settings.
     */
    public static function getInstance(): self
    {
        return self::firstOrCreate([], [
            'is_connected' => false,
        ]);
    }

    /**
     * Encrypt and set the secret key.
     */
    public function setStripeSecretKeyAttribute(?string $value): void
    {
        $this->attributes['stripe_secret_key'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt and get the secret key.
     */
    public function getStripeSecretKeyAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encrypt and set the webhook secret.
     */
    public function setStripeWebhookSecretAttribute(?string $value): void
    {
        $this->attributes['stripe_webhook_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt and get the webhook secret.
     */
    public function getStripeWebhookSecretAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encrypt and set the access token.
     */
    public function setStripeAccessTokenAttribute(?string $value): void
    {
        $this->attributes['stripe_access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt and get the access token.
     */
    public function getStripeAccessTokenAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Encrypt and set the refresh token.
     */
    public function setStripeRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['stripe_refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt and get the refresh token.
     */
    public function getStripeRefreshTokenAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if connected via OAuth.
     */
    public function isOAuthConnected(): bool
    {
        return !empty($this->stripe_user_id) && !empty($this->stripe_access_token);
    }

    /**
     * Check if Stripe is properly configured.
     */
    public function isConfigured(): bool
    {
        return $this->is_connected
            && !empty($this->stripe_publishable_key)
            && !empty($this->stripe_secret_key);
    }

    /**
     * Get the Stripe client configuration.
     */
    public function getStripeConfig(): array
    {
        return [
            'api_key' => $this->stripe_secret_key,
        ];
    }
}
