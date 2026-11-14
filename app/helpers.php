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

if (!function_exists('money_el')) {
    /**
     * Format an amount in Greek notation, e.g. 1.234,50 €.
     * Non-EUR currencies keep their ISO code after the amount.
     */
    function money_el($amount, ?string $currency = 'EUR', int $decimals = 2): string
    {
        $formatted = number_format((float) $amount, $decimals, ',', '.');
        $currency = strtoupper((string) ($currency ?: 'EUR'));

        return $currency === 'EUR' ? $formatted . ' €' : $formatted . ' ' . $currency;
    }
}

if (!function_exists('number_el')) {
    /**
     * Format a number in Greek notation (thousands ".", decimals ",").
     */
    function number_el($number, int $decimals = 0): string
    {
        return number_format((float) $number, $decimals, ',', '.');
    }
}

if (!function_exists('date_el')) {
    /**
     * Format a date as dd/mm/yyyy (optionally with time), Europe/Athens.
     */
    function date_el($date, bool $withTime = false): string
    {
        if (empty($date)) {
            return '—';
        }

        $carbon = $date instanceof \DateTimeInterface
            ? \Illuminate\Support\Carbon::instance($date)
            : \Illuminate\Support\Carbon::parse($date);

        $carbon = $carbon->copy()->setTimezone(config('app.display_timezone', 'Europe/Athens'));

        return $carbon->format($withTime ? 'd/m/Y H:i' : 'd/m/Y');
    }
}
