<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Casts\JalaliDateCast;
use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\User;
use App\Services\BaleBotService;
use App\Support\PersianNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BaleWebhookController extends Controller
{
    public function handle(Request $request, BaleBotService $baleBot): JsonResponse
    {
        // ۱. بررسی امنیتی توکن وب‌هوک
        $configuredSecret = (string) config('services.bale.webhook_secret', env('BALE_WEBHOOK_SECRET', ''));
        if ($configuredSecret !== '') {
            $providedSecret = (string) ($request->header('X-Bale-Webhook-Secret') ?? $request->query('secret', ''));
            if (! hash_equals($configuredSecret, $providedSecret)) {
                return response()->json(['ok' => false, 'error' => 'Unauthorized webhook request'], 403);
            }
        }

        $payload = $request->all();

        // ۲. پردازش کلیک روی دکمه‌های شیشه‌ای (Callback Query)
        if (isset($payload['callback_query']) && is_array($payload['callback_query'])) {
            return $this->handleCallbackQuery($payload['callback_query'], $baleBot);
        }

        $message = $payload['message'] ?? null;
        if (! is_array($message)) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) ($message['chat']['id'] ?? '');
        if ($chatId === '') {
            return response()->json(['ok' => true]);
        }

        // ۳. پردازش اشتراک‌گذاری دکمه شماره موبایل (Contact) با رعایت شرط امنیتی بله
        if (isset($message['contact']) && is_array($message['contact'])) {
            return $this->handleContactShare($chatId, $message, $baleBot);
        }

        $text = trim((string) ($message['text'] ?? ''));
        if ($text === '') {
            return response()->json(['ok' => true]);
        }

        // ۴. بررسی دستور /start و پارامترهای اتصال سریع (Deep Link)
        if (str_starts_with($text, '/start')) {
            return $this->handleStartCommand($chatId, $text, $baleBot);
        }

        // ۵. بررسی وضعیت فعلی چت (آیا کاربر یا ادمین در حال نوشتن پیام پشتیبانی است؟)
        $currentState = Cache::get("bale_state:{$chatId}");
        if (is_string($currentState) && $currentState !== '') {
            return $this->handleStatefulInput($chatId, $text, $currentState, $baleBot);
        }

        // ۶. بررسی اینکه آیا کاربر از قبل به حسابی متصل است یا خیر
        $connectedUser = User::where('bale_chat_id', $chatId)->first();

        if ($connectedUser) {
            // مسیریابی ۳ دکمه ردیف اول منوی اصلی
            if (in_array($text, ['پرداخت سریع قسط', '💳 پرداخت سریع قسط'], true)) {
                return $this->handleQuickInstallmentPay($chatId, $connectedUser, $baleBot);
            }

            if (in_array($text, ['📞 ارتباط با پشتیبان', 'ارتباط با پشتیبان'], true)) {
                Cache::put("bale_state:{$chatId}", 'awaiting_support_message', now()->addMinutes(30));
                $baleBot->sendMessage(
                    $chatId,
                    "📞 لطفاً پیام، سؤال یا درخواست خود را برای پشتیبانی «آفاق بیمه» بنویسید و ارسال کنید:\n(برای لغو، روی دکمه انصراف در پایین صفحه بزنید)",
                    null,
                    $baleBot->cancelSupportKeyboard()
                );

                return response()->json(['ok' => true]);
            }

            if (in_array($text, ['وب اپلیکیشن', '🌐 وب اپلیکیشن'], true)) {
                return $this->handleOpenWebApp($chatId, $connectedUser, $baleBot);
            }
        }

        // ۷. تلاش برای اتصال حساب از طریق کد ملی ۱۰ رقمی یا شماره موبایل تایپ‌شده
        $user = $this->findUserByDigits($text);
        if ($user) {
            $user->update(['bale_chat_id' => $chatId]);
            $baleBot->sendMessage(
                $chatId,
                "✅ حساب شما ({$user->name}) با موفقیت به سامانه اطلاع‌رسانی آفاق بیمه متصل شد.\nاز منوی پایین صفحه می‌توانید خدمات مورد نظر خود را انتخاب کنید:",
                null,
                $baleBot->mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        // اگر کاربر قبلاً متصل بوده ولی متن نامفهومی فرستاده، منوی اصلی را یادآوری کن
        if ($connectedUser) {
            $baleBot->sendMessage(
                $chatId,
                "بیمه‌گذار گرامی {$connectedUser->name}، لطفاً یکی از گزینه‌های منوی پایین صفحه را انتخاب فرمایید:",
                null,
                $baleBot->mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $baleBot->sendMessage(
            $chatId,
            "اطلاعات ارسالی در سامانه یافت نشد. لطفاً کد ملی ۱۰ رقمی یا شماره موبایل خود را به درستی ارسال کنید و یا از دکمه زیر استفاده نمایید:",
            null,
            $baleBot->requestContactKeyboard()
        );

        return response()->json(['ok' => true]);
    }

    private function handleStartCommand(string $chatId, string $text, BaleBotService $baleBot): JsonResponse
    {
        Cache::forget("bale_state:{$chatId}");

        $parts = preg_split('/\s+/', $text, 2);
        $identifier = isset($parts[1]) ? PersianNormalizer::normalize(trim($parts[1])) : '';

        if ($identifier !== '') {
            $user = null;

            if (str_starts_with(strtolower($identifier), 'u_')) {
                $userId = (int) preg_replace('/\D+/', '', $identifier);
                if ($userId > 0) {
                    $user = User::find($userId);
                }
            } else {
                $user = $this->findUserByDigits($identifier);
            }

            if ($user) {
                $user->update(['bale_chat_id' => $chatId]);
                $baleBot->sendMessage(
                    $chatId,
                    "✅ بیمه‌گذار گرامی {$user->name}، حساب کاربری شما در سامانه «آفاق بیمه» با موفقیت به ربات بله متصل شد.\nاز منوی زیر گزینه مورد نظر را انتخاب کنید:",
                    null,
                    $baleBot->mainMenuKeyboard()
                );

                return response()->json(['ok' => true]);
            }
        }

        $existingUser = User::where('bale_chat_id', $chatId)->first();
        if ($existingUser) {
            $baleBot->sendMessage(
                $chatId,
                "سلام {$existingUser->name} عزیز 🌺\nبه ربات «آفاق بیمه» خوش آمدید. لطفاً از منوی زیر خدمت مورد نظر را انتخاب کنید:",
                null,
                $baleBot->mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $baleBot->sendMessage(
            $chatId,
            "به ربات اطلاع‌رسانی «آفاق بیمه» (نمایندگی آفاقی) خوش آمدید.\nلطفاً برای اتصال حساب خود، کد ملی ۱۰ رقمی یا شماره موبایل ثبت‌شده خود را ارسال نمایید یا روی دکمه زیر بزنید:",
            null,
            $baleBot->requestContactKeyboard()
        );

        return response()->json(['ok' => true]);
    }

    private function handleContactShare(string $chatId, array $message, BaleBotService $baleBot): JsonResponse
    {
        $contact = $message['contact'];
        $fromId = $message['from']['id'] ?? null;
        $contactUserId = $contact['user_id'] ?? null;

        if ($fromId !== null && $contactUserId !== null && (string) $fromId !== (string) $contactUserId) {
            $baleBot->sendMessage(
                $chatId,
                "⛔ خطای امنیتی: لطفاً فقط شماره موبایل متعلق به حساب خودتان را با استفاده از دکمه پایین صفحه ارسال نمایید."
            );

            return response()->json(['ok' => true]);
        }

        $phone = (string) ($contact['phone_number'] ?? '');
        $user = $this->findUserByDigits($phone);

        if ($user) {
            $user->update(['bale_chat_id' => $chatId]);
            $baleBot->sendMessage(
                $chatId,
                "✅ بیمه‌گذار گرامی {$user->name}، شماره موبایل شما تایید شد و حساب شما با موفقیت به «آفاق بیمه» متصل گردید.",
                null,
                $baleBot->mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $baleBot->sendMessage(
            $chatId,
            "⚠️ کاربری با این شماره موبایل در سامانه یافت نشد. لطفاً کد ملی ۱۰ رقمی خود را تایپ و ارسال نمایید."
        );

        return response()->json(['ok' => true]);
    }

    private function handleQuickInstallmentPay(string $chatId, User $user, BaleBotService $baleBot): JsonResponse
    {
        $installments = Installment::with('policy')
            ->whereHas('policy', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->take(3)
            ->get();

        if ($installments->isEmpty()) {
            $baleBot->sendMessage(
                $chatId,
                "🎉 بیمه‌گذار گرامی {$user->name}، در حال حاضر هیچ قسط پرداخت‌نشده‌ای در پرونده شما وجود ندارد و تمامی اقساط تسویه شده‌اند.",
                null,
                $baleBot->mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        foreach ($installments as $installment) {
            $amountRial = number_format((int) $installment->amount);
            $amountToman = number_format((int) floor(((int) $installment->amount) / 10));
            $policyTitle = $installment->policy?->title ?? 'بیمه‌نامه';
            $policyNumber = $installment->policy?->policy_number ?? '—';
            $paymentId = $installment->payment_identifier ?: 'ثبت نشده';
            $statusLabel = $installment->status === 'awaiting_approval'
                ? '⏳ در انتظار تایید ادمین'
                : '💳 آماده پرداخت';

            $cardText = "📋 قبض قسط شماره {$installment->installment_number}\n"
                ."━━━━━━━━━━━━━━\n"
                ."🔹 بیمه‌نامه: {$policyTitle} ({$policyNumber})\n"
                ."📅 سررسید: {$installment->due_date_jalali}\n"
                ."💰 مبلغ: {$amountRial} ریال ({$amountToman} تومان)\n"
                ."🔢 شناسه پرداخت:\n{$paymentId}\n"
                ."📌 وضعیت: {$statusLabel}\n"
                ."━━━━━━━━━━━━━━\n"
                ."💡 راهنما: با لمس دکمه اول، وارد قبض دیجیتال و درگاه بیمه ایران شوید. پس از واریز، دکمه دوم را بزنید:";

            $signedUrl = $installment->generateSignedPaymentUrl();

            $inlineButtons = [
                [
                    [
                        'text' => '۱. مشاهده قبض و ورود به درگاه بیمه ایران ↗',
                        'url' => $signedUrl,
                    ],
                ],
            ];

            if ($installment->status !== 'awaiting_approval') {
                $inlineButtons[] = [
                    [
                        'text' => '✓ ۲. پرداخت را انجام دادم (ثبت تایید)',
                        'callback_data' => "confirm_pay:{$installment->id}",
                    ],
                ];
            }

            $baleBot->sendMessage($chatId, $cardText, null, [
                'inline_keyboard' => $inlineButtons,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    private function handleOpenWebApp(string $chatId, User $user, BaleBotService $baleBot): JsonResponse
    {
        $dashboardUrl = route('customer.dashboard');

        $buttonAction = str_starts_with($dashboardUrl, 'https://')
            ? ['text' => '🌐 ورود به وب اپلیکیشن آفاق بیمه (مینی‌اپ)', 'web_app' => ['url' => $dashboardUrl]]
            : ['text' => '🌐 ورود به وب اپلیکیشن آفاق بیمه', 'url' => $dashboardUrl];

        $baleBot->sendMessage(
            $chatId,
            "🌐 {$user->name} عزیز، برای مشاهده کامل بیمه‌نامه‌ها، تقویم اقساط و سوابق پرداخت، روی دکمه زیر کلیک کنید:",
            null,
            [
                'inline_keyboard' => [
                    [$buttonAction],
                ],
            ]
        );

        return response()->json(['ok' => true]);
    }

    private function handleStatefulInput(string $chatId, string $text, string $state, BaleBotService $baleBot): JsonResponse
    {
        if ($text === '❌ انصراف و بازگشت به منوی اصلی' || $text === '/cancel') {
            Cache::forget("bale_state:{$chatId}");
            $baleBot->sendMessage(
                $chatId,
                "عملیات لغو شد. به منوی اصلی بازگشتید:",
                null,
                $baleBot->mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if ($state === 'awaiting_support_message') {
            Cache::forget("bale_state:{$chatId}");
            $user = User::where('bale_chat_id', $chatId)->first();

            if (! $user) {
                return response()->json(['ok' => true]);
            }

            $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($admins as $admin) {
                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => 'support_message',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $admin->id,
                    'data' => json_encode([
                        'title' => "پیام پشتیبانی از {$user->name}",
                        'message' => $text,
                        'related_id' => $user->id,
                        'created_jalali' => JalaliDateCast::toJalali(now(), 'Y/m/d H:i'),
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $adminChatIds = $admins->pluck('bale_chat_id')->filter()->unique()->values()->all();
            $envAdminChatId = (string) config('services.bale.admin_chat_id', '');
            if ($envAdminChatId !== '' && ! in_array($envAdminChatId, $adminChatIds, true)) {
                $adminChatIds[] = $envAdminChatId;
            }

            $adminAlertText = "📩 پیام جدید از بیمه‌گذار در بله:\n"
                ."👤 نام: {$user->name}\n"
                ."📱 موبایل: {$user->mobile}\n"
                ."🆔 کد ملی: {$user->national_code}\n"
                ."━━━━━━━━━━━━━━\n"
                ."💬 متن پیام:\n{$text}";

            foreach ($adminChatIds as $adminChatId) {
                try {
                    $baleBot->sendMessage((string) $adminChatId, $adminAlertText, null, [
                        'inline_keyboard' => [
                            [
                                [
                                    'text' => "✉️ پاسخ مستقیم به {$user->name}",
                                    'callback_data' => "reply_user:{$user->id}",
                                ],
                            ],
                        ],
                    ]);
                } catch (\Throwable) {
                }
            }

            $baleBot->sendMessage(
                $chatId,
                "✅ پیام شما با موفقیت برای پشتیبانی «آفاق بیمه» ارسال شد. همکاران ما در اسرع وقت پاسخگوی شما خواهند بود.",
                null,
                $baleBot->mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if (str_starts_with($state, 'admin_reply:')) {
            Cache::forget("bale_state:{$chatId}");
            $targetUserId = (int) str_replace('admin_reply:', '', $state);
            $targetUser = User::find($targetUserId);

            if ($targetUser && ! empty($targetUser->bale_chat_id)) {
                $replyText = "👨‍💼 پاسخ پشتیبانی «آفاق بیمه»:\n"
                    ."━━━━━━━━━━━━━━\n"
                    .$text;

                $baleBot->sendMessage((string) $targetUser->bale_chat_id, $replyText, null, $baleBot->mainMenuKeyboard());

                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => 'support_reply',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $targetUser->id,
                    'data' => json_encode([
                        'title' => 'پاسخ پشتیبانی آفاق بیمه',
                        'message' => $text,
                        'related_id' => $targetUser->id,
                        'created_jalali' => JalaliDateCast::toJalali(now(), 'Y/m/d H:i'),
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $baleBot->sendMessage(
                    $chatId,
                    "✅ پاسخ شما با موفقیت برای بیمه‌گذار ({$targetUser->name}) در بله و پنل وب ارسال شد.",
                    null,
                    $baleBot->mainMenuKeyboard()
                );
            } else {
                $baleBot->sendMessage($chatId, "⚠️ متاسفانه شناسه بله این کاربر یافت نشد.", null, $baleBot->mainMenuKeyboard());
            }

            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => true]);
    }

    private function handleCallbackQuery(array $callbackQuery, BaleBotService $baleBot): JsonResponse
    {
        $callbackId = (string) ($callbackQuery['id'] ?? '');
        $data = (string) ($callbackQuery['data'] ?? '');
        $message = $callbackQuery['message'] ?? [];
        $chatId = (string) ($message['chat']['id'] ?? '');
        $messageId = (int) ($message['message_id'] ?? 0);

        if ($chatId === '' || $data === '') {
            return response()->json(['ok' => true]);
        }

        if (str_starts_with($data, 'confirm_pay:')) {
            $installmentId = (int) str_replace('confirm_pay:', '', $data);
            $user = User::where('bale_chat_id', $chatId)->first();
            $installment = Installment::with('policy')->find($installmentId);

            if (! $user || ! $installment || $installment->policy?->user_id !== $user->id) {
                $baleBot->answerCallbackQuery($callbackId, 'این قسط یافت نشد یا متعلق به شما نیست.', true);

                return response()->json(['ok' => true]);
            }

            if ($installment->status !== 'paid') {
                $installment->update([
                    'status' => 'awaiting_approval',
                    'followed_up_at' => now(),
                ]);
            }

            $baleBot->answerCallbackQuery($callbackId, '✅ با موفقیت ثبت شد و در انتظار تایید ادمین قرار گرفت.');

            if ($messageId > 0) {
                $updatedText = ($message['text'] ?? '')
                    ."\n\n✅ وضعیت جدید: اعلام پرداخت شما ثبت شد و در انتظار تایید نهایی ادمین است.";
                $baleBot->editMessageText($chatId, $messageId, $updatedText);
            }

            return response()->json(['ok' => true]);
        }

        if (str_starts_with($data, 'reply_user:')) {
            $targetUserId = (int) str_replace('reply_user:', '', $data);
            $targetUser = User::find($targetUserId);

            if ($targetUser) {
                Cache::put("bale_state:{$chatId}", "admin_reply:{$targetUser->id}", now()->addMinutes(30));
                $baleBot->answerCallbackQuery($callbackId, "آماده دریافت پاسخ برای {$targetUser->name}");
                $baleBot->sendMessage(
                    $chatId,
                    "✍️ لطفاً متن پاسخ خود را برای بیمه‌گذار «{$targetUser->name}» بنویسید و ارسال کنید:",
                    null,
                    $baleBot->cancelSupportKeyboard()
                );
            }

            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => true]);
    }

    private function findUserByDigits(string $input): ?User
    {
        $cleanDigits = preg_replace('/\D+/', '', PersianNormalizer::normalize($input)) ?? '';
        if (strlen($cleanDigits) < 8 || strlen($cleanDigits) > 14) {
            return null;
        }

        $normalizedNational = PersianNormalizer::cleanNationalCode($cleanDigits);
        $normalizedMobile = PersianNormalizer::cleanMobile($cleanDigits);

        return User::where('national_code', $cleanDigits)
            ->orWhere('national_code', $normalizedNational)
            ->orWhere('mobile', $cleanDigits)
            ->orWhere('mobile', $normalizedMobile)
            ->first();
    }
}