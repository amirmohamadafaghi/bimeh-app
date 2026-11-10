<x-filament-panels::page>
    {{-- جدول اصلی شرکت‌های بیمه و انواع بیمه‌نامه --}}
    <div>
        {{ $this->table }}
    </div>

    {{-- بخش سرویس‌ها (بات بله، اعلان‌های وب‌اپلیکیشن و ارسال هوشمند) --}}
    <div class="mt-8 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">⚙️ مرکز سرویس‌ها و اعلان‌های هوشمند</h2>
                <p class="text-xs text-gray-500 mt-1">مدیریت یکپارچه بات بله، اعلان‌های وب‌اپلیکیشن و ارسال خودکار پیام‌های معوقات و تمدید</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    wire:click="$set('serviceTab', 'smart_dispatch')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $serviceTab === 'smart_dispatch' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}"
                >
                    🚀 ارسال خودکار (معوقات و انقضا)
                </button>
                <button
                    type="button"
                    wire:click="$set('serviceTab', 'bale_settings_logs')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $serviceTab === 'bale_settings_logs' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}"
                >
                    🤖 تنظیمات و لاگ بات بله
                </button>
                <button
                    type="button"
                    wire:click="$set('serviceTab', 'webapp_notifications')"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $serviceTab === 'webapp_notifications' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}"
                >
                    🔔 اعلان‌های وب‌اپلیکیشن
                </button>
            </div>
        </div>

        {{-- تب ۱: ارسال خودکار با شناسایی افراد از اقساط و بیمه‌نامه‌ها --}}
        @if($serviceTab === 'smart_dispatch')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="p-5 rounded-2xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/40 dark:bg-rose-950/20 flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-bold text-rose-700 dark:text-rose-400">📢 ارسال پیام اختصاصی اقساط معوق</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                            با کلیک روی این دکمه، سیستم به صورت خودکار تمام افرادی که دارای قسط معوق در بخش اقساط هستند را پیدا کرده و پیام اخطار دیرکرد را برایشان در بات بله و وب‌اپلیکیشن ارسال می‌کند.
                        </p>
                    </div>
                    <div class="mt-5">
                        <x-filament::button color="danger" icon="heroicon-o-bolt" wire:click="triggerAutoOverdueMessages">
                            شناسایی خودکار و ارسال پیام اقساط معوق
                        </x-filament::button>
                    </div>
                </div>

                <div class="p-5 rounded-2xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/40 dark:bg-amber-950/20 flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-bold text-amber-700 dark:text-amber-400">⏳ ارسال پیام اختصاصی انقضای بیمه‌نامه</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                            با کلیک روی این دکمه، سیستم به صورت خودکار تمام بیمه‌نامه‌هایی که در آستانه انقضا هستند را از بخش بیمه‌نامه‌ها استخراج کرده و پیام یادآور تمدید را برای صاحبان آن‌ها ارسال می‌کند.
                        </p>
                    </div>
                    <div class="mt-5">
                        <x-filament::button color="warning" icon="heroicon-o-clock" wire:click="triggerAutoExpirationMessages">
                            شناسایی خودکار و ارسال پیام انقضای بیمه‌نامه
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif

        {{-- تب ۲: تنظیمات و لاگ بات بله --}}
        @if($serviceTab === 'bale_settings_logs')
            <div class="mt-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold mb-1">قالب پیام یادآور قسط</label>
                        <textarea wire:model="installmentTemplate" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-xs p-2.5"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1">قالب پیام قسط معوق</label>
                        <textarea wire:model="overdueTemplate" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-xs p-2.5"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold mb-1">قالب پیام انقضای بیمه‌نامه</label>
                        <textarea wire:model="renewalTemplate" rows="3" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-xs p-2.5"></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold">بازه بررسی انقضای بیمه‌نامه (روز قبل از انقضا):</label>
                        <input type="number" wire:model="renewalDaysBefore" class="w-20 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-xs p-1.5 text-center" />
                    </div>
                    <x-filament::button size="sm" color="success" wire:click="saveBaleSettings">
                        ذخیره تنظیمات بات بله
                    </x-filament::button>
                </div>

                <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-800 pt-4">
                    <h4 class="text-sm font-bold mb-3">آخرین لاگ‌های بات بله</h4>
                    <table class="w-full text-xs text-right">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="p-2.5">مخاطب</th>
                                <th class="p-2.5">متن پیام</th>
                                <th class="p-2.5">وضعیت</th>
                                <th class="p-2.5">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($this->baleLogs as $log)
                                <tr>
                                    <td class="p-2.5 font-bold">{{ $log->user?->name ?: $log->user?->mobile }}</td>
                                    <td class="p-2.5 max-w-md truncate">{{ $log->message }}</td>
                                    <td class="p-2.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : ($log->status === 'failed' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ $log->status === 'sent' ? 'ارسال شده' : ($log->status === 'failed' ? 'خطا' : 'در صف') }}
                                        </span>
                                    </td>
                                    <td class="p-2.5">
                                        @if($log->status === 'failed')
                                            <x-filament::button size="xs" color="warning" wire:click="retryBaleLog({{ $log->id }})">تلاش مجدد</x-filament::button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-4 text-center text-gray-400">لاگی ثبت نشده است.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- تب ۳: اعلان‌های وب‌اپلیکیشن --}}
        @if($serviceTab === 'webapp_notifications')
            <div class="mt-6 overflow-x-auto">
                <h4 class="text-sm font-bold mb-3">تاریخچه اعلان‌های وب‌اپلیکیشن و Web Push</h4>
                <table class="w-full text-xs text-right">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="p-2.5">نام کاربر</th>
                            <th class="p-2.5">کانال</th>
                            <th class="p-2.5">متن اعلان</th>
                            <th class="p-2.5">زمان ارسال</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->webAppLogs as $log)
                            <tr>
                                <td class="p-2.5 font-bold">{{ $log->user?->name ?: $log->user?->mobile }}</td>
                                <td class="p-2.5">{{ $log->channel === 'webpush' ? 'اعلان مرورگر (WebPush)' : 'داخلی وب‌اپلیکیشن' }}</td>
                                <td class="p-2.5">{{ $log->message }}</td>
                                <td class="p-2.5">{{ \App\Casts\JalaliDateCast::toJalali($log->created_at) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-4 text-center text-gray-400">اعلانی ثبت نشده است.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>