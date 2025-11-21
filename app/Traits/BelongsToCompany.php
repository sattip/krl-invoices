<?php

namespace App\Traits;

use App\Models\Company;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    /**
     * Boot the trait.
     */
    protected static function bootBelongsToCompany()
    {
        // Add global scope to filter by company
        static::addGlobalScope(new CompanyScope);

        // Automatically set company_id when creating
        static::creating(function ($model) {
            if (empty($model->company_id) && function_exists('current_company')) {
                $company = current_company();
                if ($company) {
                    $model->company_id = $company->id;
                }
            }
        });
    }

    /**
     * Get the company that owns the model.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
