<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['national_code' => '0859844145'],
            [
                'name'      => 'مدیر سیستم',
                'mobile'    => '09152687500',
                'role'      => UserRole::Admin,
                'password'  => Hash::make('09152687500'),
            ]
        );
    }
}