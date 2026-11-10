<x-filament-panels::page>
    <div class="notif-page-wrapper">

        {{-- بخش ۱: تنظیمات اعلان‌ها و قالب پیام‌ها (ذخیره خودکار بدون نیاز به دکمه دستی) --}}
        <div class="notif-section-block">
            {{ $this->form }}
        </div>

        {{-- بخش ۲: لاگ اعلان‌ها و ربات بله --}}
        <x-filament::section>
            <x-slot name="heading">
                ۲. لاگ اعلان‌ها و ربات بله
            </x-slot>
            <x-slot name="description">
                فهرست ۵۰ پیام آخر ارسال‌شده به بیمه‌گذاران (درون‌برنامه‌ای و پیام‌رسان بله)
            </x-slot>

            <div class="standard-table-box">
                <table class="afagh-luxury-table">
                    <thead>
                        <tr>
                            <th>ردیف</th>
                            <th>بیمه‌گذار</th>
                            <th>نوع اعلان</th>
                            <th>وضعیت</th>
                            <th>متن پیام</th>
                            <th>زمان ثبت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                            <tr>
                                <td class="text-xs opacity-70">{{ $index + 1 }}</td>
                                <td class="font-bold whitespace-nowrap">
                                    {{ $log->user?->name ?? '—' }}
                                    <span class="block text-xs font-normal opacity-70 font-mono" dir="ltr">{{ $log->user?->mobile ?? '' }}</span>
                                </td>
                                <td class="whitespace-nowrap">
                                    @php
                                        $typeLabel = match($log->type) {
                                            'installment_reminder' => 'یادآور قسط',
                                            'policy_renewal' => 'تمدید بیمه‌نامه',
                                            'birthday_greeting' => 'تبریک تولد',
                                            'custom_message', 'custom' => 'پیام اختصاصی',
                                            default => $log->type,
                                        };
                                    @endphp
                                    <span class="status-pill status-awaiting">{{ $typeLabel }}</span>
                                </td>
                                <td class="whitespace-nowrap">
                                    @if($log->status === 'sent')
                                        <span class="status-pill status-paid">ارسال شده</span>
                                    @elseif($log->status === 'failed')
                                        <span class="status-pill status-overdue" title="{{ $log->error_message }}">ناموفق</span>
                                    @else
                                        <span class="status-pill status-pending">در صف ارسال</span>
                                    @endif
                                </td>
                                <td class="text-xs max-w-md truncate" title="{{ $log->message }}">
                                    {{ \Illuminate\Support\Str::limit($log->message, 75) }}
                                </td>
                                <td class="text-xs font-mono whitespace-nowrap" dir="ltr">
                                    {{ \App\Casts\JalaliDateCast::toJalali($log->created_at, 'Y/m/d H:i') }}
                                </td>
                                <td>
                                    @if($log->status === 'failed')
                                        <x-filament::button wire:click="retryFailedLog({{ $log->id }})" color="warning" size="xs">
                                            ارسال مجدد
                                        </x-filament::button>
                                    @else
                                        <span class="text-xs opacity-50">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center opacity-60">
                                    هنوز هیچ لاگ اعلانی ثبت نشده است.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- بخش ۳: پنج فعالیت خودکار آخر سیستم --}}
        <x-filament::section>
            <x-slot name="heading">
                ۳. پنج فعالیت خودکار آخر سیستم (مرتبط با اعلان‌ها و پشتیبان‌گیری)
            </x-slot>
            <x-slot name="description">
                گزارش آخرین اجراهای خودکار موتور اعلان، یادآور اقساط، تبریک تولد و بکاپ دیتابیس
            </x-slot>

            <div class="space-y-2.5">
                @forelse($activities as $activity)
                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-100/70 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/70">
                        <div class="space-y-1">
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $activity->title }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $activity->details['description'] ?? ($activity->details['category'] ?? 'اجرای خودکار سیستمی') }}
                            </p>
                        </div>
                        <span class="text-xs font-mono text-slate-400 whitespace-nowrap" dir="ltr">
                            {{ $activity->created_at_jalali }}
                        </span>
                    </div>
                @empty
                    <p class="text-center py-6 text-sm opacity-60">هنوز فعالیت خودکاری ثبت نشده است.</p>
                @endforelse
            </div>
        </x-filament::section>

        {{-- بخش ۴: ارسال پیام اختصاصی گروهی یا تکی (کادر یکپارچه جستجو و انتخاب گروهی) --}}
        <x-filament::section id="custom-message-section">
            <x-slot name="heading">
                ۴. ارسال پیام اختصاصی به مشتریان (گروهی یا تکی)
            </x-slot>
            <x-slot name="description">
                جستجو، انتخاب تکی یا گروهی بیمه‌گذاران و ارسال مستقیم پیام به پنل کاربری و ربات بله
            </x-slot>

            <form wire:submit="sendCustomMessage" class="space-y-5">

                {{-- کادر یکپارچه جستجو + انتخاب گروهی بیمه‌گذاران --}}
                <div class="unified-recipient-picker">
                    {{-- نوار ابزار بالای کادر: جستجو و دکمه‌های انتخاب گروهی --}}
                    <div class="picker-toolbar">
                        <div class="picker-search-wrap">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="customerSearch"
                                placeholder="🔍 جستجوی سریع نام، شماره موبایل یا کد ملی بیمه‌گذار..."
                                class="afagh-input"
                            />
                        </div>

                        <div class="picker-actions">
                            <span class="selected-counter-badge">
                                {{ count($custom_user_ids) }} نفر انتخاب شده
                            </span>

                            <button type="button" wire:click="selectAllVisibleCustomers" class="picker-btn picker-btn-teal">
                                ✓ انتخاب همه نتایج ({{ $customers->count() }})
                            </button>

                            <button type="button" wire:click="selectOverdueCustomers" class="picker-btn picker-btn-amber">
                                ! انتخاب بدهکاران معوق
                            </button>

                            @if(count($custom_user_ids) > 0)
                                <button type="button" wire:click="clearSelectedCustomers" class="picker-btn picker-btn-rose">
                                    ✕ لغو انتخاب‌ها
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- لیست شبکه‌ای بیمه‌گذاران با قابلیت تیک زدن تکی و گروهی --}}
                    <div class="picker-grid">
                        @forelse($customers as $customer)
                            @php
                                $isSelected = in_array((int) $customer->id, array_map('intval', $custom_user_ids), true);
                            @endphp
                            <label class="picker-item {{ $isSelected ? 'picker-item-active' : '' }}">
                                <input
                                    type="checkbox"
                                    value="{{ $customer->id }}"
                                    wire:model.live="custom_user_ids"
                                    class="picker-checkbox"
                                />
                                <div class="picker-item-info">
                                    <span class="picker-item-name">{{ $customer->name }}</span>
                                    <span class="picker-item-meta" dir="ltr">
                                        {{ $customer->mobile ?: '—' }}
                                        @if($customer->national_code)
                                            | {{ $customer->national_code }}
                                        @endif
                                    </span>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full py-8 text-center text-sm opacity-60">
                                هیچ بیمه‌گذاری با این مشخصات یافت نشد.
                            </div>
                        @endforelse
                    </div>
                </div>
                @error('custom_user_ids') <span class="text-xs text-rose-500 block">{{ $message }}</span> @enderror

                {{-- کادر متن پیام اختصاصی --}}
                <div>
                    <label class="block text-xs font-bold mb-2">متن پیام اختصاصی *</label>
                    <textarea
                        wire:model="custom_message"
                        id="custom-message-textarea"
                        rows="3"
                        placeholder="متن پیام خود را برای ارسال به بیمه‌گذاران انتخاب‌شده بنویسید..."
                        class="afagh-input"
                    ></textarea>
                    @error('custom_message') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end">
                    <x-filament::button type="submit" color="success" icon="heroicon-o-paper-airplane">
                        ارسال پیام به ({{ count($custom_user_ids) }}) بیمه‌گذار انتخاب‌شده
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

    </div>
</x-filament-panels::page>