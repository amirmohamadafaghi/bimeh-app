<?php

declare(strict_types=1);

use App\Http\Controllers\Customer\BaleWebhookController;
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerPortalController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('customer.login'));
Route::get('/login', fn () => redirect()->route('customer.login'))->name('login');

// وب‌هوک خودکار ربات بله
Route::post('/webhook/bale', [BaleWebhookController::class, 'handle'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('bale.webhook');

Route::prefix('my')->name('customer.')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])
        ->middleware('throttle:customer-login')
        ->name('login.submit');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    // مسیرهای پرداخت تک‌قسطی با لینک امضاشده (بدون لاگین دائمی در کل حساب کاربری)
    Route::get('/pay/{installment}/{user}', [CustomerPortalController::class, 'signedSingleInstallmentPay'])
        ->middleware(['signed', 'throttle:20,1'])
        ->name('installments.signed-pay');

    Route::post('/pay/{installment}/confirm', [CustomerPortalController::class, 'confirmSingleInstallmentPay'])
        ->middleware('throttle:10,1')
        ->name('installments.confirm-pay');

    // مسیرهای اختصاصی نیازمند ورود کامل به پرتال بیمه‌گذاران
    Route::middleware(['auth'])->group(function () {
        // ۱. داشبورد (خوش‌آمدگویی، تقویم ماهانه اقساط، راهنمای استفاده)
        Route::get('/dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');

        // ۲. صفحه مستقل بیمه و اقساط (بیمه‌نامه‌ها، اقساط، پرداخت‌های پیش‌رو و تاریخچه)
        Route::get('/policies', [CustomerPortalController::class, 'policies'])->name('policies');

        // ۳. صفحه مستقل اعلان‌ها (پیام‌های ادمین، فیلتر، صفحه‌بندی، خواندن همه)
        Route::get('/notifications', [CustomerPortalController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/read-all', [CustomerPortalController::class, 'markAllNotificationsRead'])->name('notifications.read-all');

        // ۴. صفحه مستقل پروفایل (نمایش و تکمیل اطلاعات هویتی)
        Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
        Route::post('/profile', [CustomerPortalController::class, 'updateProfile'])
            ->middleware('throttle:10,1')
            ->name('profile.update');

        // ۵. صفحه مستقل تماس با ما
        Route::get('/contact', [CustomerPortalController::class, 'contact'])->name('contact');

        // وب‌پوش و درخواست تمدید آنلاین
        Route::post('/push-subscribe', [CustomerPortalController::class, 'savePushSubscription'])
            ->middleware('throttle:10,1')
            ->name('push.subscribe');
        Route::post('/policies/{policy}/renewal-request', [CustomerPortalController::class, 'requestPolicyRenewal'])
            ->middleware('throttle:5,1')
            ->name('policies.renewal-request');
    });
});