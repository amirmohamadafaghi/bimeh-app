<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Casts\JalaliDateCast;
use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\SystemActivity;
use App\Models\User;
use App\Services\BaleBotService;
use App\Support\PersianNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BaleWebhookController extends Controller
{
    public function handle(Request $request, BaleBotService $baleService): JsonResponse
    {
        $expectedSecret = (string) config('services.bale.webhook_secret', '');

        if ($expectedSecret === '' || $expectedSecret === 'mock') {
            // در حالت توسعه یا عدم تنظیم توکن وبهوک، بررسی هدر انجام نمی‌شود
        } else {
            $providedSecret = (string) ($request->header('X-Bale-Bot-Api-Secret-Token') ?? $request->query('secret', ''));
            if (hash_equals($expectedSecret, $providedSecret)) {
                // توکن امنیتی وبهوک معتبر است
            } else {
                return response()->json(['ok' => false, 'error' => 'Unauthorized webhook secret'], 403);
            }
        }

        $payload = $request->all();

        if (isset($payload['callback_query']) && is_array($payload['callback_query'])) {
            return $this->handleCallbackQuery($payload['callback_query'], $baleService);
        }

        $message = $payload['message'] ?? $payload['edited_message'] ?? null;

        if ($message === null || is_array($message) === false) {
            return response()->json(['ok' => true]);
        }

        $chatId = isset($message['chat']['id']) ? (string) $message['chat']['id'] : null;
        if ($chatId === null || $chatId === '') {
            return response()->json(['ok' => true]);
        }

        $contactPhone = $message['contact']['phone_number'] ?? null;
        if (empty($contactPhone)) {
            // مخاطب از دکمه اشتراک‌گذاری شماره استفاده نکرده است
        } else {
            return $this->handlePhoneBinding($chatId, (string) $contactPhone, $baleService);
        }

        $rawText = trim((string) ($message['text'] ?? ''));
        $text = PersianNormalizer::normalizeText($rawText);

        $supportState = Cache::get("bale_support_mode_{$chatId}") === true;
        if ($supportState === true && strlen($text) > 0) {
            if (str_starts_with($text, '/')) {
                Cache::forget("bale_support_mode_{$chatId}");
            } else {
                if ($text === '❌ انصراف و بازگشت به منوی اصلی' || $text === 'انصراف') {
                    Cache::forget("bale_support_mode_{$chatId}");
                    $baleService->sendMessage(
                        $chatId,
                        'درخواست ارتباط با پشتیبان لغو شد. یکی از گزینه‌های منوی اصلی را انتخاب کنید:',
                        null,
                        $baleService->mainMenuKeyboard()
                    );

                    return response()->json(['ok' => true]);
                }

                $user = User::where('bale_chat_id', $chatId)->first();
                $senderName = $user instanceof User ? ($user->name ?: $user->mobile) : "کاربر بله ({$chatId})";

                SystemActivity::create([
                    'actor_id'   => $user?->id,
                    'event_type' => 'bale_support_message',
                    'title'      => "پیام پشتیبانی جدید در بله از طرف {$senderName}",
                    'icon'       => 'app_notifications',
                    'color'      => 'amber',
                    'details'    => [
                        'chat_id'     => $chatId,
                        'mobile'      => $user?->mobile,
                        'description' => $text,
                    ],
                ]);

                Cache::forget("bale_support_mode_{$chatId}");

                $baleService->sendMessage(
                    $chatId,
                    "✅ پیام شما با موفقیت برای کارشناسان پشتیبانی نمایندگی آفاقی ثبت شد.\nهمکاران ما در اسرع وقت پاسخگوی شما خواهند بود.",
                    null,
                    $baleService->mainMenuKeyboard()
                );

                return response()->json(['ok' => true]);
            }
        }

        if (preg_match('/^\/start\s+u_(\d+)$/i', $text, $matches)) {
            $userId = (int) $matches[1];
            $user = User::find($userId);
            if ($user instanceof User) {
                $user->update(['bale_chat_id' => $chatId]);
                $baleService->sendMessage(
                    $chatId,
                    "سلام {$user->name} عزیز 🌺\nحساب کاربری شما در سامانه «آفاق بیمه» با موفقیت به ربات بله متصل شد.\nاز طریق دکمه‌های زیر می‌توانید به خدمات سریع دسترسی داشته باشید:",
                    null,
                    $baleService->mainMenuKeyboard()
                );

                return response()->json(['ok' => true]);
            }
        }

        $user = User::where('bale_chat_id', $chatId)->first();

        if ($text === 'وب اپلیکیشن' || $text === '🌐 وب اپلیکیشن') {
            $portalUrl = route('customer.dashboard');
            if ($user instanceof User) {
                $baleService->sendMessage(
                    $chatId,
                    "🌐 {$user->name} عزیز، جهت ورود مستقیم به پرتال جامع بیمه‌گذاران و مشاهده بیمه‌نامه‌ها و تقویم اقساط روی دکمه زیر کلیک کنید:",
                    null,
                    [
                        'inline_keyboard' => [
                            [
                                ['text' => '🚀 ورود به پرتال من در آفاق بیمه', 'url' => $portalUrl],
                            ],
                        ],
                    ]
                );
            } else {
                $baleService->sendMessage(
                    $chatId,
                    "🌐 جهت ورود به وب‌اپلیکیشن آفاق بیمه روی دکمه زیر کلیک کنید:\n(توجه: برای دریافت خودکار قبوض در بله، لطفاً شماره موبایل یا کد ملی خود را نیز در همین چت ارسال نمایید)",
                    null,
                    [
                        'inline_keyboard' => [
                            [
                                ['text' => '🌐 ورود به وب‌اپلیکیشن آفاق بیمه', 'url' => $portalUrl],
                            ],
                        ],
                    ]
                );
            }

            return response()->json(['ok' => true]);
        }

        if ($text === 'پرداخت سریع قسط' || $text === '💳 پرداخت سریع قسط') {
            if ($user instanceof User) {
                return $this->sendInteractiveInstallmentsMenu($chatId, $user, $baleService);
            }

            $baleService->sendMessage(
                $chatId,
                '⚠️ حساب کاربری شما هنوز به ربات متصل نشده است. لطفاً ابتدا شماره موبایل یا کد ملی ۱۰ رقمی خود را ارسال فرمایید تا اقساط شما نمایش داده شود.',
                null,
                $baleService->requestContactKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if ($text === '📞 ارتباط با پشتیبان' || $text === 'ارتباط با پشتیبان') {
            if ($user instanceof User) {
                Cache::put("bale_support_mode_{$chatId}", true, now()->addMinutes(15));
                $baleService->sendMessage(
                    $chatId,
                    "✍️ لطفاً متن پیام، سؤال یا درخواست خود را بنویسید و ارسال کنید.\nپیام شما مستقیماً در پنل مدیریت نمایندگی آفاقی ثبت خواهد شد:",
                    null,
                    $baleService->cancelSupportKeyboard()
                );

                return response()->json(['ok' => true]);
            }

            $baleService->sendMessage(
                $chatId,
                'لطفاً ابتدا شماره موبایل یا کد ملی خود را ارسال کنید تا هویت شما در سیستم شناسایی شود، سپس پیام خود را برای پشتیبان ارسال فرمایید.',
                null,
                $baleService->requestContactKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $digitsOnly = preg_replace('/[^0-9]/', '', $text) ?? '';
        if (strlen($digitsOnly) >= 8 && strlen($digitsOnly) <= 13) {
            return $this->handlePhoneBinding($chatId, $digitsOnly, $baleService);
        }

        if ($text === '/start') {
            if ($user instanceof User) {
                $baleService->sendMessage(
                    $chatId,
                    "سلام {$user->name} گرامی 🌺\nبه ربات هوشمند «آفاق بیمه» خوش آمدید.\nلطفاً یکی از گزینه‌های زیر را انتخاب فرمایید:",
                    null,
                    $baleService->mainMenuKeyboard()
                );

                return response()->json(['ok' => true]);
            }

            $baleService->sendMessage(
                $chatId,
                "سلام! به ربات رسمی «آفاق بیمه» (نمایندگی آفاقی - کد ۳۱۱۶) خوش آمدید. 🌺\n\nبرای فعال‌سازی یادآور خودکار اقساط و دسترسی به خدمات، لطفاً **شماره موبایل** یا **کد ملی** خود را تایپ و ارسال کنید (یا روی دکمه زیر بزنید):",
                null,
                $baleService->requestContactKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if ($user instanceof User) {
            $baleService->sendMessage(
                $chatId,
                "همراه گرامی {$user->name}، لطفاً یکی از گزینه‌های منوی زیر را انتخاب فرمایید:",
                null,
                $baleService->mainMenuKeyboard()
            );
        } else {
            $baleService->sendMessage(
                $chatId,
                'سلام! برای استفاده از خدمات ربات آفاق بیمه، لطفاً شماره موبایل یا کد ملی خود را ارسال نمایید.',
                null,
                $baleService->requestContactKeyboard()
            );
        }

        return response()->json(['ok' => true]);
    }

    protected function handleCallbackQuery(array $callbackQuery, BaleBotService $baleService): JsonResponse
    {
        $callbackId = (string) ($callbackQuery['id'] ?? '');
        $data = (string) ($callbackQuery['data'] ?? '');
        $message = $callbackQuery['message'] ?? [];
        $chatId = isset($message['chat']['id']) ? (string) $message['chat']['id'] : '';
        $messageId = (int) ($message['message_id'] ?? 0);

        if ($chatId === '') {
            return response()->json(['ok' => true]);
        }

        $user = User::where('bale_chat_id', $chatId)->first();
        if ($user === null) {
            if (strlen($callbackId) > 0) {
                $baleService->answerCallbackQuery($callbackId, 'ابتدا حساب کاربری خود را متصل کنید.', true);
            }

            return response()->json(['ok' => true]);
        }

        if ($data === 'back_to_installments_list') {
            if (strlen($callbackId) > 0) {
                $baleService->answerCallbackQuery($callbackId);
            }

            return $this->sendInteractiveInstallmentsMenu($chatId, $user, $baleService, $messageId);
        }

        if (preg_match('/^select_inst_(\d+)$/', $data, $m)) {
            $instId = (int) $m[1];
            $inst = Installment::with('policy')
                ->whereHas('policy', fn ($q) => $q->where('user_id', $user->id))
                ->find($instId);

            if ($inst instanceof Installment) {
                if (strlen($callbackId) > 0) {
                    $baleService->answerCallbackQuery($callbackId);
                }

                $dueJalali = JalaliDateCast::toJalali($inst->due_date);
                $amountFormatted = number_format((int) $inst->amount);
                $policyTitle = $inst->policy?->vehicle_name ?: ($inst->policy?->title ?: 'بیمه‌نامه');
                $policyNumber = $inst->policy?->policy_number ?? '—';
                $statusFa = $inst->status === 'overdue' ? '🔴 معوق' : '🟡 در انتظار پرداخت';

                $detailText = "📄 جزئیات قسط انتخاب‌شده:\n\n"
                    . "🔹 بیمه‌نامه: {$policyTitle}\n"
                    . "🔹 شماره بیمه‌نامه: {$policyNumber}\n"
                    . "🔹 شماره قسط: قسط {$inst->installment_number}\n"
                    . "🔹 تاریخ سررسید: {$dueJalali}\n"
                    . "🔹 وضعیت: {$statusFa}\n"
                    . "💰 مبلغ قابل پرداخت: {$amountFormatted} ریال\n\n"
                    . 'جهت مشاهده قبض دیجیتال و ثبت پرداخت، روی دکمه زیر کلیک کنید:';

                $inlineKeyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => "💳 پرداخت آنلاین قسط ({$amountFormatted} ریال)",
                                'url'  => $inst->generateSignedPaymentUrl(),
                            ],
                        ],
                        [
                            [
                                'text'          => '🔙 بازگشت به لیست اقساط',
                                'callback_data' => 'back_to_installments_list',
                            ],
                        ],
                    ],
                ];

                if ($messageId > 0) {
                    $baleService->editMessageText($chatId, $messageId, $detailText, $inlineKeyboard);
                } else {
                    $baleService->sendMessage($chatId, $detailText, null, $inlineKeyboard);
                }

                return response()->json(['ok' => true]);
            }

            if (strlen($callbackId) > 0) {
                $baleService->answerCallbackQuery($callbackId, 'این قسط یافت نشد یا قبلاً تسویه شده است.', true);
            }

            return response()->json(['ok' => true]);
        }

        if (strlen($callbackId) > 0) {
            $baleService->answerCallbackQuery($callbackId);
        }

        return response()->json(['ok' => true]);
    }

    protected function sendInteractiveInstallmentsMenu(
        string $chatId,
        User $user,
        BaleBotService $baleService,
        int $editMessageId = 0
    ): JsonResponse {
        $installments = Installment::with('policy')
            ->whereHas('policy', fn ($q) => $q->where('user_id', $user->id))
            ->whereIn('status', ['overdue', 'pending'])
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        if ($installments->isEmpty()) {
            $msg = "✅ {$user->name} عزیز، شما در حال حاضر هیچ قسط معوق یا در انتظار پرداختی ندارید. از خوش‌حسابی شما سپاسگزاریم! 🌺";
            if ($editMessageId > 0) {
                $baleService->editMessageText($chatId, $editMessageId, $msg);
            } else {
                $baleService->sendMessage($chatId, $msg, null, $baleService->mainMenuKeyboard());
            }

            return response()->json(['ok' => true]);
        }

        $buttons = [];
        foreach ($installments as $inst) {
            $dueJalali = JalaliDateCast::toJalali($inst->due_date);
            $amountFormatted = number_format((int) $inst->amount);
            $badge = $inst->status === 'overdue' ? '🔴 معوق' : '🟡 قسط ' . $inst->installment_number;
            $vehicle = $inst->policy?->vehicle_name ?: 'بیمه‌نامه';

            $buttons[] = [
                [
                    'text'          => "{$badge} | {$vehicle} | {$dueJalali} | {$amountFormatted} ریال",
                    'callback_data' => "select_inst_{$inst->id}",
                ],
            ];
        }

        $headerText = "💳 لیست اقساط پیش‌رو و معوق {$user->name}:\n\nلطفاً روی قسط مورد نظر خود کلیک کنید تا قبض و لینک پرداخت مستقیم آن باز شود:";
        $markup = ['inline_keyboard' => $buttons];

        if ($editMessageId > 0) {
            $baleService->editMessageText($chatId, $editMessageId, $headerText, $markup);
        } else {
            $baleService->sendMessage($chatId, $headerText, null, $markup);
        }

        return response()->json(['ok' => true]);
    }

    protected function handlePhoneBinding(string $chatId, string $rawInput, BaleBotService $baleService): JsonResponse
    {
        $user = $this->findUserByDigits($rawInput);

        if ($user instanceof User) {
            $user->update(['bale_chat_id' => $chatId]);

            $baleService->sendMessage(
                $chatId,
                "✅ {$user->name} عزیز، حساب کاربری شما در «آفاق بیمه» با موفقیت شناسایی و به ربات بله متصل شد.\n\nاز این پس یادآور اقساط و لینک پرداخت مستقیم برای شما ارسال خواهد شد. همچنین می‌توانید از منوی زیر استفاده کنید:",
                null,
                $baleService->mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $baleService->sendMessage(
            $chatId,
            "❌ کاربری با این شماره موبایل یا کد ملی در سامانه آفاق بیمه یافت نشد.\nلطفاً کد ملی ۱۰ رقمی یا شماره موبایل ثبت‌شده در بیمه‌نامه خود را با دقت ارسال نمایید."
        );

        return response()->json(['ok' => true]);
    }

    protected function findUserByDigits(string $rawInput): ?User
    {
        $digits = PersianNormalizer::cleanNumber($rawInput);
        if (strlen($digits) < 8) {
            return null;
        }

        $mobileNormalized = PersianNormalizer::cleanMobile($rawInput);
        $nationalNormalized = PersianNormalizer::cleanNationalCode($rawInput);
        $trimmedNational = ltrim($digits, '0');

        return User::query()
            ->where(function ($query) use ($mobileNormalized, $nationalNormalized, $digits, $trimmedNational): void {
                $query->where('national_code', $digits)
                    ->orWhere('mobile', $digits);

                if (strlen($mobileNormalized) >= 10) {
                    $query->orWhere('mobile', $mobileNormalized);
                }

                if (strlen($nationalNormalized) >= 8) {
                    $query->orWhere('national_code', $nationalNormalized)
                        ->orWhere('national_code', str_pad($nationalNormalized, 10, '0', STR_PAD_LEFT));
                }

                if (strlen($trimmedNational) >= 8) {
                    $query->orWhere('national_code', $trimmedNational);
                }
            })
            ->first();
    }
}