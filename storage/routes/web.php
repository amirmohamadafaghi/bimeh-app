<?php

use App\Http\Controllers\Customer\BaleWebhookController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerPortalController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('customer.login'));
Route::get('/login', fn () => redirect()->route('customer.login'))->name('login');

// وب‌هوک خودکار ربات بله (آماده به کار به محض دریافت توکن بله)
Route::post('/webhook/bale', [BaleWebhookController::class, 'handle'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('bale.webhook');

Route::prefix('my')->name('customer.')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])
        ->middleware('throttle:customer-login')
        ->name('login.submit');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    Route::get('/pay/{installment}/{user}', [CustomerPortalController::class, 'signedSingleInstallmentPay'])
        ->middleware(['signed', 'throttle:20,1'])
        ->name('installments.signed-pay');

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::post('/notifications/read-all', [CustomerPortalController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
        Route::post('/push-subscribe', [CustomerPortalController::class, 'savePushSubscription'])
            ->middleware('throttle:10,1')
            ->name('push.subscribe');
        Route::post('/pay/{installment}/confirm', [CustomerPortalController::class, 'confirmSingleInstallmentPay'])
            ->middleware('throttle:10,1')
            ->name('installments.confirm-pay');
    });
});