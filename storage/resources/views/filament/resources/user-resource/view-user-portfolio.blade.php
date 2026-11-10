<x-filament-panels::page>
    <div class="space-y-6">

        {{-- بخش ۱: کارت مشخصات هویتی و ویترین آماری بیمه‌گذار --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex flex-wrap items-center justify-between gap-4 w-full">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center text-white font-black text-base shadow-md">
                            {{ mb_substr($user->name ?: 'ب', 0, 1) }}
                        </div>
                        <div>
                            <span class="text-lg font-extrabold text-slate-900 dark:text-white block">{{ $user->name }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">پرونده جامع بیمه‌گذار و وضعیت تعهدات مالی</span>
                        </div>
                        @if($user->payment_badge === 'good_payer')
                            <span class="badge-good-payer">★ خوش‌حساب</span>
                        @elseif($user->payment_badge === 'bad_payer')
                            <span class="badge-bad-payer">▲ بدحساب</span>
                        @else
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700">عادی</span>
                        @endif
                    </div>

                    <x-filament::button wire:click="toggleEdit" color="gray" size="sm" icon="heroicon-o-pencil-square">
                        {{ $isEditing ? 'بستن فرم ویرایش' : 'ویرایش مشخصات پرونده' }}
                    </x-filament::button>
                </div>
            </x-slot>

            @if($isEditing)
                <form wire:submit="saveProfile" class="space-y-4 mb-6 p-5 rounded-2xl bg-slate-100/80 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800">
                    {{ $this->form }}
                    <div class="flex justify-end gap-2 pt-2">
                        <x-filament::button type="submit" color="success" size="sm" icon="heroicon-o-check">ذخیره تغییرات پرونده</x-filament::button>
                        <x-filament::button type="button" wire:click="toggleEdit" color="gray" size="sm">انصراف</x-filament::button>
                    </div>
                </form>
            @endif

            {{-- ۴ کارت آماری با کنتراست عالی در حالت دارک و لایت --}}
            <div class="portfolio-stats-grid mb-6">
                <div class="portfolio-stat-card stat-indigo">
                    <span class="stat-label">تعداد کل بیمه‌نامه‌ها</span>
                    <span class="stat-value">{{ number_format($policies->count()) }} <small class="text-xs font-normal opacity-80">فقره</small></span>
                </div>

                <div class="portfolio-stat-card stat-emerald">
                    <span class="stat-label">مجموع مبالغ وصول‌شده</span>
                    <span class="stat-value">{{ number_format((int) $paidInstallmentsAmount) }} <small class="text-xs font-normal opacity-80">ریال</small></span>
                </div>

                <div class="portfolio-stat-card stat-amber">
                    <span class="stat-label">مانده اقساط تعهدی</span>
                    <span class="stat-value">{{ number_format((int) $pendingAmount) }} <small class="text-xs font-normal opacity-80">ریال</small></span>
                </div>

                <div class="portfolio-stat-card stat-rose">
                    <span class="stat-label">اقساط معوق</span>
                    <span class="stat-value">{{ number_format($overdueCount) }} <small class="text-xs font-normal opacity-80">قسط</small></span>
                </div>
            </div>

            {{-- نوار شناسنامه هویتی بیمه‌گذار --}}
            <div class="portfolio-meta-grid">
                <div class="meta-item">
                    <span class="meta-key">کد ملی:</span>
                    <span class="meta-val font-mono" dir="ltr">{{ $user->national_code ?: '—' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-key">تلفن همراه:</span>
                    <span class="meta-val font-mono" dir="ltr">{{ $user->mobile ?: '—' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-key">تلفن ثابت:</span>
                    <span class="meta-val font-mono" dir="ltr">{{ $user->phone ?: '—' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-key">پروفایل فروش:</span>
                    <span class="meta-val">{{ $user->profile_title ?: '—' }}</span>
                </div>
                <div class="meta-item">
                    <span class="meta-key">تاریخ تولد:</span>
                    <span class="meta-val" dir="ltr">{{ $user->birth_date ? \App\Casts\JalaliDateCast::toJalali($user->birth_date) : '—' }}</span>
                </div>
            </div>
        </x-filament::section>

        {{-- بخش ۲: فهرست بیمه‌نامه‌های ثبت‌شده --}}
        <x-filament::section>
            <x-slot name="heading">۲. بیمه‌نامه‌های ثبت‌شده ({{ $policies->count() }} مورد)</x-slot>

            <div class="standard-table-box">
                <table class="afagh-luxury-table">
                    <thead>
                        <tr>
                            <th>شماره بیمه‌نامه</th>
                            <th>شرکت بیمه</th>
                            <th>رشته و مورد بیمه</th>
                            <th>تاریخ صدور / انقضا</th>
                            <th>مبلغ کل (ریال)</th>
                            <th>پیش‌پرداخت (ریال)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($policies as $policy)
                            <tr>
                                <td class="font-bold font-mono" dir="ltr">{{ $policy->policy_number }}</td>
                                <td>{{ $policy->insurer?->name ?? 'بیمه ایران' }}</td>
                                <td>
                                    <span class="font-semibold">{{ $policy->insurance_category ?: ($policy->title ?: 'ثالث') }}</span>
                                    @if($policy->vehicle_name)
                                        <span class="block text-xs opacity-75">{{ $policy->vehicle_name }} {{ $policy->plate_number ? "({$policy->plate_number})" : '' }}</span>
                                    @endif
                                </td>
                                <td class="text-xs font-mono" dir="ltr">
                                    {{ $policy->started_at_jalali !== '—' ? $policy->started_at_jalali : $policy->issued_at_jalali }}
                                    تا
                                    {{ $policy->expires_at_jalali }}
                                </td>
                                <td class="font-bold">{{ number_format((int) $policy->total_amount) }}</td>
                                <td class="text-emerald-500 font-bold">{{ number_format((int) ($policy->initial_payment ?: $policy->down_payment)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center opacity-60">هیچ بیمه‌نامه‌ای برای این بیمه‌گذار ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- بخش ۳: لیست اقساط این بیمه‌گذار --}}
        <x-filament::section>
            <x-slot name="heading">۳. لیست اقساط این بیمه‌گذار ({{ $allInstallments->count() }} قسط)</x-slot>

            <div class="standard-table-box">
                <table class="afagh-luxury-table">
                    <thead>
                        <tr>
                            <th>شماره قسط</th>
                            <th>شماره بیمه‌نامه</th>
                            <th>سررسید</th>
                            <th>مبلغ (ریال)</th>
                            <th>شناسه پرداخت</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allInstallments as $inst)
                            <tr>
                                <td class="font-bold">قسط {{ $inst->installment_number }}</td>
                                <td class="font-mono text-xs" dir="ltr">{{ $inst->policy?->policy_number }}</td>
                                <td class="font-mono text-xs" dir="ltr">{{ $inst->due_date_jalali }}</td>
                                <td class="font-bold">{{ number_format((int) $inst->amount) }}</td>
                                <td class="text-xs font-mono" dir="ltr">{{ $inst->payment_identifier ?: '—' }}</td>
                                <td>
                                    @if($inst->status === 'paid')
                                        <span class="status-pill status-paid">✓ وصول شده</span>
                                    @elseif($inst->status === 'awaiting_approval')
                                        <span class="status-pill status-awaiting">⏳ منتظر تایید</span>
                                    @elseif($inst->status === 'overdue')
                                        <span class="status-pill status-overdue">! معوق</span>
                                    @else
                                        <span class="status-pill status-pending">در انتظار</span>
                                    @endif
                                </td>
                                <td>
                                    @if($inst->status !== 'paid')
                                        <x-filament::button wire:click="approveInstallment({{ $inst->id }})" color="success" size="xs" icon="heroicon-o-check-circle">
                                            تایید وصول
                                        </x-filament::button>
                                    @else
                                        <span class="text-xs text-emerald-500 font-semibold">تسویه شده</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center opacity-60">هیچ قسطی برای این بیمه‌گذار ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>