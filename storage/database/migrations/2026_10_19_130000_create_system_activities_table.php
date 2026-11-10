<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_activities')) {
            Schema::create('system_activities', function (Blueprint $table) {
                $table->id();
                $table->string('event_type', 50)->index()->comment('notification_sent | excel_imported | policy_updated | installment_updated | custom');
                $table->string('title')->comment('عنوان کوتاه فارسی رویداد');
                $table->string('icon', 60)->default('heroicon-o-bolt');
                $table->string('color', 30)->default('indigo')->comment('indigo | emerald | rose | amber | blue');
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->json('details')->nullable()->comment('جزئیات کامل برای نمایش در مودال Drill-down');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_activities');
    }
};
