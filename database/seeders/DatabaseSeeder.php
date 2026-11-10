<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Insurer;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $userColumns = Schema::getColumnListing('users');

        // ۱. ایجاد یا بروزرسانی حساب مدیر ارشد اصلی سامانه (ورود با کد ملی و رمز موبایل)
        $superAdminData = [
            'name'                        => 'مدیر سیستم آفاق بیمه',
            'national_code'               => '0859844145',
            'mobile'                      => '09152687500',
            'email'                       => 'admin@afaghbimeh.ir',
            'password'                    => Hash::make('09152687500'),
            'role'                        => 'admin',
            'policyholder_type'           => 'حقیقی',
            'payment_badge'               => 'good_payer',
            'preferred_notification_hour' => 10,
        ];

        User::updateOrCreate(
            ['national_code' => $superAdminData['national_code']],
            array_intersect_key($superAdminData, array_flip($userColumns))
        );

        // ۲. ایجاد شرکت‌های بیمه پایه
        if (Schema::hasTable('insurers')) {
            $insurerColumns = Schema::getColumnListing('insurers');
            $insurers = [
                ['name' => 'بیمه ایران', 'code' => 'IRAN', 'is_active' => true],
                ['name' => 'بیمه آسیا', 'code' => 'ASIA', 'is_active' => true],
                ['name' => 'بیمه البرز', 'code' => 'ALBORZ', 'is_active' => true],
                ['name' => 'بیمه دانا', 'code' => 'DANA', 'is_active' => true],
                ['name' => 'بیمه پاسارگاد', 'code' => 'PASARGAD', 'is_active' => true],
                ['name' => 'بیمه پارسیان', 'code' => 'PARSIAN', 'is_active' => true],
            ];

            foreach ($insurers as $insurerData) {
                Insurer::updateOrCreate(
                    ['name' => $insurerData['name']],
                    array_intersect_key($insurerData, array_flip($insurerColumns))
                );
            }
        }

        // ۳. ایجاد سطر پیش‌فرض تنظیمات اعلان‌ها
        if (Schema::hasTable('notification_settings')) {
            NotificationSetting::current();
        }
    }
}