<nav class="mobile-bottom-nav fixed-bottom d-md-none" aria-label="ناوبری موبایل">
    <div class="d-flex justify-content-between align-items-center w-100 mobile-nav-inner">

        {{-- ۱. سمت راست (در RTL اولین آیتم سمت راست قرار می‌گیرد): آیکون اقساط و بیمه‌ها --}}
        <a href="{{ route('customer.policies') }}"
           class="mobile-nav-item {{ request()->routeIs('customer.policies') ? 'active' : '' }}">
            <span class="mobile-nav-icon-wrap">
                <img src="{{ asset('images/icons/app_installments.svg') }}" alt="بیمه و اقساط" class="icon-img-sm" />
            </span>
            <span class="mobile-nav-label">بیمه و اقساط</span>
        </a>

        {{-- ۲. وسط: آیکون شرکت به صورت دایره‌ای و بالاتر از بقیه (حالت FAB) و دقیقاً وسط‌چین --}}
        <div class="mobile-nav-center-slot">
            <a href="{{ route('customer.dashboard') }}"
               class="mobile-fab-company {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"
               title="صفحه اصلی آفاق بیمه">
                <div class="mobile-fab-circle">
                    <img
                        src="{{ asset('images/icons/logo-icon.png') }}"
                        alt="آفاق بیمه"
                        class="mobile-fab-logo"
                        data-afagh-transparent-logo="true"
                    />
                </div>
                <span class="mobile-fab-label">آفاق بیمه</span>
            </a>
        </div>

        {{-- ۳. سمت چپ (در RTL آخرین آیتم سمت چپ قرار می‌گیرد): آیکون پروفایل --}}
        <a href="{{ route('customer.profile') }}"
           class="mobile-nav-item {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
            <span class="mobile-nav-icon-wrap">
                <img src="{{ asset('images/icons/app_policyholders.svg') }}" alt="پروفایل" class="icon-img-sm" />
            </span>
            <span class="mobile-nav-label">پروفایل</span>
        </a>

    </div>
</nav>