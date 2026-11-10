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
    <title>ورود به پرتال بیمه‌گذاران | آفاق بیمه</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * { box-sizing: border-box; font-family: 'Vazirmatn', sans-serif; }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at top right, #112233 0%, #090d16 55%, #050811 100%);
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        html:not(.dark) body {
            background: radial-gradient(circle at top right, #e0f2fe 0%, #f8fafc 60%, #f1f5f9 100%);
            color: #0f172a;
        }
        .luxury-glass {
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        }
        html:not(.dark) .luxury-glass {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.1);
        }
        .luxury-input {
            width: 100%;
            padding: 0.85rem 1rem;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background: rgba(9, 13, 22, 0.65);
            color: #ffffff;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            outline: none;
        }
        html:not(.dark) .luxury-input {
            background: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1;
        }
        .luxury-input:focus {
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.2);
        }
        .luxury-btn {
            width: 100%;
            padding: 0.9rem 1.25rem;
            border-radius: 1rem;
            border: none;
            background: linear-gradient(135deg, #0d9488 0%, #059669 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.5);
            transition: transform 0.15s ease, opacity 0.15s ease;
        }
        .luxury-btn:hover { transform: translateY(-1px); opacity: 0.96; }
    </style>
</head>
<body>
    {{-- هدر بالای سایت --}}
    <header style="padding: 1.25rem 1.5rem; max-width: 1200px; width: 100%; margin: 0 auto; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #14b8a6, #059669); display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; color: #fff; box-shadow: 0 8px 20px rgba(20,184,166,0.35);">
                آ
            </div>
            <div>
                <div style="font-weight: 900; font-size: 1.1rem;">آفاق بیمه</div>
                <div style="font-size: 0.72rem; opacity: 0.7;">کیف پول و مدیریت هوشمند اقساط بیمه</div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button id="pwa-install-btn" type="button" style="display: none; padding: 0.5rem 0.9rem; border-radius: 12px; border: 1px solid rgba(20,184,166,0.4); background: rgba(20,184,166,0.15); color: #2dd4bf; font-size: 0.78rem; font-weight: 800; cursor: pointer;">
                📲 نصب اپلیکیشن
            </button>
            <button type="button" onclick="toggleTheme()" style="padding: 0.5rem 0.85rem; border-radius: 12px; border: 1px solid rgba(148,163,184,0.25); background: transparent; color: inherit; font-size: 0.78rem; font-weight: 700; cursor: pointer;">
                🌓 تغییر تم
            </button>
        </div>
    </header>

    {{-- کارت ورود --}}
    <main style="display: flex; align-items: center; justify-content: center; padding: 1.5rem;">
        <div class="luxury-glass" style="width: 100%; max-width: 420px; border-radius: 1.75rem; padding: 2rem;">
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <div style="display: inline-flex; padding: 0.75rem; border-radius: 1rem; background: rgba(20,184,166,0.12); color: #14b8a6; margin-bottom: 0.75rem;">
                    <img src="/images/icons/app_policyholders.svg" alt="آفاق بیمه" width="32" height="32" loading="lazy" decoding="async" />
                </div>
                <h1 style="font-size: 1.25rem; font-weight: 900; margin: 0 0 0.4rem 0;">ورود به پنل بیمه‌گذاران</h1>
                <p style="font-size: 0.8rem; opacity: 0.75; margin: 0; line-height: 1.6;">
                    برای مشاهده بیمه‌نامه‌ها و پرداخت اقساط، کد ملی و شماره موبایل خود را وارد نمایید.
                </p>
            </div>

            @if ($errors->any())
                <div style="margin-bottom: 1.25rem; padding: 0.85rem 1rem; border-radius: 1rem; background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fda4af; font-size: 0.8rem; font-weight: 700;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('customer.login.submit') }}" style="display: flex; flex-direction: column; gap: 1.15rem;">
                @csrf
                <div>
                    <label for="national_code" style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 0.45rem;">کد ملی (۱۰ رقمی)</label>
                    <input id="national_code" name="national_code" type="text" inputmode="numeric" dir="ltr" value="{{ old('national_code') }}" placeholder="0912345678" required autofocus class="luxury-input" />
                </div>

                <div>
                    <label for="mobile" style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 0.45rem;">شماره موبایل</label>
                    <input id="mobile" name="mobile" type="text" inputmode="tel" dir="ltr" placeholder="0915xxxxxxx" required class="luxury-input" />
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem;">
                    <label style="display: flex; align-items: center; gap: 0.45rem; cursor: pointer;">
                        <input type="checkbox" name="remember" value="1" checked style="accent-color: #14b8a6; width: 16px; height: 16px;">
                        <span>مرا به خاطر بسپار</span>
                    </label>
                </div>

                <button type="submit" class="luxury-btn">
                    ورود به حساب کاربری
                </button>
            </form>
        </div>
    </main>

    <footer style="text-align: center; padding: 1.25rem; font-size: 0.75rem; opacity: 0.6;">
        سامانه جامع مدیریت بیمه‌نامه‌ها و اقساط آفاق بیمه ({{ config('app.url') }})
    </footer>

    <script>
        (function () {
            const saved = localStorage.getItem('afagh_theme') || 'dark';
            document.documentElement.classList.toggle('dark', saved === 'dark');
        })();
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('afagh_theme', isDark ? 'dark' : 'light');
        }
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const btn = document.getElementById('pwa-install-btn');
            if (btn) {
                btn.style.display = 'inline-flex';
                btn.onclick = async () => {
                    deferredPrompt.prompt();
                    await deferredPrompt.userChoice;
                    deferredPrompt = null;
                    btn.style.display = 'none';
                };
            }
        });
    </script>
</body>
</html>