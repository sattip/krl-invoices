<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'vat_number',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);

                // Ensure uniqueness
                $count = static::where('slug', 'like', $company->slug . '%')->count();
                if ($count > 0) {
                    $company->slug = $company->slug . '-' . ($count + 1);
                }
            }
        });
    }

    /**
     * Get the users for the company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the invoices for the company.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the owner of the company.
     */
    public function owner()
    {
        return $this->users()->where('role', 'owner')->first();
    }

    /**
     * Get the active subscription for the company.
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    /**
     * Get all subscriptions for the company.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get invoice usage records for the company.
     */
    public function invoiceUsages(): HasMany
    {
        return $this->hasMany(InvoiceUsage::class);
    }

    /**
     * Check if the company has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        $subscription = $this->subscription;
        return $subscription && $subscription->isActive();
    }

    /**
     * Get the current plan for the company.
     */
    public function currentPlan(): ?Plan
    {
        $subscription = $this->subscription;
        return $subscription ? $subscription->plan : null;
    }

    /**
     * Get the Stripe customer ID for the company.
     */
    public function stripeCustomerId(): ?string
    {
        $subscription = $this->subscription;
        return $subscription ? $subscription->stripe_customer_id : null;
    }

    /**
     * Get the number of invoices used this month.
     */
    public function invoicesUsedThisMonth(): int
    {
        $usage = InvoiceUsage::getCurrentMonthUsage($this->id);
        return $usage->count;
    }

    /**
     * Get the invoice limit for the current month.
     */
    public function invoiceLimit(): int
    {
        $plan = $this->currentPlan();
        return $plan ? $plan->invoice_limit : 0;
    }

    /**
     * Get the number of invoices remaining this month.
     */
    public function invoicesRemainingThisMonth(): int
    {
        return max(0, $this->invoiceLimit() - $this->invoicesUsedThisMonth());
    }

    /**
     * Get the usage percentage for this month.
     */
    public function usagePercentage(): float
    {
        $limit = $this->invoiceLimit();
        if ($limit === 0) {
            return 100;
        }
        return min(100, ($this->invoicesUsedThisMonth() / $limit) * 100);
    }

    /**
     * Check if the company can create more invoices this month.
     */
    public function canCreateInvoice(): bool
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        return $this->invoicesRemainingThisMonth() > 0;
    }

    /**
     * Increment the invoice usage for the current month.
     */
    public function incrementInvoiceUsage(int $amount = 1): void
    {
        $usage = InvoiceUsage::getCurrentMonthUsage($this->id);
        $usage->incrementCount($amount);
    }

    /**
     * Check if usage is at or above the warning threshold (80%).
     */
    public function isNearingLimit(): bool
    {
        return $this->usagePercentage() >= 80;
    }

    /**
     * Check if the company is at the usage limit.
     */
    public function isAtLimit(): bool
    {
        return $this->invoicesRemainingThisMonth() === 0;
    }
}
