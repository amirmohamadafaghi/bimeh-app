<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#060913">
    <title>پرداخت قسط شماره {{ $installment->installment_number }} | آفاق بیمه</title>
    <!-- اسکریپت رسمی مینی‌اپ بله در بالاترین نقطه head -->
    <script src="https://tapi.bale.ai/miniapp.js?1"></script>
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
            font-family: 'Peyda', sans-serif;
        }
        body, button, input, select, textarea {
            font-family: 'Peyda', sans-serif !important;
        }
        body {
            margin: 0;
            min-height: 100vh;
            background-color: #060913;
            background-image:
                radial-gradient(at 15% 15%, rgba(20, 184, 166, 0.2) 0px, transparent 50%),
                radial-gradient(at 85% 80%, rgba(59, 130, 246, 0.18) 0px, transparent 50%);
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        html:not(.dark) body {
            background-color: #f1f5f9;
            background-image:
                radial-gradient(at 15% 15%, rgba(20, 184, 166, 0.14) 0px, transparent 50%),
                radial-gradient(at 85% 80%, rgba(59, 130, 246, 0.12) 0px, transparent 50%);
            color: #0f172a;
        }

        .pay-card {
            width: 100%;
            max-width: 490px;
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.75rem;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65);
        }
        html:not(.dark) .pay-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.12);
        }

        .afagh-pure-logo-img {
            width: 2.75rem;
            height: 2.75rem;
            object-fit: contain;
            flex-shrink: 0;
            background: transparent !important;
            filter: drop-shadow(0 2px 8px rgba(20, 184, 166, 0.3));
        }

        .info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            font-size: 0.85rem;
        }
        .info-key { opacity: 0.75; font-weight: 600; }
        .info-val { font-weight: 800; }

        .receipt-divider {
            margin: 1.15rem 0;
            border-top: 2px dashed rgba(148, 163, 184, 0.25);
            position: relative;
        }

        .gateway-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.95rem 1.25rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            color: #ffffff;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.45);
            transition: transform 0.15s ease;
        }
        .gateway-btn:hover { transform: translateY(-2px); }

        .step-two-box {
            padding: 1rem;
            border-radius: 1.15rem;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(148, 163, 184, 0.04);
            transition: all 0.3s ease;
        }
        .step-two-box.highlight-step {
            border-color: #14b8a6;
            background: rgba(20, 184, 166, 0.1);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.2);
        }

        .confirm-btn {
            width: 100%;
            padding: 0.95rem 1.25rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(20, 184, 166, 0.4);
            transition: transform 0.15s ease;
        }
        .confirm-btn:hover { transform: translateY(-2px); }

        #copy-banner {
            display: none;
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.9rem;
            background: rgba(20, 184, 166, 0.18);
            border: 1px solid rgba(20, 184, 166, 0.4);
            color: #2dd4bf;
            font-size: 0.8rem;
            font-weight: 800;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="pay-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
            <div style="display: inline-flex; align-items: center; gap: 0.65rem;">
                <img
                    src="{{ asset('images/icons/logo-icon.png') }}"
                    alt="آفاق بیمه"
                    class="afagh-pure-logo-img"
                    data-afagh-transparent-logo="true"
                />
                <div style="display: flex; flex-direction: column; line-height: 1.15;">
                    <span style="font-size: 1.12rem; font-weight: 900;">آفاق بیمه</span>
                    <span style="font-size: 0.68rem; font-weight: 800; color: #f59e0b;">قبض دیجیتال تک‌قسطی بیمه ایران</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.45rem;">
                <button type="button" onclick="toggleTheme()" title="تغییر تم" style="width: 36px; height: 36px; border-radius: 10px; border: 1px solid rgba(148,163,184,0.22); background: rgba(30,41,59,0.5); display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0;">
                    <img
                        src="{{ asset('images/icons/app_moon_and_sun.svg') }}"
                        onerror="if(!this.dataset.png){this.dataset.png='1';this.src='{{ asset('images/icons/app_moon_and_sun.png') }}';}"
                        alt="تغییر تم"
                        style="width: 20px; height: 20px; object-fit: contain;"
                    />
                </button>

                @auth
                    <a href="{{ route('customer.dashboard') }}" style="padding: 0.42rem 0.85rem; border-radius: 0.75rem; background: rgba(148,163,184,0.15); color: inherit; text-decoration: none; font-size: 0.78rem; font-weight: 700;">
                        بازگشت ✕
                    </a>
                @else
                    <a href="{{ route('customer.login') }}" style="padding: 0.42rem 0.85rem; border-radius: 0.75rem; background: rgba(148,163,184,0.15); color: inherit; text-decoration: none; font-size: 0.78rem; font-weight: 700;">
                        ورود به پرتال
                    </a>
                @endauth
            </div>
        </div>

        @if(session('status'))
            <div style="margin-bottom: 1.25rem; padding: 0.95rem 1rem; border-radius: 1rem; background: rgba(16,185,129,0.18); border: 1px solid rgba(16,185,129,0.4); color: #34d399; font-size: 0.82rem; font-weight: 700; line-height: 1.6;">
                ✅ {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div style="margin-bottom: 1.25rem; padding: 0.95rem 1rem; border-radius: 1rem; background: rgba(244,63,94,0.18); border: 1px solid rgba(244,63,94,0.4); color: #fda4af; font-size: 0.82rem; font-weight: 700;">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <div id="copy-banner">
            ✅ شناسه پرداخت کپی شد! در حال باز کردن درگاه رسمی بیمه ایران...
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
            <h1 style="font-size: 1.12rem; font-weight: 900; margin: 0;">
                پرداخت قسط شماره {{ $installment->installment_number }}
            </h1>
            <span style="padding: 0.22rem 0.65rem; border-radius: 9999px; font-size: 0.72rem; font-weight: 800; background: rgba(20,184,166,0.15); color: #2dd4bf;">
                {{ $installment->status === 'paid' ? 'تسویه شده ✓' : ($installment->status === 'awaiting_approval' ? 'در انتظار تایید' : 'آماده پرداخت') }}
            </span>
        </div>

        <div>
            <div class="info-row">
                <span class="info-key">بیمه‌گذار:</span>
                <span class="info-val">{{ $user->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">بیمه‌نامه:</span>
                <span class="info-val">{{ $installment->policy->title }} ({{ $installment->policy->policy_number }})</span>
            </div>
            <div class="info-row">
                <span class="info-key">سررسید شمسی:</span>
                <span class="info-val" dir="ltr">{{ $installment->due_date_jalali }}</span>
            </div>

            @if($installment->payment_identifier)
                <div class="info-row" style="border-bottom: none;">
                    <span class="info-key">شناسه پرداخت:</span>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="info-val" dir="ltr" style="font-family: monospace; color: #2dd4bf;">{{ $installment->payment_identifier }}</span>
                        <button type="button" onclick="copyIdentifierOnly('{{ $installment->payment_identifier }}')" style="padding: 0.25rem 0.55rem; border-radius: 0.5rem; border: 1px solid rgba(20,184,166,0.35); background: rgba(20,184,166,0.15); color: #2dd4bf; font-size: 0.72rem; font-weight: 800; cursor: pointer;">
                            📋 کپی
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <div class="receipt-divider"></div>

        <div style="padding: 1.15rem; border-radius: 1.2rem; background: rgba(20, 184, 166, 0.1); border: 1px solid rgba(20, 184, 166, 0.28); text-align: center; margin-bottom: 1.35rem;">
            <div style="font-size: 0.78rem; opacity: 0.8; margin-bottom: 0.25rem;">مبلغ قابل پرداخت</div>
            <div style="font-size: 1.38rem; font-weight: 900; color: #2dd4bf;">
                {{ number_format($installment->amount) }} ریال
            </div>
            <div style="font-size: 0.82rem; font-weight: 700; color: #94a3b8; margin-top: 0.2rem;">
                معادل {{ number_format((int) floor($installment->amount / 10)) }} تومان
            </div>
        </div>

        <div style="margin-bottom: 1rem;">
            <button
                type="button"
                onclick="copyAndOpenGateway('{{ $installment->payment_identifier ?? '' }}', 'https://epay.iraninsurance.ir')"
                class="gateway-btn"
            >
                <span>۱. کپی خودکار شناسه و ورود به درگاه بیمه ایران ↗</span>
            </button>
        </div>

        @if($installment->status !== 'paid')
            <div id="step-two-container" class="step-two-box">
                <form method="POST" action="{{ route('customer.installments.confirm-pay', $installment) }}">
                    @csrf
                    <p style="font-size: 0.76rem; opacity: 0.85; line-height: 1.6; margin: 0 0 0.75rem 0;">
                        ۲. پس از پرداخت در درگاه بیمه ایران، روی دکمه زیر کلیک کنید تا وضعیت قسط شما به «در انتظار تایید ادمین» تغییر یابد:
                    </p>
                    <button type="submit" class="confirm-btn">
                        ✓ پرداخت را انجام دادم (ثبت در انتظار تایید ادمین)
                    </button>
                </form>
            </div>
        @endif
    </div>

    <script>
        (function () {
            const saved = localStorage.getItem('afagh_theme') || 'dark';
            document.documentElement.classList.toggle('dark', saved === 'dark');

            // فعال‌سازی دکمه بازگشت در هدر مینی‌اپ بله در صورت باز شدن در داخل بله
            if (window.Bale && window.Bale.WebApp) {
                try {
                    window.Bale.WebApp.ready();
                    if (window.Bale.WebApp.BackButton) {
                        window.Bale.WebApp.BackButton.show();
                        window.Bale.WebApp.BackButton.onClick(function() {
                            window.Bale.WebApp.close();
                        });
                    }
                } catch (e) {}
            }
        })();

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('afagh_theme', isDark ? 'dark' : 'light');
        }

        function openExternalGateway(url) {
            if (window.Bale && window.Bale.WebApp && typeof window.Bale.WebApp.openLink === 'function') {
                window.Bale.WebApp.openLink(url);
            } else {
                window.open(url, '_blank');
            }
        }

        function copyIdentifierOnly(paymentId) {
            if (!paymentId) return;
            navigator.clipboard.writeText(paymentId).then(() => {
                const banner = document.getElementById('copy-banner');
                banner.textContent = '✅ شناسه پرداخت (' + paymentId + ') در کلیپ‌بورد کپی شد!';
                banner.style.display = 'block';
            });
        }

        function copyAndOpenGateway(paymentId, gatewayUrl) {
            const stepTwo = document.getElementById('step-two-container');
            if (stepTwo) stepTwo.classList.add('highlight-step');

            if (paymentId) {
                navigator.clipboard.writeText(paymentId).catch(() => {});
                const banner = document.getElementById('copy-banner');
                banner.textContent = '✅ شناسه پرداخت کپی شد! در حال انتقال به درگاه بیمه ایران...';
                banner.style.display = 'block';
                setTimeout(() => {
                    openExternalGateway(gatewayUrl);
                }, 600);
            } else {
                openExternalGateway(gatewayUrl);
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
            document.querySelectorAll("img[data-afagh-transparent-logo]").forEach(removeLogoWhiteBg);
        })();
    </script>
</body>
</html>