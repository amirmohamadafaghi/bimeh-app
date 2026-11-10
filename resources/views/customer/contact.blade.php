@extends('customer.layouts.app')

@section('title', 'تماس با ما | آفاق بیمه')
@section('page_title', 'تماس با ما')

@push('styles')
<style>
    .contact-minimal-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .contact-minimal-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('content')

    <section class="fi-glass-card">
        {{-- هدر معرفی نمایندگی به سبک بخش راهنما --}}
        <div style="display: flex; align-items: center; gap: 0.75rem; padding-bottom: 0.9rem; border-bottom: 1px solid var(--border-subtle); margin-bottom: 1.1rem;">
            <img
                src="{{ asset('images/icons/logo-icon.png') }}"
                alt="آفاق بیمه"
                class="afagh-pure-logo-img"
                data-afagh-transparent-logo="true"
                style="width: 2.6rem; height: 2.6rem;"
            />
            <div>
                <h1 style="margin: 0; font-size: 1rem; font-weight: 900;">
                    شرکت آریا سرای آرامش (نمایندگی آفاقی)
                </h1>
                <span style="font-size: 0.75rem; color: var(--text-muted);">
                    نمایندگی رسمی بیمه ایران — مشاوره، صدور بیمه‌نامه و پیگیری امور اقساط و خسارت
                </span>
            </div>
        </div>

        {{-- کارت‌های اطلاعات تماس با استایل مینیمال بخش راهنما --}}
        <div class="contact-minimal-grid">

            {{-- کارت ۱: تلفن تماس --}}
            <div class="minimal-sub-card" style="display: flex; flex-direction: column; justify-content: space-between; gap: 0.9rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                    <div class="minimal-icon-badge">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 style="margin: 0 0 0.25rem 0; font-size: 0.9rem; font-weight: 900;">تلفن تماس دفتر نمایندگی</h2>
                        <p style="margin: 0; font-size: 0.76rem; color: var(--text-muted);">
                            پاسخگویی و پشتیبانی تلفنی در ساعات اداری
                        </p>
                        <div dir="ltr" style="margin-top: 0.5rem; font-size: 1.05rem; font-weight: 900; font-family: monospace; color: var(--primary-teal);">
                            056-32760024
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <a href="tel:05632760024" class="btn-teal-solid" style="flex: 1;">
                        تماس مستقیم
                    </a>
                    <button type="button" onclick="copyTextToClipboard('05632760024', '✅ شماره تلفن کپی شد!')" class="btn-subtle-action">
                        کپی شماره
                    </button>
                </div>
            </div>

            {{-- کارت ۲: نشانی دفتر مرکزی --}}
            <div class="minimal-sub-card" style="display: flex; flex-direction: column; justify-content: space-between; gap: 0.9rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                    <div class="minimal-icon-badge">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 style="margin: 0 0 0.25rem 0; font-size: 0.9rem; font-weight: 900;">نشانی دفتر نمایندگی</h2>
                        <p style="margin: 0; font-size: 0.78rem; color: var(--text-secondary); line-height: 1.7;">
                            خراسان جنوبی، بشرویه، خیابان امام خمینی، روبه‌رو مخابرات، شرکت آریا سرای آرامش (نمایندگی آفاقی)
                        </p>
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="button"
                            onclick="copyTextToClipboard('خراسان جنوبی، بشرویه، خیابان امام خمینی، روبه‌رو مخابرات، شرکت آریا سرای آرامش (نمایندگی آفاقی)', '✅ آدرس نمایندگی کپی شد!')"
                            class="btn-teal-solid"
                            style="flex: 1;">
                        کپی آدرس دقیق
                    </button>
                    <a href="{{ $baleBotUrl }}" target="_blank" class="btn-subtle-action">
                        پیام‌رسان بله ↗
                    </a>
                </div>
            </div>

        </div>
    </section>

@endsection