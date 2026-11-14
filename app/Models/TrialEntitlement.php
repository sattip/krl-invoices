<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Local free trial: N days from registration and at most M invoices in total.
 *
 * - No Stripe customer, subscription or payment object is ever created for it.
 * - It never renews and never converts into a paid subscription by itself.
 * - It only applies while the company has no active paid subscription (paid wins).
 * - The counter is total, not monthly; deleting an invoice does not give a slot back.
 * - Slots are taken with one conditional UPDATE (reserveSlot) before extraction, so
 *   concurrent uploads from any entry point cannot exceed the limit.
 */
class TrialEntitlement extends Model
{
    public const CODE_EXPIRED = 'trial_expired';

    public const CODE_LIMIT = 'trial_limit_reached';

    /** Values are set only by startFor(); nothing is mass assignable from requests. */
    protected $guarded = ['*'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'invoice_limit' => 'integer',
        'invoices_used' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Grant the trial to a newly registered company. Duration and limit come only from
     * server config, never from the request.
     */
    public static function startFor(Company $company, ?CarbonInterface $now = null): self
    {
        $now = ($now ?? now())->copy();

        $trial = new self;
        $trial->forceFill([
            'company_id' => $company->id,
            'starts_at' => $now,
            'ends_at' => $now->copy()->addDays((int) config('subscription.trial.days')),
            'invoice_limit' => (int) config('subscription.trial.invoice_limit'),
            'invoices_used' => 0,
        ])->save();

        return $trial;
    }

    public function isExpired(): bool
    {
        // Ends exactly at ends_at: at that instant the trial is over.
        return ! now()->lessThan($this->ends_at);
    }

    public function remaining(): int
    {
        return max(0, $this->invoice_limit - $this->invoices_used);
    }

    public function isActive(): bool
    {
        return ! $this->isExpired() && $this->remaining() > 0;
    }

    /**
     * Why the trial cannot take $count more invoices right now, or null if it can.
     *
     * @return array{code: string, message: string}|null
     */
    public function denial(int $count = 1): ?array
    {
        if ($this->isExpired()) {
            return [
                'code' => self::CODE_EXPIRED,
                'message' => 'Η δωρεάν δοκιμή σας έληξε στις '.date_el($this->ends_at, true).'. Δεν έγινε καμία χρέωση. '
                    .'Για να συνεχίσετε, επιλέξτε πακέτο συνδρομής.',
            ];
        }

        if ($this->remaining() <= 0) {
            return [
                'code' => self::CODE_LIMIT,
                'message' => 'Χρησιμοποιήσατε και τα '.number_el($this->invoice_limit).' τιμολόγια της δωρεάν δοκιμής. '
                    .'Δεν έγινε καμία χρέωση. Για να συνεχίσετε, επιλέξτε πακέτο συνδρομής.',
            ];
        }

        if ($count > $this->remaining()) {
            return [
                'code' => self::CODE_LIMIT,
                'message' => 'Στη δωρεάν δοκιμή απομένουν '.number_el($this->remaining()).' από '.number_el($this->invoice_limit)
                    .' τιμολόγια. Επιλέξτε έως '.number_el($this->remaining()).' αρχεία ή ένα πακέτο συνδρομής.',
            ];
        }

        return null;
    }

    /**
     * Atomically take one slot. Returns false when the trial is expired or full,
     * evaluated by the database at the moment of the update (race-safe).
     */
    public function reserveSlot(): bool
    {
        $taken = static::query()
            ->whereKey($this->getKey())
            ->where('ends_at', '>', now())
            ->whereColumn('invoices_used', '<', 'invoice_limit')
            ->increment('invoices_used') === 1;

        $this->refresh();

        return $taken;
    }

    /** Give back a slot whose extraction failed (nothing was stored). */
    public function releaseSlot(): void
    {
        static::query()
            ->whereKey($this->getKey())
            ->where('invoices_used', '>', 0)
            ->decrement('invoices_used');

        $this->refresh();
    }

    /**
     * Usage summary for API responses.
     *
     * @return array{used: int, limit: int, remaining: int, ends_at: string}
     */
    public function usage(): array
    {
        return [
            'used' => $this->invoices_used,
            'limit' => $this->invoice_limit,
            'remaining' => $this->isExpired() ? 0 : $this->remaining(),
            'ends_at' => $this->ends_at->toIso8601String(),
        ];
    }
}
