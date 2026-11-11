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
            if (Schema::hasColumn('users', 'push_subscription') === false) {
                $table->json('push_subscription')->nullable()->after('bale_chat_id');
            }
        });

        Schema::table('policies', function (Blueprint $table): void {
            if (Schema::hasColumn('policies', 'deleted_at') === false) {
                $table->softDeletes();
            }
        });

        Schema::table('installments', function (Blueprint $table): void {
            if (Schema::hasColumn('installments', 'deleted_at') === false) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        //
    }
};