<?php

declare(strict_types=1);

namespace App\Services;

use App\Filament\Widgets\RecentChangesWidget;
use App\Models\Installment;
use App\Models\Insurer;
use App\Models\Policy;
use App\Models\SystemActivity;
use App\Models\User;
use App\Support\PersianNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Morilog\Jalali\Jalalian;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class ExcelImportService
{
    private int $createdUsers = 0;

    private int $updatedUsers = 0;

    private int $updatedPolicies = 0;

    private int $updatedInstallments = 0;

    /**
     * نقطه ورود استاندارد برای فراخوانی از پنل Filament
     */
    public function import(string $filePath): array
    {
        DB::disableQueryLog();

        return Installment::withoutEvents(function () use ($filePath): array {
            $result = $this->processFile($filePath);

            return [
                'users_processed'        => $this->createdUsers + $this->updatedUsers,
                'policies_processed'     => $this->updatedPolicies,
                'installments_processed' => $this->updatedInstallments,
                'new_users_count'        => $this->createdUsers,
                'updated_users_count'    => $this->updatedUsers,
                'stats'                  => $result['stats'] ?? [],
            ];
        });
    }

    public function processFile(string $filePath): array
    {
        $this->createdUsers = 0;
        $this->updatedUsers = 0;
        $this->updatedPolicies = 0;
        $this->updatedInstallments = 0;

        $spreadsheet = IOFactory::load($filePath);
        $sheetsProcessed = 0;
        $detectedTypes = [];

        DB::beginTransaction();
        try {
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $matrix = $this->extractWorksheetMatrix($worksheet);
                if (empty($matrix)) {
                    continue;
                }

                $headerInfo = $this->locateHeaderRow($matrix);
                if ($headerInfo === null) {
                    continue;
                }

                [$headerRowIndex, $headers] = $headerInfo;
                $dataRows = array_slice($matrix, $headerRowIndex + 1);
                if (empty($dataRows)) {
                    continue;
                }

                $fileType = $this->detectStructureType($headers, $dataRows);
                if ($fileType === 'unknown') {
                    continue;
                }

                $detectedTypes[] = $fileType;
                $sheetsProcessed++;

                match ($fileType) {
                    'issuance_report'   => $this->processIssuanceReport($dataRows, $headers),
                    'policies_flat'     => $this->processFlatPolicies($dataRows, $headers),
                    'installments_flat' => $this->processFlatInstallments($dataRows, $headers),
                    'generic_csv'       => $this->processGenericCsv($dataRows, $headers),
                };
            }

            if ($sheetsProcessed === 0) {
                throw new \Exception('ساختار ستون‌های فایل اکسل قابل شناسایی نبود یا فایل خالی است.');
            }

            if (Schema::hasTable('system_activities')) {
                SystemActivity::create([
                    'actor_id'   => auth()->id(),
                    'event_type' => 'excel_imported',
                    'title'      => 'پردازش و همگام‌سازی هوشمند فایل اکسل',
                    'icon'       => 'app_upload_file',
                    'color'      => 'emerald',
                    'details'    => [
                        'file_type'     => implode(', ', array_unique($detectedTypes)),
                        'created_count' => $this->createdUsers,
                        'updated_count' => $this->updatedUsers,
                        'description'   => "ایجاد بیمه‌گذار: {$this->createdUsers} | تکمیل بیمه‌گذار: {$this->updatedUsers} | بیمه‌نامه‌ها: {$this->updatedPolicies} | اقساط: {$this->updatedInstallments}",
                        'stats'         => [
                            'new_users_count'                        => $this->createdUsers,
                            'updated_users_count'                    => $this->updatedUsers,
                            'updated_policies_count'                 => $this->updatedPolicies,
                            'updated_installments_count'             => $this->updatedInstallments,
                            'updated_policies_or_installments_count' => $this->updatedPolicies + $this->updatedInstallments,
                            'status_changed_installments_count'      => $this->updatedInstallments,
                        ],
                    ],
                ]);
            }

            DB::commit();
            if (class_exists(RecentChangesWidget::class)) {
                RecentChangesWidget::clearCache();
            }

            return [
                'success' => true,
                'message' => "بیمه‌گذاران جدید: {$this->createdUsers} | تکمیل‌شده: {$this->updatedUsers} | بیمه‌نامه‌ها: {$this->updatedPolicies} | اقساط پردازش‌شده: {$this->updatedInstallments}",
                'stats'   => [
                    'created_users'        => $this->createdUsers,
                    'updated_users'        => $this->updatedUsers,
                    'updated_policies'     => $this->updatedPolicies,
                    'updated_installments' => $this->updatedInstallments,
                ],
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Excel Import Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw new \Exception('خطا در پردازش داده‌ها: ' . $e->getMessage());
        }
    }

    /**
     * استخراج دقیق سلول‌های شیت بدون افت دقت اعداد ۱۷ رقمی (شناسه پرداخت) و بدون تبدیل به نماد علمی (3.145E+16)
     */
    private function extractWorksheetMatrix(Worksheet $worksheet): array
    {
        $highestRow = $worksheet->getHighestDataRow();
        $highestColStr = $worksheet->getHighestDataColumn();
        $highestCol = Coordinate::columnIndexFromString($highestColStr);

        if ($highestRow <= 0 || $highestCol <= 0) {
            return [];
        }

        $matrix = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowCells = [];
            $hasAnyValue = false;

            for ($col = 1; $col <= $highestCol; $col++) {
                $cell = $worksheet->getCell([$col, $row]);
                $rawVal = $cell->getValue();

                if ($rawVal === null || $rawVal === '') {
                    $rowCells[] = null;
                    continue;
                }

                if ($cell->getDataType() === DataType::TYPE_NUMERIC || is_int($rawVal) || is_float($rawVal)) {
                    $formatted = trim((string) $cell->getFormattedValue());
                    // اگر فرمت سلول به صورت علمی (E+) بود یا اعشار کاذب داشت، عدد صحیح دقیق استخراج شود
                    if (stripos($formatted, 'E') !== false) {
                        $strVal = number_format((float) $rawVal, 0, '', '');
                    } elseif (is_float($rawVal) && floor($rawVal) == $rawVal) {
                        $strVal = number_format($rawVal, 0, '', '');
                    } else {
                        $strVal = (string) $rawVal;
                    }
                } else {
                    $strVal = trim((string) $rawVal);
                }

                if ($strVal !== '') {
                    $hasAnyValue = true;
                }
                $rowCells[] = $strVal;
            }

            if ($hasAnyValue) {
                $matrix[] = $rowCells;
            }
        }

        return $matrix;
    }

    /**
     * پیدا کردن خودکار سطر هدر در ۲۰ سطر اول شیت (مستقل از ردیف‌های خالی یا عنوان بالای جدول)
     */
    private function locateHeaderRow(array $matrix): ?array
    {
        $maxScan = min(count($matrix), 20);
        $knownHeaderKeywords = [
            'شماره بیمه نامه', 'شماره بیمه‌نامه', 'نام بیمه گزار', 'نام بیمه گذار',
            'کد ملی', 'کدملی', 'کد ملی/شناسه ملی/پاسپورت', 'شماره همراه', 'موبایل',
            'سررسید', 'مبلغ قسط', 'مبلغ پرداختی', 'شناسه پرداخت', 'شماره انتظامی',
            'policy_number', 'national_code', 'mobile',
        ];

        for ($i = 0; $i < $maxScan; $i++) {
            $normalizedRow = array_map(fn ($cell): string => $this->normalizeHeaderKey((string) $cell), $matrix[$i]);
            $matches = 0;

            foreach ($normalizedRow as $colName) {
                if ($colName === '') {
                    continue;
                }
                foreach ($knownHeaderKeywords as $kw) {
                    if ($colName === $this->normalizeHeaderKey($kw) || str_contains($colName, $this->normalizeHeaderKey($kw))) {
                        $matches++;
                        break;
                    }
                }
            }

            if ($matches >= 2) {
                return [$i, $normalizedRow];
            }
        }

        return null;
    }

    /**
     * تشخیص هوشمند نوع ساختار اطلاعات بر اساس ستون‌ها (بدون هیچ وابستگی به نام فایل)
     */
    private function detectStructureType(array $headers, array $dataRows): string
    {
        $hasColumn = function (array $candidates) use ($headers): bool {
            foreach ($candidates as $c) {
                if (in_array($this->normalizeHeaderKey($c), $headers, true)) {
                    return true;
                }
            }
            return false;
        };

        // ۱. فایل گزارش صدور جامع (مانند «کاربران جدید.xls» که دارای ستون «نوع» نقد/قسط، «مبلغ پرداختی» و «مبلغ» است)
        if (
            ($hasColumn(['مبلغ پرداختی']) && $hasColumn(['نوع']) && $hasColumn(['مبلغ'])) ||
            $hasColumn(['شماره بیمه نامه قبلی', 'بیمه گر قبلی', 'نوع وسیله'])
        ) {
            return 'issuance_report';
        }

        // ۲. فایل وضعیت اقساط (مانند «اقساط.xls» که دارای «سررسید» و «مبلغ قسط» یا «مبلغ مستند» است)
        if ($hasColumn(['مبلغ قسط', 'مبلغ مستند', 'تاریخ مستند', 'سررسید'])) {
            return 'installments_flat';
        }

        // ۳. فایل مشخصات بیمه‌نامه‌ها و خودروها (مانند «بیمه.xls» که دارای «شماره انتظامی» یا «نوع و سیستم مورد بیمه» است)
        if ($hasColumn(['شماره انتظامی', 'نوع و سیستم مورد بیمه', 'سال های عدم خسارت', 'مدل خودرو'])) {
            return 'policies_flat';
        }

        // ۴. فرمت انگلیسی یا CSV عمومی
        if ($hasColumn(['policy_number', 'installments_count', 'total_amount'])) {
            return 'generic_csv';
        }

        // ۵. تشخیص پشتیبان در صورتی که حداقل شماره بیمه‌نامه وجود داشته باشد
        if ($hasColumn(['شماره بیمه نامه', 'شماره بیمه‌نامه'])) {
            return 'policies_flat';
        }

        return 'unknown';
    }

    /**
     * پردازش هوشمند گزارش صدور (مانند «کاربران جدید.xls»)
     * پشتیبانی کامل از:
     * - نقد کامل (حتی اگر عنوان پروفایل اعتباری 50-50 باشد ولی کل مبلغ نقداً پرداخت شده و قسطی زیر آن نباشد؛ مثل کاربر شماره ۶)
     * - پیش‌پرداخت نقد + اقساط در سطرهای زیرین (مثل کاربر شماره ۲ با ۳ قسط و کاربر شماره ۳ با ۵ قسط)
     * - عدم دستکاری اقساط تسویه‌شده (paid) و عدم بازنویسی اطلاعات قبلی
     */
    private function processIssuanceReport(array $rows, array $headers): void
    {
        // گام ۱: گروه‌بندی ردیف‌ها به بلوک‌های مجزای هر بیمه‌نامه (سطر مادر + سطرهای قسط زیرین)
        $policyBlocks = [];
        $currentBlock = null;

        foreach ($rows as $row) {
            $rowData = $this->combineRowWithHeaders($headers, $row);
            if ($this->isSummaryOrEmptyRow($rowData)) {
                continue;
            }

            $policyNumber = $this->val($rowData, ['شماره بیمه نامه', 'شماره بیمه‌نامه', 'policy_number']);
            $rowIndexVal = $this->val($rowData, ['ردیف', 'row']);
            $paymentRowType = $this->val($rowData, ['نوع', 'نوع پرداخت']);

            if ($this->isValidPolicyNumber($policyNumber, $rowIndexVal)) {
                if ($currentBlock !== null) {
                    $policyBlocks[] = $currentBlock;
                }
                $currentBlock = [
                    'main'         => $rowData,
                    'installments' => [],
                ];
            } elseif ($currentBlock !== null) {
                $amount = $this->cleanInt($this->val($rowData, ['مبلغ', 'مبلغ قسط', 'amount']));
                if ($paymentRowType === 'قسط' || ($paymentRowType === '' && $amount > 0 && $policyNumber === '')) {
                    $currentBlock['installments'][] = $rowData;
                } elseif ($paymentRowType === 'نقد' && $amount > 0) {
                    // اگر پیش‌پرداخت نقدی دیگری در سطر زیرین ثبت شده بود به پیش‌پرداخت اضافه شود
                    $currentBlock['extra_cash'][] = $amount;
                }
            }
        }

        if ($currentBlock !== null) {
            $policyBlocks[] = $currentBlock;
        }

        // گام ۲: ثبت یا تکمیل هر بلوک بیمه‌نامه و اقساط آن
        foreach ($policyBlocks as $block) {
            $main = $block['main'];
            $instRows = $block['installments'];

            $policyNumber = $this->val($main, ['شماره بیمه نامه', 'شماره بیمه‌نامه', 'policy_number']);
            $user = $this->findOrCreateUserPreservingExisting($main);
            $insurerId = $this->resolveInsurer($this->val($main, ['بیمه گر', 'شرکت بیمه', 'بیمه گر قبلی']) ?: 'بیمه ایران');

            // استخراج نام خودرو و رشته بیمه (پشتیبانی از ۱ خودرو با ۱۰ رشته بیمه مختلف یا ۱۰ خودرو برای ۱ شخص)
            [$category, $vehicle, $title] = $this->extractCategoryAndVehicle($main);

            // محاسبه دقیق مبالغ (مبلغ کل، مبلغ نقد اولیه، و بررسی وجود اقساط)
            $totalAmount = $this->cleanInt($this->val($main, ['مبلغ پرداختی', 'حق بیمه کل', 'مبلغ کل', 'total_amount']));
            $mainRowType = $this->val($main, ['نوع', 'نوع پرداخت']);
            $mainRowAmount = $this->cleanInt($this->val($main, ['مبلغ', 'پیش پرداخت']));
            $extraCash = array_sum($block['extra_cash'] ?? []);

            $initialCashPayment = 0;
            if ($mainRowType === 'نقد' && $mainRowAmount > 0) {
                $initialCashPayment = $mainRowAmount + $extraCash;
            } elseif ($mainRowType === 'قسط' && $mainRowAmount > 0) {
                // اگر در خود سطر اول مستقیماً قسط اول نوشته شده بود
                array_unshift($instRows, $main);
                $initialCashPayment = $extraCash;
            }

            // فیلتر کردن سطرهای قسط معتبر (دارای مبلغ بزرگ‌تر از صفر)
            $validInstRows = array_values(array_filter($instRows, function (array $r): bool {
                return $this->cleanInt($this->val($r, ['مبلغ', 'مبلغ قسط', 'amount'])) > 0;
            }));

            $installmentsCount = count($validInstRows);
            $sumOfInstallments = array_sum(array_map(
                fn (array $r): int => $this->cleanInt($this->val($r, ['مبلغ', 'مبلغ قسط', 'amount'])),
                $validInstRows
            ));

            if ($totalAmount <= 0) {
                $totalAmount = $initialCashPayment + $sumOfInstallments;
            }

            // تشخیص قطعی نقد یا اقساطی بودن:
            // اگر هیچ سطر قسطی وجود ندارد (مانند کاربر شماره ۶ که پروفایلش اعتباری 50-50 است اما کل 55,192,900 ریال را نقد داده) -> نقد کامل (cash)
            $isFullCash = ($installmentsCount === 0) || ($initialCashPayment >= $totalAmount && $totalAmount > 0 && $installmentsCount === 0);
            $paymentType = $isFullCash ? 'cash' : 'installment';

            if ($isFullCash && $initialCashPayment <= 0 && $totalAmount > 0) {
                $initialCashPayment = $totalAmount;
            }

            // بررسی شماره بیمه‌نامه قبلی برای به ارث بردن پلاک و مدل خودرو در صورت وجود در دیتابیس
            $prevPolicyNumber = $this->val($main, ['شماره بیمه نامه قبلی', 'بیمه نامه قبلی']);
            $inheritedPlate = null;
            $inheritedModel = null;
            $inheritedNoClaim = null;
            $inheritedDriverNoClaim = null;

            if ($prevPolicyNumber !== '' && $prevPolicyNumber !== '--') {
                $prevPolicy = Policy::withTrashed()
                    ->where('policy_number', $prevPolicyNumber)
                    ->first();
                if ($prevPolicy) {
                    $inheritedPlate = $prevPolicy->plate_number;
                    $inheritedModel = $prevPolicy->vehicle_model;
                    $inheritedNoClaim = $prevPolicy->no_claim_years;
                    $inheritedDriverNoClaim = $prevPolicy->driver_no_claim_years;
                }
            }

            $plateNumber = $this->val($main, ['شماره انتظامی', 'پلاک', 'plate_number']) ?: $inheritedPlate;
            $vehicleModel = $this->cleanDigitsOnly($this->val($main, ['مدل خودرو', 'سال ساخت'])) ?: $inheritedModel;
            $profileTitle = $this->val($main, ['پروفایل', 'profile_title']) ?: $user->profile_title;

            $startedJalali = $this->normalizeJalaliDateString($this->val($main, ['تاریخ شروع', 'started_at']));
            $expiresJalali = $this->normalizeJalaliDateString($this->val($main, ['تاریخ انقضا', 'expires_at']));
            $issuedJalali  = $this->normalizeJalaliDateString($this->val($main, ['تاریخ صدور', 'issued_at']));

            $currentPolicy = Policy::withTrashed()->where('policy_number', $policyNumber)->first();

            if (! $currentPolicy) {
                $currentPolicy = Policy::create([
                    'user_id'               => $user->id,
                    'insurer_id'            => $insurerId,
                    'policy_number'         => $policyNumber,
                    'title'                 => $title,
                    'insurance_category'    => $category,
                    'policyholder_type'     => $this->val($main, ['نوع بیمه گذار', 'نوع بیمه‌گذار']) ?: ($user->policyholder_type ?: 'حقیقی'),
                    'vehicle_name'          => $vehicle !== '' ? $vehicle : null,
                    'plate_number'          => $plateNumber,
                    'vehicle_model'         => $vehicleModel,
                    'no_claim_years'        => $inheritedNoClaim,
                    'driver_no_claim_years' => $inheritedDriverNoClaim,
                    'profile_title'         => $profileTitle !== '' ? $profileTitle : null,
                    'total_amount'          => $totalAmount,
                    'initial_payment'       => $initialCashPayment,
                    'down_payment'          => $initialCashPayment,
                    'paid_amount'           => $initialCashPayment,
                    'payment_type'          => $paymentType,
                    'installments_count'    => $installmentsCount,
                    'started_at_jalali'     => $startedJalali,
                    'started_at'            => $this->convertToGregorian($startedJalali),
                    'expires_at_jalali'     => $expiresJalali,
                    'expires_at'            => $this->convertToGregorian($expiresJalali),
                    'issued_at_jalali'      => $issuedJalali,
                    'issued_at'             => $this->convertToGregorian($issuedJalali),
                    'description'           => ($prevPolicyNumber !== '' && $prevPolicyNumber !== '--') ? "بیمه‌نامه قبلی: {$prevPolicyNumber}" : null,
                ]);
            } else {
                if ($currentPolicy->trashed()) {
                    $currentPolicy->restore();
                }

                // قانون حفظ اطلاعات قبلی: فقط فیلدهایی که در دیتابیس خالی/صفر یا پیش‌فرض هستند تکمیل شوند
                $policyUpdates = [];

                if (empty($currentPolicy->vehicle_name) && $vehicle !== '') {
                    $policyUpdates['vehicle_name'] = $vehicle;
                }
                if (
                    (empty($currentPolicy->title) || str_starts_with((string) $currentPolicy->title, 'ثالث - بیمه‌نامه')) &&
                    $title !== ''
                ) {
                    $policyUpdates['title'] = $title;
                }
                if (empty($currentPolicy->insurance_category) && $category !== '') {
                    $policyUpdates['insurance_category'] = $category;
                }
                if (empty($currentPolicy->plate_number) && ! empty($plateNumber)) {
                    $policyUpdates['plate_number'] = $plateNumber;
                }
                if (empty($currentPolicy->vehicle_model) && ! empty($vehicleModel)) {
                    $policyUpdates['vehicle_model'] = $vehicleModel;
                }
                if (empty($currentPolicy->profile_title) && $profileTitle !== '') {
                    $policyUpdates['profile_title'] = $profileTitle;
                }
                if ((int) $currentPolicy->total_amount <= 0 && $totalAmount > 0) {
                    $policyUpdates['total_amount'] = $totalAmount;
                }
                if ((int) $currentPolicy->initial_payment <= 0 && $initialCashPayment > 0) {
                    $policyUpdates['initial_payment'] = $initialCashPayment;
                    $policyUpdates['down_payment'] = $initialCashPayment;
                }
                if (empty($currentPolicy->started_at_jalali) && $startedJalali) {
                    $policyUpdates['started_at_jalali'] = $startedJalali;
                    $policyUpdates['started_at'] = $this->convertToGregorian($startedJalali);
                }
                if (empty($currentPolicy->expires_at_jalali) && $expiresJalali) {
                    $policyUpdates['expires_at_jalali'] = $expiresJalali;
                    $policyUpdates['expires_at'] = $this->convertToGregorian($expiresJalali);
                }
                if (empty($currentPolicy->issued_at_jalali) && $issuedJalali) {
                    $policyUpdates['issued_at_jalali'] = $issuedJalali;
                    $policyUpdates['issued_at'] = $this->convertToGregorian($issuedJalali);
                }
                if (empty($currentPolicy->description) && $prevPolicyNumber !== '' && $prevPolicyNumber !== '--') {
                    $policyUpdates['description'] = "بیمه‌نامه قبلی: {$prevPolicyNumber}";
                }

                $policyUpdates['payment_type'] = $paymentType;
                $policyUpdates['installments_count'] = $installmentsCount;

                if (! empty($policyUpdates)) {
                    $currentPolicy->update($policyUpdates);
                }
            }

            $this->updatedPolicies++;

            // گام ۳: مدیریت اقساط بیمه‌نامه
            if ($isFullCash) {
                // برای بیمه‌نامه‌های نقد کامل (مثل کاربر شماره ۶)، هیچ قسطی ساخته نمی‌شود.
                // اگر از قبل قسط پرداخت‌نشده اشتباهی وجود داشته باشد پاک می‌شود، اما اقساط paid هرگز دستکاری نمی‌شوند.
                $currentPolicy->installments()
                    ->where('status', '!=', 'paid')
                    ->where(function ($q) {
                        $q->whereNull('documented_amount')->orWhere('documented_amount', '<=', 0);
                    })
                    ->delete();
            } else {
                foreach ($validInstRows as $idx => $instRow) {
                    $instNumber = $idx + 1;
                    $amount = $this->cleanInt($this->val($instRow, ['مبلغ', 'مبلغ قسط', 'amount']));
                    $dueDateJalali = $this->normalizeJalaliDateString($this->val($instRow, ['تاریخ', 'سررسید', 'due_date']));
                    $dueDateGregorian = $this->convertToGregorian($dueDateJalali);
                    $paymentIdentifier = $this->cleanDigitsOnly($this->val($instRow, ['شناسه پرداخت', 'payment_identifier']));

                    // جستجوی قسط موجود بر اساس شناسه پرداخت یا شماره قسط
                    $existingInst = null;
                    if ($paymentIdentifier !== '') {
                        $existingInst = Installment::withTrashed()
                            ->where('policy_id', $currentPolicy->id)
                            ->where('payment_identifier', $paymentIdentifier)
                            ->first();
                    }
                    if (! $existingInst) {
                        $existingInst = Installment::withTrashed()
                            ->where('policy_id', $currentPolicy->id)
                            ->where('installment_number', $instNumber)
                            ->first();
                    }

                    // قانون طلایی: اگر قسط قبلاً تسویه شده (paid) یا دارای مبلغ مستند است، مطلقاً دستکاری نشود!
                    if ($existingInst && $this->isInstallmentLockedAsPaid($existingInst)) {
                        if ($existingInst->trashed()) {
                            $existingInst->restore();
                        }
                        continue;
                    }

                    $status = 'pending';
                    if ($dueDateGregorian && $dueDateGregorian < now('Asia/Tehran')->toDateString()) {
                        $status = 'overdue';
                    }

                    if ($existingInst) {
                        if ($existingInst->trashed()) {
                            $existingInst->restore();
                        }
                        // اگر در وضعیت در انتظار تایید ادمین بود، وضعیت آن حفظ شود
                        $finalStatus = $existingInst->status === 'awaiting_approval' ? 'awaiting_approval' : $status;

                        $existingInst->update([
                            'installment_number' => $instNumber,
                            'amount'             => $amount,
                            'payment_identifier' => $paymentIdentifier !== '' ? $paymentIdentifier : $existingInst->payment_identifier,
                            'profile_title'      => $profileTitle ?: $existingInst->profile_title,
                            'due_date_jalali'    => $dueDateJalali ?: $existingInst->due_date_jalali,
                            'due_date'           => $dueDateGregorian ?? $existingInst->due_date ?? now('Asia/Tehran')->toDateString(),
                            'status'             => $finalStatus,
                            'payment_type'       => 'قسط',
                        ]);
                    } else {
                        Installment::create([
                            'policy_id'          => $currentPolicy->id,
                            'installment_number' => $instNumber,
                            'amount'             => $amount,
                            'paid_amount'        => 0,
                            'payment_identifier' => $paymentIdentifier !== '' ? $paymentIdentifier : null,
                            'profile_title'      => $profileTitle ?: null,
                            'due_date_jalali'    => $dueDateJalali,
                            'due_date'           => $dueDateGregorian ?? now('Asia/Tehran')->toDateString(),
                            'status'             => $status,
                            'payment_type'       => 'قسط',
                        ]);
                    }

                    $this->updatedInstallments++;
                }

                // بروزرسانی مجموع پرداخت‌شده روی بیمه‌نامه
                $paidInstSum = (int) $currentPolicy->installments()->where('status', 'paid')->sum('amount');
                $currentPolicy->update([
                    'paid_amount'        => $initialCashPayment + $paidInstSum,
                    'installments_count' => $currentPolicy->installments()->count(),
                ]);
            }
        }
    }

    /**
     * پردازش هوشمند فایل مشخصات بیمه‌نامه و خودرو (مانند «بیمه.xls»)
     * تکمیل اطلاعات خودرو، پلاک، سال ساخت و عدم خسارت بدون تخریب اطلاعات مالی قبلی
     */
    private function processFlatPolicies(array $rows, array $headers): void
    {
        foreach ($rows as $row) {
            $rowData = $this->combineRowWithHeaders($headers, $row);
            if ($this->isSummaryOrEmptyRow($rowData)) {
                continue;
            }

            $policyNumber = $this->val($rowData, ['شماره بیمه نامه', 'شماره بیمه‌نامه', 'policy_number']);
            $rowIndexVal = $this->val($rowData, ['ردیف', 'row']);

            if (! $this->isValidPolicyNumber($policyNumber, $rowIndexVal)) {
                continue;
            }

            $user = $this->findOrCreateUserPreservingExisting($rowData);
            $insurerId = $this->resolveInsurer($this->val($rowData, ['بیمه گر', 'شرکت بیمه']) ?: 'بیمه ایران');

            [$category, $vehicle, $title] = $this->extractCategoryAndVehicle($rowData);

            $plateNumber = $this->val($rowData, ['شماره انتظامی', 'پلاک', 'plate_number']) ?: null;
            $vehicleModel = $this->cleanDigitsOnly($this->val($rowData, ['مدل خودرو', 'سال ساخت'])) ?: null;
            $noClaimYears = $this->cleanDigitsOnly($this->val($rowData, ['سال های عدم خسارت', 'عدم خسارت'])) ?: null;
            $driverNoClaim = $this->cleanDigitsOnly($this->val($rowData, ['سال های عدم خسارت حوادث راننده', 'عدم خسارت راننده'])) ?: null;
            $expiresJalali = $this->normalizeJalaliDateString($this->val($rowData, ['تاریخ انقضا', 'انقضا', 'expires_at']));
            $expiresGregorian = $this->convertToGregorian($expiresJalali);

            $policy = Policy::withTrashed()->where('policy_number', $policyNumber)->first();

            if (! $policy) {
                $policy = Policy::create([
                    'user_id'               => $user->id,
                    'insurer_id'            => $insurerId,
                    'policy_number'         => $policyNumber,
                    'title'                 => $title,
                    'insurance_category'    => $category,
                    'vehicle_name'          => $vehicle !== '' ? $vehicle : null,
                    'plate_number'          => $plateNumber,
                    'vehicle_model'         => $vehicleModel,
                    'no_claim_years'        => $noClaimYears,
                    'driver_no_claim_years' => $driverNoClaim,
                    'expires_at_jalali'     => $expiresJalali,
                    'expires_at'            => $expiresGregorian,
                ]);
            } else {
                if ($policy->trashed()) {
                    $policy->restore();
                }

                // حفظ اطلاعات قبلی و فقط تکمیل فیلدهای خالی یا ناقص
                $updates = [];
                if (
                    (empty($policy->title) || str_starts_with((string) $policy->title, 'ثالث - بیمه‌نامه')) &&
                    $title !== ''
                ) {
                    $updates['title'] = $title;
                }
                if (empty($policy->insurance_category) && $category !== '') {
                    $updates['insurance_category'] = $category;
                }
                if (empty($policy->vehicle_name) && $vehicle !== '') {
                    $updates['vehicle_name'] = $vehicle;
                }
                if (empty($policy->plate_number) && $plateNumber !== null) {
                    $updates['plate_number'] = $plateNumber;
                }
                if (empty($policy->vehicle_model) && $vehicleModel !== null) {
                    $updates['vehicle_model'] = $vehicleModel;
                }
                if (empty($policy->no_claim_years) && $noClaimYears !== null) {
                    $updates['no_claim_years'] = $noClaimYears;
                }
                if (empty($policy->driver_no_claim_years) && $driverNoClaim !== null) {
                    $updates['driver_no_claim_years'] = $driverNoClaim;
                }
                if (empty($policy->expires_at_jalali) && $expiresJalali !== null) {
                    $updates['expires_at_jalali'] = $expiresJalali;
                    $updates['expires_at'] = $expiresGregorian;
                }

                if (! empty($updates)) {
                    $policy->update($updates);
                }
            }

            // اگر این بیمه‌نامه، «بیمه‌نامه قبلی» یک بیمه‌نامه جدیدتر از همین بیمه‌گذار باشد که هنوز پلاک ندارد، پلاک و مدل خودرو را به بیمه‌نامه جدید هم لینک کن!
            if ($plateNumber !== null) {
                Policy::where('user_id', $user->id)
                    ->whereNull('plate_number')
                    ->where(function ($q) use ($policyNumber, $vehicle) {
                        $q->where('description', 'like', "%{$policyNumber}%");
                        if ($vehicle !== '') {
                            $q->orWhere('vehicle_name', $vehicle);
                        }
                    })
                    ->update(array_filter([
                        'plate_number'          => $plateNumber,
                        'vehicle_model'         => $vehicleModel,
                        'no_claim_years'        => $noClaimYears,
                        'driver_no_claim_years' => $driverNoClaim,
                    ]));
            }

            $this->updatedPolicies++;
        }
    }

    /**
     * پردازش هوشمند فایل اقساط (مانند «اقساط.xls»)
     * - عدم دستکاری رکوردهایی که قبلاً تسویه (paid) شده‌اند
     * - بروزرسانی اقساط پرداخت‌نشده یا ثبت اقساط جدید
     */
    private function processFlatInstallments(array $rows, array $headers): void
    {
        $touchedPolicyIds = [];

        foreach ($rows as $row) {
            $rowData = $this->combineRowWithHeaders($headers, $row);
            if ($this->isSummaryOrEmptyRow($rowData)) {
                continue;
            }

            $policyNumber = $this->val($rowData, ['شماره بیمه نامه', 'شماره بیمه‌نامه', 'policy_number']);
            $rowIndexVal = $this->val($rowData, ['ردیف', 'row']);

            if (! $this->isValidPolicyNumber($policyNumber, $rowIndexVal)) {
                continue;
            }

            $user = $this->findOrCreateUserPreservingExisting($rowData);
            $insurerId = $this->resolveInsurer($this->val($rowData, ['بیمه گر', 'شرکت بیمه']) ?: 'بیمه ایران');
            $profileTitle = $this->val($rowData, ['پروفایل', 'profile_title']) ?: $user->profile_title;

            $policy = Policy::withTrashed()->where('policy_number', $policyNumber)->first();
            if (! $policy) {
                $policy = Policy::create([
                    'user_id'            => $user->id,
                    'insurer_id'         => $insurerId,
                    'policy_number'      => $policyNumber,
                    'title'              => 'ثالث - بیمه‌نامه ' . $policyNumber,
                    'insurance_category' => 'ثالث',
                    'profile_title'      => $profileTitle ?: null,
                    'payment_type'       => 'installment',
                    'total_amount'       => 0,
                ]);
                $this->updatedPolicies++;
            } elseif ($policy->trashed()) {
                $policy->restore();
            }

            $amount = $this->cleanInt($this->val($rowData, ['مبلغ قسط', 'مبلغ', 'amount']));
            if ($amount <= 0) {
                continue;
            }

            $documentedAmount = $this->cleanInt($this->val($rowData, ['مبلغ مستند', 'مبلغ وصولی', 'documented_amount']));
            $documentedDate = $this->normalizeJalaliDateString($this->val($rowData, ['تاریخ مستند', 'تاریخ وصول', 'documented_date']));
            $dueDateJalali = $this->normalizeJalaliDateString($this->val($rowData, ['سررسید', 'تاریخ سررسید', 'تاریخ', 'due_date']));
            $dueDateGregorian = $this->convertToGregorian($dueDateJalali);
            $paymentIdentifier = $this->cleanDigitsOnly($this->val($rowData, ['شناسه پرداخت', 'payment_identifier']));

            // تعیین وضعیت جدید بر اساس فایل اکسل
            if ($documentedAmount > 0 || ! empty($documentedDate)) {
                $newStatus = 'paid';
                if ($documentedAmount <= 0) {
                    $documentedAmount = $amount;
                }
            } elseif ($dueDateGregorian && $dueDateGregorian < now('Asia/Tehran')->toDateString()) {
                $newStatus = 'overdue';
            } else {
                $newStatus = 'pending';
            }

            // جستجوی دقیق قسط در دیتابیس:
            // اولویت ۱: شناسه پرداخت یکتا
            // اولویت ۲: تاریخ سررسید + مبلغ قسط
            // اولویت ۳: تاریخ سررسید در همان بیمه‌نامه
            $installment = null;
            if ($paymentIdentifier !== '') {
                $installment = Installment::withTrashed()
                    ->where('policy_id', $policy->id)
                    ->where('payment_identifier', $paymentIdentifier)
                    ->first();
            }
            if (! $installment && $dueDateJalali !== null) {
                $installment = Installment::withTrashed()
                    ->where('policy_id', $policy->id)
                    ->where('due_date_jalali', $dueDateJalali)
                    ->where('amount', $amount)
                    ->first();
            }
            if (! $installment && $dueDateJalali !== null) {
                $installment = Installment::withTrashed()
                    ->where('policy_id', $policy->id)
                    ->where('due_date_jalali', $dueDateJalali)
                    ->first();
            }

            if ($installment) {
                if ($installment->trashed()) {
                    $installment->restore();
                }

                // قانون طلایی: اگر این قسط قبلاً در سامانه «تسویه شده (paid)» یا دارای مبلغ مستند بوده است، مطلقاً دستکاری نشود!
                if ($this->isInstallmentLockedAsPaid($installment)) {
                    continue;
                }

                $finalStatus = ($installment->status === 'awaiting_approval' && $newStatus !== 'paid')
                    ? 'awaiting_approval'
                    : $newStatus;

                $installment->update([
                    'amount'             => $amount,
                    'documented_amount'  => $documentedAmount > 0 ? $documentedAmount : $installment->documented_amount,
                    'documented_date'    => $documentedDate ?? $installment->documented_date,
                    'payment_identifier' => $paymentIdentifier !== '' ? $paymentIdentifier : $installment->payment_identifier,
                    'profile_title'      => $profileTitle ?: $installment->profile_title,
                    'due_date_jalali'    => $dueDateJalali ?? $installment->due_date_jalali,
                    'due_date'           => $dueDateGregorian ?? $installment->due_date ?? now('Asia/Tehran')->toDateString(),
                    'status'             => $finalStatus,
                    'paid_amount'        => $finalStatus === 'paid' ? $documentedAmount : $installment->paid_amount,
                    'paid_at'            => $finalStatus === 'paid' ? ($this->convertToGregorian($documentedDate) ?? now()) : $installment->paid_at,
                ]);
            } else {
                $lastNumber = (int) (Installment::where('policy_id', $policy->id)->max('installment_number') ?? 0);

                Installment::create([
                    'policy_id'          => $policy->id,
                    'installment_number' => $lastNumber + 1,
                    'amount'             => $amount,
                    'documented_amount'  => $documentedAmount > 0 ? $documentedAmount : null,
                    'documented_date'    => $documentedDate,
                    'payment_identifier' => $paymentIdentifier !== '' ? $paymentIdentifier : null,
                    'profile_title'      => $profileTitle ?: null,
                    'due_date_jalali'    => $dueDateJalali,
                    'due_date'           => $dueDateGregorian ?? now('Asia/Tehran')->toDateString(),
                    'status'             => $newStatus,
                    'paid_amount'        => $newStatus === 'paid' ? $documentedAmount : 0,
                    'paid_at'            => $newStatus === 'paid' ? ($this->convertToGregorian($documentedDate) ?? now()) : null,
                    'payment_type'       => 'قسط',
                ]);
            }

            $touchedPolicyIds[$policy->id] = true;
            $this->updatedInstallments++;
        }

        // بروزرسانی خلاصه آماری بیمه‌نامه‌هایی که قسط آن‌ها تغییر کرده یا اضافه شده است
        foreach (array_keys($touchedPolicyIds) as $pid) {
            $p = Policy::find($pid);
            if (! $p) {
                continue;
            }
            $instCount = $p->installments()->count();
            $paidInstSum = (int) $p->installments()->where('status', 'paid')->sum('amount');
            $allInstSum = (int) $p->installments()->sum('amount');

            $pUpdates = [
                'installments_count' => $instCount,
                'paid_amount'        => (int) $p->initial_payment + $paidInstSum,
            ];
            if ((int) $p->total_amount <= 0 && $allInstSum > 0) {
                $pUpdates['total_amount'] = (int) $p->initial_payment + $allInstSum;
            }
            $p->update($pUpdates);
        }
    }

    /**
     * پشتیبانی از فایل‌های CSV عمومی
     */
    private function processGenericCsv(array $rows, array $headers): void
    {
        foreach ($rows as $row) {
            $rowData = $this->combineRowWithHeaders($headers, $row);
            if ($this->isSummaryOrEmptyRow($rowData)) {
                continue;
            }

            $policyNumber = $this->val($rowData, ['policy_number', 'شماره بیمه نامه']);
            if ($policyNumber === '') {
                continue;
            }

            $user = $this->findOrCreateUserPreservingExisting($rowData);
            $insurerId = $this->resolveInsurer($this->val($rowData, ['insurer', 'شرکت بیمه']) ?: 'بیمه ایران');
            $totalAmount = $this->cleanInt($this->val($rowData, ['total_amount', 'مبلغ کل']));
            $instCount = $this->cleanInt($this->val($rowData, ['installments_count', 'تعداد اقساط']));
            $title = $this->val($rowData, ['title', 'عنوان']) ?: ('بیمه ثالث - ' . $policyNumber);

            $policy = Policy::firstOrCreate(
                ['policy_number' => $policyNumber],
                [
                    'user_id'            => $user->id,
                    'insurer_id'         => $insurerId,
                    'title'              => $title,
                    'insurance_category' => 'ثالث',
                    'total_amount'       => $totalAmount,
                    'payment_type'       => $instCount > 0 ? 'installment' : 'cash',
                    'installments_count' => $instCount,
                    'issued_at'          => now('Asia/Tehran')->toDateString(),
                    'expires_at'         => now('Asia/Tehran')->addYear()->toDateString(),
                ]
            );
            $this->updatedPolicies++;

            if ($instCount > 0 && $policy->installments()->count() === 0) {
                $perAmount = (int) floor($totalAmount / $instCount);
                for ($i = 1; $i <= $instCount; $i++) {
                    $dueCarbon = now('Asia/Tehran')->addMonths($i);
                    Installment::create([
                        'policy_id'          => $policy->id,
                        'installment_number' => $i,
                        'amount'             => $perAmount,
                        'due_date'           => $dueCarbon->toDateString(),
                        'due_date_jalali'    => Jalalian::fromCarbon($dueCarbon)->format('Y/m/d'),
                        'status'             => 'pending',
                        'payment_type'       => 'قسط',
                    ]);
                    $this->updatedInstallments++;
                }
            }
        }
    }

    /**
     * ایجاد بیمه‌گذار جدید یا تکمیل فیلدهای خالی بیمه‌گذار قبلی (بدون بازنویسی اطلاعات قبلی)
     * همراه با اصلاح خودکار صفر اول موبایل (915... -> 0915...) و صفر اول کد ملی (859... -> 0859...)
     */
    private function findOrCreateUserPreservingExisting(array $rowData): User
    {
        $rawNationalCode = $this->val($rowData, ['کد ملی/شناسه ملی/پاسپورت', 'کد ملی', 'کدملی', 'شناسه ملی', 'national_code']);
        $rawMobile       = $this->val($rowData, ['شماره همراه', 'موبایل', 'تلفن همراه', 'mobile']);
        $rawPhone        = $this->val($rowData, ['شماره تلفن', 'تلفن', 'تلفن ثابت', 'phone']);
        $rawName         = $this->val($rowData, ['نام بیمه گزار', 'نام بیمه گذار', 'نام بیمه‌گذار', 'بیمه گذار', 'name']);
        $rawAddress      = $this->val($rowData, ['آدرس', 'نشانی', 'address']);
        $rawHolderType   = $this->val($rowData, ['نوع بیمه گذار', 'نوع بیمه‌گذار', 'policyholder_type']);
        $rawProfile      = $this->val($rowData, ['پروفایل', 'profile_title']);

        $nationalCode = $this->normalizeIranianNationalCode($rawNationalCode);
        $mobile       = $this->normalizeIranianMobile($rawMobile);
        $phone        = $this->cleanDigitsOnly($rawPhone);
        $name         = PersianNormalizer::normalizeText($rawName);

        $user = null;

        if ($nationalCode !== '') {
            $user = User::withTrashed()->where('national_code', $nationalCode)->first();
        }
        if (! $user && $mobile !== '') {
            $user = User::withTrashed()->where('mobile', $mobile)->first();
        }
        if (! $user && $name !== '') {
            $user = User::withTrashed()->where('name', $name)->first();
        }

        if ($user && $user->trashed()) {
            $user->restore();
        }

        if (! $user) {
            $fallbackMobile = $mobile !== ''
                ? $mobile
                : ($nationalCode !== '' ? $nationalCode : '09' . random_int(100000000, 999999999));

            if (User::withTrashed()->where('mobile', $fallbackMobile)->exists()) {
                $fallbackMobile = '09' . random_int(100000000, 999999999);
            }

            $user = User::create([
                'name'              => $name !== '' ? $name : 'بیمه‌گذار',
                'national_code'     => $nationalCode !== '' ? $nationalCode : null,
                'mobile'            => $fallbackMobile,
                'phone'             => $phone !== '' ? $phone : null,
                'address'           => $rawAddress !== '' ? $rawAddress : null,
                'policyholder_type' => $rawHolderType !== '' ? $rawHolderType : 'حقیقی',
                'profile_title'     => $rawProfile !== '' ? $rawProfile : null,
                'password'          => bcrypt($fallbackMobile),
                'role'              => 'customer',
            ]);
            $this->createdUsers++;
        } else {
            // قانون عدم بازنویسی اطلاعات قبلی: فقط فیلدهایی که در دیتابیس خالی هستند تکمیل شوند
            $updates = [];

            if ((empty($user->name) || $user->name === 'بیمه‌گذار') && $name !== '') {
                $updates['name'] = $name;
            }
            if (empty($user->national_code) && $nationalCode !== '') {
                if (! User::withTrashed()->where('national_code', $nationalCode)->where('id', '!=', $user->id)->exists()) {
                    $updates['national_code'] = $nationalCode;
                }
            }
            // اگر موبایل قبلی کد ملی یا شماره موقت بوده یا بدون صفر اول ثبت شده بود، به موبایل استاندارد اصلاح شود
            if ($mobile !== '' && (empty($user->mobile) || $user->mobile === $user->national_code || strlen((string) $user->mobile) === 10)) {
                if (! User::withTrashed()->where('mobile', $mobile)->where('id', '!=', $user->id)->exists()) {
                    $updates['mobile'] = $mobile;
                }
            }
            if (empty($user->phone) && $phone !== '') {
                $updates['phone'] = $phone;
            }
            if (empty($user->address) && $rawAddress !== '') {
                $updates['address'] = $rawAddress;
            }
            if (empty($user->policyholder_type) && $rawHolderType !== '') {
                $updates['policyholder_type'] = $rawHolderType;
            }
            if (empty($user->profile_title) && $rawProfile !== '') {
                $updates['profile_title'] = $rawProfile;
            }

            if (! empty($updates)) {
                $user->update($updates);
                $this->updatedUsers++;
            }
        }

        return $user;
    }

    /**
     * استخراج هوشمند رشته بیمه (ثالث، بدنه، آتش‌سوزی، عمر، مسئولیت، حوادث و...) و نام خودرو
     */
    private function extractCategoryAndVehicle(array $rowData): array
    {
        $explicitCategory = $this->val($rowData, ['رشته', 'رشته بیمه', 'نوع بیمه', 'موضوع بیمه', 'insurance_category']);

        $combinedVehicle = $this->val($rowData, ['نوع و سیستم مورد بیمه', 'مورد بیمه', 'vehicle_name']);
        if ($combinedVehicle !== '') {
            $combinedVehicle = trim(str_replace('--', '', $combinedVehicle));
            $combinedVehicle = preg_replace('/\s+/u', ' ', $combinedVehicle);
        } else {
            $parts = array_filter([
                $this->val($rowData, ['نوع وسیله']),
                $this->val($rowData, ['نوع سیستم', 'سیستم']),
                $this->val($rowData, ['تیپ']),
            ], fn (string $v): bool => $v !== '' && $v !== '--');

            $combinedVehicle = trim(preg_replace('/\s+/u', ' ', implode(' ', $parts)));
        }

        if ($explicitCategory !== '' && $explicitCategory !== '--') {
            $category = $explicitCategory;
        } else {
            // تشخیص رشته بیمه از روی متن یا شماره بیمه‌نامه
            $checkText = $combinedVehicle . ' ' . $this->val($rowData, ['عنوان', 'title']);
            if (str_contains($checkText, 'بدنه')) {
                $category = 'بدنه';
            } elseif (str_contains($checkText, 'آتش') || str_contains($checkText, 'مسکونی')) {
                $category = 'آتش‌سوزی';
            } elseif (str_contains($checkText, 'مسئولیت')) {
                $category = 'مسئولیت';
            } elseif (str_contains($checkText, 'عمر') || str_contains($checkText, 'زندگی')) {
                $category = 'عمر و سرمایه‌گذاری';
            } elseif (str_contains($checkText, 'درمان')) {
                $category = 'درمان';
            } else {
                $category = 'ثالث';
            }
        }

        $title = $combinedVehicle !== ''
            ? "{$category} - {$combinedVehicle}"
            : $category;

        return [$category, $combinedVehicle, $title];
    }

    /**
     * بررسی اینکه آیا قسط در سامانه تسویه شده است و نباید دستکاری شود
     */
    private function isInstallmentLockedAsPaid(Installment $installment): bool
    {
        return $installment->status === 'paid'
            || ((int) $installment->documented_amount > 0)
            || ((int) $installment->paid_amount > 0 && $installment->status === 'paid');
    }

    /**
     * اصلاح خودکار شماره موبایل (تبدیل 9151371442 به 09151371442 و +98915... به 0915...)
     */
    private function normalizeIranianMobile(?string $raw): string
    {
        $digits = $this->cleanDigitsOnly($raw);
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '00989') && strlen($digits) === 14) {
            $digits = '0' . substr($digits, 4);
        } elseif (str_starts_with($digits, '989') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '9') && strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    /**
     * اصلاح خودکار کد ملی که صفر اول آن در اکسل افتاده است (مثلاً 859840417 -> 0859840417)
     */
    private function normalizeIranianNationalCode(?string $raw): string
    {
        $digits = $this->cleanDigitsOnly($raw);
        if ($digits === '') {
            return '';
        }

        if (strlen($digits) >= 8 && strlen($digits) < 10) {
            $digits = str_pad($digits, 10, '0', STR_PAD_LEFT);
        }

        return $digits;
    }

    private function resolveInsurer(?string $insurerName): ?int
    {
        if (! Schema::hasTable('insurers')) {
            return null;
        }

        $cleanName = PersianNormalizer::normalizeText((string) ($insurerName ?: 'بیمه ایران'));
        if ($cleanName === '' || $cleanName === '--') {
            $cleanName = 'بیمه ایران';
        }

        $attributes = [];
        if (Schema::hasColumn('insurers', 'is_active')) {
            $attributes['is_active'] = true;
        }

        $insurer = Insurer::firstOrCreate(
            ['name' => $cleanName],
            $attributes
        );

        return $insurer->id;
    }

    private function isValidPolicyNumber(?string $policyNumber, ?string $rowIndexValue = null): bool
    {
        if (empty($policyNumber)) {
            return false;
        }
        if (
            str_contains((string) $rowIndexValue, 'جمع') ||
            str_contains((string) $rowIndexValue, 'مجموع') ||
            str_contains($policyNumber, 'جمع') ||
            str_contains($policyNumber, 'مجموع')
        ) {
            return false;
        }

        return str_contains($policyNumber, '/');
    }

    private function isSummaryOrEmptyRow(array $rowData): bool
    {
        $hasData = false;
        foreach ($rowData as $key => $val) {
            $str = trim((string) $val);
            if ($str !== '' && $str !== '--') {
                $hasData = true;
            }
            if (in_array($key, ['ردیف', 'تاریخ صدور', 'شماره بیمه نامه'], true)) {
                if (str_contains($str, 'جمع کل') || $str === 'مجموع' || $str === 'جمع') {
                    return true;
                }
            }
        }

        return ! $hasData;
    }

    private function normalizeJalaliDateString(?string $jalaliDate): ?string
    {
        if ($jalaliDate === null) {
            return null;
        }
        $clean = trim(str_replace('-', '/', PersianNormalizer::normalizeText($jalaliDate)));
        if ($clean === '' || $clean === '--' || ! str_contains($clean, '/')) {
            return null;
        }

        $parts = explode('/', $clean);
        if (count($parts) !== 3 || ! is_numeric($parts[0])) {
            return null;
        }

        return sprintf('%04d/%02d/%02d', (int) $parts[0], (int) $parts[1], (int) $parts[2]);
    }

    private function convertToGregorian(?string $jalaliDate): ?string
    {
        $normalized = $this->normalizeJalaliDateString($jalaliDate);
        if ($normalized === null) {
            return null;
        }

        try {
            $parts = explode('/', $normalized);
            return (new Jalalian((int) $parts[0], (int) $parts[1], (int) $parts[2]))
                ->toCarbon()
                ->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function normalizeHeaderKey(string $header): string
    {
        $norm = PersianNormalizer::normalizeText($header);
        $norm = str_replace(["\u{200C}", "\r", "\n", "\t"], ' ', $norm);
        $norm = preg_replace('/\s+/u', ' ', trim($norm));

        return mb_strtolower((string) $norm);
    }

    private function combineRowWithHeaders(array $headers, array $row): array
    {
        $combined = [];
        foreach ($headers as $index => $header) {
            if ($header !== '') {
                $combined[$header] = $row[$index] ?? null;
            }
        }

        return $combined;
    }

    private function val(array $rowData, array $candidateKeys): string
    {
        foreach ($candidateKeys as $key) {
            $normKey = $this->normalizeHeaderKey($key);
            if (array_key_exists($normKey, $rowData) && $rowData[$normKey] !== null) {
                $v = trim(PersianNormalizer::normalizeText((string) $rowData[$normKey]));
                $v = preg_replace('/\s+/u', ' ', $v);
                if ($v !== '') {
                    return (string) $v;
                }
            }
        }

        return '';
    }

    private function cleanDigitsOnly(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $norm = PersianNormalizer::normalizeText($value);
        // اگر رشته به صورت 859840417.0 بود بخش اعشاری صفر آن حذف شود
        if (preg_match('/^(\d+)\.0+$/', trim($norm), $m)) {
            return $m[1];
        }

        return preg_replace('/[^0-9]/', '', $norm) ?? '';
    }

    private function cleanInt(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }
        $digits = $this->cleanDigitsOnly((string) $value);

        return $digits !== '' ? (int) $digits : 0;
    }
}