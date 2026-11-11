<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'permissions') === false) {
                $table->json('permissions')->nullable()->after('role');
            }
            if (Schema::hasColumn('users', 'birth_date') === false) {
                $table->string('birth_date', 20)->nullable()->after('mobile');
            }
            if (Schema::hasColumn('users', 'gender') === false) {
                $table->string('gender', 10)->nullable()->after('birth_date');
            }
        });

        Schema::table('insurers', function (Blueprint $table): void {
            if (Schema::hasColumn('insurers', 'service_type') === false) {
                $table->string('service_type')->nullable()->after('code');
            }
            if (Schema::hasColumn('insurers', 'contact_phone') === false) {
                $table->string('contact_phone', 50)->nullable()->after('service_type');
            }
        });
    }

    public function down(): void
    {
        //
    }
};