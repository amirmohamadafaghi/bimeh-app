<header class="afagh-top-header sticky-top">
    {{-- ۱. چیدمان مخصوص موبایل (فقط در عرض موبایل: اعلان و پشتیبانی سمت راست، لوگو سمت چپ) --}}
    <div class="d-flex d-md-none justify-content-between align-items-center w-100">
        {{-- سمت راست هدر موبایل: آیکون اعلان‌ها + کنار آن به سمت چپ آیکون پشتیبانی تلفن + تغییر تم --}}
        <div class="d-flex align-items-center gap-2">
            {{-- گوشه سمت راست: آیکون اعلان‌ها --}}
            <a href="{{ route('customer.notifications') }}"
               class="header-action-btn {{ request()->routeIs('customer.notifications') ? 'active' : '' }}"
               title="اعلان‌ها">
                <img src="{{ asset('images/icons/app_notifications.svg') }}" alt="اعلان‌ها" class="icon-img-sm" />
                @if(($unreadCount ?? 0) > 0)
                    <span class="notif-counter-badge">{{ $unreadCount }}</span>
                @endif
            </a>

            {{-- کنار آن به سمت چپ: آیکون پشتیبانی تلفن --}}
            <a href="{{ route('customer.contact') }}"
               class="header-action-btn {{ request()->routeIs('customer.contact') ? 'active' : '' }}"
               title="پشتیبانی و تماس با ما">
                <svg class="w-5 h-5" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </a>

            {{-- دکمه تغییر تم روشن/تیره --}}
            <button type="button" onclick="toggleTheme()" class="header-action-btn" title="تغییر تم">
                <img src="{{ asset('images/icons/app_moon_and_sun.svg') }}" alt="تغییر تم" class="icon-img-sm" />
            </button>
        </div>

        {{-- سمت چپ هدر موبایل: آیکون و لوگوی شرکت بیمه --}}
        <a href="{{ route('customer.dashboard') }}" class="afagh-pure-brand ms-auto" style="flex-direction: row-reverse;">
            <img
                src="{{ asset('images/icons/logo-icon.png') }}"
                alt="آفاق بیمه"
                class="afagh-pure-logo-img"
                data-afagh-transparent-logo="true"
                style="width: 2.35rem; height: 2.35rem;"
            />
            <div class="afagh-pure-brand-text" style="align-items: flex-end; text-align: left;">
                <span class="afagh-pure-title" style="font-size: 1.02rem;">آفاق بیمه</span>
                <span class="afagh-pure-subtitle" style="font-size: 0.64rem;">نمایندگی آفاقی</span>
            </div>
        </a>
    </div>

    {{-- ۲. چیدمان مخصوص دسکتاپ (حفظ ساختار استاندارد دسکتاپ) --}}
    <div class="d-none d-md-flex justify-content-between align-items-center w-100">
        <div class="d-flex align-items-center gap-3">
            <span class="desktop-page-indicator">
                @yield('page_title', 'پرتال هوشمند بیمه‌گذاران')
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            {{-- دکمه نصب وب‌اپلیکیشن --}}
            <button id="pwa-install-header-btn" type="button" onclick="triggerPwaInstall()" class="pwa-pill-btn" style="display: none;">
                <img src="{{ asset('images/icons/app_computer_mode.svg') }}" alt="نصب" class="icon-img-xs me-1" />
                <span>نصب وب‌اپلیکیشن</span>
            </button>

            {{-- میان‌بر پنل مدیریت ویژه ادمین و منجر --}}
            @if(isset($user) && ($user->isAdmin() || $user->isManager()))
                <a href="{{ url('/admin') }}" class="admin-switch-pill">
                    <img src="{{ asset('images/icons/app_admin.svg') }}" alt="پنل مدیریت" class="icon-img-xs me-1" />
                    <span>پنل مدیریت</span>
                </a>
            @endif

            {{-- تغییر تم --}}
            <button type="button" onclick="toggleTheme()" class="header-action-btn" title="تغییر تم">
                <img src="{{ asset('images/icons/app_moon_and_sun.svg') }}" alt="تغییر تم" class="icon-img-sm" />
            </button>

            {{-- پشتیبانی و تماس با ما --}}
            <a href="{{ route('customer.contact') }}"
               class="header-action-btn {{ request()->routeIs('customer.contact') ? 'active' : '' }}"
               title="تماس با ما">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </a>

            {{-- اعلان‌ها --}}
            <a href="{{ route('customer.notifications') }}"
               class="header-action-btn {{ request()->routeIs('customer.notifications') ? 'active' : '' }}"
               title="اعلان‌ها">
                <img src="{{ asset('images/icons/app_notifications.svg') }}" alt="اعلان‌ها" class="icon-img-sm" />
                @if(($unreadCount ?? 0) > 0)
                    <span class="notif-counter-badge">{{ $unreadCount }}</span>
                @endif
            </a>

            {{-- پروفایل کاربر --}}
            <a href="{{ route('customer.profile') }}" class="desktop-profile-pill {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                <span>{{ $user->name ?: 'پروفایل کاربری' }}</span>
                <span class="avatar-badge">{{ mb_substr($user->name ?: 'م', 0, 1) }}</span>
            </a>
        </div>
    </div>
</header>