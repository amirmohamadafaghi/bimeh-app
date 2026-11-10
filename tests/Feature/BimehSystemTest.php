<?php

namespace Tests\Feature;

use App\Models\Installment;
use App\Models\Insurer;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\Policy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BimehSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        NotificationSetting::current();
        Queue::fake();
    }

    public function test_marks_past_due_pending_installments_as_overdue_and_records_audit_log(): void
    {
        $user = User::factory()->create(['mobile' => '09123334455', 'national_code' => '1234567890']);
        $insurer = Insurer::create(['name' => 'بیمه ایران']);
        $policy = Policy::create([
            'user_id' => $user->id,
            'insurer_id' => $insurer->id,
            'policy_number' => '1405/314/101',
            'title' => 'ثالث تست',
        ]);

        $installment = Installment::create([
            'policy_id' => $policy->id,
            'installment_number' => 1,
            'amount' => 15000000,
            'due_date' => now('Asia/Tehran')->subDays(3)->toDateString(),
            'status' => 'pending',
        ]);

        Artisan::call('bimeh:mark-overdue');

        $this->assertSame('overdue', $installment->fresh()->status);
        $this->assertTrue($installment->audits()->where('new_status', 'overdue')->exists());
    }

    public function test_sends_installment_reminders_idempotently_without_duplicates_on_repeated_runs(): void
    {
        $user = User::factory()->create([
            'mobile' => '09125556677',
            'national_code' => '1122334455',
            'bale_chat_id' => '998877',
        ]);
        $insurer = Insurer::create(['name' => 'بیمه ایران']);
        $policy = Policy::create([
            'user_id' => $user->id,
            'insurer_id' => $insurer->id,
            'policy_number' => '1405/314/202',
            'title' => 'بدنه تست',
        ]);

        Installment::create([
            'policy_id' => $policy->id,
            'installment_number' => 1,
            'amount' => 30000000,
            'due_date' => now('Asia/Tehran')->toDateString(),
            'status' => 'pending',
        ]);

        Artisan::call('bimeh:send-installment-reminders');
        Artisan::call('bimeh:send-installment-reminders');

        $this->assertSame(1, NotificationLog::count());
    }

    public function test_sends_policy_renewal_reminders_15_days_before_expiration(): void
    {
        $user = User::factory()->create([
            'mobile' => '09127778899',
            'national_code' => '5566778899',
            'bale_chat_id' => '112233',
        ]);
        $insurer = Insurer::create(['name' => 'بیمه ایران']);

        Policy::create([
            'user_id' => $user->id,
            'insurer_id' => $insurer->id,
            'policy_number' => '1405/314/303',
            'title' => 'ثالث تمدیدی',
            'expires_at' => now('Asia/Tehran')->addDays(15)->toDateString(),
        ]);

        Artisan::call('bimeh:send-renewal-reminders');

        $this->assertSame(1, NotificationLog::where('type', 'policy_renewal')->count());
    }

    public function test_automatically_assigns_good_payer_and_bad_payer_badges_via_observer(): void
    {
        $user = User::factory()->create(['mobile' => '09129990000', 'national_code' => '9988776655']);
        $insurer = Insurer::create(['name' => 'بیمه ایران']);
        $policy = Policy::create([
            'user_id' => $user->id,
            'insurer_id' => $insurer->id,
            'policy_number' => '1405/314/404',
            'title' => 'تست برچسب',
        ]);

        $i1 = Installment::create([
            'policy_id' => $policy->id,
            'installment_number' => 1,
            'amount' => 10000000,
            'due_date' => now('Asia/Tehran')->subDays(10)->toDateString(),
            'status' => 'pending',
        ]);

        $i2 = Installment::create([
            'policy_id' => $policy->id,
            'installment_number' => 2,
            'amount' => 10000000,
            'due_date' => now('Asia/Tehran')->subDays(5)->toDateString(),
            'status' => 'pending',
        ]);

        $i1->update(['status' => 'overdue']);
        $i2->update(['status' => 'overdue']);

        $this->assertSame('bad_payer', $user->fresh()->payment_badge);

        $i1->update(['status' => 'paid', 'paid_at' => now('Asia/Tehran')->subDays(12)]);
        $i2->update(['status' => 'paid', 'paid_at' => now('Asia/Tehran')->subDays(6)]);

        $this->assertSame('good_payer', $user->fresh()->payment_badge);
    }

    public function test_customer_can_login_with_persian_digits_and_9_digit_national_code(): void
    {
        $user = User::factory()->create([
            'national_code' => '0859738541',
            'mobile' => '09151371442',
            'role' => 'customer',
        ]);

        $response = $this->post(route('customer.login.submit'), [
            'national_code' => '۸۵۹۷۳۸۵۴۱',
            'mobile' => '۰۹۱۵۱۳۷۱۴۴۲',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_signed_single_installment_payment_marks_status_as_awaiting_approval(): void
    {
        $user = User::factory()->create(['national_code' => '0859840417', 'mobile' => '09151371000']);
        $insurer = Insurer::create(['name' => 'بیمه ایران']);
        $policy = Policy::create([
            'user_id' => $user->id,
            'insurer_id' => $insurer->id,
            'policy_number' => '1405/314/60020/1604',
            'title' => 'ثالث پراید',
        ]);

        $installment = Installment::create([
            'policy_id' => $policy->id,
            'installment_number' => 1,
            'amount' => 14600000,
            'due_date' => now('Asia/Tehran')->addDays(10)->toDateString(),
            'status' => 'pending',
        ]);

        $signedUrl = $installment->generateSignedPaymentUrl();

        $this->get($signedUrl)->assertOk();
        $this->assertAuthenticatedAs($user);

        $this->post(route('customer.installments.confirm-pay', $installment))
            ->assertRedirect(route('customer.dashboard'));

        $this->assertSame('awaiting_approval', $installment->fresh()->status);
    }
}
