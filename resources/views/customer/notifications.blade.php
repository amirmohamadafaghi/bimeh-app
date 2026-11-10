@extends('customer.layouts.app')

@section('title', 'اعلان‌ها | آفاق بیمه')
@section('page_title', 'اعلان‌ها')

@section('content')

    <section class="fi-glass-card">
        {{-- هدر یکپارچه و مینیمال اعلان‌ها --}}
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding-bottom: 0.9rem; border-bottom: 1px solid var(--border-subtle); margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div class="minimal-icon-badge">
                    <img src="{{ asset('images/icons/app_notifications.svg') }}" alt="اعلان‌ها" class="icon-img-sm" />
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 0.96rem; font-weight: 900;">مرکز پیام‌ها و اعلان‌ها</h2>
                    <span style="font-size: 0.72rem; color: var(--text-muted);">
                        پیام‌های مدیریت و یادآورهای خودکار سررسید اقساط
                    </span>
                </div>
            </div>

            {{-- ابزارهای کاربردی: فیلتر + خواندن همه + بله + نوتیفیکیشن مرورگر --}}
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.45rem;">
                <div style="display: inline-flex; background: var(--bg-subtle); border: 1px solid var(--border-card); border-radius: 0.7rem; padding: 0.18rem; gap: 0.18rem;">
                    <a href="{{ route('customer.notifications', ['filter' => 'all']) }}"
                       style="padding: 0.32rem 0.7rem; border-radius: 0.55rem; text-decoration: none; font-size: 0.74rem; font-weight: 800; {{ $currentFilter === 'all' ? 'background: var(--primary-teal-soft); color: var(--primary-teal); border: 1px solid var(--primary-teal-border);' : 'color: var(--text-secondary);' }}">
                        همه
                    </a>
                    <a href="{{ route('customer.notifications', ['filter' => 'unread']) }}"
                       style="padding: 0.32rem 0.7rem; border-radius: 0.55rem; text-decoration: none; font-size: 0.74rem; font-weight: 800; {{ $currentFilter === 'unread' ? 'background: var(--primary-teal-soft); color: var(--primary-teal); border: 1px solid var(--primary-teal-border);' : 'color: var(--text-secondary);' }}">
                        خوانده‌نشده ({{ $unreadCount }})
                    </a>
                </div>

                <form method="POST" action="{{ route('customer.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn-subtle-action" title="علامت‌گذاری همه به عنوان خوانده‌شده">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7M5 7l4 4L15 5"/>
                        </svg>
                        <span>خواندن همه</span>
                    </button>
                </form>

                <button type="button" id="enable-webpush-btn" onclick="enableBrowserPush()" class="btn-subtle-action">
                    <span>{{ !empty($user->push_subscription) ? '✓ اعلان مرورگر' : 'اعلان مرورگر' }}</span>
                </button>

                <a href="{{ $baleBotUrl }}" target="_blank" class="btn-teal-solid" style="padding: 0.44rem 0.85rem; font-size: 0.74rem;">
                    <span>{{ !empty($user->bale_chat_id) ? 'ربات بله (متصل)' : 'اتصال به بله ↗' }}</span>
                </a>
            </div>
        </div>

        {{-- لیست پیام‌ها با کارت‌های مینیمال هم‌سبک با بخش راهنما --}}
        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
            @forelse($paginatedNotifications as $index => $notif)
                <div class="minimal-sub-card" style="display: flex; align-items: flex-start; gap: 0.85rem; {{ $notif['is_unread'] ? 'border-color: var(--primary-teal);' : '' }}">
                    <div class="minimal-icon-badge" style="width: 34px; height: 34px; font-size: 0.8rem;">
                        {{ $index + 1 }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.4rem; margin-bottom: 0.3rem;">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <strong style="font-size: 0.86rem; font-weight: 900;">{{ $notif['title'] }}</strong>
                                @if($notif['is_unread'])
                                    <span style="padding: 0.1rem 0.45rem; border-radius: 6px; background: var(--primary-teal-soft); color: var(--primary-teal); font-size: 0.66rem; font-weight: 800;">
                                        جدید
                                    </span>
                                @endif
                            </div>
                            <span dir="ltr" style="font-size: 0.72rem; font-family: monospace; color: var(--text-muted);">
                                {{ $notif['created_jalali'] }}
                            </span>
                        </div>
                        <p style="margin: 0; font-size: 0.8rem; color: var(--text-secondary); line-height: 1.7; white-space: pre-line;">{{ $notif['message'] }}</p>
                    </div>
                </div>
            @empty
                <div class="minimal-sub-card" style="text-align: center; padding: 2.2rem 1rem; color: var(--text-muted);">
                    <p style="margin: 0; font-size: 0.82rem; font-weight: 700;">هیچ اعلانی در این بخش وجود ندارد.</p>
                </div>
            @endforelse
        </div>

        @if($paginatedNotifications->hasPages())
            <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                @if(! $paginatedNotifications->onFirstPage())
                    <a href="{{ $paginatedNotifications->previousPageUrl() }}" class="btn-subtle-action">صفحه قبل</a>
                @endif
                <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted);">
                    صفحه {{ $paginatedNotifications->currentPage() }} از {{ $paginatedNotifications->lastPage() }}
                </span>
                @if($paginatedNotifications->hasMorePages())
                    <a href="{{ $paginatedNotifications->nextPageUrl() }}" class="btn-subtle-action">صفحه بعد</a>
                @endif
            </div>
        @endif
    </section>

@endsection

@push('scripts')
<script>
    async function enableBrowserPush() {
        if (!('Notification' in window)) {
            alert('مرورگر شما از اعلان‌های وب پشتیبانی نمی‌کند.');
            return;
        }
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') return;

        fetch('{{ route('customer.push.subscribe') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ endpoint: window.location.origin, keys: { p256dh: 'browser-granted', auth: 'active' } })
        })
        .then(r => r.json())
        .then(() => {
            const btn = document.getElementById('enable-webpush-btn');
            if (btn) btn.textContent = '✓ اعلان مرورگر';
        })
        .catch(() => {});
    }
</script>
@endpush