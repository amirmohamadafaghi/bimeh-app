<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#090d16">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="آفاق بیمه">
    <link rel="manifest" href="/manifest.json">
    <title>@yield('title', 'پرتال بیمه‌گذاران | آفاق بیمه')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        @font-face {
            font-family: 'Peyda';
            font-style: normal;
            font-weight: 300;
            font-display: swap;
            src: url('/fonts/peyda/Peyda-Light.woff2') format('woff2'),
                 url('/fonts/peyda/Peyda-Light.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Peyda';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('/fonts/peyda/Peyda-Regular.woff2') format('woff2'),
                 url('/fonts/peyda/Peyda-Regular.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Peyda';
            font-style: normal;
            font-weight: 500;
            font-display: swap;
            src: url('/fonts/peyda/Peyda-Medium.woff2') format('woff2'),
                 url('/fonts/peyda/Peyda-Medium.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Peyda';
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url('/fonts/peyda/Peyda-SemiBold.woff2') format('woff2'),
                 url('/fonts/peyda/Peyda-SemiBold.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Peyda';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('/fonts/peyda/Peyda-Bold.woff2') format('woff2'),
                 url('/fonts/peyda/Peyda-Bold.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Peyda';
            font-style: normal;
            font-weight: 800;
            font-display: swap;
            src: url('/fonts/peyda/Peyda-ExtraBold.woff2') format('woff2'),
                 url('/fonts/peyda/Peyda-ExtraBold.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Peyda';
            font-style: normal;
            font-weight: 900;
            font-display: swap;
            src: url('/fonts/peyda/Peyda-Black.woff2') format('woff2'),
                 url('/fonts/peyda/Peyda-Black.ttf') format('truetype');
        }

        :root {
            --bg-body: #f1f5f9;
            --bg-glass-header: rgba(255, 255, 255, 0.90);
            --bg-glass-sidebar: rgba(255, 255, 255, 0.94);
            --bg-card: #ffffff;
            --bg-subtle: #f8fafc;
            --border-card: rgba(148, 163, 184, 0.26);
            --border-subtle: rgba(226, 232, 240, 0.85);
            --shadow-card: 0 8px 22px -8px rgba(15, 23, 42, 0.05);
            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            --primary-teal: #0d9488;
            --primary-teal-light: #14b8a6;
            --primary-teal-soft: rgba(20, 184, 166, 0.10);
            --primary-teal-border: rgba(20, 184, 166, 0.28);
        }

        html.dark {
            --bg-body: #090d16;
            --bg-glass-header: rgba(15, 23, 42, 0.88);
            --bg-glass-sidebar: rgba(15, 23, 42, 0.92);
            --bg-card: #111827;
            --bg-subtle: rgba(30, 41, 59, 0.48);
            --border-card: rgba(71, 85, 105, 0.45);
            --border-subtle: rgba(51, 65, 85, 0.45);
            --shadow-card: 0 14px 28px -10px rgba(0, 0, 0, 0.45);
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --primary-teal: #14b8a6;
            --primary-teal-light: #2dd4bf;
            --primary-teal-soft: rgba(20, 184, 166, 0.12);
            --primary-teal-border: rgba(20, 184, 166, 0.30);
        }

        * {
            box-sizing: border-box;
            font-family: 'Peyda', sans-serif;
        }
        body, button, input, select, textarea {
            font-family: 'Peyda', sans-serif !important;
        }
        body {
            margin: 0;
            min-height: 100vh;
            direction: rtl;
            background-color: var(--bg-body);
            color: var(--text-primary);
            overflow-x: hidden;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        .d-flex { display: flex !important; }
        .d-none { display: none !important; }
        .justify-content-between { justify-content: space-between !important; }
        .justify-content-center { justify-content: center !important; }
        .align-items-center { align-items: center !important; }
        .w-100 { width: 100% !important; }
        .gap-1 { gap: 0.25rem !important; }
        .gap-2 { gap: 0.5rem !important; }
        .gap-3 { gap: 0.75rem !important; }
        .me-1 { margin-left: 0.25rem !important; }
        .me-2 { margin-left: 0.5rem !important; }
        .ms-1 { margin-right: 0.25rem !important; }
        .ms-2 { margin-right: 0.5rem !important; }
        .ms-auto { margin-right: auto !important; }
        .sticky-top { position: sticky; top: 0; z-index: 35; }
        .fixed-bottom { position: fixed; right: 0; left: 0; bottom: 0; z-index: 45; }

        @media (min-width: 768px) {
            .d-md-none { display: none !important; }
            .d-md-flex { display: flex !important; }
        }
        @media (max-width: 767.98px) {
            body {
                padding-bottom: calc(5.2rem + env(safe-area-inset-bottom)) !important;
            }
        }

        .portal-app-shell {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* سایدبار مینیمال دسکتاپ */
        .afagh-desktop-sidebar {
            width: 248px;
            flex-shrink: 0;
            background: var(--bg-glass-sidebar);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-left: 1px solid var(--border-card);
            padding: 1.15rem 0.9rem;
            flex-direction: column;
            justify-content: space-between;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 40;
        }
        .sidebar-brand-box {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.25rem 0.4rem 1.05rem 0.4rem;
            border-bottom: 1px solid var(--border-subtle);
            margin-bottom: 0.85rem;
            text-decoration: none;
            color: inherit;
        }
        .afagh-pure-logo-img {
            width: 2.5rem;
            height: 2.5rem;
            object-fit: contain;
            flex-shrink: 0;
            background: transparent !important;
            filter: drop-shadow(0 2px 8px rgba(59, 130, 246, 0.25));
        }
        .afagh-pure-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
            color: inherit;
        }
        .afagh-pure-brand-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.15;
        }
        .afagh-pure-title {
            font-size: 1.1rem;
            font-weight: 900;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }
        .afagh-pure-subtitle {
            font-size: 0.66rem;
            font-weight: 800;
            color: #f59e0b;
        }

        .sidebar-nav-list {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.62rem 0.8rem;
            border-radius: 0.75rem;
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 0.82rem;
            font-weight: 700;
            border: 1px solid transparent;
            transition: all 0.16s ease;
        }
        .sidebar-nav-link:hover {
            background: var(--bg-subtle);
            color: var(--text-primary);
        }
        .sidebar-nav-link.active {
            background: var(--primary-teal-soft);
            border-color: var(--primary-teal-border);
            color: var(--primary-teal-light);
            font-weight: 900;
        }
        html:not(.dark) .sidebar-nav-link.active {
            color: #0f766e;
        }
        .sidebar-unread-pill {
            margin-right: auto;
            background: #f43f5e;
            color: #ffffff;
            font-size: 0.66rem;
            font-weight: 800;
            padding: 0.08rem 0.45rem;
            border-radius: 9999px;
        }
        .sidebar-Soft-pill {
            margin-right: auto;
            background: var(--primary-teal-soft);
            border: 1px solid var(--primary-teal-border);
            color: var(--primary-teal);
            font-size: 0.66rem;
            font-weight: 800;
            padding: 0.05rem 0.45rem;
            border-radius: 9999px;
        }

        .sidebar-footer-box {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding-top: 0.85rem;
            border-top: 1px solid var(--border-subtle);
        }
        .sidebar-bale-card {
            padding: 0.6rem 0.75rem;
            border-radius: 0.75rem;
            background: var(--bg-subtle);
            border: 1px solid var(--border-card);
            text-decoration: none;
            color: var(--text-primary);
            transition: border-color 0.2s ease;
        }
        .sidebar-bale-card:hover {
            border-color: var(--primary-teal);
        }
        .sidebar-logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.55rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(244, 63, 94, 0.28);
            background: rgba(244, 63, 94, 0.08);
            color: #fb7185;
            font-size: 0.78rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.16s ease;
        }
        .sidebar-logout-btn:hover {
            background: #e11d48;
            color: #ffffff;
        }

        .portal-main-column {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .afagh-top-header {
            background: var(--bg-glass-header);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-card);
            padding: 0.6rem 1.25rem;
        }
        .desktop-page-indicator {
            font-size: 0.92rem;
            font-weight: 900;
            color: var(--text-primary);
        }
        .header-action-btn {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 0.7rem;
            border: 1px solid var(--border-card);
            background: var(--bg-subtle);
            color: var(--text-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.16s ease;
            padding: 0;
        }
        .header-action-btn:hover, .header-action-btn.active {
            border-color: var(--primary-teal);
            background: var(--primary-teal-soft);
            color: var(--primary-teal);
        }
        .notif-counter-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #f43f5e;
            color: #ffffff;
            font-size: 0.62rem;
            font-weight: 900;
            min-width: 16px;
            height: 16px;
            padding: 0 3px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .desktop-profile-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.28rem 0.35rem 0.28rem 0.75rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border-card);
            background: var(--bg-subtle);
            color: var(--text-primary);
            text-decoration: none;
            font-size: 0.78rem;
            font-weight: 800;
            transition: all 0.16s ease;
        }
        .desktop-profile-pill:hover, .desktop-profile-pill.active {
            border-color: var(--primary-teal);
        }
        .avatar-badge {
            width: 26px;
            height: 26px;
            border-radius: 0.55rem;
            background: var(--primary-teal-soft);
            border: 1px solid var(--primary-teal-border);
            color: var(--primary-teal);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 0.78rem;
        }

        .pwa-pill-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.38rem 0.75rem;
            border-radius: 0.7rem;
            background: var(--primary-teal-soft);
            border: 1px solid var(--primary-teal-border);
            color: var(--primary-teal);
            font-size: 0.74rem;
            font-weight: 800;
            cursor: pointer;
        }
        .admin-switch-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.38rem 0.8rem;
            border-radius: 0.7rem;
            background: var(--primary-teal-soft);
            border: 1px solid var(--primary-teal-border);
            color: var(--primary-teal);
            text-decoration: none;
            font-size: 0.74rem;
            font-weight: 800;
        }

        /* نوار پایین موبایل */
        .mobile-bottom-nav {
            background: var(--bg-glass-header);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-top: 1px solid var(--border-card);
            padding: 0.35rem 1.15rem calc(0.4rem + env(safe-area-inset-bottom));
            box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.22);
        }
        .mobile-nav-inner {
            max-width: 460px;
            margin: 0 auto;
            position: relative;
        }
        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.2rem;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 700;
            min-width: 76px;
            padding: 0.2rem 0.35rem;
            transition: all 0.2s ease;
        }
        .mobile-nav-item.active {
            color: var(--primary-teal);
            font-weight: 900;
        }
        .mobile-nav-icon-wrap {
            width: 32px;
            height: 28px;
            border-radius: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mobile-nav-item.active .mobile-nav-icon-wrap {
            background: var(--primary-teal-soft);
        }

        .mobile-nav-center-slot {
            display: flex;
            justify-content: center;
            align-items: center;
            min-width: 84px;
        }
        .mobile-fab-company {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-primary);
            position: relative;
            top: -18px;
            margin-bottom: -14px;
        }
        .mobile-fab-circle {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: var(--bg-card);
            border: 2px solid var(--primary-teal);
            box-shadow: 0 6px 18px rgba(20, 184, 166, 0.32);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mobile-fab-logo {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }
        .mobile-fab-label {
            font-size: 0.66rem;
            font-weight: 900;
            margin-top: 0.18rem;
            color: var(--text-primary);
        }

        .icon-img-xs { width: 15px; height: 15px; object-fit: contain; }
        .icon-img-sm { width: 18px; height: 18px; object-fit: contain; }
        .icon-img-md { width: 22px; height: 22px; object-fit: contain; }

        /* کانتینر و کارت‌های مینیمال هم‌سبک با بخش راهنما */
        .portal-content-container {
            width: 100%;
            max-width: 1120px;
            margin: 1.25rem auto;
            padding: 0 1.15rem;
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }
        .fi-glass-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 1.15rem;
            padding: 1.25rem;
            box-shadow: var(--shadow-card);
        }

        /* باکس‌های داخلی هم‌سبک با آیتم‌های راهنما */
        .minimal-sub-card {
            padding: 1rem 1.1rem;
            border-radius: 0.95rem;
            background: var(--bg-subtle);
            border: 1px solid var(--border-card);
            transition: border-color 0.18s ease;
        }
        .minimal-sub-card:hover {
            border-color: var(--primary-teal-border);
        }
        .minimal-icon-badge {
            width: 36px;
            height: 36px;
            border-radius: 0.72rem;
            background: var(--primary-teal-soft);
            border: 1px solid var(--primary-teal-border);
            color: var(--primary-teal);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 0.88rem;
            flex-shrink: 0;
        }

        .afagh-input {
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid var(--border-card);
            background-color: var(--bg-subtle);
            color: var(--text-primary);
            padding: 0.55rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .afagh-input:focus {
            border-color: var(--primary-teal);
            box-shadow: 0 0 0 2px var(--primary-teal-soft);
        }

        .btn-teal-solid {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.48rem 0.95rem;
            border-radius: 0.72rem;
            border: 1px solid rgba(20, 184, 166, 0.4);
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }
        .btn-teal-solid:hover {
            transform: translateY(-1px);
            opacity: 0.94;
        }

        .btn-subtle-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.45rem 0.85rem;
            border-radius: 0.7rem;
            border: 1px solid var(--border-card);
            background: var(--bg-subtle);
            color: var(--text-primary);
            font-size: 0.76rem;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: border-color 0.16s ease, background 0.16s ease;
        }
        .btn-subtle-action:hover {
            border-color: var(--primary-teal);
            color: var(--primary-teal);
        }

        #copy-toast {
            position: fixed;
            bottom: 5.2rem;
            left: 50%;
            transform: translateX(-50%);
            background: #0d9488;
            color: #ffffff;
            padding: 0.55rem 1.1rem;
            border-radius: 0.75rem;
            font-size: 0.78rem;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(13, 148, 136, 0.35);
            z-index: 100;
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body>

    <div id="copy-toast">✅ در کلیپ‌بورد کپی شد!</div>

    <div class="portal-app-shell">
        @include('components.desktop-sidebar')

        <div class="portal-main-column">
            @include('components.mobile-header')

            <main class="portal-content-container">
                @if(session('status'))
                    <div style="padding: 0.75rem 1rem; border-radius: 0.85rem; background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3); color: #10b981; font-size: 0.8rem; font-weight: 800;">
                        ✓ {{ session('status') }}
                    </div>
                @endif

                @if(session('error') || $errors->any())
                    <div style="padding: 0.75rem 1rem; border-radius: 0.85rem; background: rgba(244,63,94,0.12); border: 1px solid rgba(244,63,94,0.3); color: #f43f5e; font-size: 0.8rem; font-weight: 800;">
                        ! {{ session('error') ?? $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @include('components.mobile-bottom-nav')

    <script>
        (function () {
            const saved = localStorage.getItem('afagh_theme') || 'dark';
            document.documentElement.classList.toggle('dark', saved === 'dark');
        })();

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('afagh_theme', isDark ? 'dark' : 'light');
        }

        function copyTextToClipboard(text, customMessage) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                const toast = document.getElementById('copy-toast');
                toast.textContent = customMessage || '✅ در کلیپ‌بورد کپی شد!';
                toast.style.display = 'block';
                setTimeout(() => { toast.style.display = 'none'; }, 2400);
            });
        }

        let deferredCustomerPrompt = null;
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredCustomerPrompt = e;
            const hBtn = document.getElementById('pwa-install-header-btn');
            if (hBtn) hBtn.style.display = 'inline-flex';
        });
        async function triggerPwaInstall() {
            if (deferredCustomerPrompt) {
                deferredCustomerPrompt.prompt();
                await deferredCustomerPrompt.userChoice;
                deferredCustomerPrompt = null;
            }
        }

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
                        var alpha = Math.max(0, Math.min(255, Math.round((245 - minRG) * 1.25)));
                        if (alpha < 18) {
                            d[i + 3] = 0;
                        } else {
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
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll("img[data-afagh-transparent-logo]").forEach(removeLogoWhiteBg);
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>