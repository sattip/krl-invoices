<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceUsage extends Model
{
    use HasFactory;

    protected $table = 'invoice_usage';

    protected $fillable = [
        'company_id',
        'year',
        'month',
        'count',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'count' => 'integer',
    ];

    /**
     * Get the company that owns this usage record.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get or create usage record for the current month.
     */
    public static function getCurrentMonthUsage(int $companyId): self
    {
        return self::firstOrCreate(
            [
                'company_id' => $companyId,
                'year' => now()->year,
                'month' => now()->month,
            ],
            [
                'count' => 0,
            ]
        );
    }

    /**
     * Increment the usage count.
     */
    public function incrementCount(int $amount = 1): void
    {
        $this->increment('count', $amount);
    }

    /**
     * Get the period string (e.g., "November 2025").
     */
    public function getPeriodStringAttribute(): string
    {
        return date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }
}
