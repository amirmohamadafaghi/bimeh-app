<aside class="afagh-desktop-sidebar d-none d-md-flex">
    <div>
        {{-- برندینگ بالای سایدبار --}}
        <a href="{{ route('customer.dashboard') }}" class="sidebar-brand-box">
            <img
                src="{{ asset('images/icons/logo-icon.png') }}"
                alt="آفاق بیمه"
                class="afagh-pure-logo-img"
                data-afagh-transparent-logo="true"
            />
            <div class="afagh-pure-brand-text">
                <span class="afagh-pure-title">آفاق بیمه</span>
                <span class="afagh-pure-subtitle">پرتال بیمه‌گذاران</span>
            </div>
        </a>

        {{-- لینک‌های منوی عمودی سمت راست --}}
        <nav class="sidebar-nav-list">
            <a href="{{ route('customer.dashboard') }}"
               class="sidebar-nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                <img src="{{ asset('images/icons/app_dashboard.svg') }}" alt="داشبورد" class="icon-img-sm" />
                <span>داشبورد</span>
            </a>

            <a href="{{ route('customer.policies') }}"
               class="sidebar-nav-link {{ request()->routeIs('customer.policies') ? 'active' : '' }}">
                <img src="{{ asset('images/icons/app_installments.svg') }}" alt="بیمه و اقساط" class="icon-img-sm" />
                <span>بیمه و اقساط</span>
                @if(($upcomingCount ?? 0) > 0)
                    <span class="sidebar-Soft-pill">{{ $upcomingCount }}</span>
                @endif
            </a>

            <a href="{{ route('customer.notifications') }}"
               class="sidebar-nav-link {{ request()->routeIs('customer.notifications') ? 'active' : '' }}">
                <img src="{{ asset('images/icons/app_notifications.svg') }}" alt="اعلان‌ها" class="icon-img-sm" />
                <span>اعلان‌ها</span>
                @if(($unreadCount ?? 0) > 0)
                    <span class="sidebar-unread-pill">{{ $unreadCount }}</span>
                @endif
            </a>

            <a href="{{ route('customer.profile') }}"
               class="sidebar-nav-link {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                <img src="{{ asset('images/icons/app_policyholders.svg') }}" alt="پروفایل" class="icon-img-sm" />
                <span>پروفایل کاربری</span>
            </a>

            <a href="{{ route('customer.contact') }}"
               class="sidebar-nav-link {{ request()->routeIs('customer.contact') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary-teal);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span>تماس با ما</span>
            </a>
        </nav>
    </div>

    {{-- بخش پایین سایدبار با طراحی مینیمال --}}
    <div class="sidebar-footer-box">
        <a href="{{ $baleBotUrl ?? '#' }}" target="_blank" class="sidebar-bale-card">
            <div class="d-flex align-items-center justify-content-between">
                <span style="font-size: 0.76rem; font-weight: 800;">ربات پیام‌رسان بله</span>
                <span style="font-size: 0.68rem; font-weight: 800; padding: 0.1rem 0.45rem; border-radius: 6px; background: {{ !empty($user->bale_chat_id) ? 'rgba(16,185,129,0.14)' : 'rgba(245,158,11,0.14)' }}; color: {{ !empty($user->bale_chat_id) ? '#10b981' : '#f59e0b' }};">
                    {{ !empty($user->bale_chat_id) ? 'متصل ✓' : 'اتصال ↗' }}
                </span>
            </div>
        </a>

        <form method="POST" action="{{ route('customer.logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout-btn">
                <img src="{{ asset('images/icons/app_logout.svg') }}" alt="خروج" class="icon-img-xs" />
                <span>خروج از حساب</span>
            </button>
        </form>
    </div>
</aside>