<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('policy_number')->unique();
            $table->string('title')->nullable()->default('بیمه‌نامه');
            $table->string('insurance_category')->nullable()->default('ثالث');
            $table->string('vehicle_name')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('no_claim_years')->nullable();
            $table->string('driver_no_claim_years')->nullable();
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->date('started_at')->nullable();
            $table->string('started_at_jalali')->nullable();
            $table->date('issued_at')->nullable();
            $table->string('issued_at_jalali')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('expires_at_jalali')->nullable();
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
