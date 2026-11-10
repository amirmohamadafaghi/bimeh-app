@extends('customer.layouts.app')

@section('title', 'بیمه و اقساط | آفاق بیمه')
@section('page_title', 'بیمه و اقساط')

@push('styles')
<style>
    .progress-track {
        width: 100%;
        height: 6px;
        background: rgba(148, 163, 184, 0.18);
        border-radius: 9999px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #14b8a6 0%, #10b981 100%);
        border-radius: 9999px;
    }
    .iran-plate-badge {
        display: inline-flex;
        align-items: center;
        direction: ltr;
        background: #f8fafc;
        color: #0f172a;
        border: 1px solid #334155;
        border-radius: 6px;
        overflow: hidden;
        font-weight: 900;
        font-size: 0.74rem;
        height: 24px;
    }
    .iran-plate-flag {
        background: #1d4ed8;
        color: #ffffff;
        height: 100%;
        padding: 0 4px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 0.38rem;
        line-height: 1;
        font-family: monospace;
    }
    .iran-plate-number {
        padding: 0 0.5rem;
        white-space: nowrap;
    }

    .installment-minimal-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        padding: 0.75rem 0.95rem;
        border-radius: 0.85rem;
        background: var(--bg-subtle);
        border: 1px solid var(--border-card);
    }

    .status-tag {
        display: inline-flex;
        align-items: center;
        padding: 0.18rem 0.55rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 800;
    }
    .tag-overdue { background: rgba(244, 63, 94, 0.14); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
    .tag-today { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
    .tag-upcoming { background: var(--primary-teal-soft); color: var(--primary-teal); border: 1px solid var(--primary-teal-border); }

    .filter-pill-group {
        display: inline-flex;
        background: var(--bg-subtle);
        border: 1px solid var(--border-card);
        border-radius: 0.75rem;
        padding: 0.2rem;
        gap: 0.2rem;
    }
    .filter-pill-btn {
        padding: 0.35rem 0.75rem;
        border-radius: 0.55rem;
        border: none;
        background: transparent;
        color: var(--text-secondary);
        font-size: 0.74rem;
        font-weight: 800;
        cursor: pointer;
    }
    .filter-pill-btn.active {
        background: var(--primary-teal-soft);
        border: 1px solid var(--primary-teal-border);
        color: var(--primary-teal);
    }
</style>
@endpush

@section('content')

    @php
        $todayTehran = now('Asia/Tehran')->startOfDay();
    @endphp

    {{-- نوار ابزار بالای صفحه: خلاصه تعهدات + جستجو و فیلتر در یک کارت مینیمال --}}
    <section class="fi-glass-card" style="padding: 1rem 1.2rem;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.85rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="minimal-icon-badge">
                    <img src="{{ asset('images/icons/app_installments.svg') }}" alt="اقساط" class="icon-img-sm" />
                </div>
                <div>
                    <div style="font-size: 0.88rem; font-weight: 900;">
                        تعهدات ۳ ماه آینده: {{ $upcomingCount }} قسط — {{ number_format($upcomingTotal) }} ریال
                        @if($upcomingTotal > 0)
                            <span style="font-size: 0.74rem; color: var(--text-muted); font-weight: 700;">({{ number_format((int) floor($upcomingTotal / 10)) }} تومان)</span>
                        @endif
                    </div>
                    <span style="font-size: 0.72rem; color: var(--text-muted);">
                        تعداد کل بیمه‌نامه‌های ثبت‌شده: {{ $policies->count() }} فقره
                    </span>
                </div>
            </div>

            @if($policies->count() > 0)
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.55rem;">
                    <input
                        type="text"
                        class="afagh-input"
                        style="width: 210px; padding: 0.42rem 0.75rem; font-size: 0.76rem;"
                        placeholder="جستجوی بیمه، خودرو یا پلاک..."
                        oninput="filterPoliciesBySearch(this.value)"
                    />
                    <div class="filter-pill-group">
                        <button type="button" class="filter-pill-btn active" onclick="filterInstallments('all', this)">همه</button>
                        <button type="button" class="filter-pill-btn" onclick="filterInstallments('unpaid', this)">مانده</button>
                        <button type="button" class="filter-pill-btn" onclick="filterInstallments('paid', this)">تسویه‌شده</button>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- فهرست بیمه‌نامه‌ها با نمایش آکاردئونی و مینیمال اقساط --}}
    <section style="display: flex; flex-direction: column; gap: 1rem;">
        @forelse($policies as $policy)
            @php
                $totalInstCount = $policy->installments->count();
                $paidInstCount = $policy->installments->where('status', 'paid')->count();
                $progressPercent = $totalInstCount > 0 ? (int) round(($paidInstCount / $totalInstCount) * 100) : 100;

                $daysToExpire = $policy->expires_at
                    ? (int) $todayTehran->diffInDays($policy->expires_at->copy()->startOfDay(), false)
                    : null;

                $hasCollapsible = $totalInstCount > 1;
                $firstUnpaidInstId = $policy->installments
                    ->whereIn('status', ['overdue', 'pending', 'awaiting_approval'])
                    ->sortBy('due_date')
                    ->first()?->id;
            @endphp
            <div
                class="fi-glass-card policy-card-item"
                data-search-text="{{ $policy->title }} {{ $policy->policy_number }} {{ $policy->vehicle_name }} {{ $policy->plate_number }}"
            >
                {{-- هدر جمع‌وجور بیمه‌نامه --}}
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--border-subtle); margin-bottom: 0.85rem;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem;">
                        <span class="minimal-icon-badge" style="width: 32px; height: 32px;">
                            <img src="{{ asset('images/icons/app_reports.svg') }}" alt="بیمه" class="icon-img-xs" />
                        </span>
                        <div>
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                                <strong style="font-size: 0.94rem; font-weight: 900;">{{ $policy->title }}</strong>
                                <span dir="ltr" style="font-size: 0.74rem; font-family: monospace; color: var(--text-muted); background: var(--bg-subtle); padding: 0.1rem 0.45rem; border-radius: 5px; border: 1px solid var(--border-card);">
                                    {{ $policy->policy_number }}
                                </span>
                                @if($policy->plate_number)
                                    <span class="iran-plate-badge">
                                        <span class="iran-plate-flag"><span>I.R.</span><span>IRAN</span></span>
                                        <span class="iran-plate-number">{{ $policy->plate_number }}</span>
                                    </span>
                                @endif
                            </div>
                            <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 0.2rem;">
                                @if($policy->vehicle_name)
                                    <span>خودرو: {{ $policy->vehicle_name }} | </span>
                                @endif
                                @if($policy->total_amount > 0)
                                    <span>مبلغ کل: {{ number_format($policy->total_amount) }} ریال ({{ number_format((int) floor($policy->total_amount / 10)) }} تومان)</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.74rem; color: var(--text-secondary);" dir="ltr">
                        <span>انقضا: <strong>{{ $policy->expires_at_jalali ?: '—' }}</strong></span>
                        @if($totalInstCount > 0)
                            <span style="padding: 0.18rem 0.55rem; border-radius: 6px; background: var(--primary-teal-soft); color: var(--primary-teal); font-weight: 800;" dir="rtl">
                                {{ $paidInstCount }}/{{ $totalInstCount }} تسویه ({{ $progressPercent }}٪)
                            </span>
                        @endif
                    </div>
                </div>

                {{-- هشدار تمدید بیمه‌نامه (در صورت نزدیک بودن به انقضا) --}}
                @if($daysToExpire !== null && $daysToExpire <= 30)
                    <div style="margin-bottom: 0.85rem; padding: 0.65rem 0.9rem; border-radius: 0.8rem; background: rgba(245, 158, 11, 0.10); border: 1px solid rgba(245, 158, 11, 0.28); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.6rem;">
                        <span style="font-size: 0.76rem; font-weight: 800; color: #fbbf24;">
                            @if($daysToExpire < 0)
                                اعتبار این بیمه‌نامه {{ abs($daysToExpire) }} روز پیش به پایان رسیده است.
                            @elseif($daysToExpire === 0)
                                اعتبار این بیمه‌نامه امروز به پایان می‌رسد.
                            @else
                                {{ $daysToExpire }} روز تا پایان اعتبار این بیمه‌نامه باقی مانده است.
                            @endif
                        </span>
                        <form method="POST" action="{{ route('customer.policies.renewal-request', $policy) }}">
                            @csrf
                            <button type="submit" style="padding: 0.35rem 0.75rem; border-radius: 0.6rem; border: none; background: #f59e0b; color: #0f172a; font-size: 0.72rem; font-weight: 900; cursor: pointer;">
                                درخواست تمدید آنلاین
                            </button>
                        </form>
                    </div>
                @endif

                {{-- لیست اقساط (به صورت پیش‌فرض فقط قسط بعدی باز است) --}}
                <div style="display: flex; flex-direction: column; gap: 0.55rem;">
                    @forelse($policy->installments as $idx => $inst)
                        @php
                            $isPaidGroup = in_array($inst->status, ['paid', 'awaiting_approval'], true);
                            $filterGroup = $isPaidGroup ? 'paid' : 'unpaid';

                            $daysDiff = $inst->due_date
                                ? (int) $todayTehran->diffInDays($inst->due_date->copy()->startOfDay(), false)
                                : null;

                            $isDefaultVisible = ! $hasCollapsible
                                || ($firstUnpaidInstId ? ($inst->id === $firstUnpaidInstId) : ($idx === 0));
                        @endphp
                        <div
                            class="installment-minimal-row"
                            data-policy-id="{{ $policy->id }}"
                            data-filter-group="{{ $filterGroup }}"
                            data-default-visible="{{ $isDefaultVisible ? '1' : '0' }}"
                            style="display: {{ $isDefaultVisible ? 'flex' : 'none' }};"
                        >
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem;">
                                <span class="minimal-icon-badge" style="width: 30px; height: 30px; font-size: 0.76rem;">
                                    {{ $inst->installment_number }}
                                </span>
                                <div>
                                    <span style="font-weight: 900; font-size: 0.84rem;">قسط {{ $inst->installment_number }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-right: 0.4rem;">
                                        سررسید: <strong dir="ltr" style="color: var(--text-secondary);">{{ $inst->due_date_jalali }}</strong>
                                    </span>
                                </div>

                                @if(! $isPaidGroup && $daysDiff !== null)
                                    @if($daysDiff < 0)
                                        <span class="status-tag tag-overdue">{{ abs($daysDiff) }} روز تاخیر</span>
                                    @elseif($daysDiff === 0)
                                        <span class="status-tag tag-today">سررسید امروز</span>
                                    @else
                                        <span class="status-tag tag-upcoming">{{ $daysDiff }} روز مانده</span>
                                    @endif
                                @endif
                            </div>

                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.55rem;">
                                <div style="font-size: 0.82rem; font-weight: 900; color: var(--text-primary);">
                                    {{ number_format($inst->amount) }} ریال
                                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700;">({{ number_format((int) floor($inst->amount / 10)) }} تومان)</span>
                                </div>

                                @if($inst->payment_identifier)
                                    <button type="button" onclick="copyTextToClipboard('{{ $inst->payment_identifier }}', '✅ شناسه پرداخت کپی شد!')" class="btn-subtle-action" style="padding: 0.28rem 0.55rem; font-size: 0.7rem; font-family: monospace;" dir="ltr">
                                        ID: {{ $inst->payment_identifier }}
                                    </button>
                                @endif

                                @if(in_array($inst->status, ['pending', 'overdue']))
                                    <a href="{{ $inst->generateSignedPaymentUrl() }}" class="btn-teal-solid" style="padding: 0.38rem 0.85rem; font-size: 0.75rem;">
                                        پرداخت قسط
                                    </a>
                                @elseif($inst->status === 'awaiting_approval')
                                    <span style="font-size: 0.74rem; color: #38bdf8; font-weight: 800;">در انتظار تایید</span>
                                @else
                                    <span style="font-size: 0.74rem; color: #10b981; font-weight: 800;">✓ تسویه شده</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="font-size: 0.78rem; color: var(--text-muted); padding: 0.25rem 0;">
                            این بیمه‌نامه به صورت نقدی صادر شده و فاقد قسط است.
                        </div>
                    @endforelse
                </div>

                @if($hasCollapsible)
                    <button
                        type="button"
                        class="btn-subtle-action"
                        style="width: 100%; margin-top: 0.65rem; font-size: 0.74rem; border-style: dashed;"
                        data-expanded="0"
                        onclick="togglePolicyInstallments({{ $policy->id }}, {{ $totalInstCount }}, this)"
                    >
                        مشاهده تمام {{ $totalInstCount }} قسط این بیمه‌نامه ⌄
                    </button>
                @endif
            </div>
        @empty
            <div class="fi-glass-card" style="text-align: center; padding: 2.5rem 1.25rem;">
                <p style="margin: 0; font-size: 0.88rem; font-weight: 800; color: var(--text-muted);">
                    هنوز هیچ بیمه‌نامه‌ای برای کد ملی شما ثبت نشده است.
                </p>
            </div>
        @endforelse
    </section>

    {{-- بخش پایینی صفحه: خلاصه پرداخت‌های پیش‌رو و تاریخچه پرداخت‌ها (مینیمال و فشرده) --}}
    <section class="fi-glass-card">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.9rem; padding-bottom: 0.7rem; border-bottom: 1px solid var(--border-subtle);">
            <div style="display: flex; align-items: center; gap: 0.55rem;">
                <img src="{{ asset('images/icons/app_reports.svg') }}" alt="سوابق" class="icon-img-sm" />
                <span style="font-size: 0.92rem; font-weight: 900;">سوابق و برنامه پرداخت‌ها</span>
            </div>

            <div class="filter-pill-group">
                <button type="button" id="tab-upcoming-btn" class="filter-pill-btn active" onclick="switchPaymentTab('upcoming')">
                    پیش‌رو ({{ $upcomingInstallments->count() }})
                </button>
                <button type="button" id="tab-history-btn" class="filter-pill-btn" onclick="switchPaymentTab('history')">
                    تاریخچه ({{ $paymentHistory->count() }})
                </button>
            </div>
        </div>

        <div id="panel-upcoming" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.65rem;">
            @forelse($upcomingInstallments as $upInst)
                <div class="minimal-sub-card" style="padding: 0.75rem 0.9rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <div style="font-size: 0.76rem;">
                        <strong style="font-weight: 900; display: block;">قسط {{ $upInst->installment_number }} — {{ $upInst->policy?->title }}</strong>
                        <span style="color: var(--text-muted);">
                            {{ $upInst->due_date_jalali }} | {{ number_format((int) floor($upInst->amount / 10)) }} تومان
                        </span>
                    </div>
                    <a href="{{ $upInst->generateSignedPaymentUrl() }}" class="btn-teal-solid" style="padding: 0.35rem 0.7rem; font-size: 0.72rem;">
                        پرداخت
                    </a>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 1rem; font-size: 0.78rem; color: var(--text-muted);">
                    قسط پیش‌رویی وجود ندارد.
                </div>
            @endforelse
        </div>

        <div id="panel-history" style="display: none; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.65rem;">
            @forelse($paymentHistory as $hisInst)
                @php $hisAmount = (int) ($hisInst->documented_amount ?: $hisInst->amount); @endphp
                <div class="minimal-sub-card" style="padding: 0.75rem 0.9rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <div style="font-size: 0.76rem;">
                        <strong style="font-weight: 900; display: block;">قسط {{ $hisInst->installment_number }} — {{ $hisInst->policy?->title }}</strong>
                        <span style="color: var(--text-muted);">{{ number_format((int) floor($hisAmount / 10)) }} تومان</span>
                    </div>
                    <span style="font-size: 0.72rem; font-weight: 800; color: {{ $hisInst->status === 'paid' ? '#10b981' : '#38bdf8' }};">
                        {{ $hisInst->status === 'paid' ? '✓ تسویه' : 'در انتظار تایید' }}
                    </span>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 1rem; font-size: 0.78rem; color: var(--text-muted);">
                    هنوز سابقه پرداختی ثبت نشده است.
                </div>
            @endforelse
        </div>
    </section>

@endsection

@push('scripts')
<script>
    function togglePolicyInstallments(policyId, totalCount, btn) {
        const isExpanded = btn.getAttribute('data-expanded') === '1';
        const rows = document.querySelectorAll('.installment-minimal-row[data-policy-id="' + policyId + '"]');
        rows.forEach(row => {
            if (isExpanded) {
                row.style.display = row.getAttribute('data-default-visible') === '1' ? 'flex' : 'none';
            } else {
                row.style.display = 'flex';
            }
        });
        btn.setAttribute('data-expanded', isExpanded ? '0' : '1');
        btn.textContent = isExpanded
            ? ('مشاهده تمام ' + totalCount + ' قسط این بیمه‌نامه ⌄')
            : 'نمایش خلاصه اقساط ⌃';
    }

    function filterPoliciesBySearch(rawQuery) {
        const persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        const arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        let q = (rawQuery || '').trim().toLowerCase();
        for (let i = 0; i < 10; i++) {
            q = q.replace(new RegExp(persian[i], 'g'), i).replace(new RegExp(arabic[i], 'g'), i);
        }
        document.querySelectorAll('.policy-card-item').forEach(card => {
            let text = (card.getAttribute('data-search-text') || '').toLowerCase();
            for (let i = 0; i < 10; i++) {
                text = text.replace(new RegExp(persian[i], 'g'), i).replace(new RegExp(arabic[i], 'g'), i);
            }
            card.style.display = (q === '' || text.includes(q)) ? 'block' : 'none';
        });
    }

    function filterInstallments(mode, btn) {
        btn.parentElement.querySelectorAll('.filter-pill-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.installment-minimal-row').forEach(row => {
            const group = row.getAttribute('data-filter-group');
            row.style.display = (mode === 'all' || group === mode) ? 'flex' : 'none';
        });
    }

    function switchPaymentTab(tab) {
        const upPanel = document.getElementById('panel-upcoming');
        const hisPanel = document.getElementById('panel-history');
        const upBtn = document.getElementById('tab-upcoming-btn');
        const hisBtn = document.getElementById('tab-history-btn');
        if (tab === 'upcoming') {
            upPanel.style.display = 'grid';
            hisPanel.style.display = 'none';
            upBtn.classList.add('active');
            hisBtn.classList.remove('active');
        } else {
            upPanel.style.display = 'none';
            hisPanel.style.display = 'grid';
            hisBtn.classList.add('active');
            upBtn.classList.remove('active');
        }
    }
</script>
@endpush