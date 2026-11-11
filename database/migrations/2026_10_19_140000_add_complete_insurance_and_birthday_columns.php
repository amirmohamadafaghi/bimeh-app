<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notification_settings')) {
            Schema::table('notification_settings', function (Blueprint $table): void {
                if (Schema::hasColumn('notification_settings', 'birthday_greeting_enabled') === false) {
                    $table->boolean('birthday_greeting_enabled')->default(true)->after('adaptive_timing_enabled');
                }
                if (Schema::hasColumn('notification_settings', 'birthday_template') === false) {
                    $table->text('birthday_template')->nullable()->after('renewal_template');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};