<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Insurer;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminNationalCode = (string) env('ADMIN_DEFAULT_NATIONAL_CODE', '0000000000');
        $adminMobile = (string) env('ADMIN_DEFAULT_MOBILE', '09120000000');

        $existingAdmin = User::withTrashed()
            ->where('national_code', $adminNationalCode)
            ->first();

        if ($existingAdmin instanceof User) {
            if ($existingAdmin->trashed()) {
                $existingAdmin->restore();
            }
            $existingAdmin->update([
                'role' => 'admin',
            ]);
        } else {
            User::create([
                'name'          => 'مدیریت آفاق بیمه (نمایندگی ۳۱۱۶)',
                'national_code' => $adminNationalCode,
                'mobile'        => $adminMobile,
                'password'      => Hash::make($adminMobile),
                'role'          => 'admin',
                'payment_badge' => 'good_payer',
            ]);
        }

        Insurer::firstOrCreate(
            ['name' => 'بیمه ایران'],
            [
                'code'         => '3116',
                'service_type' => 'ثالث، بدنه، آتش‌سوزی، مسئولیت و درمان',
                'is_active'    => true,
            ]
        );

        NotificationSetting::current();
    }
}