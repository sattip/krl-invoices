<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Greek is the default. Public pages carry their language in the URL
 * (/ = el, /en = en) through a route default "locale"; visiting them also
 * remembers the choice in the session, so auth pages (login, register,
 * password reset) follow the visitor's language. Unknown values fall back to el.
 *
 * The logged-in application (dashboard, invoices, billing, admin) and emails
 * are translated to Greek only for now, so it is always rendered in Greek to
 * avoid half-English AdminLTE screens. Public /en pages stay English.
 */
class SetLocale
{
    public const SUPPORTED = ['el', 'en'];

    public const DEFAULT = 'el';

    public function handle(Request $request, Closure $next): Response
    {
        $routeLocale = $request->route()?->defaults['locale'] ?? null;

        if (in_array($routeLocale, self::SUPPORTED, true)) {
            $locale = $routeLocale;
            if ($request->hasSession()) {
                $request->session()->put('locale', $locale);
            }
        } elseif ($request->user()) {
            $locale = self::DEFAULT;
        } else {
            $locale = $request->hasSession() ? $request->session()->get('locale', self::DEFAULT) : self::DEFAULT;
        }

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = self::DEFAULT;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
