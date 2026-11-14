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
            return $this->errorResponse($request, 'Ο λογαριασμός σας δεν είναι συνδεδεμένος με κάποια εταιρεία.');
        }

        if (!$company->hasActiveSubscription() && ($trial = $company->trialEntitlement)) {
            $denial = $trial->denial();

            return $denial
                ? $this->errorResponse($request, $denial['message'], 'billing.plans', $denial['code'])
                : $next($request);
        }

        if (!$company->hasActiveSubscription()) {
            return $this->errorResponse(
                $request,
                'Δεν υπάρχει ενεργή συνδρομή. Εγγραφείτε σε κάποιο πακέτο για να δημιουργήσετε τιμολόγια.',
                'billing.plans'
            );
        }

        if (!$company->canCreateInvoice()) {
            $plan = $company->currentPlan();
            $message = "Έχετε φτάσει το μηνιαίο όριο των " . number_el($plan->invoice_limit) . " τιμολογίων. ";
            $message .= "Αναβαθμίστε το πακέτο σας για να δημιουργήσετε περισσότερα τιμολόγια.";

            return $this->errorResponse($request, $message, 'billing.plans');
        }

        return $next($request);
    }

    /**
     * Return appropriate error response based on request type.
     */
    protected function errorResponse(Request $request, string $message, string $redirect = 'dashboard', string $code = 'invoice_limit_reached'): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $message,
                'code' => $code,
            ], 403);
        }

        return redirect()->route($redirect)->with('error', $message);
    }
}
