<?php

use App\Http\Controllers\Admin\StripeConnectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\Auth\SubscriptionRegistrationController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Invoice routes
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // API Token routes
    Route::get('/api-tokens', [ApiTokenController::class, 'index'])->name('api.tokens.index');
    Route::post('/api-tokens', [ApiTokenController::class, 'store'])->name('api.tokens.store');
    Route::delete('/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api.tokens.destroy');

    // API Documentation
    Route::get('/api-documentation', function () {
        return view('api-documentation.index');
    })->name('api.documentation');

    // Company settings (for regular users)
    Route::get('/company/settings', [CompanyController::class, 'settings'])->name('company.settings');
    Route::put('/company/settings', [CompanyController::class, 'updateSettings'])->name('company.settings.update');

    // Billing routes
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/plans', [BillingController::class, 'plans'])->name('billing.plans');
    Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/upgrade', [BillingController::class, 'upgrade'])->name('billing.upgrade');
    Route::post('/billing/downgrade', [BillingController::class, 'downgrade'])->name('billing.downgrade');
    Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/resume', [BillingController::class, 'resume'])->name('billing.resume');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::get('/billing/checkout/success', [BillingController::class, 'checkoutSuccess'])->name('billing.checkout.success');
    Route::get('/billing/checkout/cancel', [BillingController::class, 'checkoutCancel'])->name('billing.checkout.cancel');
});

// Super Admin routes
Route::middleware(['auth', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin dashboard redirect
    Route::get('/', function () {
        return redirect()->route('admin.companies.index');
    });

    // Company management
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::post('/companies/{company}/assign-user', [CompanyController::class, 'assignUser'])->name('companies.assign-user');

    // User management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');

    // Stripe settings
    Route::get('/stripe/setup', [StripeConnectController::class, 'setup'])->name('stripe.setup');
    Route::post('/stripe/save-credentials', [StripeConnectController::class, 'saveCredentials'])->name('stripe.save-credentials');
    Route::post('/stripe/test', [StripeConnectController::class, 'testConnection'])->name('stripe.test');
    Route::delete('/stripe/disconnect', [StripeConnectController::class, 'disconnect'])->name('stripe.disconnect');
    Route::get('/stripe/plans', [StripeConnectController::class, 'plans'])->name('stripe.plans');
    Route::post('/stripe/plans', [StripeConnectController::class, 'updatePlans'])->name('stripe.update-plans');
    Route::post('/stripe/sync-plans', [StripeConnectController::class, 'syncPlans'])->name('stripe.sync-plans');
});

// Stop impersonating (needs to be accessible even when impersonating)
Route::middleware('auth')->post('/stop-impersonating', [UserController::class, 'stopImpersonating'])->name('stop-impersonating');

// Registration with subscription (guest routes)
Route::middleware('guest')->group(function () {
    Route::get('/register/checkout', [SubscriptionRegistrationController::class, 'checkout'])->name('register.checkout');
    Route::post('/register/checkout', [SubscriptionRegistrationController::class, 'processCheckout'])->name('register.process-checkout');
    Route::get('/register/success', [SubscriptionRegistrationController::class, 'success'])->name('register.success');
    Route::get('/register/cancel', [SubscriptionRegistrationController::class, 'cancel'])->name('register.cancel');
});

// Stripe Webhook (no auth, no CSRF)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhooks.stripe');

require __DIR__.'/auth.php';
