<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (Schema::hasColumn('users', 'address') === false) {
                    $table->text('address')->nullable()->after('mobile');
                }
                if (Schema::hasColumn('users', 'phone') === false) {
                    $table->string('phone', 30)->nullable()->after('mobile');
                }
                if (Schema::hasColumn('users', 'policyholder_type') === false) {
                    $table->string('policyholder_type', 30)->nullable()->default('حقیقی')->after('name');
                }
                if (Schema::hasColumn('users', 'profile_title') === false) {
                    $table->string('profile_title')->nullable()->after('policyholder_type');
                }
            });
        }

        if (Schema::hasTable('policies')) {
            Schema::table('policies', function (Blueprint $table): void {
                if (Schema::hasColumn('policies', 'started_at') === false) {
                    $table->date('started_at')->nullable()->after('profile_title');
                }
                if (Schema::hasColumn('policies', 'started_at_jalali') === false) {
                    $table->string('started_at_jalali', 20)->nullable()->after('started_at');
                }
                if (Schema::hasColumn('policies', 'issued_at_jalali') === false) {
                    $table->string('issued_at_jalali', 20)->nullable()->after('issued_at');
                }
                if (Schema::hasColumn('policies', 'expires_at_jalali') === false) {
                    $table->string('expires_at_jalali', 20)->nullable()->after('expires_at');
                }
                if (Schema::hasColumn('policies', 'driver_no_claim_years') === false) {
                    $table->string('driver_no_claim_years', 20)->nullable()->after('no_claim_years');
                }
                if (Schema::hasColumn('policies', 'policyholder_type') === false) {
                    $table->string('policyholder_type', 30)->nullable()->default('حقیقی')->after('insurance_category');
                }
            });
        }

        if (Schema::hasTable('installments')) {
            Schema::table('installments', function (Blueprint $table): void {
                if (Schema::hasColumn('installments', 'due_date_jalali') === false) {
                    $table->string('due_date_jalali', 20)->nullable()->after('due_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'address')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('address');
            });
        }
    }
};