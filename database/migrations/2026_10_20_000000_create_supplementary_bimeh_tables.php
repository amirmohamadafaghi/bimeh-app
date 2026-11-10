<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations (تکمیل ساختار ۲۱ جدولی دیتابیس).
     */
    public function up(): void
    {
        // جدول ۱۷: دسته‌بندی و رشته‌های بیمه (ثالث، بدنه، عمر، آتش‌سوزی و ...)
        if (! Schema::hasTable('insurance_categories')) {
            Schema::create('insurance_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // جدول ۱۸: اطلاعات خودروها و اموال مورد بیمه
        if (! Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('vehicle_name');
                $table->string('plate_number')->nullable()->index();
                $table->string('chassis_number')->nullable();
                $table->string('motor_number')->nullable();
                $table->string('production_year')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // جدول ۱۹: تراکنش‌ها و پرداخت‌های ثبت‌شده برای اقساط
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('installment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('amount')->default(0);
                $table->string('payment_method')->default('online');
                $table->string('tracking_code')->nullable();
                $table->string('reference_id')->nullable();
                $table->string('status')->default('completed');
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        // جدول ۲۰: تاریخچه بارگذاری و پردازش فایل‌های اکسل
        if (! Schema::hasTable('excel_imports')) {
            Schema::create('excel_imports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('file_name');
                $table->string('file_path')->nullable();
                $table->unsignedInteger('created_users_count')->default(0);
                $table->unsignedInteger('updated_users_count')->default(0);
                $table->unsignedInteger('updated_policies_count')->default(0);
                $table->unsignedInteger('updated_installments_count')->default(0);
                $table->string('status')->default('completed');
                $table->timestamps();
            });
        }

        // جدول ۲۱: اسناد، تصاویر چک‌ها و پیوست‌های پرونده بیمه‌گذار
        if (! Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('policy_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('installment_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('file_path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('excel_imports');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('insurance_categories');
    }
};
