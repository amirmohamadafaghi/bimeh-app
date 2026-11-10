<?php

namespace Database\Seeders;

use App\Models\Installment;
use App\Models\Insurer;
use App\Models\NotificationSetting;
use App\Models\Policy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $userColumns = Schema::getColumnListing('users');

        // ۱. ایجاد حساب مدیر ارشد اصلی
        $superAdminData = [
            'name' => 'مدیر ارشد آفاق بیمه',
            'national_code' => '0859844145',
            'mobile' => '09152687500',
            'email' => 'admin@afaghbimeh.ir',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'policyholder_type' => 'حقیقی',
            'payment_badge' => 'good_payer',
            'preferred_notification_hour' => 10,
        ];

        User::updateOrCreate(
            ['national_code' => $superAdminData['national_code']],
            array_intersect_key($superAdminData, array_flip($userColumns))
        );

        // ۲. ایجاد حساب پشتیبان سیستم
        $systemAdminData = [
            'name' => 'مدیر سیستم آفاق بیمه',
            'national_code' => '0011223344',
            'mobile' => '09120000000',
            'email' => 'system@afaghbimeh.ir',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'policyholder_type' => 'حقیقی',
            'payment_badge' => 'none',
            'preferred_notification_hour' => 10,
        ];

        User::updateOrCreate(
            ['mobile' => $systemAdminData['mobile']],
            array_intersect_key($systemAdminData, array_flip($userColumns))
        );

        // ۳. ایجاد شرکت‌های بیمه پایه
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

        // ۴. ایجاد سطر پیش‌فرض تنظیمات اعلان (اصلاح شده)
        if (Schema::hasTable('notification_settings')) {
            NotificationSetting::current();
        }

        // ۵. ایجاد بیمه‌گذاران نمونه به همراه بیمه‌نامه و اقساط
        $defaultInsurer = Schema::hasTable('insurers') ? Insurer::first() : null;

        $sampleCustomers = [
            [
                'name' => 'علی محمدی',
                'national_code' => '0012345678',
                'mobile' => '09121112233',
                'policyholder_type' => 'حقیقی',
                'payment_badge' => 'good_payer',
                'profile_title' => 'اعتباری 50-50',
                'birth_date' => '1365/04/15',
                'address' => 'تهران، خیابان ولیعصر، پلاک ۱۲۰',
                'policy' => [
                    'policy_number' => '1405/1001',
                    'title' => 'بیمه ثالث - پژو ۲۰۷ اتوماتیک',
                    'insurance_category' => 'ثالث',
                    'vehicle_name' => 'پژو ۲۰۷ اتوماتیک',
                    'plate_number' => '۱۲ ب ۳۴۵ ایران ۶۶',
                    'total_amount' => 120000000,
                ],
            ],
            [
                'name' => 'سارا احمدی',
                'national_code' => '0023456789',
                'mobile' => '09123334455',
                'policyholder_type' => 'حقیقی',
                'payment_badge' => 'none',
                'profile_title' => 'اقساطی ۶ ماهه',
                'birth_date' => '1370/07/29',
                'address' => 'مشهد، بلوار وکیل‌آباد، پلاک ۴۵',
                'policy' => [
                    'policy_number' => '1405/1002',
                    'title' => 'بیمه بدنه - دنا پلاس توربو',
                    'insurance_category' => 'بدنه',
                    'vehicle_name' => 'دنا پلاس توربو',
                    'plate_number' => '۳۴ ج ۵۶۷ ایران ۱۲',
                    'total_amount' => 90000000,
                ],
            ],
            [
                'name' => 'شرکت توسعه تجارت پایدار',
                'national_code' => '1010123456',
                'mobile' => '09125556677',
                'policyholder_type' => 'حقوقی',
                'payment_badge' => 'bad_payer',
                'profile_title' => 'سازمانی ویژه',
                'birth_date' => '1380/01/10',
                'address' => 'تهران، سعادت‌آباد، بلوار دریا، پلاک ۸۸',
                'policy' => [
                    'policy_number' => '1405/1003',
                    'title' => 'بیمه ثالث - کامیونت ایسوزو',
                    'insurance_category' => 'ثالث',
                    'vehicle_name' => 'کامیونت ایسوزو',
                    'plate_number' => '۵۶ ع ۷۸۹ ایران ۱۱',
                    'total_amount' => 180000000,
                ],
            ],
        ];

        $policyColumns = Schema::getColumnListing('policies');
        $installmentColumns = Schema::getColumnListing('installments');

        foreach ($sampleCustomers as $customerData) {
            $policyInfo = $customerData['policy'];
            unset($customerData['policy']);

            $customerPayload = array_merge($customerData, [
                'password' => Hash::make('12345678'),
                'role' => 'customer',
                'preferred_notification_hour' => 10,
            ]);

            $customer = User::updateOrCreate(
                ['national_code' => $customerData['national_code']],
                array_intersect_key($customerPayload, array_flip($userColumns))
            );

            $policyAttributes = [
                'user_id' => $customer->id,
                'insurer_id' => $defaultInsurer?->id,
                'title' => $policyInfo['title'],
                'insurance_category' => $policyInfo['insurance_category'],
                'vehicle_name' => $policyInfo['vehicle_name'],
                'plate_number' => $policyInfo['plate_number'],
                'total_amount' => $policyInfo['total_amount'],
                'issued_at' => now()->subMonth()->toDateString(),
                'expires_at' => now()->addMonths(11)->toDateString(),
                'status' => 'active',
            ];

            $policy = Policy::updateOrCreate(
                ['policy_number' => $policyInfo['policy_number']],
                array_intersect_key($policyAttributes, array_flip($policyColumns))
            );

            $installmentAmount = (int) ($policyInfo['total_amount'] / 3);

            $installmentPayloads = [
                [
                    'installment_number' => 1,
                    'amount' => $installmentAmount,
                    'paid_amount' => $installmentAmount,
                    'documented_amount' => $installmentAmount,
                    'documented_date' => '1405/07/01',
                    'payment_identifier' => 'PAY-'.$policy->id.'-01',
                    'profile_title' => $customer->profile_title ?? 'عادی',
                    'due_date' => now()->subDays(15)->toDateString(),
                    'paid_at' => now()->subDays(15),
                    'status' => 'paid',
                ],
                [
                    'installment_number' => 2,
                    'amount' => $installmentAmount,
                    'paid_amount' => 0,
                    'payment_identifier' => 'PAY-'.$policy->id.'-02',
                    'profile_title' => $customer->profile_title ?? 'عادی',
                    'due_date' => now()->subDays(2)->toDateString(),
                    'status' => 'overdue',
                ],
                [
                    'installment_number' => 3,
                    'amount' => $installmentAmount,
                    'paid_amount' => 0,
                    'payment_identifier' => 'PAY-'.$policy->id.'-03',
                    'profile_title' => $customer->profile_title ?? 'عادی',
                    'due_date' => now()->addDays(20)->toDateString(),
                    'status' => 'pending',
                ],
            ];

            foreach ($installmentPayloads as $inst) {
                Installment::updateOrCreate(
                    [
                        'policy_id' => $policy->id,
                        'installment_number' => $inst['installment_number'],
                    ],
                    array_intersect_key($inst, array_flip($installmentColumns))
                );
            }
        }
    }
}
