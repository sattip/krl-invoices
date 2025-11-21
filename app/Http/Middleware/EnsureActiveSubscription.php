<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Routes that should be excluded from subscription check.
     */
    protected array $except = [
        'billing.*',
        'profile.*',
        'logout',
        'admin.*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Super admins bypass subscription check
        if ($user->is_super_admin) {
            return $next($request);
        }

        // Check if route is excluded
        if ($this->shouldExclude($request)) {
            return $next($request);
        }

        $company = current_company();

        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'No company associated with your account.');
        }

        if (!$company->hasActiveSubscription()) {
            return redirect()->route('billing.plans')
                ->with('warning', 'Please subscribe to a plan to access this feature.');
        }

        return $next($request);
    }

    /**
     * Check if the current route should be excluded from subscription check.
     */
    protected function shouldExclude(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
