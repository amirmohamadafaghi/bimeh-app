<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable()->after('mobile');
            }
            if (! Schema::hasColumn('users', 'birth_date')) {
                $table->string('birth_date', 20)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable()->after('birth_date');
            }
            if (! Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'profile_title')) {
                $table->string('profile_title')->nullable()->after('address');
            }
        });

        Schema::table('insurers', function (Blueprint $table) {
            if (! Schema::hasColumn('insurers', 'policy_type')) {
                $table->string('policy_type')->nullable()->after('name');
            }
            if (! Schema::hasColumn('insurers', 'policy_types')) {
                $table->json('policy_types')->nullable()->after('policy_type');
            }
        });

        Schema::table('policies', function (Blueprint $table) {
            if (! Schema::hasColumn('policies', 'vehicle_name')) {
                $table->string('vehicle_name')->nullable()->after('title');
            }
            if (! Schema::hasColumn('policies', 'plate_number')) {
                $table->string('plate_number')->nullable()->after('vehicle_name');
            }
            if (! Schema::hasColumn('policies', 'vehicle_model')) {
                $table->string('vehicle_model')->nullable()->after('plate_number');
            }
            if (! Schema::hasColumn('policies', 'profile_title')) {
                $table->string('profile_title')->nullable()->after('vehicle_model');
            }
        });

        Schema::table('installments', function (Blueprint $table) {
            if (! Schema::hasColumn('installments', 'payment_identifier')) {
                $table->string('payment_identifier')->nullable()->after('amount');
            }
            if (! Schema::hasColumn('installments', 'documented_amount')) {
                $table->unsignedBigInteger('documented_amount')->nullable()->after('payment_identifier');
            }
            if (! Schema::hasColumn('installments', 'documented_date')) {
                $table->string('documented_date')->nullable()->after('documented_amount');
            }
            if (! Schema::hasColumn('installments', 'profile_title')) {
                $table->string('profile_title')->nullable()->after('documented_date');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `installments` MODIFY COLUMN `status` VARCHAR(32) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        //
    }
};
