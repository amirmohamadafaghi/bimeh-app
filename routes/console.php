<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| زمان‌بندی دقیق و خودکار سامانه «آفاق بیمه» (به وقت Asia/Tehran)
|--------------------------------------------------------------------------
*/

// ۱. ساعت 01:30 بامداد: شناسایی و معوق کردن اقساط سررسید گذشته
Schedule::command('bimeh:mark-overdue')
    ->dailyAt('01:30')
    ->timezone('Asia/Tehran')
    ->withoutOverlapping();

// ۲. ساعت 04:00 بامداد: پشتیبان‌گیری خودکار و فشرده از دیتابیس
Schedule::command('bimeh:backup-database')
    ->dailyAt('04:00')
    ->timezone('Asia/Tehran')
    ->withoutOverlapping();

// ۳. ساعت 07:30 صبح: ارسال هوشمند یادآور اقساط، اخطار دیرکرد و تبریک تولد
Schedule::command('bimeh:send-installment-reminders')
    ->dailyAt('07:30')
    ->timezone('Asia/Tehran')
    ->withoutOverlapping();

// ۴. ساعت 07:30 صبح: ارسال یادآور تمدید بیمه‌نامه‌های در آستانه انقضا
Schedule::command('bimeh:send-renewal-reminders')
    ->dailyAt('07:30')
    ->timezone('Asia/Tehran')
    ->withoutOverlapping();

// ۵. ساعت 15:00 عصر: پاک‌سازی فایل‌های موقت آپلودی اکسل و Livewire
Schedule::call(function (): void {
    foreach (['app/private/imports', 'app/private/livewire-tmp'] as $subPath) {
        $dir = storage_path($subPath);
        if (File::isDirectory($dir)) {
            File::cleanDirectory($dir);
        }
    }
})->dailyAt('15:00')->timezone('Asia/Tehran')->name('cleanup-temp-uploads');

// ۶. هر دقیقه: پردازش صف ارسال پیام‌های ربات بله در محیط cPanel
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping();