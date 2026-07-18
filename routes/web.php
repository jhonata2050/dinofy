<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Client as Client;
use Illuminate\Support\Facades\Route;

// ─── Webhooks (sem CSRF) ───
Route::post('/webhooks/cajupay', [WebhookController::class, 'cajupay'])->name('webhooks.cajupay');
Route::post('/webhooks/woovi', [WebhookController::class, 'woovi'])->name('webhooks.woovi');

// ─── Checkout Público ───
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('index');
    Route::get('/payment/{token}', [\App\Http\Controllers\CheckoutController::class, 'payment'])->name('payment');
    Route::get('/payment/{token}/check', [\App\Http\Controllers\CheckoutController::class, 'checkPayment'])->name('payment.check');
    Route::post('/check-subdomain', [\App\Http\Controllers\CheckoutController::class, 'checkSubdomain'])->name('check-subdomain');
    Route::get('/{slug}', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('show');
    Route::post('/{slug}', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('process');
});

// ─── Login do Cliente (/) ───
Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [Client\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [Client\AuthController::class, 'login']);

// ─── Portal do Cliente ───
Route::prefix('client')->name('client.')->group(function () {
    Route::get('/login', [Client\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Client\AuthController::class, 'login']);

    Route::get('/forgot-password', [Client\PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [Client\PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [Client\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [Client\PasswordResetController::class, 'reset'])->name('password.update');

    Route::middleware('auth:tenant')->group(function () {
        Route::post('/logout', [Client\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Client\DashboardController::class)->name('dashboard');

        Route::get('/billing', [Client\BillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/{invoice}', [Client\BillingController::class, 'show'])->name('billing.show');
        Route::post('/billing/{invoice}/generate-pix', [Client\BillingController::class, 'generatePix'])->name('billing.generate-pix');
        Route::get('/billing/{invoice}/check-payment', [Client\BillingController::class, 'checkPayment'])->name('billing.check-payment');

        Route::get('/plans', [Client\PlanController::class, 'index'])->name('plans.index');
        Route::post('/plans/upgrade', [Client\PlanController::class, 'requestUpgrade'])->name('plans.upgrade');

        Route::get('/tickets', [Client\TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [Client\TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [Client\TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [Client\TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [Client\TicketController::class, 'reply'])->name('tickets.reply');

        Route::get('/settings', [Client\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings/profile', [Client\SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::put('/settings/password', [Client\SettingsController::class, 'updatePassword'])->name('settings.password');

        Route::get('/api/resources', [Client\ApiController::class, 'resources'])->name('api.resources');
    });
});

// ─── Admin Master ───
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [\App\Http\Controllers\Admin\PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Admin\PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Admin\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Admin\PasswordResetController::class, 'reset'])->name('password.update');

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('tenants', TenantController::class);
        Route::post('/tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('/tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('/tenants/{tenant}/reprovision', [TenantController::class, 'reprovision'])->name('tenants.reprovision');
        Route::post('/tenants/{tenant}/docker/{action}', [TenantController::class, 'docker'])->name('tenants.docker')->where('action', 'start|stop|restart');

        Route::post('/tenants/{tenant}/domains', [DomainController::class, 'store'])->name('domains.store');
        Route::post('/domains/{domain}/verify', [DomainController::class, 'verify'])->name('domains.verify');
        Route::delete('/domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');

        Route::resource('plans', PlanController::class)->except(['show', 'destroy']);

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::post('/invoices/{invoice}/confirm-payment', [InvoiceController::class, 'confirmPayment'])->name('invoices.confirm-payment');
        Route::post('/invoices/{invoice}/generate-charge', [InvoiceController::class, 'generateCharge'])->name('invoices.generate-charge');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-gateway', [SettingsController::class, 'testGateway'])->name('settings.test-gateway');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('/update', [UpdateController::class, 'index'])->name('update.index');
        Route::post('/update/pull', [UpdateController::class, 'pull'])->name('update.pull');
        Route::post('/update/deploy', [UpdateController::class, 'deploy'])->name('update.deploy');
        Route::post('/update/tenant/{tenant}', [UpdateController::class, 'updateTenant'])->name('update.tenant');

        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\TicketController::class, 'index'])->name('index');
            Route::get('/{ticket}', [App\Http\Controllers\Admin\TicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('reply');
            Route::patch('/{ticket}/status', [App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('status');
        });

        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/charts/revenue', [ApiController::class, 'revenueChart'])->name('charts.revenue');
            Route::get('/charts/tenant-growth', [ApiController::class, 'tenantGrowthChart'])->name('charts.tenant-growth');
            Route::get('/charts/plan-distribution', [ApiController::class, 'planDistributionChart'])->name('charts.plan-distribution');
            Route::get('/charts/invoice-status', [ApiController::class, 'invoiceStatusChart'])->name('charts.invoice-status');
            Route::get('/system-health', [ApiController::class, 'systemHealth'])->name('system-health');
        });
    });
});
