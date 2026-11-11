<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('bimeh:mark-overdue')
    ->dailyAt('00:05')
    ->timezone('Asia/Tehran')
    ->withoutOverlapping();

Schedule::command('bimeh:send-installment-reminders')
    ->dailyAt('07:30')
    ->timezone('Asia/Tehran')
    ->withoutOverlapping();

Schedule::command('bimeh:send-renewal-reminders')
    ->dailyAt('09:00')
    ->timezone('Asia/Tehran')
    ->withoutOverlapping();

Schedule::command('bimeh:backup-database')
    ->dailyAt('02:30')
    ->timezone('Asia/Tehran')
    ->withoutOverlapping();