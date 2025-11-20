<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, BelongsToCompany;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'company_id',
        'invoice_number',
        'invoice_date',
        'issuer_name',
        'issuer_vat',
        'issuer_address',
        'customer_name',
        'customer_vat',
        'customer_address',
        'currency',
        'subtotal',
        'vat_total',
        'grand_total',
        'file_path',
        'original_filename',
        'raw_response',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    /**
     * Get the user that owns the invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the line items for the invoice.
     */
    public function lineItems(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    /**
     * Get the discounts for the invoice.
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(InvoiceDiscount::class);
    }

    /**
     * Get the other charges for the invoice.
     */
    public function otherCharges(): HasMany
    {
        return $this->hasMany(InvoiceOtherCharge::class);
    }

    /**
     * Calculate the sum of line item totals.
     */
    public function getCalculatedSubtotalAttribute(): float
    {
        return $this->lineItems->sum('line_total');
    }

    /**
     * Calculate total discounts.
     */
    public function getTotalDiscountsAttribute(): float
    {
        return $this->discounts->sum('amount');
    }

    /**
     * Calculate total other charges.
     */
    public function getTotalOtherChargesAttribute(): float
    {
        return $this->otherCharges->sum('amount');
    }

    /**
     * Check if calculated totals match stored totals.
     */
    public function getTotalsMatchAttribute(): bool
    {
        $calculated = $this->calculated_subtotal - $this->total_discounts + $this->total_other_charges + $this->vat_total;
        return abs($calculated - $this->grand_total) < 0.01;
    }
}
