<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ۱. اطمینان از وجود ستون‌های تکمیلی در جدول users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('mobile');
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable()->after('mobile');
            }
            if (! Schema::hasColumn('users', 'birth_date')) {
                $table->string('birth_date', 20)->nullable()->after('gender');
            }
        });

        // ۲. اطمینان از وجود تمام ستون‌های مالی و اقساطی در جدول policies (رفع باگ شرط total_amount)
        Schema::table('policies', function (Blueprint $table) {
            if (! Schema::hasColumn('policies', 'policyholder_type')) {
                $table->string('policyholder_type')->nullable()->default('حقیقی')->after('insurance_category');
            }
            if (! Schema::hasColumn('policies', 'profile_title')) {
                $table->string('profile_title')->nullable()->after('driver_no_claim_years');
            }
            if (! Schema::hasColumn('policies', 'initial_payment')) {
                $table->unsignedBigInteger('initial_payment')->default(0)->after('total_amount');
            }
            if (! Schema::hasColumn('policies', 'paid_amount')) {
                $table->unsignedBigInteger('paid_amount')->default(0)->after('initial_payment');
            }
            if (! Schema::hasColumn('policies', 'payment_type')) {
                $table->string('payment_type', 30)->default('installment')->after('paid_amount');
            }
            if (! Schema::hasColumn('policies', 'down_payment')) {
                $table->unsignedBigInteger('down_payment')->default(0)->after('payment_type');
            }
            if (! Schema::hasColumn('policies', 'installments_count')) {
                $table->unsignedInteger('installments_count')->default(0)->after('down_payment');
            }
            if (! Schema::hasColumn('policies', 'first_due_date')) {
                $table->date('first_due_date')->nullable()->after('installments_count');
            }
        });
    }

    public function down(): void
    {
        //
    }
};