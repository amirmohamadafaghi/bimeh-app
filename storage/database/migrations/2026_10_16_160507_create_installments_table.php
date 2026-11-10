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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('installment_number')->default(1);
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedBigInteger('paid_amount')->nullable()->default(0);
            $table->unsignedBigInteger('documented_amount')->nullable();
            $table->string('documented_date')->nullable();
            $table->string('payment_identifier')->nullable();
            $table->string('profile_title')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('status')->default('pending');
            $table->string('tracking_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
