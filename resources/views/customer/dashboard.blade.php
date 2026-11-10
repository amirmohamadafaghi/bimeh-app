@extends('customer.layouts.app')

@section('title', 'داشبورد | آفاق بیمه')
@section('page_title', 'داشبورد')

@push('styles')
<style>
    /* ۱. نوار خوش‌آمدگویی مینیمال (هم‌سبک با بخش راهنما) */
    .welcome-minimal-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.85rem;
        padding: 1rem 1.2rem;
    }

    /* ۲. چیدمان دو ستونه برای تقویم مینیمال + خلاصه کاربردی ماه جاری */
    .dashboard-main-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.1rem;
    }
    @media (min-width: 920px) {
        .dashboard-main-grid {
            grid-template-columns: 340px 1fr;
            align-items: stretch;
        }
    }

    /* ویجت تقویم مینیمال و جمع‌وجور */
    .mini-cal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        padding-bottom: 0.65rem;
        border-bottom: 1px solid var(--border-subtle);
    }
    .mini-cal-weekdays {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.25rem;
        text-align: center;
        margin-bottom: 0.35rem;
    }
    .mini-cal-weekday {
        font-size: 0.68rem;
        font-weight: 800;
        color: var(--text-muted);
        padding: 0.2rem 0;
    }
    .mini-cal-days {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.3rem;
    }
    .mini-cal-day {
        height: 36px;
        border-radius: 0.6rem;
        border: 1px solid var(--border-subtle);
        background: var(--bg-subtle);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 800;
        position: relative;
        user-select: none;
        transition: all 0.15s ease;
    }
    .mini-cal-day.empty {
        background: transparent;
        border-color: transparent;
    }

    /* روز جاری بدون قسط: آبی مینیمال */
    .mini-cal-day.state-blue {
        background: rgba(37, 99, 235, 0.22) !important;
        border-color: #3b82f6 !important;
        color: #60a5fa !important;
        font-weight: 900;
    }
    html:not(.dark) .mini-cal-day.state-blue {
        background: #2563eb !important;
        color: #ffffff !important;
    }

    /* روز دارای قسط: زرد مینیمال */
    .mini-cal-day.state-yellow {
        background: rgba(245, 158, 11, 0.20) !important;
        border-color: #f59e0b !important;
        color: #fbbf24 !important;
        font-weight: 900;
        cursor: pointer;
    }
    html:not(.dark) .mini-cal-day.state-yellow {
        background: #fef3c7 !important;
        color: #b45309 !important;
    }

    /* امروز دارای قسط: قرمز مینیمال */
    .mini-cal-day.state-red {
        background: rgba(225, 29, 72, 0.24) !important;
        border-color: #f43f5e !important;
        color: #fda4af !important;
        font-weight: 900;
        cursor: pointer;
    }
    html:not(.dark) .mini-cal-day.state-red {
        background: #e11d48 !important;
        color: #ffffff !important;
    }

    .mini-cal-legend {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: 0.8rem;
        padding-top: 0.65rem;
        border-top: 1px solid var(--border-subtle);
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--text-muted);
    }
    .legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    /* ۳. کارت‌های آموزش و راهنمای وب‌اپلیکیشن (دقیقاً مطابق نسخه تاییدشده شما) */
    .guide-steps-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .guide-steps-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    .guide-step-card {
        padding: 1.15rem;
        border-radius: 1rem;
        background: var(--bg-subtle);
        border: 1px solid var(--border-card);
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
    }
    .guide-step-num {
        width: 38px;
        height: 38px;
        border-radius: 0.75rem;
        background: var(--primary-teal-soft);
        border: 1px solid var(--primary-teal-border);
        color: var(--primary-teal);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')

    @php
        $monthInstallments = collect($calendar['days'])
            ->where('has_installment', true)
            ->flatMap(function ($d) use ($calendar) {
                return collect($d['installments'])->map(function ($inst) use ($d, $calendar) {
                    $inst['day_num'] = $d['day'];
                    $inst['month_name'] = $calendar['month_name'];
                    $inst['is_today'] = $d['is_today'];
                    return $inst;
                });
            });
    @endphp

    {{-- بخش ۱: نوار خوش‌آمدگویی مینیمال --}}
    <section class="fi-glass-card welcome-minimal-bar">
        <div style="display: flex; align-items: center; gap: 0.85rem;">
            <div class="minimal-icon-badge">
                <img src="{{ asset('images/icons/app_dashboard.svg') }}" alt="داشبورد" class="icon-img-sm" />
            </div>
            <div>
                <p style="margin: 0; font-size: 0.88rem; font-weight: 800; color: var(--text-primary); line-height: 1.6;">
                    {{ $welcomeMessage }}
                </p>
                <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 700;">
                    امروز: {{ $calendar['today_full_jalali'] }}
                </span>
            </div>
        </div>

        <a href="{{ route('customer.policies') }}" class="btn-subtle-action">
            <img src="{{ asset('images/icons/app_installments.svg') }}" alt="اقساط" class="icon-img-xs" />
            <span>بیمه و اقساط ({{ $upcomingCount }})</span>
        </a>
    </section>

    {{-- بخش ۲: تقویم مینیمال در کنار خلاصه کاربردی اقساط --}}
    <section class="dashboard-main-grid">

        {{-- ستون راست: ویجت تقویم مینیمال --}}
        <div class="fi-glass-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="mini-cal-header">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <img src="{{ asset('images/icons/app_calendar.svg') }}" alt="تقویم" class="icon-img-sm" />
                        <span style="font-size: 0.9rem; font-weight: 900;">{{ $calendar['month_name'] }} {{ $calendar['year'] }}</span>
                    </div>
                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--primary-teal); background: var(--primary-teal-soft); padding: 0.18rem 0.55rem; border-radius: 6px;">
                        {{ $monthInstallments->count() > 0 ? $monthInstallments->count() . ' قسط در این ماه' : 'بدون قسط در این ماه' }}
                    </span>
                </div>

                <div class="mini-cal-weekdays">
                    <div class="mini-cal-weekday">ش</div>
                    <div class="mini-cal-weekday">ی</div>
                    <div class="mini-cal-weekday">د</div>
                    <div class="mini-cal-weekday">س</div>
                    <div class="mini-cal-weekday">چ</div>
                    <div class="mini-cal-weekday">پ</div>
                    <div class="mini-cal-weekday">ج</div>
                </div>

                <div class="mini-cal-days">
                    @for($empty = 0; $empty < $calendar['start_offset']; $empty++)
                        <div class="mini-cal-day empty"></div>
                    @endfor

                    @foreach($calendar['days'] as $dayItem)
                        @php
                            $stateClass = match($dayItem['state']) {
                                'blue'   => 'state-blue',
                                'yellow' => 'state-yellow',
                                'red'    => 'state-red',
                                default  => '',
                            };
                        @endphp
                        <div
                            class="mini-cal-day {{ $stateClass }}"
                            @if($dayItem['has_installment'])
                                onclick="highlightMonthInstallment({{ $dayItem['day'] }})"
                                title="دارای سررسید قسط"
                            @endif
                        >
                            {{ $dayItem['day'] }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mini-cal-legend">
                <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                    <span class="legend-dot" style="background: #3b82f6;"></span> امروز
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                    <span class="legend-dot" style="background: #f59e0b;"></span> روز قسط
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                    <span class="legend-dot" style="background: #e11d48;"></span> قسط امروز
                </span>
            </div>
        </div>

        {{-- ستون چپ: خلاصه کاربردی وضعیت بیمه‌نامه‌ها و سررسیدهای نزدیک --}}
        <div class="fi-glass-card" style="display: flex; flex-direction: column; justify-content: space-between; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem; padding-bottom: 0.65rem; border-bottom: 1px solid var(--border-subtle);">
                    <div style="display: flex; align-items: center; gap: 0.55rem;">
                        <img src="{{ asset('images/icons/app_installments.svg') }}" alt="وضعیت" class="icon-img-sm" />
                        <span style="font-size: 0.92rem; font-weight: 900;">خلاصه وضعیت و سررسیدهای پیش‌رو</span>
                    </div>
                    <a href="{{ route('customer.policies') }}" style="font-size: 0.75rem; font-weight: 800; color: var(--primary-teal); text-decoration: none;">
                        مشاهده همه ↗
                    </a>
                </div>

                {{-- ۲ باکس آماری کوچک و مینیمال --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; margin-bottom: 0.9rem;">
                    <div class="minimal-sub-card" style="padding: 0.8rem 0.95rem;">
                        <span style="font-size: 0.72rem; color: var(--text-muted); display: block;">بیمه‌نامه‌های فعال شما</span>
                        <strong style="font-size: 0.98rem; font-weight: 900; display: block; margin-top: 0.2rem;">
                            {{ $policies->count() }} فقره بیمه‌نامه
                        </strong>
                    </div>
                    <div class="minimal-sub-card" style="padding: 0.8rem 0.95rem;">
                        <span style="font-size: 0.72rem; color: var(--text-muted); display: block;">تعهدات ۳ ماه آینده ({{ $upcomingCount }} قسط)</span>
                        <strong style="font-size: 0.95rem; font-weight: 900; color: var(--primary-teal); display: block; margin-top: 0.2rem;">
                            {{ number_format($upcomingTotal) }} ریال
                            @if($upcomingTotal > 0)
                                <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700;">({{ number_format((int) floor($upcomingTotal / 10)) }} تومان)</span>
                            @endif
                        </strong>
                    </div>
                </div>

                {{-- لیست نزدیک‌ترین اقساط (بسیار جمع‌وجور و کاربردی) --}}
                <div style="display: flex; flex-direction: column; gap: 0.55rem;">
                    @forelse($upcomingInstallments->take(3) as $upInst)
                        <div class="minimal-sub-card" style="padding: 0.7rem 0.9rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem;">
                            <div style="font-size: 0.78rem;">
                                <strong style="font-weight: 900;">قسط {{ $upInst->installment_number }} — {{ $upInst->policy?->title }}</strong>
                                <span style="display: block; font-size: 0.72rem; color: var(--text-muted); margin-top: 0.15rem;">
                                    سررسید: <span dir="ltr">{{ $upInst->due_date_jalali }}</span> |
                                    مبلغ: {{ number_format($upInst->amount) }} ریال ({{ number_format((int) floor($upInst->amount / 10)) }} تومان)
                                </span>
                            </div>
                            <a href="{{ $upInst->generateSignedPaymentUrl() }}" class="btn-teal-solid" style="padding: 0.36rem 0.8rem; font-size: 0.74rem;">
                                پرداخت
                            </a>
                        </div>
                    @empty
                        <div class="minimal-sub-card" style="text-align: center; padding: 1.15rem; font-size: 0.8rem; color: var(--text-muted);">
                            هیچ قسط پرداخت‌نشده‌ای برای شما ثبت نشده است.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </section>

    {{-- بخش ۳: راهنمای استفاده و امکانات وب‌اپلیکیشن آفاق بیمه (دقیقاً بدون تغییر مطابق تایید شما) --}}
    <section class="fi-glass-card">
        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 1.15rem;">
            <img src="{{ asset('images/icons/app_more_details.svg') }}" alt="راهنما" class="icon-img-md" />
            <div>
                <h2 style="margin: 0; font-size: 1.05rem; font-weight: 900;">
                    راهنمای استفاده و امکانات وب‌اپلیکیشن آفاق بیمه
                </h2>
                <span style="font-size: 0.76rem; color: var(--text-muted);">
                    چگونه از خدمات آنلاین پرتال بیمه‌گذاران به بهترین شکل استفاده کنیم؟
                </span>
            </div>
        </div>

        <div class="guide-steps-grid">
            <div class="guide-step-card">
                <div class="guide-step-num">۱</div>
                <div>
                    <h3 style="margin: 0 0 0.35rem 0; font-size: 0.9rem; font-weight: 900;">مشاهده بیمه‌نامه‌ها و پرداخت آسان اقساط</h3>
                    <p style="margin: 0; font-size: 0.78rem; color: var(--text-secondary); line-height: 1.7;">
                        از منوی <strong>«بیمه و اقساط»</strong> می‌توانید لیست تمام بیمه‌نامه‌های خود را ببینید. با زدن دکمه <strong>«پرداخت قسط»</strong>، شناسه پرداخت به صورت خودکار کپی شده و مستقیماً وارد درگاه رسمی بیمه ایران می‌شوید.
                    </p>
                </div>
            </div>

            <div class="guide-step-card">
                <div class="guide-step-num">۲</div>
                <div>
                    <h3 style="margin: 0 0 0.35rem 0; font-size: 0.9rem; font-weight: 900;">ثبت تایید پرداخت و پیگیری وضعیت</h3>
                    <p style="margin: 0; font-size: 0.78rem; color: var(--text-secondary); line-height: 1.7;">
                        پس از واریز وجه در درگاه بیمه ایران، در همان صفحه پرداخت روی دکمه <strong>«پرداخت را انجام دادم»</strong> بزنید تا قسط شما در وضعیت تایید قرار گیرد و در بخش تاریخچه پرداخت‌ها ثبت شود.
                    </p>
                </div>
            </div>

            <div class="guide-step-card">
                <div class="guide-step-num">۳</div>
                <div>
                    <h3 style="margin: 0 0 0.35rem 0; font-size: 0.9rem; font-weight: 900;">اتصال به ربات بله و دریافت یادآور خودکار</h3>
                    <p style="margin: 0; font-size: 0.78rem; color: var(--text-secondary); line-height: 1.7;">
                        با اتصال یک‌کلیکی حساب خود به <strong>ربات پیام‌رسان بله آفاق بیمه</strong>، چند روز قبل از سررسید هر قسط و همچنین هنگام نزدیک شدن به انقضای بیمه‌نامه، پیام یادآور به همراه لینک پرداخت دریافت خواهید کرد.
                    </p>
                </div>
            </div>

            <div class="guide-step-card">
                <div class="guide-step-num">۴</div>
                <div>
                    <h3 style="margin: 0 0 0.35rem 0; font-size: 0.9rem; font-weight: 900;">تکمیل پروفایل و تمدید آنلاین بیمه‌نامه</h3>
                    <p style="margin: 0; font-size: 0.78rem; color: var(--text-secondary); line-height: 1.7;">
                        در صفحه <strong>«پروفایل»</strong> می‌توانید تاریخ تولد و آدرس دقیق خود را تکمیل نمایید. همچنین ۳۰ روز مانده به انقضای هر بیمه‌نامه، دکمه <strong>«درخواست تمدید آنلاین»</strong> برای شما فعال می‌شود.
                    </p>
                </div>
            </div>
        </div>
    </section>

@endsection