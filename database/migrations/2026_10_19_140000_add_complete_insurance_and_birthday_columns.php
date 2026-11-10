<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('mobile');
            }
            if (! Schema::hasColumn('users', 'policyholder_type')) {
                $table->string('policyholder_type')->default('حقیقی')->after('role');
            }
            if (! Schema::hasColumn('users', 'payment_badge')) {
                $table->string('payment_badge')->default('none')->after('policyholder_type');
            }
            if (! Schema::hasColumn('users', 'suggested_badge')) {
                $table->string('suggested_badge')->default('none')->after('payment_badge');
            }
            if (! Schema::hasColumn('users', 'average_delay_days')) {
                $table->integer('average_delay_days')->default(0)->after('suggested_badge');
            }
            if (! Schema::hasColumn('users', 'profile_title')) {
                $table->string('profile_title')->nullable()->after('average_delay_days');
            }
            if (! Schema::hasColumn('users', 'birth_date')) {
                $table->string('birth_date')->nullable()->after('profile_title');
            }
            if (! Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('birth_date');
            }
            if (! Schema::hasColumn('users', 'bale_chat_id')) {
                $table->string('bale_chat_id')->nullable()->after('address');
            }
            if (! Schema::hasColumn('users', 'preferred_notification_hour')) {
                $table->unsignedTinyInteger('preferred_notification_hour')->default(10)->after('bale_chat_id');
            }
            if (! Schema::hasColumn('users', 'permissions')) {
                $table->json('permissions')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('insurers', function (Blueprint $table) {
            if (! Schema::hasColumn('insurers', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (! Schema::hasColumn('insurers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('code');
            }
        });

        Schema::table('policies', function (Blueprint $table) {
            if (! Schema::hasColumn('policies', 'insurance_category')) {
                $table->string('insurance_category')->nullable()->default('ثالث');
            }
            if (! Schema::hasColumn('policies', 'vehicle_name')) {
                $table->string('vehicle_name')->nullable();
            }
            if (! Schema::hasColumn('policies', 'plate_number')) {
                $table->string('plate_number')->nullable();
            }
            if (! Schema::hasColumn('policies', 'vehicle_model')) {
                $table->string('vehicle_model')->nullable();
            }
            if (! Schema::hasColumn('policies', 'no_claim_years')) {
                $table->string('no_claim_years')->nullable();
            }
            if (! Schema::hasColumn('policies', 'driver_no_claim_years')) {
                $table->string('driver_no_claim_years')->nullable();
            }
            if (! Schema::hasColumn('policies', 'initial_payment')) {
                $table->unsignedBigInteger('initial_payment')->default(0)->after('total_amount');
            }
            if (! Schema::hasColumn('policies', 'started_at')) {
                $table->date('started_at')->nullable();
            }
            if (! Schema::hasColumn('policies', 'started_at_jalali')) {
                $table->string('started_at_jalali')->nullable();
            }
            if (! Schema::hasColumn('policies', 'issued_at_jalali')) {
                $table->string('issued_at_jalali')->nullable();
            }
            if (! Schema::hasColumn('policies', 'expires_at_jalali')) {
                $table->string('expires_at_jalali')->nullable();
            }
            if (! Schema::hasColumn('policies', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('installments', function (Blueprint $table) {
            if (! Schema::hasColumn('installments', 'paid_amount')) {
                $table->unsignedBigInteger('paid_amount')->nullable()->default(0);
            }
            if (! Schema::hasColumn('installments', 'payment_type')) {
                $table->string('payment_type')->nullable();
            }
            if (! Schema::hasColumn('installments', 'payment_identifier')) {
                $table->string('payment_identifier')->nullable();
            }
            if (! Schema::hasColumn('installments', 'documented_amount')) {
                $table->unsignedBigInteger('documented_amount')->nullable();
            }
            if (! Schema::hasColumn('installments', 'documented_date')) {
                $table->string('documented_date')->nullable();
            }
            if (! Schema::hasColumn('installments', 'profile_title')) {
                $table->string('profile_title')->nullable();
            }
            if (! Schema::hasColumn('installments', 'due_date_jalali')) {
                $table->string('due_date_jalali')->nullable();
            }
            if (! Schema::hasColumn('installments', 'tracking_code')) {
                $table->string('tracking_code')->nullable();
            }
            if (! Schema::hasColumn('installments', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        if (Schema::hasTable('notification_settings')) {
            Schema::table('notification_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('notification_settings', 'birthday_greeting_enabled')) {
                    $table->boolean('birthday_greeting_enabled')->default(true);
                }
                if (! Schema::hasColumn('notification_settings', 'birthday_template')) {
                    $table->text('birthday_template')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('notification_settings')) {
            Schema::table('notification_settings', function (Blueprint $table) {
                $cols = array_filter(['birthday_greeting_enabled', 'birthday_template'], fn ($c) => Schema::hasColumn('notification_settings', $c));
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::table('installments', function (Blueprint $table) {
            $cols = array_filter(['payment_type', 'due_date_jalali'], fn ($c) => Schema::hasColumn('installments', $c));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('policies', function (Blueprint $table) {
            $cols = array_filter(['initial_payment'], fn ($c) => Schema::hasColumn('policies', $c));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter(['phone', 'policyholder_type', 'average_delay_days', 'permissions'], fn ($c) => Schema::hasColumn('users', $c));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
