<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $company = function_exists('current_company') ? current_company() : null;

        if ($company) {
            $builder->where($model->getTable() . '.company_id', $company->id);

            return;
        }

        if ($this->maySeeAllCompanies()) {
            return;
        }

        // Fail closed: an authenticated non-super-admin without a company, or an
        // unauthenticated HTTP request, must never see other companies' rows.
        $builder->whereRaw('1 = 0');
    }

    /**
     * Unscoped access is kept only where it was intended: super admins without a
     * company context, and trusted CLI/queue/scheduler code (no HTTP user).
     */
    protected function maySeeAllCompanies(): bool
    {
        $user = auth()->user();

        if ($user) {
            return (bool) ($user->is_super_admin ?? false);
        }

        return app()->runningInConsole();
    }
}
