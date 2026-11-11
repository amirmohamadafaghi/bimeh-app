<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table): void {
            if (Schema::hasColumn('policies', 'insurance_category') === false) {
                $table->string('insurance_category')->default('ثالث')->after('title');
            }
            if (Schema::hasColumn('policies', 'vehicle_name') === false) {
                $table->string('vehicle_name')->nullable()->after('insurance_category');
            }
            if (Schema::hasColumn('policies', 'plate_number') === false) {
                $table->string('plate_number')->nullable()->after('vehicle_name');
            }
            if (Schema::hasColumn('policies', 'vehicle_model') === false) {
                $table->string('vehicle_model', 30)->nullable()->after('plate_number');
            }
            if (Schema::hasColumn('policies', 'no_claim_years') === false) {
                $table->string('no_claim_years', 30)->nullable()->after('vehicle_model');
            }
            if (Schema::hasColumn('policies', 'profile_title') === false) {
                $table->string('profile_title')->nullable()->after('no_claim_years');
            }
            if (Schema::hasColumn('policies', 'initial_payment') === false) {
                $table->unsignedBigInteger('initial_payment')->default(0)->after('total_amount');
            }
            if (Schema::hasColumn('policies', 'paid_amount') === false) {
                $table->unsignedBigInteger('paid_amount')->default(0)->after('initial_payment');
            }
        });

        Schema::table('installments', function (Blueprint $table): void {
            if (Schema::hasColumn('installments', 'paid_amount') === false) {
                $table->unsignedBigInteger('paid_amount')->default(0)->after('amount');
            }
            if (Schema::hasColumn('installments', 'documented_amount') === false) {
                $table->unsignedBigInteger('documented_amount')->nullable()->after('paid_amount');
            }
            if (Schema::hasColumn('installments', 'documented_date') === false) {
                $table->string('documented_date', 30)->nullable()->after('documented_amount');
            }
            if (Schema::hasColumn('installments', 'payment_identifier') === false) {
                $table->string('payment_identifier', 100)->nullable()->after('documented_date');
            }
            if (Schema::hasColumn('installments', 'payment_type') === false) {
                $table->string('payment_type', 30)->default('قسط')->after('payment_identifier');
            }
            if (Schema::hasColumn('installments', 'profile_title') === false) {
                $table->string('profile_title')->nullable()->after('payment_type');
            }
        });
    }

    public function down(): void
    {
        //
    }
};