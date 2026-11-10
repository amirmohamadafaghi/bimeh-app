<x-filament-widgets::widget>
    <style>
        .exec-dashboard-wrap {
            direction: rtl;
            font-family: inherit;
        }
        /* بنر بالای صفحه */
        .exec-hero-card {
            background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
            border-radius: 1.25rem;
            padding: 1.5rem 2rem;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            margin-bottom: 1.75rem;
        }
        .exec-hero-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .exec-hero-inner {
                flex-direction: row;
                gap: 1.5rem;
            }
        }
        .exec-hero-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            justify-content: flex-start;
        }
        .exec-hero-center {
            flex: 1;
            text-align: center;
            font-size: 1.65rem;
            font-weight: 900;
            color: #ffffff;
            white-space: nowrap;
        }
        .exec-hero-left {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .exec-date-numeric {
            font-size: 1.85rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.05em;
            color: #ffffff;
        }

        /* شبکه دو ستونه گزارشات */
        .exec-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
            align-items: start;
        }
        @media (min-width: 1024px) {
            .exec-grid-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* کارت‌های اصلی گزارشات */
        .exec-panel {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        }
        .dark .exec-panel {
            background-color: #111827;
            border-color: #1f2937;
            box-shadow: 0 8px 14px -3px rgba(0, 0, 0, 0.35);
        }
        .exec-panel-header {
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            cursor: pointer;
            user-select: none;
        }
        .exec-panel-header:hover {
            background-color: #f8fafc;
        }
        .dark .exec-panel-header:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }
        .exec-header-right {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-width: 0;
        }
        .exec-title {
            font-size: 0.92rem;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            line-height: 1.5;
            gap: 0.5rem;
        }
        .dark .exec-title {
            color: #f8fafc;
        }
        .exec-time-badge {
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
            background: #f1f5f9;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
        }
        .dark .exec-time-badge {
            color: #94a3b8;
            background: rgba(255,255,255,0.05);
        }

        /* آیکون‌ها */
        .exec-header-icon {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            max-width: 28px !important;
            object-fit: contain;
            flex-shrink: 0;
        }
        .exec-section-icon {
            width: 26px !important;
            height: 26px !important;
            min-width: 26px !important;
            max-width: 26px !important;
            object-fit: contain;
            flex-shrink: 0;
        }
        .exec-calendar-icon {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            max-width: 38px !important;
            object-fit: contain;
            flex-shrink: 0;
        }
        .exec-toggle-wrap {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            max-width: 18px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.25s ease;
        }
        .exec-toggle-wrap.is-open {
            transform: rotate(180deg);
        }
        .exec-toggle-icon {
            width: 18px !important;
            height: 18px !important;
            max-width: 18px !important;
            max-height: 18px !important;
            object-fit: contain;
            display: block;
        }

        /* بدنه و ردیف‌های داخل هر کارت */
        .exec-panel-body {
            padding: 0 1.25rem 1.25rem 1.25rem;
        }
        .exec-stat-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }
        .exec-stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.8rem 1rem;
            border-radius: 0.75rem;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
        }
        .dark .exec-stat-row {
            background-color: #161f30;
            border-color: #26334d;
        }
        .exec-stat-label {
            font-size: 0.84rem;
            font-weight: 700;
            color: #1e293b;
        }
        .dark .exec-stat-label {
            color: #e2e8f0;
        }
        .exec-badge {
            padding: 0.25rem 0.65rem;
            border-radius: 0.45rem;
            font-size: 0.78rem;
            font-weight: 800;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .header-badge-indigo {
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 0.1rem 0.55rem;
            border-radius: 0.35rem;
            font-weight: 800;
            font-size: 0.9rem;
            margin: 0 0.35rem;
        }
        .dark .header-badge-indigo {
            background-color: rgba(67, 56, 202, 0.4);
            color: #818cf8;
        }

        /* رنگ‌بندی نشانگرها */
        .badge-indigo { background: #eef2ff; color: #4338ca; }
        .badge-sky    { background: #e0f2fe; color: #0369a1; }
        .badge-emerald{ background: #d1fae5; color: #047857; }
        .badge-rose   { background: #ffe4e6; color: #be123c; }
        .badge-amber  { background: #fef3c7; color: #b45309; }

        .dark .badge-indigo { background: rgba(99, 102, 241, 0.15); color: #818cf8; }
        .dark .badge-sky    { background: rgba(14, 165, 233, 0.15); color: #38bdf8; }
        .dark .badge-emerald{ background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .dark .badge-rose   { background: rgba(244, 63, 94, 0.15);  color: #fb7185; }
        .dark .badge-amber  { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
    </style>

    <div class="exec-dashboard-wrap">

        {{-- ۱. کارت تاریخ بالای صفحه --}}
        <div class="exec-hero-card">
            <div class="exec-hero-inner">
                <div class="exec-hero-right">
                    <img src="{{ asset('images/icons/app_calendar.svg') }}" width="38" height="38" class="exec-calendar-icon" alt="تقویم" />
                    <span class="text-base md:text-lg font-extrabold text-white whitespace-nowrap">{{ $todayJalaliText }}</span>
                </div>
                <div class="exec-hero-center">
                    خوش آمدید
                </div>
                <div class="exec-hero-left">
                    <span class="exec-date-numeric font-mono" dir="ltr">
                        {{ $todayJalaliNumeric }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ۲. بخش گزارشات --}}
        <div class="space-y-3">
            <div class="flex items-center gap-2 px-1 mb-2">
                <img src="{{ asset('images/icons/app_reports.svg') }}" width="26" height="26" class="exec-section-icon" alt="گزارشات" />
                <h2 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">گزارشات</h2>
            </div>

            <div class="exec-grid-2">

                {{-- کارت اعلان‌ها --}}
                <div x-data="{ openNotif: true }" class="exec-panel">
                    <div @click="openNotif = !openNotif" class="exec-panel-header">
                        <div class="exec-header-right">
                            <img src="{{ asset('images/icons/app_notifications.svg') }}" width="28" height="28" class="exec-header-icon" alt="اعلان‌ها" />
                            <div class="exec-title">
                                <span>ارسال اعلان و پیام برای</span>
                                <span class="header-badge-indigo">{{ number_format($notificationReport['total_users']) }}</span>
                                <span>بیمه‌گذار</span>
                            </div>
                        </div>
                        <div class="exec-toggle-wrap" :class="{ 'is-open': openNotif }">
                            <img src="{{ asset('images/icons/app_more_details.svg') }}" width="18" height="18" class="exec-toggle-icon" alt="جزئیات" />
                        </div>
                    </div>

                    <div x-show="openNotif" x-transition class="exec-panel-body">
                        <div class="exec-stat-list">
                            <div class="exec-stat-row">
                                <span class="exec-stat-label">سررسید اقساط روز جاری (ارسال اعلان امروز)</span>
                                <span class="exec-badge badge-indigo">{{ number_format($notificationReport['today_count']) }} فقره</span>
                            </div>
                            <div class="exec-stat-row">
                                <span class="exec-stat-label">یادآوری سررسید اقساط (۲ روز مانده به سررسید)</span>
                                <span class="exec-badge badge-sky">{{ number_format($notificationReport['two_days_count']) }} فقره</span>
                            </div>
                            <div class="exec-stat-row">
                                <span class="exec-stat-label">یادآوری سررسید اقساط (۷ روز مانده به سررسید)</span>
                                <span class="exec-badge badge-emerald">{{ number_format($notificationReport['seven_days_count']) }} فقره</span>
                            </div>
                            <div class="exec-stat-row">
                                <span class="exec-stat-label">اخطار اقساط معوق و دیرکرد پرداخت</span>
                                <span class="exec-badge badge-rose">{{ number_format($notificationReport['overdue_count']) }} فقره</span>
                            </div>
                            <div class="exec-stat-row">
                                <span class="exec-stat-label">یادآور تمدید بیمه‌نامه‌های در آستانه انقضا (۱۰ روز آینده)</span>
                                <span class="exec-badge badge-amber">{{ number_format($notificationReport['expiring_10_days_count']) }} بیمه‌نامه</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- کارت آخرین گزارش اکسل --}}
                <div class="space-y-4">
                    @if (!empty($excelReport))
                        <div x-data="{ openExcel: true }" class="exec-panel">
                            <div @click="openExcel = !openExcel" class="exec-panel-header">
                                <div class="exec-header-right">
                                    <img src="{{ asset('images/icons/app_upload_file.svg') }}" width="28" height="28" class="exec-header-icon" alt="اکسل" />
                                    <div class="exec-title">
                                        گزارش آخرین همگام‌سازی اکسل
                                        <span class="exec-time-badge">{{ $excelReport['time_jalali'] }}</span>
                                    </div>
                                </div>

                                <div class="exec-toggle-wrap" :class="{ 'is-open': openExcel }">
                                    <img src="{{ asset('images/icons/app_more_details.svg') }}" width="18" height="18" class="exec-toggle-icon" alt="جزئیات" />
                                </div>
                            </div>

                            <div x-show="openExcel" x-transition class="exec-panel-body">
                                <div class="exec-stat-list">
                                    <div class="exec-stat-row">
                                        <span class="exec-stat-label">کاربران جدید ثبت‌شده</span>
                                        <span class="exec-badge badge-emerald">{{ number_format($excelReport['new_users_count']) }} کاربر</span>
                                    </div>
                                    <div class="exec-stat-row">
                                        <span class="exec-stat-label">کاربران بروزرسانی‌شده</span>
                                        <span class="exec-badge badge-indigo">{{ number_format($excelReport['updated_users_count']) }} کاربر</span>
                                    </div>
                                    <div class="exec-stat-row">
                                        <span class="exec-stat-label">بیمه‌نامه‌های ثبت/بروزشده</span>
                                        <span class="exec-badge badge-sky">{{ number_format($excelReport['updated_policies_count']) }} مورد</span>
                                    </div>
                                    <div class="exec-stat-row">
                                        <span class="exec-stat-label">اقساط ثبت/وصول‌شده</span>
                                        <span class="exec-badge badge-amber">{{ number_format($excelReport['updated_installments_count']) }} مورد</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div x-data="{ openEmptyExcel: true }" class="exec-panel">
                            <div @click="openEmptyExcel = !openEmptyExcel" class="exec-panel-header">
                                <div class="exec-header-right">
                                    <img src="{{ asset('images/icons/app_upload_file.svg') }}" width="28" height="28" class="exec-header-icon" alt="اکسل" />
                                    <div class="exec-title">
                                        گزارش پردازش فایل‌های اکسل
                                    </div>
                                </div>

                                <div class="exec-toggle-wrap" :class="{ 'is-open': openEmptyExcel }">
                                    <img src="{{ asset('images/icons/app_more_details.svg') }}" width="18" height="18" class="exec-toggle-icon" alt="جزئیات" />
                                </div>
                            </div>

                            <div x-show="openEmptyExcel" x-transition class="exec-panel-body">
                                <div class="exec-stat-list">
                                    <div class="exec-stat-row">
                                        <span class="exec-stat-label">تاکنون فایلی آپلود نشده است</span>
                                        <span class="exec-badge badge-emerald">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</x-filament-widgets::widget>