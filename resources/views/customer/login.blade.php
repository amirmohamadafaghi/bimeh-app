<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#060a12">
    <link rel="manifest" href="/manifest.json">
    <title>ورود به پرتال بیمه‌گذاران - آفاق بیمه</title>
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

        * {
            box-sizing: border-box;
            font-family: "Peyda", sans-serif;
        }
        body, button, input, select, textarea {
            font-family: "Peyda", sans-serif !important;
        }
        body {
            margin: 0;
            min-height: 100vh;
            background-color: #060913;
            background-image:
                radial-gradient(at 12% 15%, rgba(20, 184, 166, 0.22) 0px, transparent 50%),
                radial-gradient(at 88% 20%, rgba(59, 130, 246, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 90%, rgba(99, 102, 241, 0.15) 0px, transparent 55%),
                linear-gradient(180deg, #060913 0%, #0b1120 100%);
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            direction: rtl;
            position: relative;
            overflow-x: hidden;
            transition: background 0.35s ease, color 0.35s ease;
        }
        html:not(.dark) body {
            background-color: #f1f5f9;
            background-image:
                radial-gradient(at 10% 10%, rgba(20, 184, 166, 0.18) 0px, transparent 50%),
                radial-gradient(at 90% 15%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 50% 90%, rgba(245, 158, 11, 0.1) 0px, transparent 55%),
                linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            color: #0f172a;
        }

        /* دکمه شناور تغییر تم */
        .theme-switch-floating {
            position: fixed;
            top: 1.25rem;
            left: 1.25rem;
            z-index: 40;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        html:not(.dark) .theme-switch-floating {
            background: rgba(255, 255, 255, 0.8);
            border-color: rgba(15, 23, 42, 0.12);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }
        .theme-switch-floating:hover {
            transform: translateY(-2px) scale(1.04);
            border-color: rgba(20, 184, 166, 0.5);
        }
        .theme-switch-floating img {
            width: 24px;
            height: 24px;
            object-fit: contain;
            transition: transform 0.35s ease;
        }
        .theme-switch-floating:active img {
            transform: rotate(45deg);
        }

        /* کارت شیشه‌ای ورود */
        .login-card {
            width: 100%;
            max-width: 27.5rem;
            background: rgba(15, 23, 42, 0.62);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.75rem;
            padding: 2.4rem 2rem;
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.65),
                inset 0 1px 1px rgba(255, 255, 255, 0.12);
            position: relative;
            overflow: hidden;
            animation: cardAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        html:not(.dark) .login-card {
            background: rgba(255, 255, 255, 0.78);
            border-color: rgba(255, 255, 255, 0.9);
            box-shadow:
                0 20px 45px -15px rgba(15, 23, 42, 0.12),
                0 0 0 1px rgba(15, 23, 42, 0.06);
        }
        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(18px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.85rem;
            text-align: center;
        }
        .afagh-pure-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.85rem;
            background: transparent !important;
        }
        .afagh-pure-logo-img {
            width: 4.1rem;
            height: 4.1rem;
            object-fit: contain;
            flex-shrink: 0;
            background: transparent !important;
            filter: drop-shadow(0 4px 12px rgba(20, 184, 166, 0.32));
        }
        .afagh-pure-brand-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.15;
        }
        .afagh-pure-title {
            font-size: 1.7rem;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: -0.02em;
        }
        html:not(.dark) .afagh-pure-title {
            color: #0f172a;
        }
        .afagh-pure-subtitle {
            font-size: 0.84rem;
            font-weight: 800;
            color: #f59e0b;
        }
        .login-heading {
            font-size: 1.12rem;
            font-weight: 800;
            color: #cbd5e1;
            margin: 0.25rem 0 0 0;
        }
        html:not(.dark) .login-heading {
            color: #334155;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }
        .form-label {
            font-size: 0.84rem;
            font-weight: 700;
            color: #e2e8f0;
        }
        html:not(.dark) .form-label {
            color: #1e293b;
        }
        .form-label span {
            color: #f43f5e;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-input {
            width: 100%;
            padding: 0.78rem 1rem;
            border-radius: 0.9rem;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background-color: rgba(15, 23, 42, 0.55);
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            outline: none;
            transition: all 0.2s ease;
        }
        html:not(.dark) .form-input {
            background-color: rgba(255, 255, 255, 0.9);
            border-color: rgba(148, 163, 184, 0.45);
            color: #0f172a;
        }
        .form-input.has-toggle {
            padding-right: 2.75rem;
        }
        .form-input:focus {
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.22);
            background-color: rgba(15, 23, 42, 0.8);
        }
        html:not(.dark) .form-input:focus {
            background-color: #ffffff;
        }
        .pwd-toggle-btn {
            position: absolute;
            right: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            transition: color 0.15s ease;
        }
        .pwd-toggle-btn:hover {
            color: #14b8a6;
        }

        .form-error {
            font-size: 0.78rem;
            font-weight: 700;
            color: #fb7185;
            margin-top: 0.2rem;
        }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            margin-bottom: 1.5rem;
            font-size: 0.84rem;
            font-weight: 700;
            color: #cbd5e1;
            cursor: pointer;
            user-select: none;
        }
        html:not(.dark) .remember-row {
            color: #475569;
        }
        .remember-row input {
            width: 1.1rem;
            height: 1.1rem;
            accent-color: #14b8a6;
            cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            padding: 0.85rem 1.25rem;
            border-radius: 0.95rem;
            border: none;
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 55%, #0f766e 100%);
            color: #ffffff;
            font-size: 0.98rem;
            font-weight: 900;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 22px -5px rgba(20, 184, 166, 0.45);
        }
        .submit-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px -5px rgba(20, 184, 166, 0.55);
        }
        .submit-btn:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none;
        }
        .btn-spinner {
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255, 255, 255, 0.35);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
            display: none;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    {{-- دکمه تغییر تم با آیکون اختصاصی app_moon_and_sun --}}
    <button type="button" class="theme-switch-floating" onclick="toggleTheme()" title="تغییر تم روشن / تیره">
        <img
            src="{{ asset('images/icons/app_moon_and_sun.svg') }}"
            onerror="if(!this.dataset.png){this.dataset.png='1';this.src='{{ asset('images/icons/app_moon_and_sun.png') }}';}"
            alt="تغییر تم"
        />
    </button>

    <div class="login-card">
        <div class="login-header">
            <div class="afagh-pure-brand">
                <img
                    src="{{ asset('images/icons/logo-icon.png') }}"
                    alt="آفاق بیمه"
                    class="afagh-pure-logo-img"
                    data-afagh-transparent-logo="true"
                />
                <div class="afagh-pure-brand-text">
                    <span class="afagh-pure-title">آفاق بیمه</span>
                    <span class="afagh-pure-subtitle">وب اپلیکیشن</span>
                </div>
            </div>
            <h1 class="login-heading">ورود به پرتال بیمه‌گذاران</h1>
        </div>

        <form id="customer-login-form" method="POST" action="{{ route('customer.login.submit') }}">
            @csrf

            <div class="form-group">
                <label for="national_code" class="form-label">
                    کد ملی <span>*</span>
                </label>
                <div class="input-wrapper">
                    <input
                        id="national_code"
                        type="text"
                        name="national_code"
                        value="{{ old('national_code') }}"
                        required
                        autofocus
                        inputmode="numeric"
                        maxlength="10"
                        dir="ltr"
                        placeholder="کد ملی ۱۰ رقمی"
                        class="form-input"
                        oninput="normalizeDigits(this)"
                    />
                </div>
                @error('national_code')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="mobile" class="form-label">
                    شماره موبایل (رمز عبور) <span>*</span>
                </label>
                <div class="input-wrapper">
                    <input
                        id="mobile"
                        type="password"
                        name="mobile"
                        required
                        inputmode="numeric"
                        maxlength="11"
                        dir="ltr"
                        placeholder="0912..."
                        class="form-input has-toggle"
                        oninput="normalizeDigits(this)"
                    />
                    <button type="button" class="pwd-toggle-btn" onclick="toggleMobileVisibility()" title="نمایش / مخفی کردن شماره موبایل">
                        <svg id="eye-open-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eye-closed-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('mobile')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <label class="remember-row">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} />
                <span>مرا به خاطر بسپار</span>
            </label>

            <button type="submit" id="login-submit-btn" class="submit-btn">
                <span id="login-btn-text">ورود</span>
                <span id="login-btn-spinner" class="btn-spinner"></span>
            </button>
        </form>
    </div>

    <script>
        (function () {
            const saved = localStorage.getItem('afagh_theme') || 'dark';
            document.documentElement.classList.toggle('dark', saved === 'dark');
        })();

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('afagh_theme', isDark ? 'dark' : 'light');
        }

        function normalizeDigits(el) {
            const persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            const arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            let val = el.value;
            for (let i = 0; i < 10; i++) {
                val = val.replace(new RegExp(persian[i], 'g'), i).replace(new RegExp(arabic[i], 'g'), i);
            }
            el.value = val.replace(/[^0-9]/g, '');
        }

        function toggleMobileVisibility() {
            const input = document.getElementById('mobile');
            const openIcon = document.getElementById('eye-open-icon');
            const closedIcon = document.getElementById('eye-closed-icon');
            if (input.type === 'password') {
                input.type = 'text';
                openIcon.style.display = 'none';
                closedIcon.style.display = 'block';
            } else {
                input.type = 'password';
                openIcon.style.display = 'block';
                closedIcon.style.display = 'none';
            }
        }

        document.getElementById('customer-login-form').addEventListener('submit', function () {
            const btn = document.getElementById('login-submit-btn');
            const txt = document.getElementById('login-btn-text');
            const spn = document.getElementById('login-btn-spinner');
            btn.disabled = true;
            txt.textContent = 'در حال بررسی اطلاعات...';
            spn.style.display = 'inline-block';
        });

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
            document.querySelectorAll("img[data-afagh-transparent-logo]").forEach(removeLogoWhiteBg);
        })();
    </script>
</body>
</html>