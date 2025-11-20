<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInvoiceLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Super admins bypass limit check
        if ($user->is_super_admin) {
            return $next($request);
        }

        $company = current_company();

        if (!$company) {
            return $this->errorResponse($request, 'No company associated with your account.');
        }

        if (!$company->hasActiveSubscription()) {
            return $this->errorResponse(
                $request,
                'No active subscription. Please subscribe to create invoices.',
                'billing.plans'
            );
        }

        if (!$company->canCreateInvoice()) {
            $plan = $company->currentPlan();
            $message = "You've reached your monthly limit of {$plan->invoice_limit} invoices. ";
            $message .= "Please upgrade your plan to create more invoices.";

            return $this->errorResponse($request, $message, 'billing.plans');
        }

        return $next($request);
    }

    /**
     * Return appropriate error response based on request type.
     */
    protected function errorResponse(Request $request, string $message, string $redirect = 'dashboard'): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $message,
                'code' => 'invoice_limit_reached',
            ], 403);
        }

        return redirect()->route($redirect)->with('error', $message);
    }
}
