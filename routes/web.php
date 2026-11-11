<?php

declare(strict_types=1);

use App\Http\Controllers\Customer\BaleWebhookController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
Route::post('/login', [CustomerAuthController::class, 'login'])
    ->middleware('throttle:customer-login')
    ->name('customer.login.submit');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

Route::post('/webhook/bale', [BaleWebhookController::class, 'handle'])->name('webhook.bale');

Route::get('/pay/installment/{installment}/{user}', [CustomerPortalController::class, 'signedSingleInstallmentPay'])
    ->middleware('signed')
    ->name('customer.installment.single-pay');
Route::post('/pay/installment/{installment}/confirm', [CustomerPortalController::class, 'confirmSingleInstallmentPay'])
    ->name('customer.installment.single-pay.confirm');

Route::middleware('auth')->group(function (): void {
    Route::get('/', [CustomerPortalController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/dashboard', [CustomerPortalController::class, 'dashboard']);
    Route::get('/policies', [CustomerPortalController::class, 'policies'])->name('customer.policies');
    Route::post('/policies/{policy}/renewal-request', [CustomerPortalController::class, 'requestPolicyRenewal'])
        ->name('customer.policies.renewal');
    Route::get('/notifications', [CustomerPortalController::class, 'notifications'])->name('customer.notifications');
    Route::post('/notifications/read-all', [CustomerPortalController::class, 'markAllNotificationsRead'])
        ->name('customer.notifications.read-all');
    Route::post('/push/subscribe', [CustomerPortalController::class, 'savePushSubscription'])
        ->name('customer.push.subscribe');
    Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('customer.profile');
    Route::post('/profile', [CustomerPortalController::class, 'updateProfile'])->name('customer.profile.update');
    Route::get('/contact', [CustomerPortalController::class, 'contact'])->name('customer.contact');
});