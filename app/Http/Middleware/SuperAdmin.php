<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            abort(403, 'Δεν επιτρέπεται η πρόσβαση. Απαιτούνται δικαιώματα super admin.');
        }

        return $next($request);
    }
}
