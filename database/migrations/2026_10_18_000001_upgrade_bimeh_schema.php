<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ۱. ارتقای جدول کاربران (شامل رفع خطای ستون name)
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('id')->comment('نام و نام خانوادگی');
            }
            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'finance', 'agent', 'customer'])->default('customer')->after('is_admin');
            }
            if (! Schema::hasColumn('users', 'bale_chat_id')) {
                $table->string('bale_chat_id')->nullable()->after('mobile')->comment('شناسه چت ربات بله');
            }
            if (! Schema::hasColumn('users', 'payment_badge')) {
                $table->enum('payment_badge', ['none', 'good_payer', 'bad_payer'])->default('none')->comment('برچسب فعلی: خوش‌حساب یا بدحساب');
            }
            if (! Schema::hasColumn('users', 'suggested_badge')) {
                $table->enum('suggested_badge', ['none', 'good_payer', 'bad_payer'])->default('none')->comment('پیشنهاد هوشمند سیستم برای کارتابل ادمین');
            }
            if (! Schema::hasColumn('users', 'push_subscription')) {
                $table->json('push_subscription')->nullable()->comment('اشتراک VAPID Web Push');
            }
        });

        // ۲. ارتقای جدول بیمه‌نامه‌ها
        Schema::table('policies', function (Blueprint $table) {
            if (! Schema::hasColumn('policies', 'total_amount')) {
                $table->unsignedBigInteger('total_amount')->default(0);
                $table->enum('payment_type', ['cash', 'installment'])->default('installment');
                $table->unsignedBigInteger('down_payment')->default(0);
                $table->unsignedInteger('installments_count')->default(1);
                $table->date('first_due_date')->nullable();
            }
            if (! Schema::hasColumn('policies', 'issued_at')) {
                $table->date('issued_at')->nullable()->after('title')->comment('تاریخ صدور بیمه‌نامه');
            }
            if (! Schema::hasColumn('policies', 'expires_at')) {
                $table->date('expires_at')->nullable()->after('issued_at')->comment('تاریخ پایان اعتبار بیمه‌نامه');
            }
            if (! Schema::hasColumn('policies', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ۳. ارتقای جدول اقساط
        Schema::table('installments', function (Blueprint $table) {
            if (! Schema::hasColumn('installments', 'installment_number')) {
                $table->unsignedInteger('installment_number')->default(1)->after('policy_id');
            }
            if (! Schema::hasColumn('installments', 'followed_up_at')) {
                $table->timestamp('followed_up_at')->nullable()->comment('زمان پیگیری در کارتابل ادمین');
            }
            if (! Schema::hasColumn('installments', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ۴. جدول تنظیمات داینامیک اعلان‌ها
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->json('reminder_offsets')->comment('آفست‌های یادآوری به روز: [-3, 0, 2, 7]');
            $table->unsignedInteger('renewal_days_before')->default(15)->comment('آستانه یادآور تمدید (روز)');
            $table->boolean('adaptive_timing_enabled')->default(true)->comment('فعال‌سازی زمان‌بندی تطبیقی');
            $table->text('installment_template')->comment('قالب پیام یادآوری قسط');
            $table->text('overdue_template')->comment('قالب پیام اخطار دیرکرد');
            $table->text('renewal_template')->comment('قالب پیام یادآور تمدید بیمه‌نامه');
            $table->timestamps();
        });

        // ۵. جدول لاگ اعلان‌ها
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel')->default('bale')->comment('bale | database | webpush');
            $table->string('type')->comment('installment_reminder | policy_renewal | test_message');
            $table->unsignedBigInteger('related_id')->nullable()->comment('شناسه قسط یا بیمه‌نامه');
            $table->integer('offset_day')->default(0);
            $table->string('idempotency_key')->unique()->comment('user_id:type:related_id:offset_day:channel');
            $table->text('message');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // ۶. جدول Audit Log اقساط
        Schema::create('installment_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_id')->constrained('installments')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type')->default('system')->comment('admin | customer | command');
            $table->string('action')->comment('created | status_changed | amount_changed | paid');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->unsignedBigInteger('old_amount')->nullable();
            $table->unsignedBigInteger('new_amount')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // ۷. جدول اعلان‌های درون‌برنامه‌ای استاندارد لاراول
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
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

        Schema::table('installments', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['installment_number', 'followed_up_at']);
        });

        Schema::table('policies', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['total_amount', 'payment_type', 'down_payment', 'installments_count', 'first_due_date', 'issued_at', 'expires_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'role', 'bale_chat_id', 'payment_badge', 'suggested_badge', 'push_subscription']);
        });
    }
};
