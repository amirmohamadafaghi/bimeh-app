<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Installment;
use App\Models\Policy;
use App\Models\User;
use App\Services\NotificationDispatcherService;
use App\Support\PersianNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BimehSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_persian_normalizer_converts_digits_and_mobile(): void
    {
        $this->assertSame('09123456789', PersianNormalizer::cleanMobile('۰۹۱۲۳۴۵۶۷۸۹'));
        $this->assertSame('09123456789', PersianNormalizer::cleanMobile('989123456789'));
        $this->assertSame('0012345678', PersianNormalizer::cleanNationalCode('۱۲۳۴۵۶۷۸'));
    }

    public function test_notification_dispatcher_enforces_idempotency(): void
    {
        $user = User::factory()->create([
            'bale_chat_id' => '123456',
        ]);
        $policy = Policy::factory()->create(['user_id' => $user->id]);
        $installment = Installment::factory()->create(['policy_id' => $policy->id]);

        $dispatcher = app(NotificationDispatcherService::class);

        $firstLog = $dispatcher->dispatch(
            $user,
            'installment_reminder',
            (int) $installment->id,
            -3,
            'پیام تست اول'
        );

        $secondLog = $dispatcher->dispatch(
            $user,
            'installment_reminder',
            (int) $installment->id,
            -3,
            'پیام تست تکراری'
        );

        $this->assertNotNull($firstLog);
        $this->assertNull($secondLog);
    }

    public function test_bale_webhook_rejects_empty_digit_binding(): void
    {
        User::factory()->create([
            'national_code' => null,
            'mobile'        => '09121112233',
        ]);

        $response = $this->postJson('/webhook/bale', [
            'message' => [
                'chat' => ['id' => '998877'],
                'text' => 'سلام',
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('users', [
            'bale_chat_id' => '998877',
        ]);
    }
}