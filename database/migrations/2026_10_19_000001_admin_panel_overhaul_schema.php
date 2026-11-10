<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurers', function (Blueprint $table) {
            if (! Schema::hasColumn('insurers', 'policy_type')) {
                $table->string('policy_type')->nullable()->after('name')->comment('نوع بیمه‌نامه');
            }
        });

        Schema::table('policies', function (Blueprint $table) {
            if (! Schema::hasColumn('policies', 'vehicle_name')) {
                $table->string('vehicle_name')->nullable()->after('title')->comment('نام خودرو / مورد بیمه');
            }
            if (! Schema::hasColumn('policies', 'plate_number')) {
                $table->string('plate_number')->nullable()->after('vehicle_name')->comment('شماره پلاک خودرو');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insurers', function (Blueprint $table) {
            $table->dropColumn(['policy_type']);
        });

        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn(['vehicle_name', 'plate_number']);
        });
    }
};
