<?php

use App\Models\Company;

if (!function_exists('current_company')) {
    /**
     * Get the current company from the app container.
     */
    function current_company(): ?Company
    {
        if (app()->bound('current_company')) {
            return app('current_company');
        }

        // Fallback to auth user's company
        if (auth()->check() && auth()->user()->company) {
            return auth()->user()->company;
        }

        return null;
    }
}
