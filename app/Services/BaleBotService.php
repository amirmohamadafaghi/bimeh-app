<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BaleBotService
{
    public function sendMessage(
        ?string $chatId,
        string $text,
        ?string $signedPaymentUrl = null,
        ?array $customReplyMarkup = null
    ): bool {
        if (empty($chatId)) {
            throw new RuntimeException('شناسه چت بله (bale_chat_id) برای این مشتری ثبت نشده است.');
        }

        $token = (string) config('services.bale.bot_token', 'mock');

        if ($token === '' || $token === 'mock') {
            if ($chatId === 'fail_test') {
                throw new RuntimeException('خطای شبیه‌سازی‌شده عدم دسترسی به سرور بله.');
            }

            return true;
        }

        $payload = [
            'chat_id' => $chatId,
            'text'    => $text,
        ];

        if (is_array($customReplyMarkup)) {
            $payload['reply_markup'] = json_encode($customReplyMarkup, JSON_UNESCAPED_UNICODE);
        } elseif (is_string($signedPaymentUrl) && strlen($signedPaymentUrl) > 0) {
            $payload['reply_markup'] = json_encode([
                'inline_keyboard' => [
                    [
                        [
                            'text' => '💳 پرداخت مستقیم این قسط (قبض دیجیتال)',
                            'url'  => $signedPaymentUrl,
                        ],
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE);
        }

        $response = Http::timeout(10)->post("https://tapi.bale.ai/bot{$token}/sendMessage", $payload);

        if ($response->successful()) {
            return true;
        }

        throw new RuntimeException('خطای API بله: ' . $response->body());
    }

    public function editMessageText(string $chatId, int $messageId, string $text, ?array $replyMarkup = null): bool
    {
        $token = (string) config('services.bale.bot_token', 'mock');
        if ($token === '' || $token === 'mock') {
            return true;
        }

        $payload = [
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'text'       => $text,
        ];

        if (is_array($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup, JSON_UNESCAPED_UNICODE);
        }

        $response = Http::timeout(10)->post("https://tapi.bale.ai/bot{$token}/editMessageText", $payload);

        return $response->successful();
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): bool
    {
        $token = (string) config('services.bale.bot_token', 'mock');
        if ($token === '' || $token === 'mock') {
            return true;
        }

        $response = Http::timeout(10)->post("https://tapi.bale.ai/bot{$token}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
            'show_alert'        => $showAlert,
        ]);

        return $response->successful();
    }

    public function mainMenuKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => 'وب اپلیکیشن'],
                    ['text' => '📞 ارتباط با پشتیبان'],
                    ['text' => 'پرداخت سریع قسط'],
                ],
            ],
            'resize_keyboard'   => true,
            'one_time_keyboard' => false,
        ];
    }

    public function requestContactKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '📱 اشتراک‌گذاری سریع شماره موبایل', 'request_contact' => true],
                ],
            ],
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
        ];
    }

    public function cancelSupportKeyboard(): array
    {
        return [
            'keyboard' => [
                [
                    ['text' => '❌ انصراف و بازگشت به منوی اصلی'],
                ],
            ],
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
        ];
    }
}