<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'deleted_at'], 'idx_users_role_deleted');
            $table->index('payment_badge', 'idx_users_payment_badge');
            $table->index('birth_date', 'idx_users_birth_date');
        });

        Schema::table('policies', function (Blueprint $table) {
            $table->index(['user_id', 'deleted_at'], 'idx_policies_user_deleted');
            $table->index(['expires_at', 'deleted_at'], 'idx_policies_expires_deleted');
        });

        Schema::table('installments', function (Blueprint $table) {
            $table->index(['status', 'due_date', 'deleted_at'], 'idx_installments_status_due_deleted');
            $table->index(['policy_id', 'installment_number'], 'idx_installments_policy_number');
            $table->index('payment_identifier', 'idx_installments_payment_identifier');
        });

        if (Schema::hasTable('notification_logs')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->index(['type', 'offset_day', 'created_at'], 'idx_notif_logs_type_offset_created');
                $table->index(['status', 'created_at'], 'idx_notif_logs_status_created');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_deleted');
            $table->dropIndex('idx_users_payment_badge');
            $table->dropIndex('idx_users_birth_date');
        });

        Schema::table('policies', function (Blueprint $table) {
            $table->dropIndex('idx_policies_user_deleted');
            $table->dropIndex('idx_policies_expires_deleted');
        });

        Schema::table('installments', function (Blueprint $table) {
            $table->dropIndex('idx_installments_status_due_deleted');
            $table->dropIndex('idx_installments_policy_number');
            $table->dropIndex('idx_installments_payment_identifier');
        });

        if (Schema::hasTable('notification_logs')) {
            Schema::table('notification_logs', function (Blueprint $table) {
                $table->dropIndex('idx_notif_logs_type_offset_created');
                $table->dropIndex('idx_notif_logs_status_created');
            });
        }
    }
};