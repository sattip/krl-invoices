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

if (!function_exists('lroute')) {
    /**
     * Localized URL for a public route: "<name>.en" when the locale is English
     * and such a route exists, otherwise the Greek/default "<name>".
     */
    function lroute(string $name, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $base = preg_replace('/\.en$/', '', $name);

        if ($locale === 'en' && \Illuminate\Support\Facades\Route::has($base . '.en')) {
            return route($base . '.en', $parameters);
        }

        return route($base, $parameters);
    }
}

if (!function_exists('locale_alternates')) {
    /**
     * el/en URLs of the current public page (for hreflang, canonical and the language switch).
     * Returns null for routes without an English twin.
     *
     * @return array{el: string, en: string}|null
     */
    function locale_alternates(): ?array
    {
        $name = request()->route()?->getName();
        if (!$name) {
            return null;
        }

        $base = preg_replace('/\.en$/', '', $name);
        if (!\Illuminate\Support\Facades\Route::has($base . '.en')) {
            return null;
        }

        return ['el' => route($base), 'en' => route($base . '.en')];
    }
}
