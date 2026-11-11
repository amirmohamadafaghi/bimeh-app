<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Casts\JalaliDateCast;
use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\NotificationLog;
use App\Models\Policy;
use App\Models\SystemActivity;
use App\Models\User;
use App\Support\PersianNormalizer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $common = $this->getCommonPortalData($user);

        $messages = config('customer_welcome.messages', [
            'همراه گرامی :name، به پرتال هوشمند آفاق بیمه خوش آمدید.',
        ]);
        $rawWelcome = $messages[array_rand($messages)] ?? 'به پرتال آفاق بیمه خوش آمدید.';
        $welcomeMessage = str_replace(':name', $user->name ?: 'بیمه‌گذار گرامی', $rawWelcome);

        $calendar = $this->buildCurrentJalaliMonthCalendar($common['policies']);

        return view('customer.dashboard', array_merge($common, [
            'welcomeMessage' => $welcomeMessage,
            'calendar'       => $calendar,
        ]));
    }

    public function policies(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $common = $this->getCommonPortalData($user);

        return view('customer.policies', $common);
    }

    public function notifications(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $common = $this->getCommonPortalData($user);
        $filter = $request->query('filter', 'all') === 'unread' ? 'unread' : 'all';

        $dbNotifsQuery = $filter === 'unread'
            ? $user->unreadNotifications()
            : $user->notifications();

        $dbNotifs = $dbNotifsQuery->latest()->get()->map(function ($n): array {
            return [
                'id'             => (string) $n->id,
                'title'          => $n->data['title'] ?? 'اعلان سیستم آفاق بیمه',
                'message'        => $n->data['message'] ?? '',
                'created_jalali' => $n->data['created_jalali'] ?? JalaliDateCast::toJalali($n->created_at, 'Y/m/d H:i'),
                'created_at'     => $n->created_at,
                'is_unread'      => $n->read_at === null,
                'type_label'     => 'پیام سامانه',
            ];
        });

        $logs = NotificationLog::where('user_id', $user->id)
            ->latest()
            ->limit(100)
            ->get()
            ->map(function (NotificationLog $log): array {
                $typeLabel = match ($log->type) {
                    'installment_reminder'      => 'یادآور قسط',
                    'policy_renewal'            => 'تمدید بیمه‌نامه',
                    'birthday_greeting'         => 'تبریک تولد',
                    'custom_message', 'custom'  => 'پیام مدیریت',
                    default                     => 'اعلان سیستم',
                };

                return [
                    'id'             => 'log-' . $log->id,
                    'title'          => $typeLabel,
                    'message'        => (string) $log->message,
                    'created_jalali' => JalaliDateCast::toJalali($log->created_at, 'Y/m/d H:i'),
                    'created_at'     => $log->created_at,
                    'is_unread'      => false,
                    'type_label'     => $typeLabel,
                ];
            });

        $merged = $dbNotifs->concat($logs)
            ->unique(fn ($item): string => trim((string) $item['message']) . '|' . substr((string) $item['created_jalali'], 0, 10))
            ->sortByDesc('created_at')
            ->values();

        if ($filter === 'unread') {
            $merged = $merged->where('is_unread', true)->values();
        }

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $merged->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedNotifications = new LengthAwarePaginator(
            $currentItems,
            $merged->count(),
            $perPage,
            $currentPage,
            [
                'path'  => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('customer.notifications', array_merge($common, [
            'paginatedNotifications' => $paginatedNotifications,
            'currentFilter'          => $filter,
        ]));
    }

    public function profile(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $common = $this->getCommonPortalData($user);

        return view('customer.profile', $common);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'birth_date' => ['nullable', 'string', 'max:20'],
            'gender'     => ['nullable', 'in:male,female'],
            'address'    => ['nullable', 'string', 'max:500'],
        ], [
            'gender.in'   => 'مقدار جنسیت انتخاب‌شده معتبر نیست.',
            'address.max' => 'آدرس واردشده نباید بیشتر از ۵۰۰ کاراکتر باشد.',
        ]);

        $updates = [];

        if (array_key_exists('birth_date', $validated)) {
            $updates['birth_date'] = PersianNormalizer::normalizeText(trim((string) $validated['birth_date'])) ?: null;
        }

        if (array_key_exists('gender', $validated)) {
            $updates['gender'] = $validated['gender'] ?: null;
        }

        if (Schema::hasColumn('users', 'address') && array_key_exists('address', $validated)) {
            $updates['address'] = trim((string) $validated['address']) ?: null;
        }

        $user->forceFill($updates)->save();

        return redirect()
            ->route('customer.profile')
            ->with('status', 'اطلاعات پروفایل شما با موفقیت بروزرسانی شد.');
    }

    public function contact(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $common = $this->getCommonPortalData($user);

        return view('customer.contact', $common);
    }

    public function markAllNotificationsRead(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return back()->with('status', 'تمامی اعلان‌ها به عنوان خوانده‌شده علامت‌گذاری شدند.');
    }

    public function savePushSubscription(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (Schema::hasColumn('users', 'push_subscription')) {
            $user->forceFill([
                'push_subscription' => $request->all(),
            ])->save();
        }

        return response()->json([
            'ok'      => true,
            'message' => 'اعلان‌های مرورگر با موفقیت برای حساب شما فعال شد.',
        ]);
    }

    public function requestPolicyRenewal(Request $request, Policy $policy): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ((int) $policy->user_id === (int) $user->id) {
            SystemActivity::create([
                'actor_id'   => $user->id,
                'event_type' => 'renewal_requested',
                'title'      => "درخواست تمدید آنلاین بیمه‌نامه ({$policy->policy_number}) توسط {$user->name}",
                'icon'       => 'app_policies',
                'color'      => 'amber',
                'details'    => [
                    'policy_id'     => $policy->id,
                    'policy_number' => $policy->policy_number,
                    'customer_name' => $user->name,
                    'mobile'        => $user->mobile,
                ],
            ]);

            return back()->with('status', 'درخواست تمدید بیمه‌نامه شما ثبت شد. کارشناسان نمایندگی آفاقی به زودی با شما تماس خواهند گرفت.');
        }

        abort(403);
    }

    public function signedSingleInstallmentPay(Request $request, Installment $installment, User $user): View
    {
        $installment->loadMissing('policy.user');

        if ((int) $installment->policy?->user_id === (int) $user->id) {
            return view('customer.single-pay', [
                'installment' => $installment,
                'user'        => $user,
            ]);
        }

        abort(403, 'این لینک پرداخت متعلق به حساب کاربری مشخص‌شده نیست.');
    }

    public function confirmSingleInstallmentPay(Request $request, Installment $installment): RedirectResponse
    {
        $installment->loadMissing('policy.user');

        if ($installment->status === 'paid') {
            // قسط از قبل پرداخت شده است
        } else {
            $installment->update([
                'status' => 'awaiting_approval',
            ]);

            SystemActivity::create([
                'actor_id'   => $installment->policy?->user_id,
                'event_type' => 'installment_awaiting_approval',
                'title'      => "اعلام پرداخت قسط شماره {$installment->installment_number} بیمه‌نامه {$installment->policy?->policy_number}",
                'icon'       => 'app_installments',
                'color'      => 'info',
                'details'    => [
                    'installment_id' => $installment->id,
                    'amount'         => $installment->amount,
                ],
            ]);
        }

        return back()->with('status', 'پرداخت شما ثبت شد و در وضعیت «در انتظار تایید ادمین» قرار گرفت.');
    }

    protected function getCommonPortalData(User $user): array
    {
        $policies = Policy::with(['installments' => fn ($q) => $q->orderBy('installment_number')])
            ->where('user_id', $user->id)
            ->latest('issued_at')
            ->get();

        $allInstallments = $policies->flatMap(fn (Policy $p) => $p->installments);
        $today = now('Asia/Tehran')->startOfDay();
        $threeMonthsLater = $today->copy()->addDays(90)->endOfDay();

        $upcomingThreeMonths = $allInstallments->filter(function (Installment $inst) use ($threeMonthsLater): bool {
            if (in_array($inst->status, ['pending', 'overdue'], true) && $inst->due_date instanceof Carbon) {
                return $inst->due_date->lte($threeMonthsLater);
            }

            return false;
        });

        $upcomingInstallments = $allInstallments
            ->whereIn('status', ['pending', 'overdue'])
            ->sortBy('due_date')
            ->values();

        $paymentHistory = $allInstallments
            ->whereIn('status', ['paid', 'awaiting_approval'])
            ->sortByDesc('updated_at')
            ->values();

        $botUsername = trim((string) config('services.bale.bot_username', 'afagh_bimeh_bot'), '@');
        $baleBotUrl = "https://ble.ir/{$botUsername}?start=u_{$user->id}";

        return [
            'user'                 => $user,
            'policies'             => $policies,
            'unreadCount'          => $user->unreadNotifications()->count(),
            'notifications'        => $user->notifications()->latest()->limit(20)->get(),
            'upcomingCount'        => $upcomingThreeMonths->count(),
            'upcomingTotal'        => (int) $upcomingThreeMonths->sum('amount'),
            'upcomingInstallments' => $upcomingInstallments,
            'paymentHistory'       => $paymentHistory,
            'baleBotUrl'           => $baleBotUrl,
        ];
    }

    protected function buildCurrentJalaliMonthCalendar($policies): array
    {
        $todayCarbon = now('Asia/Tehran')->startOfDay();
        $todayJalaliStr = PersianNormalizer::normalizeText(JalaliDateCast::toJalali($todayCarbon, 'Y/m/d'));
        $parts = explode('/', $todayJalaliStr);

        $jYear = (int) ($parts[0] ?? 1405);
        $jMonth = (int) ($parts[1] ?? 1);
        $jDay = (int) ($parts[2] ?? 1);

        $monthNames = [
            1  => 'فروردین',
            2  => 'اردیبهشت',
            3  => 'خرداد',
            4  => 'تیر',
            5  => 'مرداد',
            6  => 'شهریور',
            7  => 'مهر',
            8  => 'آبان',
            9  => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        $daysInMonth = match (true) {
            $jMonth <= 6  => 31,
            $jMonth <= 11 => 30,
            default       => $this->isJalaliLeapYear($jYear) ? 30 : 29,
        };

        $firstDayCarbon = $todayCarbon->copy()->subDays($jDay - 1);
        $startDayOfWeek = ($firstDayCarbon->dayOfWeek + 1) % 7;

        $installmentsByDay = [];
        foreach ($policies as $policy) {
            foreach ($policy->installments as $inst) {
                if (in_array($inst->status, ['pending', 'overdue'], true)) {
                    $instJalali = PersianNormalizer::normalizeText((string) $inst->due_date_jalali);
                    $instParts = explode('/', $instJalali);
                    if (count($instParts) === 3) {
                        $iy = (int) $instParts[0];
                        $im = (int) $instParts[1];
                        $id = (int) $instParts[2];
                        if ($iy === $jYear && $im === $jMonth) {
                            $installmentsByDay[$id][] = [
                                'id'                 => $inst->id,
                                'installment_number' => $inst->installment_number,
                                'policy_title'       => $policy->title,
                                'policy_number'      => $policy->policy_number,
                                'amount'             => (int) $inst->amount,
                                'status'             => $inst->status,
                                'pay_url'            => $inst->generateSignedPaymentUrl(),
                            ];
                        }
                    }
                }
            }
        }

        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayInsts = $installmentsByDay[$d] ?? [];
            $hasInstallment = count($dayInsts) > 0;
            $isToday = ($d === $jDay);

            $state = 'normal';
            if ($isToday && $hasInstallment) {
                $state = 'red';
            } elseif ($isToday) {
                $state = 'blue';
            } elseif ($hasInstallment) {
                $state = 'yellow';
            }

            $days[] = [
                'day'             => $d,
                'is_today'        => $isToday,
                'has_installment' => $hasInstallment,
                'state'           => $state,
                'installments'    => $dayInsts,
            ];
        }

        return [
            'year'              => $jYear,
            'month'             => $jMonth,
            'month_name'        => $monthNames[$jMonth] ?? '',
            'today_day'         => $jDay,
            'today_full_jalali' => $todayJalaliStr,
            'start_offset'      => $startDayOfWeek,
            'days_in_month'     => $daysInMonth,
            'days'              => $days,
        ];
    }

    protected function isJalaliLeapYear(int $jy): bool
    {
        $rem = ($jy + 2346) % 2820;

        return ((($rem + 38) * 31) % 128) < 31;
    }
}