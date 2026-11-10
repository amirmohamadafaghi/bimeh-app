<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت تک‌قسطی | آفاق بیمه</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.getItem('afagh_theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>body { font-family: 'Vazirmatn', sans-serif; }</style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex items-center justify-center p-4">
    <main class="max-w-md w-full bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between">
            <span class="px-3 py-1 text-xs font-extrabold rounded-full bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                تراکنش تک‌قسطی بیمه ایران
            </span>
            <a href="{{ route('customer.dashboard') }}" class="text-xs font-bold text-slate-400 hover:text-slate-700 dark:hover:text-white">
                بازگشت ✕
            </a>
        </div>

        <h1 class="text-xl font-black text-slate-900 dark:text-white mt-4">
            پرداخت قسط شماره {{ $installment->installment_number }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            بیمه‌نامه: {{ $installment->policy->title }} ({{ $installment->policy->policy_number }})
        </p>

        <div class="my-6 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/70 dark:border-slate-800 space-y-2.5 text-xs sm:text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">بیمه‌گذار:</span>
                <strong class="font-extrabold">{{ $user->name }}</strong>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">سررسید شمسی:</span>
                <strong class="font-extrabold">{{ $installment->due_date_jalali }}</strong>
            </div>
            @if($installment->payment_identifier)
                <div class="flex justify-between">
                    <span class="text-slate-500">شناسه پرداخت:</span>
                    <strong class="font-mono text-indigo-600 dark:text-indigo-400">{{ $installment->payment_identifier }}</strong>
                </div>
            @endif
            <div class="flex justify-between text-base text-emerald-600 dark:text-emerald-400 pt-2 border-t border-slate-200 dark:border-slate-700">
                <span class="font-bold">مبلغ قابل پرداخت:</span>
                <strong class="font-black">{{ number_format($installment->amount) }} ریال</strong>
            </div>
        </div>

        <a
            href="https://epay.iraninsurance.ir"
            target="_blank"
            rel="noopener noreferrer"
            class="block w-full text-center py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-indigo-500/20 transition mb-4"
        >
            ۱. ورود به درگاه رسمی بیمه ایران (پرداخت قسط) ↗
        </a>

        <form method="POST" action="{{ route('customer.installments.confirm-pay', $installment) }}" class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
            @csrf
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                ۲. پس از پرداخت در درگاه بیمه ایران، روی دکمه زیر کلیک کنید تا وضعیت قسط شما به <strong>«در انتظار تایید ادمین»</strong> تغییر یابد (نیازی به وارد کردن کد رهگیری نیست).
            </p>
            <button
                type="submit"
                class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs sm:text-sm shadow-md shadow-emerald-500/20 transition focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
                ✓ پرداخت را انجام دادم (ثبت در انتظار تایید ادمین)
            </button>
        </form>
    </main>
</body>
</html>