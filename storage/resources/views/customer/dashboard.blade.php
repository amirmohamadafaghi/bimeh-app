<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>پنل کاربری بیمه‌گذار | آفاق بیمه</title>
    <link rel="manifest" href="/manifest.json">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        };
        if (localStorage.getItem('afagh_theme') === 'dark' || (!('afagh_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen pb-16 transition-colors duration-200">

    {{-- هدر بالای سایت به سبک کیف پول من (دقیقاً مطابق علامت‌گذاری تصویر) --}}
    <header class="sticky top-0 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            
            {{-- سمت راست هدر: فقط لوگوی برند (حذف نام و موبایل و کدملی طبق دایره قرمز) --}}
            <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-xl p-1" aria-label="صفحه اصلی آفاق بیمه">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-lg shadow-md shadow-indigo-500/20">
                    آ
                </div>
                <span class="font-black text-base sm:text-lg text-slate-900 dark:text-white tracking-tight">آفاق بیمه</span>
            </a>

            {{-- سمت چپ هدر: چیدمان دقیق ۳ دایره (مشکی: حالت شب/روز | سبز: آیکون اعلان‌ها | آبی: پروفایل در کنج چپ) --}}
            <div class="flex items-center gap-2 sm:gap-2.5">
                
                {{-- ۱. محل دایره مشکی: دکمه حالت شب و روز سایت --}}
                <button
                    type="button"
                    id="themeToggleBtn"
                    onclick="toggleTheme()"
                    aria-label="تغییر حالت نمایش شب و روز"
                    title="تغییر حالت شب و روز"
                    class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-amber-400 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    {{-- آیکون ماه (در حالت روز نمایش داده می‌شود) --}}
                    <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                    {{-- آیکون خورشید (در حالت شب نمایش داده می‌شود) --}}
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M7.05 16.95l-1.414 1.414m12.728 0l-1.414-1.414M7.05 7.05L5.636 5.636M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                    </svg>
                </button>

                {{-- ۲. محل دایره سبز: آیکون اعلان‌ها --}}
                <button
                    type="button"
                    id="notifToggleBtn"
                    onclick="toggleNotificationsPanel()"
                    aria-expanded="false"
                    aria-controls="notificationsCard"
                    aria-label="مشاهده اعلان‌ها و تنظیمات خبررسانی"
                    title="اعلان‌های من"
                    class="relative w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 flex items-center justify-center transition focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($unreadCount > 0)
                        <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 bg-rose-600 text-white text-[11px] font-black rounded-full flex items-center justify-center shadow">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </button>

                {{-- ۳. محل دایره آبی (کنارِ چپِ هدر): دکمه پروفایل مشتری --}}
                <button
                    type="button"
                    id="profileToggleBtn"
                    onclick="openProfileDrawer()"
                    aria-haspopup="dialog"
                    aria-expanded="false"
                    aria-controls="profileDrawer"
                    aria-label="مشاهده پروفایل و سوابق مالی"
                    title="پروفایل کاربری"
                    class="flex items-center gap-2 pl-2.5 pr-1.5 py-1.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-sm">
                        {{ mb_substr($user->name ?: 'م', 0, 1) }}
                    </span>
                    <span class="text-xs font-extrabold hidden sm:inline">{{ $user->name ?: 'پروفایل من' }}</span>
                </button>

            </div>
        </div>
    </header>

    {{-- محتوای اصلی صفحه --}}
    <main class="max-w-6xl mx-auto px-4 sm:px-6 mt-6 space-y-6">

        {{-- بازخورد روشن برای عملیات موفق یا خطا (مدیریت خطا و پیام‌ها) --}}
        @if(session('status'))
            <div role="status" aria-live="polite" class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 font-bold text-xs sm:text-sm flex items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg" aria-hidden="true">✅</span>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:opacity-75 text-xs font-black px-2 py-1" aria-label="بستن پیام">✕</button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div role="alert" aria-live="assertive" class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 font-bold text-xs sm:text-sm flex items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg" aria-hidden="true">⚠️</span>
                    <span>{{ session('error') ?? $errors->first() }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 hover:opacity-75 text-xs font-black px-2 py-1" aria-label="بستن خطا">✕</button>
            </div>
        @endif

        {{-- پیام بازخورد جاوااسکریپت (برای Web Push و غیره) --}}
        <div id="clientFeedbackBox" role="status" aria-live="polite" class="hidden p-4 rounded-2xl border font-bold text-xs sm:text-sm items-center justify-between gap-3 shadow-sm">
            <span id="clientFeedbackText"></span>
            <button type="button" onclick="hideClientFeedback()" class="text-xs font-black px-2 py-1" aria-label="بستن پیام">✕</button>
        </div>

        {{-- کارت بالای سایت: مرکز اعلان‌ها، دکمه خواندن همه، Web Push و لینک مستقیم ربات بله (باز شونده با کلیک روی آیکون اعلان در هدر) --}}
        <section
            id="notificationsCard"
            role="region"
            aria-labelledby="notificationsTitle"
            class="hidden bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border-2 border-emerald-500/40 dark:border-emerald-500/30 shadow-xl space-y-5 transition-all"
        >
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black">🔔</span>
                    <div>
                        <h2 id="notificationsTitle" class="text-base sm:text-lg font-black text-slate-900 dark:text-white">مرکز اعلان‌ها و خبررسانی هوشمند</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">مشاهده هشدارهای دیرکرد، یادآورهای تمدید بیمه‌نامه و تاییدیه پرداخت‌های قبلی</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('customer.notifications.read-all') }}">
                            @csrf
                            <button
                                type="submit"
                                class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-indigo-600 dark:text-indigo-400 text-xs font-extrabold transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            >
                                ✓ خواندن همه ({{ $unreadCount }})
                            </button>
                        </form>
                    @endif
                    <button
                        type="button"
                        onclick="toggleNotificationsPanel()"
                        class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white text-xs font-bold"
                        aria-label="بستن کارت اعلان‌ها"
                    >
                        بستن ✕
                    </button>
                </div>
            </div>

            {{-- دکمه‌های مجزا برای فعال‌سازی Web Push مرورگر و ربات بله --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200/70 dark:border-slate-800">
                <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                    <div>
                        <div class="text-xs font-black text-slate-900 dark:text-white">📲 اعلان مرورگر (Web Push)</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">دریافت آنی سررسید اقساط روی مرورگر موبایل و دسکتاپ</div>
                    </div>
                    <button
                        type="button"
                        id="webPushBtn"
                        onclick="registerWebPush()"
                        class="shrink-0 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        فعال‌سازی Web Push
                    </button>
                </div>

                <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                    <div>
                        <div class="text-xs font-black text-slate-900 dark:text-white">🤖 اعلان خودکار در ربات بله</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">با کلیک و زدن دکمه «شروع / Start» در بله، اعلان‌ها برایتان ارسال می‌شود</div>
                    </div>
                    <a
                        href="{{ $baleBotUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="shrink-0 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        ورود و استارت ربات بله ↗
                    </a>
                </div>
            </div>

            {{-- لیست اعلان‌های دریافتی --}}
            <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                @forelse($notifications as $notif)
                    <div class="p-4 rounded-2xl border transition {{ is_null($notif->read_at) ? 'bg-indigo-50/70 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800' : 'bg-slate-50/70 dark:bg-slate-800/40 border-slate-200/80 dark:border-slate-800' }}">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                @if(is_null($notif->read_at))
                                    <span class="w-2 h-2 rounded-full bg-indigo-600" aria-label="خوانده نشده"></span>
                                @endif
                                <span class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white">{{ $notif->data['title'] ?? 'اعلان سیستم' }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium">{{ $notif->data['created_jalali'] ?? '' }}</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed whitespace-pre-line">{{ $notif->data['message'] ?? '' }}</p>
                    </div>
                @empty
                    <div class="text-center py-8 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                        <div class="text-2xl mb-1" aria-hidden="true">📭</div>
                        <p class="text-xs font-bold text-slate-600 dark:text-slate-300">هنوز هیچ اعلانی برای شما ثبت نشده است.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">هشدارهای سررسید اقساط، تمدید بیمه‌نامه و تاییدیه‌های پرداخت در این بخش نمایش داده می‌شوند.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- کارت شفافیت تعهدات پیش‌رو --}}
        <section class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/90 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <span class="inline-block text-xs font-extrabold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/80 px-3 py-1 rounded-full">
                    شفافیت تعهدات پیش‌رو
                </span>
                <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white mt-2">
                    ۳ ماه آینده: {{ $upcomingCount }} قسط به مبلغ کل {{ number_format($upcomingTotal) }} ریال
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    توجه: طبق مقررات درگاه بیمه ایران، پرداخت هر قسط به صورت مجزا و تک‌قسطی انجام می‌شود.
                </p>
            </div>
            <button
                type="button"
                onclick="openProfileDrawer('billing')"
                class="shrink-0 px-4 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-extrabold transition focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                💳 مشاهده سوابق مالی در پروفایل
            </button>
        </section>

        {{-- بخش خط زمانی بیمه‌نامه‌ها و اقساط + طراحی حالت خالی (Empty State) برای کاربران بدون بیمه‌نامه --}}
        <section aria-labelledby="timelineHeading" class="space-y-5">
            <div class="flex items-center justify-between">
                <h2 id="timelineHeading" class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                    📜 خط زمانی بیمه‌نامه‌ها و اقساط (Policy Timeline)
                </h2>
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                    تعداد کل بیمه‌نامه‌ها: {{ $policies->count() }}
                </span>
            </div>

            @forelse($policies as $policy)
                <article class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/90 dark:border-slate-800 shadow-sm">
                    <div class="flex flex-wrap justify-between items-start gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-extrabold px-2.5 py-1 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                                    {{ $policy->insurer?->name ?? 'بیمه ایران' }}
                                </span>
                                @if($policy->vehicle_name)
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        🚗 {{ $policy->vehicle_name }} {{ $policy->plate_number ? "({$policy->plate_number})" : '' }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-2">
                                {{ $policy->title }} — شماره: <span dir="ltr" class="font-mono text-indigo-600 dark:text-indigo-400">{{ $policy->policy_number }}</span>
                            </h3>
                        </div>
                        <div class="text-right sm:text-left text-xs text-slate-500 dark:text-slate-400 space-y-1">
                            <div>تاریخ صدور: <strong class="text-slate-800 dark:text-slate-200">{{ $policy->issued_at_jalali }}</strong></div>
                            <div>پایان اعتبار: <strong class="text-amber-600 dark:text-amber-400">{{ $policy->expires_at_jalali }}</strong></div>
                        </div>
                    </div>

                    {{-- تایم‌لاین عمودی اقساط --}}
                    <div class="relative border-r-2 border-slate-200 dark:border-slate-700 mr-3 sm:mr-4 pr-5 sm:pr-6 mt-6 space-y-5">
                        
                        {{-- نقطه صدور --}}
                        <div class="relative">
                            <span class="absolute -right-[29px] sm:-right-[33px] top-1 w-4 h-4 rounded-full bg-indigo-600 ring-4 ring-indigo-100 dark:ring-indigo-950"></span>
                            <div class="text-xs sm:text-sm font-extrabold text-indigo-600 dark:text-indigo-400">
                                صدور بیمه‌نامه ({{ $policy->issued_at_jalali }})
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                مبلغ کل: {{ number_format($policy->total_amount) }} ریال
                                @if($policy->down_payment > 0)
                                    | پیش‌پرداخت: {{ number_format($policy->down_payment) }} ریال
                                @endif
                            </div>
                        </div>

                        {{-- لیست اقساط بیمه‌نامه --}}
                        @forelse($policy->installments as $inst)
                            @php
                                $dotColor = match($inst->status) {
                                    'paid' => 'bg-emerald-500 ring-emerald-100 dark:ring-emerald-950',
                                    'awaiting_approval' => 'bg-blue-500 ring-blue-100 dark:ring-blue-950',
                                    'overdue' => 'bg-rose-600 ring-rose-100 dark:ring-rose-950',
                                    default => 'bg-amber-400 ring-amber-100 dark:ring-amber-950',
                                };
                                $badgeClass = match($inst->status) {
                                    'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
                                    'awaiting_approval' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
                                    'overdue' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
                                    default => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                                };
                                $statusLabel = match($inst->status) {
                                    'paid' => 'پرداخت موفق (تایید شده)',
                                    'awaiting_approval' => 'در انتظار تایید ادمین',
                                    'overdue' => 'معوق (سررسید گذشته)',
                                    default => 'در انتظار پرداخت',
                                };
                            @endphp
                            <div class="relative p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <span class="absolute -right-[29px] sm:-right-[33px] top-5 w-4 h-4 rounded-full {{ $dotColor }} ring-4"></span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-black text-xs sm:text-sm text-slate-900 dark:text-white">
                                            قسط شماره {{ $inst->installment_number }} از {{ max(1, $policy->installments_count ?: $policy->installments->count()) }}
                                        </span>
                                        <span class="text-[11px] font-extrabold px-2.5 py-0.5 rounded-full {{ $badgeClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 flex flex-wrap gap-x-4 gap-y-1">
                                        <span>سررسید: <strong class="text-slate-800 dark:text-slate-200">{{ $inst->due_date_jalali }}</strong></span>
                                        <span>مبلغ: <strong class="text-slate-900 dark:text-white">{{ number_format($inst->amount) }} ریال</strong></span>
                                        @if($inst->documented_date)
                                            <span>تاریخ سند وصول: <strong class="text-emerald-600 dark:text-emerald-400">{{ $inst->documented_date }}</strong></span>
                                        @endif
                                    </div>
                                </div>

                                @if(in_array($inst->status, ['pending', 'overdue']))
                                    <a
                                        href="{{ $inst->generateSignedPaymentUrl() }}"
                                        class="shrink-0 text-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                    >
                                        💳 پرداخت تک‌قسطی (درگاه بیمه ایران)
                                    </a>
                                @elseif($inst->status === 'awaiting_approval')
                                    <span class="shrink-0 text-center px-3.5 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-xs font-bold">
                                        ⏳ ثبت شده (منتظر تایید اکسل ادمین)
                                    </span>
                                @else
                                    <span class="shrink-0 text-center px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 text-xs font-bold">
                                        ✓ تسویه شده
                                    </span>
                                @endif
                            </div>
                        @empty
                            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold">
                                این بیمه‌نامه به صورت نقدی صادر شده و فاقد قسط است.
                            </div>
                        @endforelse

                        {{-- نقطه پایان اعتبار --}}
                        <div class="relative">
                            <span class="absolute -right-[29px] sm:-right-[33px] top-1 w-4 h-4 rounded-full bg-slate-400 ring-4 ring-slate-100 dark:ring-slate-900"></span>
                            <div class="text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-400">
                                پایان اعتبار و موعد تمدید ({{ $policy->expires_at_jalali }})
                            </div>
                        </div>

                    </div>
                </article>
            @empty
                {{-- حالت خالی (Empty State) استاندارد برای کاربران بدون بیمه‌نامه --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-10 sm:p-14 border border-dashed border-slate-300 dark:border-slate-800 text-center shadow-sm">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-3xl mb-4" aria-hidden="true">
                        🛡️
                    </div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                        هنوز هیچ بیمه‌نامه‌ای برای کد ملی شما ثبت نشده است
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-2 leading-relaxed">
                        به محض صدور بیمه‌نامه یا بارگذاری لیست اکسل توسط مدیریت آفاق بیمه، مشخصات خودرو، بیمه‌نامه‌ها و جدول اقساط شما به صورت خودکار در این صفحه نمایش داده خواهد شد.
                    </p>
                </div>
            @endforelse
        </section>

    </main>

    {{-- ========================================================================= --}}
    {{-- دراور / مودال جامع پروفایل مشتری (کاملاً غیرقابل ویرایش - فقط نمایش) --}}
    {{-- ========================================================================= --}}
    <div
        id="profileDrawerBackdrop"
        onclick="closeProfileDrawer()"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden transition-opacity"
        aria-hidden="true"
    ></div>

    <aside
        id="profileDrawer"
        role="dialog"
        aria-modal="true"
        aria-labelledby="profileDrawerTitle"
        tabindex="-1"
        class="fixed top-0 left-0 bottom-0 w-full max-w-lg bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 z-50 shadow-2xl overflow-y-auto hidden flex-col justify-between"
    >
        <div class="p-5 sm:p-6 space-y-6">
            
            {{-- هدر پروفایل --}}
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-[11px] font-extrabold text-slate-600 dark:text-slate-300">
                        🔒 فقط قابل مشاهده (غیرقابل ویرایش)
                    </span>
                    <h2 id="profileDrawerTitle" class="text-base font-black text-slate-900 dark:text-white">پروفایل و سوابق مالی بیمه‌گذار</h2>
                </div>
                <button
                    type="button"
                    onclick="closeProfileDrawer()"
                    aria-label="بستن پنجره پروفایل"
                    class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center font-black"
                >
                    ✕
                </button>
            </div>

            {{-- ۱. خلاصه پروفایل (آواتار، نام، ایمیل، تاریخ تولد، تعداد بیمه‌نامه‌ها) --}}
            <section aria-label="خلاصه پروفایل" class="p-4 rounded-2xl bg-gradient-to-l from-indigo-600 to-blue-600 text-white shadow-md">
                <div class="flex items-center gap-4">
                    {{-- آواتار --}}
                    <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-2xl font-black shrink-0 border border-white/30">
                        {{ mb_substr($user->name ?: 'م', 0, 1) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base sm:text-lg font-black truncate">{{ $user->name ?: 'نام ثبت نشده' }}</h3>
                            @if($user->payment_badge === 'good_payer')
                                <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-amber-400 text-amber-950">★ خوش‌حساب</span>
                            @endif
                        </div>
                        <div class="text-xs text-indigo-100 mt-1 truncate" dir="ltr">
                            📧 {{ $user->email ?: 'ایمیل ثبت نشده' }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4 pt-3 border-t border-white/20 text-xs">
                    <div class="bg-white/10 rounded-xl p-2.5">
                        <span class="block text-[10px] text-indigo-100">تاریخ تولد:</span>
                        <strong class="font-extrabold">{{ $user->birth_date ?: 'ثبت نشده' }}</strong>
                    </div>
                    <div class="bg-white/10 rounded-xl p-2.5">
                        <span class="block text-[10px] text-indigo-100">تعداد بیمه‌نامه‌ها:</span>
                        <strong class="font-extrabold">{{ $policies->count() }} بیمه‌نامه</strong>
                    </div>
                </div>
            </section>

            {{-- ۲. فیلدهای هویتی (نام و نام خانوادگی، کد ملی، تاریخ تولد، جنسیت - کاملاً غیرقابل ویرایش) --}}
            <section aria-labelledby="identitySectionTitle" class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 id="identitySectionTitle" class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        🪪 اطلاعات هویتی (غیرقابل ویرایش)
                    </h3>
                    <span class="text-[11px] text-slate-400">نمایش رسمی سامانه</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-800">
                        <span class="block text-[11px] text-slate-400">نام و نام خانوادگی</span>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 select-all">{{ $user->name ?: 'ثبت نشده' }}</div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-800">
                        <span class="block text-[11px] text-slate-400">کد ملی</span>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 font-mono select-all" dir="ltr">{{ $user->national_code ?: 'ثبت نشده' }}</div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-800">
                        <span class="block text-[11px] text-slate-400">تاریخ تولد</span>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white mt-1">{{ $user->birth_date ?: 'ثبت نشده' }}</div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-800">
                        <span class="block text-[11px] text-slate-400">جنسیت</span>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white mt-1">{{ $user->gender_label }}</div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-800">
                        <span class="block text-[11px] text-slate-400">شماره موبایل</span>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 font-mono" dir="ltr">{{ $user->mobile ?: 'ثبت نشده' }}</div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-800">
                        <span class="block text-[11px] text-slate-400">ایمیل</span>
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white mt-1 truncate" dir="ltr">{{ $user->email ?: 'ثبت نشده' }}</div>
                    </div>
                </div>
            </section>

            {{-- ۳. پرداخت‌ها و مالی (Payments & Billing): تاریخچه پرداخت‌ها + پرداخت‌های پیش‌رو --}}
            <section id="profileBillingSection" aria-labelledby="billingSectionTitle" class="space-y-3">
                <h3 id="billingSectionTitle" class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    💳 پرداخت‌ها و مالی (Payments & Billing)
                </h3>

                {{-- تب‌های سوئیچ بین تاریخچه پرداخت‌ها و پرداخت‌های پیش‌رو --}}
                <div class="grid grid-cols-2 gap-2 p-1 rounded-2xl bg-slate-100 dark:bg-slate-800" role="tablist">
                    <button
                        type="button"
                        id="tabBtnUpcoming"
                        role="tab"
                        aria-selected="true"
                        onclick="switchBillingTab('upcoming')"
                        class="py-2 px-3 rounded-xl text-xs font-extrabold transition bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm"
                    >
                        پرداخت‌های پیش‌رو ({{ $upcomingInstallments->count() }})
                    </button>
                    <button
                        type="button"
                        id="tabBtnHistory"
                        role="tab"
                        aria-selected="false"
                        onclick="switchBillingTab('history')"
                        class="py-2 px-3 rounded-xl text-xs font-extrabold transition text-slate-600 dark:text-slate-400"
                    >
                        تاریخچه پرداخت‌ها ({{ $paymentHistory->count() }})
                    </button>
                </div>

                {{-- پنل اول: پرداخت‌های پیش‌رو --}}
                <div id="tabPanelUpcoming" role="tabpanel" class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                    @forelse($upcomingInstallments as $upInst)
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-black text-slate-900 dark:text-white">
                                    قسط {{ $upInst->installment_number }} — <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ $upInst->policy?->policy_number }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    سررسید: <strong>{{ $upInst->due_date_jalali }}</strong> | مبلغ: <strong>{{ number_format($upInst->amount) }} ریال</strong>
                                </div>
                            </div>
                            <span class="shrink-0 px-2.5 py-1 rounded-xl text-[10px] font-extrabold {{ $upInst->status === 'overdue' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                                {{ $upInst->status === 'overdue' ? 'معوق' : 'در انتظار پرداخت' }}
                            </span>
                        </div>
                    @empty
                        <div class="p-6 text-center rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-dashed border-slate-200 dark:border-slate-800 text-xs text-slate-400">
                            هیچ قسط پیش‌رو یا بدهی باز برای شما وجود ندارد.
                        </div>
                    @endforelse
                </div>

                {{-- پنل دوم: تاریخچه پرداخت‌ها با وضعیت موفق / در انتظار تایید --}}
                <div id="tabPanelHistory" role="tabpanel" class="hidden space-y-2.5 max-h-60 overflow-y-auto pr-1">
                    @forelse($paymentHistory as $hisInst)
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-black text-slate-900 dark:text-white">
                                    قسط {{ $hisInst->installment_number }} — <span class="font-mono text-indigo-600 dark:text-indigo-400">{{ $hisInst->policy?->policy_number }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    مبلغ: <strong>{{ number_format($hisInst->documented_amount ?: $hisInst->amount) }} ریال</strong>
                                    @if($hisInst->documented_date)
                                        | تاریخ سند: <strong>{{ $hisInst->documented_date }}</strong>
                                    @elseif($hisInst->paid_at)
                                        | تاریخ: <strong>{{ $hisInst->paid_at_jalali }}</strong>
                                    @endif
                                </div>
                            </div>
                            @if($hisInst->status === 'paid')
                                <span class="shrink-0 px-2.5 py-1 rounded-xl text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    ✓ موفق (تایید شده)
                                </span>
                            @else
                                <span class="shrink-0 px-2.5 py-1 rounded-xl text-[10px] font-extrabold bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                    ⏳ در انتظار تایید
                                </span>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-dashed border-slate-200 dark:border-slate-800 text-xs text-slate-400">
                            هنوز سابقه پرداختی ثبت نشده است.
                        </div>
                    @endforelse
                </div>
            </section>

        </div>

        {{-- ۴. پایان پروفایل: دکمه خروج از حساب کاربری --}}
        <div class="p-5 sm:p-6 border-t border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900">
            <form method="POST" action="{{ route('customer.logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs sm:text-sm shadow-lg shadow-rose-500/20 flex items-center justify-center gap-2 transition focus:outline-none focus:ring-2 focus:ring-rose-500"
                >
                    <span>🚪 خروج از حساب کاربری</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- اسکریپت‌های تعاملی، مدیریت تم، دسترسی‌پذیری (کیبورد Escape) و Web Push --}}
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }

        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('afagh_theme', isDark ? 'dark' : 'light');
        }

        function toggleNotificationsPanel() {
            const card = document.getElementById('notificationsCard');
            const btn = document.getElementById('notifToggleBtn');
            const isHidden = card.classList.contains('hidden');
            if (isHidden) {
                card.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                card.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
            }
        }

        function openProfileDrawer(targetTab = null) {
            const drawer = document.getElementById('profileDrawer');
            const backdrop = document.getElementById('profileDrawerBackdrop');
            const btn = document.getElementById('profileToggleBtn');

            backdrop.classList.remove('hidden');
            drawer.classList.remove('hidden');
            drawer.classList.add('flex');
            btn.setAttribute('aria-expanded', 'true');

            if (targetTab === 'billing') {
                switchBillingTab('upcoming');
                document.getElementById('profileBillingSection')?.scrollIntoView({ behavior: 'smooth' });
            } else {
                drawer.focus();
            }
        }

        function closeProfileDrawer() {
            const drawer = document.getElementById('profileDrawer');
            const backdrop = document.getElementById('profileDrawerBackdrop');
            const btn = document.getElementById('profileToggleBtn');

            backdrop.classList.add('hidden');
            drawer.classList.add('hidden');
            drawer.classList.remove('flex');
            btn.setAttribute('aria-expanded', 'false');
        }

        function switchBillingTab(tab) {
            const upcomingPanel = document.getElementById('tabPanelUpcoming');
            const historyPanel = document.getElementById('tabPanelHistory');
            const upcomingBtn = document.getElementById('tabBtnUpcoming');
            const historyBtn = document.getElementById('tabBtnHistory');

            if (tab === 'upcoming') {
                upcomingPanel.classList.remove('hidden');
                historyPanel.classList.add('hidden');
                upcomingBtn.className = 'py-2 px-3 rounded-xl text-xs font-extrabold transition bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm';
                historyBtn.className = 'py-2 px-3 rounded-xl text-xs font-extrabold transition text-slate-600 dark:text-slate-400';
                upcomingBtn.setAttribute('aria-selected', 'true');
                historyBtn.setAttribute('aria-selected', 'false');
            } else {
                upcomingPanel.classList.add('hidden');
                historyPanel.classList.remove('hidden');
                historyBtn.className = 'py-2 px-3 rounded-xl text-xs font-extrabold transition bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm';
                upcomingBtn.className = 'py-2 px-3 rounded-xl text-xs font-extrabold transition text-slate-600 dark:text-slate-400';
                historyBtn.setAttribute('aria-selected', 'true');
                upcomingBtn.setAttribute('aria-selected', 'false');
            }
        }

        function showClientFeedback(message, isError = false) {
            const box = document.getElementById('clientFeedbackBox');
            const text = document.getElementById('clientFeedbackText');
            text.textContent = message;
            box.className = isError
                ? 'flex p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 font-bold text-xs sm:text-sm items-center justify-between gap-3 shadow-sm'
                : 'flex p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 font-bold text-xs sm:text-sm items-center justify-between gap-3 shadow-sm';
        }

        function hideClientFeedback() {
            document.getElementById('clientFeedbackBox').classList.add('hidden');
        }

        async function registerWebPush() {
            if (!('Notification' in window)) {
                showClientFeedback('مرورگر شما از اعلان‌های Web Push پشتیبانی نمی‌کند.', true);
                return;
            }
            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    showClientFeedback('مجوز نمایش اعلان در مرورگر داده نشد. لطفاً دسترسی Notification را در مرورگر فعال کنید.', true);
                    return;
                }

                const response = await fetch("{{ route('customer.push.subscribe') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ browser: navigator.userAgent, status: 'granted' })
                });

                const resData = await response.json();
                showClientFeedback(resData.message || 'اعلان‌های مرورگر (Web Push) با موفقیت فعال شد.');
            } catch (e) {
                showClientFeedback('خطا در ثبت اشتراک اعلان مرورگر. لطفاً دوباره تلاش کنید.', true);
            }
        }

        // پشتیبانی از دکمه Escape کیبورد برای دسترسی‌پذیری (Accessibility)
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeProfileDrawer();
            }
        });
    </script>
</body>
</html>