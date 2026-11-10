<x-filament-panels::page>
    <style>
        /* ==================== سیستم طراحی مستقل پرونده ۳۶۰ درجه آفاق بیمه ==================== */
        .afagh-360-wrapper {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            direction: rtl;
            padding-bottom: 2rem;
        }

        /* ۱. هدر اصلی بالا (Top Bar) */
        .afagh-top-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 1.25rem 1.75rem;
            border-radius: 1.25rem;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 8px 24px -6px rgba(15, 23, 42, 0.06);
        }
        .dark .afagh-top-bar {
            background: linear-gradient(145deg, #111827 0%, #0f172a 100%);
            border-color: rgba(71, 85, 105, 0.65);
            box-shadow: 0 14px 30px -8px rgba(0, 0, 0, 0.55);
        }

        .afagh-top-identity {
            display: flex;
            align-items: center;
            gap: 1.1rem;
        }

        .afagh-avatar-circle {
            width: 3.4rem;
            height: 3.4rem;
            border-radius: 1.05rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            font-weight: 900;
            background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%);
            color: #ffffff;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(20, 184, 166, 0.35);
            border: 2px solid rgba(255, 255, 255, 0.15);
        }

        .afagh-top-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .afagh-top-title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.6rem;
        }

        .afagh-user-name {
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }
        .dark .afagh-user-name {
            color: #ffffff;
        }

        .afagh-top-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.65rem;
        }

        /* ۲. سیستم دکمه‌های استاندارد */
        .afagh-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.6rem 1.15rem;
            border-radius: 0.85rem;
            font-size: 0.82rem;
            font-weight: 800;
            line-height: 1.2;
            cursor: pointer;
            transition: all 0.18s ease;
            text-decoration: none !important;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .afagh-btn svg {
            width: 1.1rem;
            height: 1.1rem;
            flex-shrink: 0;
        }
        .afagh-btn-primary {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff !important;
            border-color: #14b8a6;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }
        .afagh-btn-primary:hover {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(20, 184, 166, 0.45);
        }

        .afagh-btn-indigo {
            background-color: rgba(99, 102, 241, 0.14);
            color: #4338ca !important;
            border-color: rgba(99, 102, 241, 0.4);
        }
        .dark .afagh-btn-indigo {
            background-color: rgba(99, 102, 241, 0.18);
            color: #a5b4fc !important;
            border-color: rgba(99, 102, 241, 0.45);
        }
        .afagh-btn-indigo:hover {
            background-color: rgba(99, 102, 241, 0.28);
        }

        .afagh-btn-secondary {
            background-color: #f1f5f9;
            color: #1e293b !important;
            border-color: #cbd5e1;
        }
        .dark .afagh-btn-secondary {
            background-color: #1e293b;
            color: #e2e8f0 !important;
            border-color: #475569;
        }
        .afagh-btn-secondary:hover {
            background-color: #e2e8f0;
        }
        .dark .afagh-btn-secondary:hover {
            background-color: #334155;
        }

        .afagh-btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            border-color: #34d399;
        }
        .afagh-btn-success:hover {
            opacity: 0.92;
        }

        .afagh-btn-danger {
            background-color: rgba(244, 63, 94, 0.14);
            color: #e11d48 !important;
            border-color: rgba(244, 63, 94, 0.35);
        }
        .dark .afagh-btn-danger {
            color: #fda4af !important;
        }
        .afagh-btn-danger:hover {
            background-color: rgba(244, 63, 94, 0.25);
        }

        .afagh-btn-sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.76rem;
            border-radius: 0.65rem;
        }
        .afagh-btn-icon {
            padding: 0.45rem;
            border-radius: 0.65rem;
        }

        /* ۳. چیدمان ۲ ستونه اصلی (سایدبار راست + محتوای چپ) */
        .afagh-main-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        @media (min-width: 1024px) {
            .afagh-main-grid {
                grid-template-columns: 350px minmax(0, 1fr);
            }
        }
        @media (min-width: 1280px) {
            .afagh-main-grid {
                grid-template-columns: 375px minmax(0, 1fr);
            }
        }

        .afagh-sidebar-stack {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* ۴. کارت‌های استاندارد */
        .afagh-panel-card {
            border-radius: 1.25rem;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 6px 20px -6px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }
        .dark .afagh-panel-card {
            background-color: #111827;
            border-color: rgba(71, 85, 105, 0.65);
            box-shadow: 0 12px 28px -8px rgba(0, 0, 0, 0.45);
        }

        .afagh-card-padded {
            padding: 1.35rem 1.5rem;
        }

        .afagh-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding-bottom: 0.9rem;
            margin-bottom: 1.1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .afagh-card-header {
            border-bottom-color: #1e293b;
        }

        .afagh-card-title {
            font-size: 0.88rem;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .afagh-card-title {
            color: #f8fafc;
        }

        /* ۵. لیست مشخصات هویتی در سایدبار */
        .afagh-identity-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .afagh-identity-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.7rem 0.95rem;
            border-radius: 0.8rem;
            font-size: 0.82rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: border-color 0.15s ease;
        }
        .dark .afagh-identity-row {
            background-color: #1e293b;
            border-color: rgba(71, 85, 105, 0.55);
        }
        .afagh-identity-key {
            color: #64748b;
            font-weight: 700;
        }
        .dark .afagh-identity-key {
            color: #94a3b8;
        }
        .afagh-identity-val {
            color: #0f172a;
            font-weight: 800;
        }
        .dark .afagh-identity-val {
            color: #ffffff;
        }

        .afagh-address-box {
            margin-top: 0.25rem;
            padding: 0.85rem 1rem;
            border-radius: 0.8rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 0.8rem;
            line-height: 1.6;
        }
        .dark .afagh-address-box {
            background-color: #1e293b;
            border-color: rgba(71, 85, 105, 0.55);
        }

        /* ۶. مینی‌کارت‌های خلاصه مالی در سایدبار */
        .afagh-mini-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .afagh-mini-card {
            padding: 1.05rem 1.2rem;
            border-radius: 1rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-right-width: 5px;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }
        .dark .afagh-mini-card {
            background-color: #1e293b;
            border-color: rgba(71, 85, 105, 0.55);
        }
        .afagh-mini-card-emerald { border-right-color: #10b981 !important; }
        .afagh-mini-card-amber   { border-right-color: #f59e0b !important; }
        .afagh-mini-card-rose    { border-right-color: #f43f5e !important; }
        .afagh-mini-card-indigo  { border-right-color: #6366f1 !important; }

        .afagh-mini-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .afagh-mini-label {
            font-size: 0.8rem;
            font-weight: 800;
            color: #475569;
        }
        .dark .afagh-mini-label {
            color: #cbd5e1;
        }
        .afagh-mini-value {
            font-size: 1.3rem;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .dark .afagh-mini-value {
            color: #ffffff;
        }
        .afagh-mini-sub {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 0.55rem;
            border-top: 1px dashed rgba(148, 163, 184, 0.3);
            font-size: 0.74rem;
            color: #64748b;
            font-weight: 700;
        }
        .dark .afagh-mini-sub {
            color: #94a3b8;
        }

        /* ۷. نوار تب‌ها و نوار ابزار افقی فیلترها */
        .afagh-tabs-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.65rem;
            padding: 1rem 1.35rem;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .afagh-tabs-bar {
            background-color: #0b1120;
            border-bottom-color: #1e293b;
        }

        .afagh-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.65rem 1.15rem;
            border-radius: 0.85rem;
            font-size: 0.84rem;
            font-weight: 800;
            color: #475569;
            background: transparent;
            border: 1px solid transparent;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .dark .afagh-tab-btn {
            color: #94a3b8;
        }
        .afagh-tab-btn svg {
            width: 1.1rem;
            height: 1.1rem;
        }
        .afagh-tab-btn:hover {
            background-color: rgba(148, 163, 184, 0.12);
            color: #0f172a;
        }
        .dark .afagh-tab-btn:hover {
            color: #ffffff;
        }
        .afagh-tab-btn-active {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important;
            color: #ffffff !important;
            border-color: #14b8a6 !important;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        }

        .afagh-tab-badge {
            padding: 0.12rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.73rem;
            font-weight: 900;
            background-color: rgba(148, 163, 184, 0.2);
            color: inherit;
        }
        .afagh-tab-btn-active .afagh-tab-badge {
            background-color: rgba(255, 255, 255, 0.22);
            color: #ffffff;
        }

        /* نوار ابزار فیلتر و جستجو */
        .afagh-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            padding: 1.15rem 1.35rem;
            background-color: rgba(148, 163, 184, 0.04);
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .afagh-toolbar {
            border-bottom-color: #1e293b;
        }

        .afagh-filter-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            flex: 1 1 auto;
        }

        .afagh-search-input {
            flex: 1 1 240px;
            max-width: 340px;
            min-width: 200px;
            border-radius: 0.8rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #0f172a;
            padding: 0.58rem 0.95rem;
            font-size: 0.82rem;
            outline: none;
            transition: border-color 0.15s ease;
        }
        .dark .afagh-search-input {
            background-color: #0f172a;
            color: #f8fafc;
            border-color: #334155;
        }
        .afagh-search-input:focus, .afagh-select-filter:focus, .afagh-form-control:focus {
            border-color: #14b8a6 !important;
            box-shadow: 0 0 0 2px rgba(20, 184, 166, 0.2);
        }

        .afagh-select-filter {
            width: auto !important;
            min-width: 175px;
            border-radius: 0.8rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #0f172a;
            padding: 0.56rem 0.9rem;
            font-size: 0.8rem;
            font-weight: 700;
            outline: none;
            cursor: pointer;
        }
        .dark .afagh-select-filter {
            background-color: #0f172a;
            color: #f8fafc;
            border-color: #334155;
        }

        /* ۸. فرم‌ها، گریدها و فیلدها */
        .afagh-form-grid-3 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.1rem 1.25rem;
        }
        @media (min-width: 640px) {
            .afagh-form-grid-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 900px) {
            .afagh-form-grid-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .afagh-form-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.1rem 1.25rem;
        }
        @media (min-width: 640px) {
            .afagh-form-grid-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .afagh-col-span-full {
            grid-column: 1 / -1;
        }

        .afagh-field-group {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .afagh-field-label {
            font-size: 0.79rem;
            font-weight: 800;
            color: #334155;
        }
        .dark .afagh-field-label {
            color: #cbd5e1;
        }

        .afagh-form-control {
            width: 100%;
            border-radius: 0.8rem;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            color: #0f172a;
            padding: 0.62rem 0.9rem;
            font-size: 0.84rem;
            outline: none;
            transition: all 0.15s ease;
        }
        .dark .afagh-form-control {
            background-color: #1e293b;
            color: #ffffff;
            border-color: #475569;
        }

        .afagh-form-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.35rem;
            padding-top: 1.1rem;
            border-top: 1px solid #e2e8f0;
        }
        .dark .afagh-form-footer {
            border-top-color: #1e293b;
        }

        /* ۹. جدول استاندارد */
        .afagh-clean-table {
            width: 100%;
            border-collapse: collapse;
            text-align: right;
            font-size: 0.83rem;
        }
        .afagh-clean-table thead th {
            padding: 0.9rem 1.25rem;
            font-size: 0.77rem;
            font-weight: 800;
            background-color: #f1f5f9;
            color: #334155;
            border-bottom: 1px solid #cbd5e1;
            white-space: nowrap;
        }
        .dark .afagh-clean-table thead th {
            background-color: #1e293b;
            color: #cbd5e1;
            border-bottom-color: #334155;
        }
        .afagh-clean-table tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #0f172a;
        }
        .dark .afagh-clean-table tbody td {
            border-bottom-color: #1e293b;
            color: #f1f5f9;
        }
        .afagh-clean-table tbody tr:hover {
            background-color: rgba(20, 184, 166, 0.04);
        }

        .afagh-cell-primary {
            font-size: 0.85rem;
            font-weight: 800;
            color: #0f172a;
        }
        .dark .afagh-cell-primary {
            color: #ffffff;
        }
        .afagh-cell-secondary {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        .dark .afagh-cell-secondary {
            color: #94a3b8;
        }

        .afagh-row-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        /* ۱۰. بج‌های وضعیت */
        .afagh-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.7rem;
            border-radius: 0.55rem;
            font-size: 0.74rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .afagh-badge-success { background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .dark .afagh-badge-success { background-color: rgba(6, 95, 70, 0.6); color: #a7f3d0; border-color: #10b981; }

        .afagh-badge-danger { background-color: #ffe4e6; color: #9f1239; border: 1px solid #fda4af; }
        .dark .afagh-badge-danger { background-color: rgba(159, 18, 57, 0.6); color: #fecdd3; border-color: #f43f5e; }

        .afagh-badge-warning { background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .dark .afagh-badge-warning { background-color: rgba(146, 64, 14, 0.6); color: #fde68a; border-color: #f59e0b; }

        .afagh-badge-info { background-color: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .dark .afagh-badge-info { background-color: rgba(30, 64, 175, 0.6); color: #bfdbfe; border-color: #3b82f6; }

        .afagh-badge-neutral { background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
        .dark .afagh-badge-neutral { background-color: #1e293b; color: #cbd5e1; border-color: #475569; }

        /* ۱۱. حالت خالی (Empty State) */
        .afagh-empty-box {
            margin: 1.5rem;
            padding: 3.25rem 1.5rem;
            border-radius: 1.15rem;
            border: 1px dashed rgba(148, 163, 184, 0.35);
            background-color: rgba(148, 163, 184, 0.03);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0.85rem;
        }
        .afagh-empty-icon {
            width: 3.75rem;
            height: 3.75rem;
            border-radius: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(20, 184, 166, 0.14);
            color: #14b8a6;
            border: 1px solid rgba(20, 184, 166, 0.3);
        }

        /* ۱۲. پنجره مودال استاندارد */
        .afagh-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 60;
            background-color: rgba(2, 6, 23, 0.78);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            overflow-y: auto;
        }
        .afagh-modal-box {
            width: 100%;
            max-width: 52rem;
            border-radius: 1.35rem;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65);
            overflow: hidden;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            direction: rtl;
        }
        .dark .afagh-modal-box {
            background-color: #0f172a;
            border-color: #334155;
        }
        .afagh-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 1.65rem;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .afagh-modal-header {
            background-color: #111827;
            border-bottom-color: #1e293b;
        }
        .afagh-modal-body {
            padding: 1.65rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.35rem;
        }
        .afagh-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1.15rem 1.65rem;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        .dark .afagh-modal-footer {
            background-color: #111827;
            border-top-color: #1e293b;
        }
    </style>

    <div class="afagh-360-wrapper">
        {{-- ==================== ۱. هدر اصلی بالا (بدون زیرنویس و بدون دکمه تکراری ثبت بیمه‌نامه) ==================== --}}
        <div class="afagh-top-bar">
            <div class="afagh-top-identity">
                <div class="afagh-avatar-circle">
                    {{ mb_substr($user->name ?: 'ب', 0, 1) }}
                </div>
                <div class="afagh-top-info">
                    <div class="afagh-top-title-row">
                        <h1 class="afagh-user-name">{{ $user->name }}</h1>

                        @if($user->payment_badge === 'good_payer')
                            <span class="afagh-badge afagh-badge-success">★ خوش‌حساب</span>
                        @elseif($user->payment_badge === 'bad_payer')
                            <span class="afagh-badge afagh-badge-danger">▲ بدحساب</span>
                        @else
                            <span class="afagh-badge afagh-badge-neutral">وضعیت: عادی</span>
                        @endif

                        <span class="afagh-badge afagh-badge-info">
                            {{ $user->policyholder_type ?: 'حقیقی' }}
                        </span>

                        @if(!empty($user->bale_chat_id))
                            <span class="afagh-badge afagh-badge-success" title="Chat ID: {{ $user->bale_chat_id }}">
                                ✓ متصل به ربات بله
                            </span>
                        @else
                            <span class="afagh-badge afagh-badge-warning">
                                ربات بله متصل نیست
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="afagh-top-actions">
                <a href="{{ $notificationTargetUrl }}" class="afagh-btn afagh-btn-indigo">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span>ارسال پیام بله</span>
                </a>

                <button type="button" wire:click="toggleEdit" class="afagh-btn afagh-btn-secondary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>{{ $isEditing ? 'بستن فرم ویرایش' : 'ویرایش مشخصات' }}</span>
                </button>
            </div>
        </div>

        {{-- ==================== کارت ویرایش اطلاعات هویتی ==================== --}}
        @if($isEditing)
            <div class="afagh-panel-card afagh-card-padded" style="border: 2px solid rgba(20, 184, 166, 0.45);">
                <div class="afagh-card-header">
                    <h3 class="afagh-card-title">
                        <span>✎ ویرایش اطلاعات هویتی و تنظیمات بیمه‌گذار</span>
                    </h3>
                    <button type="button" wire:click="toggleEdit" class="afagh-btn afagh-btn-danger afagh-btn-sm">
                        انصراف و بستن
                    </button>
                </div>

                <form wire:submit="saveProfile" autocomplete="off">
                    <div class="afagh-form-grid-3">
                        <div class="afagh-field-group">
                            <label class="afagh-field-label">نام و نام خانوادگی / شرکت *</label>
                            <input type="text" wire:model="editData.name" required class="afagh-form-control" autocomplete="off" />
                        </div>

                        <div class="afagh-field-group">
                            <label class="afagh-field-label">کد ملی / شناسه ملی</label>
                            <input type="text" wire:model="editData.national_code" maxlength="15" class="afagh-form-control" dir="ltr" autocomplete="off" />
                        </div>

                        <div class="afagh-field-group">
                            <label class="afagh-field-label">تلفن همراه *</label>
                            <input type="text" wire:model="editData.mobile" required class="afagh-form-control" dir="ltr" autocomplete="off" />
                        </div>

                        <div class="afagh-field-group">
                            <label class="afagh-field-label">تلفن ثابت</label>
                            <input type="text" wire:model="editData.phone" class="afagh-form-control" dir="ltr" autocomplete="off" />
                        </div>

                        <div class="afagh-field-group">
                            <label class="afagh-field-label">تاریخ تولد (شمسی)</label>
                            <input type="text" wire:model="editData.birth_date" class="afagh-form-control" dir="ltr" placeholder="1370/01/01" autocomplete="off" />
                        </div>

                        <div class="afagh-field-group">
                            <label class="afagh-field-label">نوع بیمه‌گذار</label>
                            <select wire:model="editData.policyholder_type" class="afagh-form-control">
                                <option value="حقیقی">حقیقی</option>
                                <option value="حقوقی">حقوقی</option>
                            </select>
                        </div>

                        <div class="afagh-field-group">
                            <label class="afagh-field-label">پروفایل فروش / معرف</label>
                            <input type="text" wire:model="editData.profile_title" class="afagh-form-control" placeholder="مثال: اعتباری 50-50" autocomplete="off" />
                        </div>

                        <div class="afagh-field-group">
                            <label class="afagh-field-label">وضعیت خوش‌حسابی *</label>
                            <select wire:model="editData.payment_badge" class="afagh-form-control">
                                <option value="good_payer">★ خوش‌حساب</option>
                                <option value="normal">عادی</option>
                                <option value="bad_payer">▲ بدحساب</option>
                            </select>
                        </div>

                        <div class="afagh-field-group">
                            <label class="afagh-field-label">شناسه چت بله (Chat ID)</label>
                            <input type="text" wire:model="editData.bale_chat_id" class="afagh-form-control" dir="ltr" autocomplete="off" data-lpignore="true" placeholder="عددی یا خالی" />
                        </div>

                        <div class="afagh-field-group">
                            <label class="afagh-field-label">رمز عبور جدید پرتال (اختیاری)</label>
                            <input type="password" wire:model="editData.new_password" class="afagh-form-control" dir="ltr" autocomplete="new-password" placeholder="فقط در صورت نیاز به تغییر پر کنید" />
                        </div>

                        <div class="afagh-field-group" style="grid-column: span 2;">
                            <label class="afagh-field-label">آدرس کامل</label>
                            <input type="text" wire:model="editData.address" class="afagh-form-control" placeholder="نشانی محل سکونت یا محل کار..." autocomplete="off" />
                        </div>
                    </div>

                    <div class="afagh-form-footer">
                        <button type="button" wire:click="toggleEdit" class="afagh-btn afagh-btn-secondary">
                            انصراف
                        </button>
                        <button type="submit" class="afagh-btn afagh-btn-primary">
                            ✓ ذخیره تغییرات پرونده
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- ==================== ۲. چیدمان ۲ ستونه (سایدبار راست + تب‌های چپ) ==================== --}}
        <div class="afagh-main-grid">

            {{-- ستون سمت راست (سایدبار اطلاعات ثابت و خلاصه مالی) --}}
            <aside class="afagh-sidebar-stack">

                {{-- کارت ۱ سایدبار: مشخصات هویتی و تماس --}}
                <div class="afagh-panel-card afagh-card-padded">
                    <div class="afagh-card-header">
                        <h2 class="afagh-card-title">۱. مشخصات هویتی و تماس</h2>
                        <button type="button" wire:click="toggleEdit" class="afagh-btn afagh-btn-secondary afagh-btn-sm">
                            ویرایش
                        </button>
                    </div>

                    <div class="afagh-identity-list">
                        <div class="afagh-identity-row">
                            <span class="afagh-identity-key">کد ملی / شناسه:</span>
                            <span class="afagh-identity-val" style="font-family: monospace;" dir="ltr">{{ $user->national_code ?: '—' }}</span>
                        </div>

                        <div class="afagh-identity-row">
                            <span class="afagh-identity-key">تلفن همراه:</span>
                            <a href="tel:{{ $user->mobile }}" class="afagh-identity-val" style="font-family: monospace; color: #14b8a6; text-decoration: none;" dir="ltr">
                                {{ $user->mobile ?: '—' }}
                            </a>
                        </div>

                        <div class="afagh-identity-row">
                            <span class="afagh-identity-key">تلفن ثابت:</span>
                            <span class="afagh-identity-val" style="font-family: monospace;" dir="ltr">{{ $user->phone ?: '—' }}</span>
                        </div>

                        <div class="afagh-identity-row">
                            <span class="afagh-identity-key">تاریخ تولد:</span>
                            <span class="afagh-identity-val" dir="ltr">
                                {{ $user->birth_date ? \App\Casts\JalaliDateCast::toJalali($user->birth_date) : '—' }}
                            </span>
                        </div>

                        <div class="afagh-identity-row">
                            <span class="afagh-identity-key">پروفایل فروش:</span>
                            <span class="afagh-identity-val">{{ $user->profile_title ?: '—' }}</span>
                        </div>

                        @if(!empty($user->address))
                            <div class="afagh-address-box">
                                <strong style="display:block; margin-bottom: 0.25rem; color: #94a3b8;">آدرس:</strong>
                                <span>{{ $user->address }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- کارت ۲ سایدبار: تراز و خلاصه مالی پرونده (بدون برچسب واحد: ریال) --}}
                <div class="afagh-panel-card afagh-card-padded">
                    <div class="afagh-card-header">
                        <h2 class="afagh-card-title">۲. تراز و خلاصه مالی پرونده</h2>
                    </div>

                    <div class="afagh-mini-list">
                        {{-- مینی‌کارت ۱: مجموع مبالغ وصول‌شده --}}
                        <div class="afagh-mini-card afagh-mini-card-emerald">
                            <div class="afagh-mini-top">
                                <span class="afagh-mini-label">مجموع مبالغ وصول‌شده</span>
                                <span class="afagh-badge afagh-badge-success">تسویه شده</span>
                            </div>
                            <div class="afagh-mini-value">
                                {{ number_format($paidInstallmentsAmount) }} <small style="font-size: 0.78rem; font-weight: 600; opacity: 0.75;">ریال</small>
                            </div>
                            <div class="afagh-mini-sub">
                                <span>نقد و پیش‌پرداخت: {{ number_format($paidFromCashAndInitial) }}</span>
                                <span>اقساط: {{ number_format($paidFromInstallments) }}</span>
                            </div>
                        </div>

                        {{-- مینی‌کارت ۲: مانده بدهی و اقساط تعهدی --}}
                        <div class="afagh-mini-card afagh-mini-card-amber">
                            <div class="afagh-mini-top">
                                <span class="afagh-mini-label">مانده بدهی و اقساط تعهدی</span>
                                <span class="afagh-badge afagh-badge-warning">جاری</span>
                            </div>
                            <div class="afagh-mini-value">
                                {{ number_format($pendingAmount) }} <small style="font-size: 0.78rem; font-weight: 600; opacity: 0.75;">ریال</small>
                            </div>
                        </div>

                        {{-- مینی‌کارت ۳: اقساط معوق --}}
                        <div class="afagh-mini-card afagh-mini-card-rose">
                            <div class="afagh-mini-top">
                                <span class="afagh-mini-label">اقساط معوق (سررسید گذشته)</span>
                                <span class="afagh-badge afagh-badge-danger">{{ $overdueCount }} قسط</span>
                            </div>
                            <div class="afagh-mini-value">
                                {{ number_format($overdueAmount) }} <small style="font-size: 0.78rem; font-weight: 600; opacity: 0.75;">ریال</small>
                            </div>
                            @if($overdueCount > 0)
                                <div class="afagh-mini-sub">
                                    <button
                                        type="button"
                                        wire:click="$set('activeTab', 'installments'); $set('installmentStatusFilter', 'overdue')"
                                        style="background:none; border:none; color:#f43f5e; font-weight:800; font-size:0.76rem; cursor:pointer; padding:0;"
                                    >
                                        مشاهده سریع اقساط معوق ←
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- مینی‌کارت ۴: کل بیمه‌نامه‌ها --}}
                        <div class="afagh-mini-card afagh-mini-card-indigo">
                            <div class="afagh-mini-top">
                                <span class="afagh-mini-label">تعداد کل بیمه‌نامه‌ها</span>
                                <span class="afagh-badge afagh-badge-info">{{ $totalInstallmentsCount }} قسط ثبت‌شده</span>
                            </div>
                            <div class="afagh-mini-value">
                                {{ $totalPoliciesCount }} <small style="font-size: 0.78rem; font-weight: 600; opacity: 0.75;">فقره بیمه‌نامه</small>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- ==================== ستون سمت چپ (سیستم تب‌بندی و جداول) ==================== --}}
            <main class="afagh-panel-card">
                {{-- نوار تب‌ها --}}
                <div class="afagh-tabs-bar">
                    <button
                        type="button"
                        wire:click="setTab('policies')"
                        class="afagh-tab-btn {{ $activeTab === 'policies' ? 'afagh-tab-btn-active' : '' }}"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>بیمه‌نامه‌ها</span>
                        <span class="afagh-tab-badge">{{ $totalPoliciesCount }}</span>
                    </button>

                    <button
                        type="button"
                        wire:click="setTab('installments')"
                        class="afagh-tab-btn {{ $activeTab === 'installments' ? 'afagh-tab-btn-active' : '' }}"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span>اقساط مالی</span>
                        <span class="afagh-tab-badge">{{ $totalInstallmentsCount }}</span>
                        @if($overdueCount > 0)
                            <span class="afagh-badge afagh-badge-danger" style="padding: 0.1rem 0.45rem; font-size: 0.68rem;">
                                {{ $overdueCount }} معوق
                            </span>
                        @endif
                    </button>

                    <button
                        type="button"
                        wire:click="setTab('history')"
                        class="afagh-tab-btn {{ $activeTab === 'history' ? 'afagh-tab-btn-active' : '' }}"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>تاریخچه و ارتباطات</span>
                        <span class="afagh-tab-badge">{{ $historyTotalCount }}</span>
                    </button>
                </div>

                {{-- ==================== محتوای تب ۱: بیمه‌نامه‌ها ==================== --}}
                @if($activeTab === 'policies')
                    <div>
                        <div class="afagh-toolbar">
                            <div class="afagh-filter-row">
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="policySearch"
                                    placeholder="🔍 جستجو شماره بیمه‌نامه، خودرو، پلاک..."
                                    class="afagh-search-input"
                                />

                                <select wire:model.live="policyStatusFilter" class="afagh-select-filter">
                                    <option value="all">وضعیت اعتبار: همه</option>
                                    <option value="active">فعال (جاری)</option>
                                    <option value="expiring_soon">در آستانه انقضا (۳۰ روز)</option>
                                    <option value="expired">منقضی شده</option>
                                </select>

                                <select wire:model.live="policyPaymentFilter" class="afagh-select-filter">
                                    <option value="all">روش پرداخت: همه</option>
                                    <option value="cash">پرداخت نقدی کل</option>
                                    <option value="installment">فروش اقساطی</option>
                                </select>

                                @if($policySearch !== '' || $policyStatusFilter !== 'all' || $policyPaymentFilter !== 'all')
                                    <button type="button" wire:click="resetPolicyFilters" class="afagh-btn afagh-btn-danger afagh-btn-sm">
                                        پاک کردن فیلترها
                                    </button>
                                @endif
                            </div>

                            <button type="button" wire:click="openCreatePolicyModal" class="afagh-btn afagh-btn-primary afagh-btn-sm">
                                <span>+ ثبت بیمه‌نامه جدید</span>
                            </button>
                        </div>

                        @if($policies->isEmpty())
                            <div class="afagh-empty-box">
                                <div class="afagh-empty-icon">
                                    <svg style="width:2rem; height:2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="afagh-card-title" style="font-size: 0.95rem;">
                                    هیچ بیمه‌نامه‌ای برای نمایش وجود ندارد
                                </h3>
                                <p style="font-size: 0.8rem; color: #94a3b8; max-width: 28rem; margin: 0;">
                                    @if($totalPoliciesCount === 0)
                                        هنوز بیمه‌نامه‌ای برای این بیمه‌گذار ثبت نشده است. می‌توانید از طریق ایمپورت اکسل یا دکمه زیر بیمه‌نامه جدید ثبت کنید.
                                    @else
                                        هیچ بیمه‌نامه‌ای با فیلترهای انتخابی شما مطابقت ندارد.
                                    @endif
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    @if($totalPoliciesCount === 0)
                                        <button type="button" wire:click="openCreatePolicyModal" class="afagh-btn afagh-btn-primary">
                                            + ثبت اولین بیمه‌نامه
                                        </button>
                                    @else
                                        <button type="button" wire:click="resetPolicyFilters" class="afagh-btn afagh-btn-secondary">
                                            نمایش همه بیمه‌نامه‌ها
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div style="overflow-x: auto;">
                                <table class="afagh-clean-table">
                                    <thead>
                                        <tr>
                                            <th>شماره و شرکت</th>
                                            <th>مورد بیمه و پلاک</th>
                                            <th>اعتبار (صدور / انقضا)</th>
                                            <th>وضعیت مالی و پرداخت</th>
                                            <th style="text-align: left;">عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($policies as $policy)
                                            @php
                                                $isCash = $policy->payment_type === 'cash'
                                                    || ($policy->installments->isEmpty() && $policy->total_amount > 0);
                                                $paidInstCount = $policy->installments->where('status', 'paid')->count();
                                                $totalInstCount = $policy->installments->count();
                                                $daysLeft = $policy->expires_at
                                                    ? (int) now('Asia/Tehran')->startOfDay()->diffInDays($policy->expires_at->copy()->startOfDay(), false)
                                                    : null;
                                            @endphp
                                            <tr wire:key="policy-row-{{ $policy->id }}">
                                                <td>
                                                    <div class="afagh-cell-primary" style="font-family: monospace;" dir="ltr">
                                                        {{ $policy->policy_number }}
                                                    </div>
                                                    <div class="afagh-cell-secondary">
                                                        {{ $policy->insurer?->name ?? 'بیمه ایران' }} • {{ $policy->insurance_type }}
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="afagh-cell-primary">
                                                        {{ $policy->vehicle_name ?: ($policy->title ?: '—') }}
                                                    </div>
                                                    <div class="afagh-cell-secondary">
                                                        @if($policy->plate_number)
                                                            <span>پلاک: {{ $policy->plate_number }}</span>
                                                        @endif
                                                        @if($policy->vehicle_model)
                                                            <span> • مدل {{ $policy->vehicle_model }}</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="afagh-cell-primary" style="font-size: 0.8rem;">
                                                        <span style="color:#94a3b8;">صدور:</span>
                                                        <span dir="ltr">{{ $policy->started_at_jalali }}</span>
                                                    </div>
                                                    <div class="afagh-cell-secondary" style="display:flex; align-items:center; gap:0.4rem;">
                                                        <span>انقضا:</span>
                                                        <strong dir="ltr" style="color:inherit;">{{ $policy->expires_at_jalali }}</strong>
                                                        @if($daysLeft !== null)
                                                            @if($daysLeft < 0)
                                                                <span class="afagh-badge afagh-badge-danger">منقضی</span>
                                                            @elseif($daysLeft <= 30)
                                                                <span class="afagh-badge afagh-badge-warning">{{ $daysLeft }} روز مانده</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="afagh-cell-primary">
                                                        {{ number_format((int) $policy->total_amount) }} ریال
                                                    </div>
                                                    <div class="afagh-cell-secondary" style="display:flex; flex-wrap:wrap; align-items:center; gap:0.4rem;">
                                                        @if($isCash)
                                                            <span class="afagh-badge afagh-badge-success">پرداخت نقدی کل</span>
                                                        @else
                                                            <span class="afagh-badge afagh-badge-info">
                                                                اقساطی ({{ $paidInstCount }}/{{ $totalInstCount }})
                                                            </span>
                                                            @if($policy->initial_payment > 0)
                                                                <span>پیش‌پرداخت: {{ number_format((int) $policy->initial_payment) }}</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="afagh-row-actions">
                                                        <button
                                                            type="button"
                                                            wire:click="openPolicyDetail({{ $policy->id }})"
                                                            class="afagh-btn afagh-btn-indigo afagh-btn-sm"
                                                        >
                                                            جزئیات
                                                        </button>

                                                        <button
                                                            type="button"
                                                            wire:click="openEditPolicyModal({{ $policy->id }})"
                                                            class="afagh-btn afagh-btn-secondary afagh-btn-icon"
                                                            title="ویرایش بیمه‌نامه"
                                                        >
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>

                                                        <button
                                                            type="button"
                                                            wire:click="deletePolicy({{ $policy->id }})"
                                                            wire:confirm="آیا از حذف این بیمه‌نامه و اقساط مرتبط با آن اطمینان دارید؟"
                                                            class="afagh-btn afagh-btn-danger afagh-btn-icon"
                                                            title="حذف بیمه‌نامه"
                                                        >
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ==================== محتوای تب ۲: اقساط مالی ==================== --}}
                @if($activeTab === 'installments')
                    <div>
                        <div class="afagh-toolbar">
                            <div class="afagh-filter-row">
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="installmentSearch"
                                    placeholder="🔍 جستجو شماره بیمه‌نامه، شناسه پرداخت، مبلغ..."
                                    class="afagh-search-input"
                                />

                                <select wire:model.live="installmentStatusFilter" class="afagh-select-filter">
                                    <option value="all">همه وضعیت‌ها ({{ $totalInstallmentsCount }})</option>
                                    <option value="overdue">معوق - سررسید گذشته ({{ $overdueCount }})</option>
                                    <option value="pending">در انتظار پرداخت</option>
                                    <option value="awaiting_approval">منتظر تایید ادمین</option>
                                    <option value="paid">وصول شده (تسویه)</option>
                                </select>

                                <select wire:model.live="installmentSort" class="afagh-select-filter">
                                    <option value="due_date_asc">مرتب‌سازی: نزدیک‌ترین سررسید</option>
                                    <option value="due_date_desc">مرتب‌سازی: جدیدترین سررسید</option>
                                    <option value="amount_desc">مرتب‌سازی: بیشترین مبلغ</option>
                                    <option value="number_asc">مرتب‌سازی: شماره قسط</option>
                                </select>

                                @if($installmentSearch !== '' || $installmentStatusFilter !== 'all')
                                    <button type="button" wire:click="resetInstallmentFilters" class="afagh-btn afagh-btn-danger afagh-btn-sm">
                                        پاک کردن فیلترها
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if($allInstallments->isEmpty())
                            <div class="afagh-empty-box">
                                <div class="afagh-empty-icon">
                                    <svg style="width:2rem; height:2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <h3 class="afagh-card-title" style="font-size: 0.95rem;">
                                    هیچ قسطی برای نمایش وجود ندارد
                                </h3>
                                <p style="font-size: 0.8rem; color: #94a3b8; max-width: 28rem; margin: 0;">
                                    @if($totalInstallmentsCount === 0)
                                        برای این بیمه‌گذار قسطی ثبت نشده است (ممکن است بیمه‌نامه‌ها نقدی باشند یا هنوز فایل اکسل اقساط بارگذاری نشده باشد).
                                    @else
                                        هیچ قسطی با فیلتر انتخاب‌شده یافت نشد.
                                    @endif
                                </p>
                                @if($totalInstallmentsCount > 0)
                                    <div style="margin-top: 0.5rem;">
                                        <button type="button" wire:click="resetInstallmentFilters" class="afagh-btn afagh-btn-secondary">
                                            نمایش تمام اقساط
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div style="overflow-x: auto;">
                                <table class="afagh-clean-table">
                                    <thead>
                                        <tr>
                                            <th>قسط و بیمه‌نامه</th>
                                            <th>سررسید و تاریخ وصول</th>
                                            <th>مبلغ و شناسه پرداخت</th>
                                            <th>وضعیت</th>
                                            <th style="text-align: left;">عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allInstallments as $inst)
                                            <tr wire:key="inst-row-{{ $inst->id }}">
                                                <td>
                                                    <div class="afagh-cell-primary">
                                                        قسط شماره {{ $inst->installment_number }}
                                                    </div>
                                                    <div class="afagh-cell-secondary" style="font-family: monospace;" dir="ltr">
                                                        {{ $inst->policy?->policy_number ?? '—' }}
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="afagh-cell-primary" dir="ltr">
                                                        {{ $inst->due_date_jalali }}
                                                    </div>
                                                    @if($inst->status === 'paid' && ($inst->documented_date || $inst->paid_at))
                                                        <div class="afagh-cell-secondary" style="color: #10b981;">
                                                            وصول: {{ $inst->documented_date ?: $inst->paid_at_jalali }}
                                                        </div>
                                                    @endif
                                                </td>

                                                <td>
                                                    <div class="afagh-cell-primary">
                                                        {{ number_format((int) $inst->amount) }} ریال
                                                    </div>
                                                    @if($inst->payment_identifier)
                                                        <div class="afagh-cell-secondary" style="font-family: monospace;">
                                                            شناسه: {{ $inst->payment_identifier }}
                                                        </div>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($inst->status === 'paid')
                                                        <span class="afagh-badge afagh-badge-success">✓ وصول شده</span>
                                                    @elseif($inst->status === 'awaiting_approval')
                                                        <span class="afagh-badge afagh-badge-info">منتظر تایید</span>
                                                    @elseif($inst->status === 'overdue')
                                                        <span class="afagh-badge afagh-badge-danger">! معوق</span>
                                                    @else
                                                        <span class="afagh-badge afagh-badge-warning">در انتظار</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <div class="afagh-row-actions">
                                                        @if($inst->status !== 'paid' && (auth()->user()?->hasFinancialAccess() ?? false))
                                                            <button
                                                                type="button"
                                                                wire:click="approveInstallment({{ $inst->id }})"
                                                                wire:confirm="آیا وصول این قسط را تایید می‌کنید؟"
                                                                class="afagh-btn afagh-btn-success afagh-btn-sm"
                                                            >
                                                                ✓ تایید وصول
                                                            </button>
                                                        @endif

                                                        <button
                                                            type="button"
                                                            wire:click="openEditInstallmentModal({{ $inst->id }})"
                                                            class="afagh-btn afagh-btn-secondary afagh-btn-icon"
                                                            title="ویرایش قسط"
                                                        >
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ==================== محتوای تب ۳: تاریخچه و ارتباطات ==================== --}}
                @if($activeTab === 'history')
                    <div>
                        <div class="afagh-toolbar">
                            <div class="afagh-filter-row">
                                <button
                                    type="button"
                                    wire:click="$set('historyFilter', 'all')"
                                    class="afagh-btn afagh-btn-sm {{ $historyFilter === 'all' ? 'afagh-btn-primary' : 'afagh-btn-secondary' }}"
                                >
                                    همه رویدادها ({{ $historyTotalCount }})
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('historyFilter', 'notifications')"
                                    class="afagh-btn afagh-btn-sm {{ $historyFilter === 'notifications' ? 'afagh-btn-primary' : 'afagh-btn-secondary' }}"
                                >
                                    پیام‌های ارسالی بله ({{ $notificationLogs->count() }})
                                </button>
                                <button
                                    type="button"
                                    wire:click="$set('historyFilter', 'audits')"
                                    class="afagh-btn afagh-btn-sm {{ $historyFilter === 'audits' ? 'afagh-btn-primary' : 'afagh-btn-secondary' }}"
                                >
                                    لاگ تغییرات مالی ({{ $audits->count() }})
                                </button>
                            </div>

                            <a href="{{ $notificationTargetUrl }}" class="afagh-btn afagh-btn-indigo afagh-btn-sm">
                                + ارسال پیام جدید به این بیمه‌گذار
                            </a>
                        </div>

                        <div class="afagh-card-padded" style="display:flex; flex-direction:column; gap:1.5rem;">
                            @if($historyFilter === 'all' || $historyFilter === 'notifications')
                                <div>
                                    <h3 class="afagh-card-title" style="margin-bottom: 0.85rem;">
                                        سوابق پیام‌ها و اعلان‌های ارسالی (ربات بله و پرتال)
                                    </h3>
                                    @if($notificationLogs->isEmpty())
                                        <div class="afagh-empty-box" style="margin:0; padding:1.75rem;">
                                            <span style="font-size:0.8rem; color:#94a3b8;">هنوز هیچ پیام یا اعلانی برای این بیمه‌گذار ارسال نشده است.</span>
                                        </div>
                                    @else
                                        <div style="overflow-x: auto; border: 1px solid rgba(148,163,184,0.25); border-radius: 0.9rem;">
                                            <table class="afagh-clean-table">
                                                <thead>
                                                    <tr>
                                                        <th>زمان ثبت</th>
                                                        <th>نوع پیام</th>
                                                        <th>متن پیام</th>
                                                        <th>وضعیت ارسال</th>
                                                        <th style="text-align: left;">عملیات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($notificationLogs as $log)
                                                        <tr wire:key="notif-log-{{ $log->id }}">
                                                            <td style="font-family: monospace; white-space: nowrap;" dir="ltr">
                                                                {{ \App\Casts\JalaliDateCast::toJalali($log->created_at, 'Y/m/d H:i') }}
                                                            </td>
                                                            <td>
                                                                <span class="afagh-badge afagh-badge-neutral">
                                                                    {{ match($log->type) {
                                                                        'installment_reminder' => 'یادآور قسط',
                                                                        'policy_renewal'       => 'یادآور تمدید',
                                                                        'birthday_greeting'    => 'تبریک تولد',
                                                                        'custom_message'       => 'پیام اختصاصی',
                                                                        default                => $log->type,
                                                                    } }}
                                                                </span>
                                                            </td>
                                                            <td style="max-width: 24rem;">
                                                                <div style="font-size:0.8rem; line-height:1.5;">{{ $log->message }}</div>
                                                                @if($log->error_message)
                                                                    <div style="font-size:0.72rem; color:#f43f5e; margin-top:0.25rem;">{{ $log->error_message }}</div>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($log->status === 'sent')
                                                                    <span class="afagh-badge afagh-badge-success">ارسال شده</span>
                                                                @elseif($log->status === 'failed')
                                                                    <span class="afagh-badge afagh-badge-danger">خطا در ارسال</span>
                                                                @else
                                                                    <span class="afagh-badge afagh-badge-warning">در صف ارسال</span>
                                                                @endif
                                                            </td>
                                                            <td style="text-align: left;">
                                                                @if($log->status === 'failed')
                                                                    <button
                                                                        type="button"
                                                                        wire:click="retryNotification({{ $log->id }})"
                                                                        class="afagh-btn afagh-btn-secondary afagh-btn-sm"
                                                                    >
                                                                        ارسال مجدد
                                                                    </button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($historyFilter === 'all' || $historyFilter === 'audits')
                                <div>
                                    <h3 class="afagh-card-title" style="margin-bottom: 0.85rem;">
                                        تاریخچه تغییرات مالی و حسابرسی اقساط (Audit Log)
                                    </h3>
                                    @if($audits->isEmpty())
                                        <div class="afagh-empty-box" style="margin:0; padding:1.75rem;">
                                            <span style="font-size:0.8rem; color:#94a3b8;">هنوز هیچ تغییر وضعیتی روی اقساط این پرونده ثبت نشده است.</span>
                                        </div>
                                    @else
                                        <div style="overflow-x: auto; border: 1px solid rgba(148,163,184,0.25); border-radius: 0.9rem;">
                                            <table class="afagh-clean-table">
                                                <thead>
                                                    <tr>
                                                        <th>زمان دقیق</th>
                                                        <th>اقدام‌کننده</th>
                                                        <th>بیمه‌نامه / قسط</th>
                                                        <th>تغییر وضعیت</th>
                                                        <th>توضیحات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($audits as $audit)
                                                        <tr wire:key="audit-row-{{ $audit->id }}">
                                                            <td style="font-family: monospace; white-space: nowrap;" dir="ltr">
                                                                {{ $audit->created_at_jalali }}
                                                            </td>
                                                            <td>
                                                                <span class="afagh-badge afagh-badge-info">
                                                                    {{ $audit->actor?->name ?: "سیستم ({$audit->actor_type})" }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="afagh-cell-primary">قسط {{ $audit->installment?->installment_number ?? '—' }}</div>
                                                                <div class="afagh-cell-secondary" style="font-family: monospace;" dir="ltr">
                                                                    {{ $audit->installment?->policy?->policy_number ?? '' }}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @if($audit->old_status)
                                                                    <span style="color:#94a3b8;">{{ $audit->old_status }}</span>
                                                                    <span style="margin: 0 0.25rem;">←</span>
                                                                @endif
                                                                <strong style="color:#10b981;">{{ $audit->new_status }}</strong>
                                                            </td>
                                                            <td>{{ $audit->description }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </main>
        </div>

        {{-- ==================== مودال ۱: مشاهده جزئیات کامل بیمه‌نامه ==================== --}}
        @if($showPolicyDetailModal && $viewingPolicy)
            @php
                $isViewingCash = $viewingPolicy->payment_type === 'cash'
                    || ($viewingPolicy->installments->isEmpty() && $viewingPolicy->total_amount > 0);
            @endphp
            <div class="afagh-modal-backdrop" wire:click.self="closePolicyDetail">
                <div class="afagh-modal-box">
                    <div class="afagh-modal-header">
                        <div>
                            <h3 class="afagh-card-title" style="font-size: 1rem;">
                                <span>جزئیات کامل بیمه‌نامه:</span>
                                <span style="font-family: monospace;" dir="ltr">{{ $viewingPolicy->policy_number }}</span>
                            </h3>
                            <div class="afagh-cell-secondary">
                                {{ $viewingPolicy->insurer?->name ?? 'بیمه ایران' }} • {{ $viewingPolicy->title }}
                            </div>
                        </div>
                        <button type="button" wire:click="closePolicyDetail" class="afagh-btn afagh-btn-secondary afagh-btn-sm">
                            ✕ بستن
                        </button>
                    </div>

                    <div class="afagh-modal-body">
                        <div class="afagh-form-grid-3">
                            <div class="afagh-identity-row">
                                <span class="afagh-identity-key">مورد بیمه (خودرو):</span>
                                <span class="afagh-identity-val">{{ $viewingPolicy->vehicle_name ?: '—' }}</span>
                            </div>
                            <div class="afagh-identity-row">
                                <span class="afagh-identity-key">شماره پلاک:</span>
                                <span class="afagh-identity-val">{{ $viewingPolicy->plate_number ?: '—' }}</span>
                            </div>
                            <div class="afagh-identity-row">
                                <span class="afagh-identity-key">مدل خودرو:</span>
                                <span class="afagh-identity-val">{{ $viewingPolicy->vehicle_model ?: '—' }}</span>
                            </div>
                            <div class="afagh-identity-row">
                                <span class="afagh-identity-key">عدم خسارت (ثالث/راننده):</span>
                                <span class="afagh-identity-val">
                                    {{ $viewingPolicy->no_claim_years ?: '0' }} سال / {{ $viewingPolicy->driver_no_claim_years ?: '0' }} سال
                                </span>
                            </div>
                            <div class="afagh-identity-row">
                                <span class="afagh-identity-key">اعتبار بیمه‌نامه:</span>
                                <span class="afagh-identity-val" dir="ltr">
                                    {{ $viewingPolicy->started_at_jalali }} → {{ $viewingPolicy->expires_at_jalali }}
                                </span>
                            </div>
                            <div class="afagh-identity-row">
                                <span class="afagh-identity-key">مبلغ کل ({{ $isViewingCash ? 'نقدی' : 'اقساطی' }}):</span>
                                <span class="afagh-identity-val" style="color: #14b8a6;">
                                    {{ number_format((int) $viewingPolicy->total_amount) }} ریال
                                </span>
                            </div>
                        </div>

                        <div>
                            <h4 class="afagh-card-title" style="margin-bottom: 0.75rem;">
                                وضعیت پرداخت و اقساط این بیمه‌نامه
                            </h4>
                            @if($isViewingCash)
                                <div class="afagh-mini-card afagh-mini-card-emerald">
                                    <span class="afagh-mini-label">
                                        ✓ این بیمه‌نامه به صورت «پرداخت نقدی کل» به مبلغ {{ number_format((int) $viewingPolicy->total_amount) }} ریال تسویه شده است.
                                    </span>
                                </div>
                            @elseif($viewingPolicy->installments->isEmpty())
                                <div class="afagh-empty-box" style="margin:0; padding:1.5rem;">
                                    <span style="font-size:0.8rem; color:#94a3b8;">هیچ ردیف قسطی برای این بیمه‌نامه ثبت نشده است.</span>
                                </div>
                            @else
                                <div style="overflow-x: auto; border: 1px solid rgba(148,163,184,0.25); border-radius: 0.9rem;">
                                    <table class="afagh-clean-table">
                                        <thead>
                                            <tr>
                                                <th>شماره قسط</th>
                                                <th>سررسید</th>
                                                <th>مبلغ (ریال)</th>
                                                <th>شناسه پرداخت</th>
                                                <th>وضعیت</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewingPolicy->installments as $pInst)
                                                <tr>
                                                    <td style="font-weight:800;">قسط {{ $pInst->installment_number }}</td>
                                                    <td dir="ltr">{{ $pInst->due_date_jalali }}</td>
                                                    <td style="font-weight:800;">{{ number_format((int) $pInst->amount) }}</td>
                                                    <td style="font-family:monospace;">{{ $pInst->payment_identifier ?: '—' }}</td>
                                                    <td>
                                                        @if($pInst->status === 'paid')
                                                            <span class="afagh-badge afagh-badge-success">وصول شده</span>
                                                        @elseif($pInst->status === 'overdue')
                                                            <span class="afagh-badge afagh-badge-danger">معوق</span>
                                                        @else
                                                            <span class="afagh-badge afagh-badge-warning">در انتظار</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="afagh-modal-footer">
                        <button
                            type="button"
                            wire:click="openEditPolicyModal({{ $viewingPolicy->id }}); closePolicyDetail();"
                            class="afagh-btn afagh-btn-primary"
                        >
                            ✎ ویرایش این بیمه‌نامه
                        </button>
                        <button type="button" wire:click="closePolicyDetail" class="afagh-btn afagh-btn-secondary">
                            بستن
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ==================== مودال ۲: ثبت / ویرایش بیمه‌نامه ==================== --}}
        @if($showPolicyFormModal)
            <div class="afagh-modal-backdrop" wire:click.self="closePolicyFormModal">
                <div class="afagh-modal-box">
                    <div class="afagh-modal-header">
                        <h3 class="afagh-card-title" style="font-size: 1rem;">
                            {{ $editingPolicyId ? '✎ ویرایش بیمه‌نامه' : '+ ثبت بیمه‌نامه جدید برای ' . $user->name }}
                        </h3>
                        <button type="button" wire:click="closePolicyFormModal" class="afagh-btn afagh-btn-secondary afagh-btn-sm">
                            ✕
                        </button>
                    </div>

                    <form wire:submit="savePolicy">
                        <div class="afagh-modal-body">
                            <div class="afagh-form-grid-3">
                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">شماره بیمه‌نامه *</label>
                                    <input type="text" wire:model="policyFormData.policy_number" required class="afagh-form-control" dir="ltr" placeholder="1405/..." />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">شرکت بیمه</label>
                                    <select wire:model="policyFormData.insurer_id" class="afagh-form-control">
                                        @foreach($insurers as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">عنوان و رشته بیمه</label>
                                    <input type="text" wire:model="policyFormData.title" class="afagh-form-control" placeholder="بیمه شخص ثالث" />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">نام و تیپ خودرو</label>
                                    <input type="text" wire:model="policyFormData.vehicle_name" class="afagh-form-control" placeholder="پژو 206 تیپ 5" />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">شماره پلاک انتظامی</label>
                                    <input type="text" wire:model="policyFormData.plate_number" class="afagh-form-control" placeholder="12 ب 345 ایران 66" />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">مدل خودرو (سال)</label>
                                    <input type="text" wire:model="policyFormData.vehicle_model" class="afagh-form-control" dir="ltr" placeholder="1402" />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">تاریخ صدور (شمسی)</label>
                                    <input type="text" wire:model="policyFormData.issued_at_jalali" class="afagh-form-control" dir="ltr" placeholder="1405/08/01" />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">تاریخ انقضا (شمسی)</label>
                                    <input type="text" wire:model="policyFormData.expires_at_jalali" class="afagh-form-control" dir="ltr" placeholder="1406/08/01" />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">نوع پرداخت *</label>
                                    <select wire:model.live="policyFormData.payment_type" class="afagh-form-control">
                                        <option value="installment">فروش اقساطی</option>
                                        <option value="cash">پرداخت نقدی کل بیمه‌نامه</option>
                                    </select>
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">مبلغ کل بیمه‌نامه (ریال)</label>
                                    <input type="number" wire:model="policyFormData.total_amount" class="afagh-form-control" dir="ltr" />
                                </div>

                                @if(($policyFormData['payment_type'] ?? 'installment') === 'installment')
                                    <div class="afagh-field-group">
                                        <label class="afagh-field-label">مبلغ پیش‌پرداخت نقدی (ریال)</label>
                                        <input type="number" wire:model="policyFormData.initial_payment" class="afagh-form-control" dir="ltr" />
                                    </div>

                                    @if(!$editingPolicyId)
                                        <div class="afagh-field-group">
                                            <label class="afagh-field-label">تعداد اقساط (تولید خودکار)</label>
                                            <input type="number" wire:model="policyFormData.installments_count" class="afagh-form-control" dir="ltr" min="0" max="24" />
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="afagh-modal-footer">
                            <button type="button" wire:click="closePolicyFormModal" class="afagh-btn afagh-btn-secondary">
                                انصراف
                            </button>
                            <button type="submit" class="afagh-btn afagh-btn-primary">
                                ✓ {{ $editingPolicyId ? 'ذخیره تغییرات بیمه‌نامه' : 'ثبت بیمه‌نامه' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- ==================== مودال ۳: ویرایش قسط ==================== --}}
        @if($showInstallmentFormModal)
            <div class="afagh-modal-backdrop" wire:click.self="closeInstallmentFormModal">
                <div class="afagh-modal-box" style="max-width: 34rem;">
                    <div class="afagh-modal-header">
                        <h3 class="afagh-card-title" style="font-size: 1rem;">
                            ✎ ویرایش اطلاعات قسط
                        </h3>
                        <button type="button" wire:click="closeInstallmentFormModal" class="afagh-btn afagh-btn-secondary afagh-btn-sm">
                            ✕
                        </button>
                    </div>

                    <form wire:submit="saveInstallment">
                        <div class="afagh-modal-body">
                            <div class="afagh-form-grid-2">
                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">شماره قسط</label>
                                    <input type="number" wire:model="installmentFormData.installment_number" class="afagh-form-control" dir="ltr" />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">تاریخ سررسید (شمسی)</label>
                                    <input type="text" wire:model="installmentFormData.due_date_jalali" class="afagh-form-control" dir="ltr" placeholder="1405/08/01" />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">مبلغ قسط (ریال)</label>
                                    <input type="number" wire:model="installmentFormData.amount" class="afagh-form-control" dir="ltr" />
                                </div>

                                <div class="afagh-field-group">
                                    <label class="afagh-field-label">شناسه پرداخت</label>
                                    <input type="text" wire:model="installmentFormData.payment_identifier" class="afagh-form-control" dir="ltr" />
                                </div>

                                <div class="afagh-field-group afagh-col-span-full">
                                    <label class="afagh-field-label">وضعیت قسط</label>
                                    <select wire:model="installmentFormData.status" class="afagh-form-control">
                                        <option value="pending">در انتظار پرداخت</option>
                                        <option value="awaiting_approval">در انتظار تایید ادمین</option>
                                        <option value="overdue">معوق (سررسید گذشته)</option>
                                        <option value="paid">وصول شده (تسویه نهایی)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="afagh-modal-footer">
                            <button type="button" wire:click="closeInstallmentFormModal" class="afagh-btn afagh-btn-secondary">
                                لغو
                            </button>
                            <button type="submit" class="afagh-btn afagh-btn-primary">
                                ✓ ذخیره تغییرات قسط
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>