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
            if (Schema::hasColumn('users', 'payment_badge') === false) {
                $table->string('payment_badge')->default('normal')->after('bale_chat_id');
                $table->string('suggested_badge')->nullable()->after('payment_badge');
                $table->integer('average_delay_days')->default(0)->after('suggested_badge');
            }
        });

        Schema::table('policies', function (Blueprint $table): void {
            if (Schema::hasColumn('policies', 'total_amount') === false) {
                $table->unsignedBigInteger('total_amount')->default(0)->after('expires_at');
            }
            if (Schema::hasColumn('policies', 'payment_type') === false) {
                $table->string('payment_type')->default('installment')->after('total_amount');
            }
            if (Schema::hasColumn('policies', 'down_payment') === false) {
                $table->unsignedBigInteger('down_payment')->default(0)->after('payment_type');
            }
            if (Schema::hasColumn('policies', 'installments_count') === false) {
                $table->unsignedInteger('installments_count')->default(1)->after('down_payment');
            }
            if (Schema::hasColumn('policies', 'first_due_date') === false) {
                $table->date('first_due_date')->nullable()->after('installments_count');
            }
        });

        if (Schema::hasTable('notification_settings') === false) {
            Schema::create('notification_settings', function (Blueprint $table): void {
                $table->id();
                $table->json('reminder_offsets');
                $table->unsignedInteger('renewal_days_before')->default(15);
                $table->boolean('adaptive_timing_enabled')->default(true);
                $table->text('installment_template');
                $table->text('overdue_template');
                $table->text('renewal_template');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('notification_logs') === false) {
            Schema::create('notification_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('channel')->default('bale');
                $table->string('type');
                $table->unsignedBigInteger('related_id')->nullable();
                $table->integer('offset_day')->default(0);
                $table->string('idempotency_key')->unique();
                $table->text('message');
                $table->string('status')->default('queued');
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('installment_audits') === false) {
            Schema::create('installment_audits', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('installment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('actor_type')->default('system');
                $table->string('action');
                $table->string('old_status')->nullable();
                $table->string('new_status')->nullable();
                $table->unsignedBigInteger('old_amount')->nullable();
                $table->unsignedBigInteger('new_amount')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('notifications') === false) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_audits');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_settings');
    }
};