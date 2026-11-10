<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\ManageAdminPermissions;
use App\Filament\Widgets\RecentChangesWidget;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName('آفاق بیمه')
            ->brandLogo(fn (): HtmlString => new HtmlString('
                <div class="afagh-pure-brand">
                    <img
                        src="' . asset('images/icons/logo-icon.png') . '"
                        alt="آفاق بیمه"
                        class="afagh-pure-logo-img"
                        data-afagh-transparent-logo="true"
                    />
                    <div class="afagh-pure-brand-text">
                        <span class="afagh-pure-title">آفاق بیمه</span>
                        <span class="afagh-pure-subtitle">وب اپلیکیشن</span>
                    </div>
                </div>
            '))
            ->font('Peyda', provider: LocalFontProvider::class)
            ->darkMode(true)
            ->colors([
                'primary' => Color::Teal,
                'danger'  => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info'    => Color::Indigo,
            ])
            ->topNavigation()
            ->maxContentWidth(MaxWidth::Full)
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): HtmlString => new HtmlString('
                    <div class="flex items-center gap-1.5">
                        <button id="pwa-install-admin-btn" type="button" style="display:none;" class="items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/70 border border-teal-300/60 dark:border-teal-700/60 rounded-xl hover:bg-teal-100 dark:hover:bg-teal-900 transition">
                            <span>📲 نصب وب‌اپلیکیشن</span>
                        </button>
                        ' . (auth()->user()?->isAdmin() ? '
                        <a href="' . ManageAdminPermissions::getUrl() . '" class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                            <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <span>اختیارات</span>
                        </a>
                        ' : '') . '
                    </div>
                ')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('
                    <link rel="manifest" href="/manifest.json">
                    <meta name="theme-color" content="#0f172a">
                    <style>
                        @font-face {
                            font-family: "Peyda";
                            font-style: normal;
                            font-weight: 300;
                            font-display: swap;
                            src: url("/fonts/peyda/Peyda-Light.woff2") format("woff2"),
                                 url("/fonts/peyda/Peyda-Light.ttf") format("truetype");
                        }
                        @font-face {
                            font-family: "Peyda";
                            font-style: normal;
                            font-weight: 400;
                            font-display: swap;
                            src: url("/fonts/peyda/Peyda-Regular.woff2") format("woff2"),
                                 url("/fonts/peyda/Peyda-Regular.ttf") format("truetype");
                        }
                        @font-face {
                            font-family: "Peyda";
                            font-style: normal;
                            font-weight: 500;
                            font-display: swap;
                            src: url("/fonts/peyda/Peyda-Medium.woff2") format("woff2"),
                                 url("/fonts/peyda/Peyda-Medium.ttf") format("truetype");
                        }
                        @font-face {
                            font-family: "Peyda";
                            font-style: normal;
                            font-weight: 600;
                            font-display: swap;
                            src: url("/fonts/peyda/Peyda-SemiBold.woff2") format("woff2"),
                                 url("/fonts/peyda/Peyda-SemiBold.ttf") format("truetype");
                        }
                        @font-face {
                            font-family: "Peyda";
                            font-style: normal;
                            font-weight: 700;
                            font-display: swap;
                            src: url("/fonts/peyda/Peyda-Bold.woff2") format("woff2"),
                                 url("/fonts/peyda/Peyda-Bold.ttf") format("truetype");
                        }
                        @font-face {
                            font-family: "Peyda";
                            font-style: normal;
                            font-weight: 800;
                            font-display: swap;
                            src: url("/fonts/peyda/Peyda-ExtraBold.woff2") format("woff2"),
                                 url("/fonts/peyda/Peyda-ExtraBold.ttf") format("truetype");
                        }
                        @font-face {
                            font-family: "Peyda";
                            font-style: normal;
                            font-weight: 900;
                            font-display: swap;
                            src: url("/fonts/peyda/Peyda-Black.woff2") format("woff2"),
                                 url("/fonts/peyda/Peyda-Black.ttf") format("truetype");
                        }

                        :root {
                            --font-family: "Peyda", sans-serif !important;
                        }

                        body, .fi-body, button, input, select, textarea {
                            font-family: "Peyda", sans-serif !important;
                        }

                        body, .fi-body {
                            direction: rtl;
                            overflow-x: hidden;
                            background-color: #f8fafc;
                        }
                        .dark body, .dark .fi-body {
                            background-color: #090d16 !important;
                            color: #f1f5f9;
                        }

                        /* استایل خالص لوگوی اصلی آفاق بیمه بدون هیچ بک‌گراند سفید */
                        .afagh-pure-brand {
                            display: inline-flex;
                            align-items: center;
                            gap: 0.7rem;
                            background: transparent !important;
                            border: none !important;
                            box-shadow: none !important;
                            padding: 0 !important;
                        }
                        .afagh-pure-logo-img {
                            width: 2.75rem;
                            height: 2.75rem;
                            object-fit: contain;
                            flex-shrink: 0;
                            background: transparent !important;
                            filter: drop-shadow(0 2px 8px rgba(59, 130, 246, 0.28));
                        }
                        .afagh-pure-brand-text {
                            display: flex;
                            flex-direction: column;
                            align-items: flex-start;
                            line-height: 1.15;
                        }
                        .afagh-pure-title {
                            font-size: 1.2rem;
                            font-weight: 900;
                            color: #0f172a;
                            letter-spacing: -0.02em;
                        }
                        .dark .afagh-pure-title {
                            color: #ffffff;
                        }
                        .afagh-pure-subtitle {
                            font-size: 0.7rem;
                            font-weight: 800;
                            color: #f59e0b;
                        }

                        /* بزرگ‌تر شدن لوگوی اصلی در صفحه ورود ادمین (/admin/login) */
                        .fi-simple-main .fi-simple-header {
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            gap: 0.75rem !important;
                            margin-bottom: 1.25rem !important;
                        }
                        .fi-simple-main .afagh-pure-brand {
                            gap: 0.9rem;
                            margin-bottom: 0.25rem;
                        }
                        .fi-simple-main .afagh-pure-logo-img {
                            width: 4.1rem;
                            height: 4.1rem;
                        }
                        .fi-simple-main .afagh-pure-title {
                            font-size: 1.7rem;
                        }
                        .fi-simple-main .afagh-pure-subtitle {
                            font-size: 0.85rem;
                        }
                        .fi-simple-main .fi-simple-header-heading {
                            font-size: 1.25rem !important;
                            font-weight: 800 !important;
                            opacity: 0.9;
                        }

                        /* نوار بالای پنل */
                        .fi-topbar nav {
                            backdrop-filter: blur(16px);
                            -webkit-backdrop-filter: blur(16px);
                            background-color: rgba(255, 255, 255, 0.9) !important;
                            border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
                        }
                        .dark .fi-topbar nav {
                            background-color: rgba(15, 23, 42, 0.9) !important;
                            border-bottom: 1px solid rgba(51, 65, 85, 0.5) !important;
                        }

                        /* کانتینر اصلی */
                        .fi-main {
                            width: 100% !important;
                            max-width: 1400px !important;
                            margin-inline: auto !important;
                            padding-inline: 1.25rem !important;
                            padding-bottom: 2.5rem !important;
                        }

                        /* کادر استاندارد برای جداول Filament */
                        .fi-ta {
                            width: 100% !important;
                            max-width: 100% !important;
                            border-radius: 1.25rem !important;
                            border: 1px solid rgba(148, 163, 184, 0.28) !important;
                            background-color: #ffffff !important;
                            box-shadow: 0 12px 28px -8px rgba(15, 23, 42, 0.06) !important;
                            overflow: hidden !important;
                        }
                        .dark .fi-ta {
                            background-color: #111827 !important;
                            border-color: rgba(71, 85, 105, 0.55) !important;
                            box-shadow: 0 16px 32px -10px rgba(0, 0, 0, 0.5) !important;
                        }
                        .fi-ta-content {
                            width: 100% !important;
                            overflow-x: auto !important;
                            padding-bottom: 0.25rem !important;
                        }
                        .fi-ta-table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                        }
                        .fi-ta-header-cell, .fi-ta-cell {
                            padding-top: 0.65rem !important;
                            padding-bottom: 0.65rem !important;
                            padding-inline: 0.6rem !important;
                            vertical-align: middle !important;
                        }
                        .fi-ta-actions {
                            padding-inline-end: 0.75rem !important;
                            gap: 0.35rem !important;
                            justify-content: flex-end !important;
                        }
                        .fi-topbar-item-icon, .fi-btn-icon {
                            width: 1.15rem !important;
                            height: 1.15rem !important;
                        }

                        .notif-page-wrapper {
                            display: flex;
                            flex-direction: column;
                            gap: 1.5rem;
                        }
                        .notif-section-block .fi-fo-component-ctn {
                            gap: 1.5rem !important;
                        }
                        .fi-section {
                            border-radius: 1.25rem !important;
                            border: 1px solid rgba(226, 232, 240, 0.9) !important;
                            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04) !important;
                        }
                        .dark .fi-section {
                            background-color: #111827 !important;
                            border-color: rgba(51, 65, 85, 0.6) !important;
                            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.45) !important;
                        }

                        /* کادر یکپارچه جستجو و انتخاب گروهی بیمه‌گذاران */
                        .unified-recipient-picker {
                            border-radius: 1.1rem;
                            border: 1px solid rgba(148, 163, 184, 0.3);
                            background-color: #f8fafc;
                            overflow: hidden;
                        }
                        .dark .unified-recipient-picker {
                            background-color: #0b1120;
                            border-color: rgba(71, 85, 105, 0.55);
                        }
                        .picker-toolbar {
                            display: flex;
                            flex-wrap: wrap;
                            align-items: center;
                            justify-content: space-between;
                            gap: 0.75rem;
                            padding: 0.85rem 1rem;
                            background-color: rgba(148, 163, 184, 0.08);
                            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
                        }
                        .picker-search-wrap { flex: 1 1 260px; }
                        .picker-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
                        .selected-counter-badge {
                            padding: 0.35rem 0.75rem;
                            border-radius: 0.65rem;
                            font-size: 0.75rem;
                            font-weight: 800;
                            background: rgba(20, 184, 166, 0.15);
                            color: #14b8a6;
                            border: 1px solid rgba(20, 184, 166, 0.35);
                        }
                        .picker-btn {
                            padding: 0.4rem 0.75rem;
                            border-radius: 0.65rem;
                            font-size: 0.75rem;
                            font-weight: 700;
                            cursor: pointer;
                        }
                        .picker-btn-teal { background: rgba(16, 185, 129, 0.14); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.35); }
                        .picker-btn-amber { background: rgba(245, 158, 11, 0.14); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.35); }
                        .picker-btn-rose { background: rgba(244, 63, 94, 0.14); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.35); }
                        .picker-grid {
                            display: grid;
                            grid-template-columns: repeat(1, minmax(0, 1fr));
                            gap: 0.5rem;
                            padding: 0.85rem;
                            max-height: 260px;
                            overflow-y: auto;
                        }
                        @media (min-width: 640px) { .picker-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
                        @media (min-width: 1024px) { .picker-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
                        .picker-item {
                            display: flex; align-items: center; gap: 0.65rem; padding: 0.6rem 0.75rem;
                            border-radius: 0.75rem; border: 1px solid rgba(148, 163, 184, 0.2);
                            background-color: rgba(255, 255, 255, 0.6); cursor: pointer;
                        }
                        .dark .picker-item { background-color: rgba(17, 24, 39, 0.75); border-color: rgba(51, 65, 85, 0.5); }
                        .picker-item-active { background-color: rgba(20, 184, 166, 0.12) !important; border-color: #14b8a6 !important; }
                        .picker-checkbox { width: 1.05rem !important; height: 1.05rem !important; accent-color: #0d9488; }
                        .picker-item-info { display: flex; flex-direction: column; min-width: 0; }
                        .picker-item-name { font-size: 0.8rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                        .picker-item-meta { font-size: 0.7rem; opacity: 0.7; font-family: monospace; }

                        .afagh-input { width: 100%; border-radius: 0.8rem; border: 1px solid rgba(148, 163, 184, 0.35); background-color: #ffffff; color: #0f172a; padding: 0.6rem 0.85rem; font-size: 0.84rem; background-image: none !important; }
                        .dark .afagh-input { background-color: #0f172a; color: #f8fafc; border-color: rgba(71, 85, 105, 0.6); background-image: none !important; }
                    </style>
                    <script>
                        /* حذف خودکار و ۱۰۰٪ دقیق پس‌زمینه سفید لوگوی اصلی با حفظ کیفیت لبه‌ها */
                        (function() {
                            function removeLogoWhiteBg(img) {
                                if (!img || img.dataset.bgRemoved === "1") return;
                                if (!img.complete || img.naturalWidth === 0) {
                                    img.addEventListener("load", function() { removeLogoWhiteBg(img); }, { once: true });
                                    return;
                                }
                                try {
                                    var canvas = document.createElement("canvas");
                                    var w = img.naturalWidth;
                                    var h = img.naturalHeight;
                                    canvas.width = w;
                                    canvas.height = h;
                                    var ctx = canvas.getContext("2d");
                                    ctx.drawImage(img, 0, 0);
                                    var imgData = ctx.getImageData(0, 0, w, h);
                                    var d = imgData.data;

                                    for (var i = 0; i < d.length; i += 4) {
                                        var r = d[i], g = d[i + 1], b = d[i + 2];
                                        var minRG = Math.min(r, g);
                                        /* محاسبه شفافیت بر اساس میزان سفیدی پیکسل */
                                        var alpha = Math.max(0, Math.min(255, Math.round((245 - minRG) * 1.25)));
                                        if (alpha < 18) {
                                            d[i + 3] = 0;
                                        } else {
                                            /* حفظ رنگ آبی اصلی لوگو با درخشندگی مناسب روی تم تیره و روشن */
                                            d[i] = 56;
                                            d[i + 1] = 128;
                                            d[i + 2] = 248;
                                            d[i + 3] = alpha;
                                        }
                                    }
                                    ctx.putImageData(imgData, 0, 0);
                                    img.dataset.bgRemoved = "1";
                                    img.src = canvas.toDataURL("image/png");
                                } catch (e) {}
                            }

                            function processAllLogos() {
                                document.querySelectorAll("img[data-afagh-transparent-logo]").forEach(removeLogoWhiteBg);
                            }

                            document.addEventListener("DOMContentLoaded", processAllLogos);
                            document.addEventListener("livewire:navigated", processAllLogos);
                            setTimeout(processAllLogos, 150);
                        })();
                    </script>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                RecentChangesWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}