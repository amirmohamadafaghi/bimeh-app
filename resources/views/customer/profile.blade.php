@extends('customer.layouts.app')

@section('title', 'پروفایل کاربری | آفاق بیمه')
@section('page_title', 'پروفایل کاربری')

@push('styles')
<style>
    .profile-minimal-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.1rem;
    }
    @media (min-width: 820px) {
        .profile-minimal-grid {
            grid-template-columns: 310px 1fr;
            align-items: start;
        }
    }
</style>
@endpush

@section('content')

    <div class="profile-minimal-grid">

        {{-- ستون راست: مشخصات هویتی رسمی (بدون تکرار و بدون کارت سبز بزرگ) --}}
        <section class="fi-glass-card" style="display: flex; flex-direction: column; gap: 0.9rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--border-subtle);">
                <div class="minimal-icon-badge" style="width: 42px; height: 42px; font-size: 1rem;">
                    {{ mb_substr($user->name ?: 'م', 0, 1) }}
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 0.96rem; font-weight: 900;">{{ $user->name ?: 'نام ثبت نشده' }}</h2>
                    <span style="font-size: 0.72rem; color: var(--text-muted);">بیمه‌گذار رسمی آفاق بیمه</span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.55rem;">
                <div class="minimal-sub-card" style="padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.74rem; color: var(--text-muted);">کد ملی</span>
                    <strong dir="ltr" style="font-size: 0.82rem; font-family: monospace;">{{ $user->national_code ?: '—' }}</strong>
                </div>

                <div class="minimal-sub-card" style="padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.74rem; color: var(--text-muted);">شماره موبایل</span>
                    <strong dir="ltr" style="font-size: 0.82rem; font-family: monospace;">{{ $user->mobile ?: '—' }}</strong>
                </div>

                <div class="minimal-sub-card" style="padding: 0.65rem 0.85rem; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.74rem; color: var(--text-muted);">تعداد بیمه‌نامه‌ها</span>
                    <strong style="font-size: 0.82rem; color: var(--primary-teal);">{{ $policies->count() }} فقره</strong>
                </div>
            </div>

            <form method="POST" action="{{ route('customer.logout') }}" style="margin-top: 0.25rem;">
                @csrf
                <button type="submit" class="sidebar-logout-btn">
                    <img src="{{ asset('images/icons/app_logout.svg') }}" alt="خروج" class="icon-img-xs" />
                    <span>خروج از حساب کاربری</span>
                </button>
            </form>
        </section>

        {{-- ستون چپ: فرم تکمیل و ویرایش اطلاعات پروفایل --}}
        <section class="fi-glass-card">
            <div style="display: flex; align-items: center; gap: 0.65rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--border-subtle); margin-bottom: 1rem;">
                <div class="minimal-icon-badge">
                    <img src="{{ asset('images/icons/app_edit.svg') }}" alt="ویرایش" class="icon-img-sm" />
                </div>
                <div>
                    <h2 style="margin: 0; font-size: 0.96rem; font-weight: 900;">تکمیل و ویرایش اطلاعات فردی</h2>
                    <span style="font-size: 0.72rem; color: var(--text-muted);">
                        تاریخ تولد، جنسیت و نشانی پستی خود را جهت ارسال بیمه‌نامه تکمیل نمایید
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('customer.profile.update') }}" style="display: flex; flex-direction: column; gap: 0.95rem;">
                @csrf

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.85rem;">
                    <div>
                        <label for="birth_date" style="display: block; font-size: 0.78rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--text-secondary);">
                            تاریخ تولد شمسی
                        </label>
                        <input
                            type="text"
                            id="birth_date"
                            name="birth_date"
                            value="{{ old('birth_date', $user->birth_date) }}"
                            placeholder="مثال: 1370/01/01"
                            dir="ltr"
                            class="afagh-input"
                        />
                    </div>

                    <div>
                        <label for="gender" style="display: block; font-size: 0.78rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--text-secondary);">
                            جنسیت
                        </label>
                        <select id="gender" name="gender" class="afagh-input">
                            <option value="">-- انتخاب کنید --</option>
                            <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>مرد</option>
                            <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>زن</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="address" style="display: block; font-size: 0.78rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--text-secondary);">
                        آدرس دقیق محل سکونت یا محل کار
                    </label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        placeholder="استان، شهر، خیابان، کوچه، پلاک..."
                        class="afagh-input"
                    >{{ old('address', $user->address ?? '') }}</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-teal-solid">
                        <span>ذخیره تغییرات پروفایل</span>
                    </button>
                </div>
            </form>
        </section>

    </div>

@endsection