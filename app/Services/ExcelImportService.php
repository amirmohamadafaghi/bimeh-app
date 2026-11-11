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
use Illuminate\Support\Facades\Hash;
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
                    if (stripos($formatted, 'E') === false) {
                        if (is_float($rawVal) && floor($rawVal) == $rawVal) {
                            $strVal = number_format($rawVal, 0, '', '');
                        } else {
                            $strVal = (string) $rawVal;
                        }
                    } else {
                        $strVal = number_format((float) $rawVal, 0, '', '');
                    }
                } else {
                    $strVal = trim((string) $rawVal);
                }

                if ($strVal === '') {
                    // سلول خالی است
                } else {
                    $hasAnyValue = true;
                }
                $rowCells[] = $strVal;
            }

            if ($hasAnyValue === true) {
                $matrix[] = $rowCells;
            }
        }

        return $matrix;
    }

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
                    $normKw = $this->normalizeHeaderKey($kw);
                    if ($colName === $normKw || str_contains($colName, $normKw)) {
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

        if (
            ($hasColumn(['مبلغ پرداختی']) && $hasColumn(['نوع']) && $hasColumn(['مبلغ'])) ||
            $hasColumn(['شماره بیمه نامه قبلی', 'بیمه گر قبلی', 'نوع وسیله'])
        ) {
            return 'issuance_report';
        }

        if ($hasColumn(['مبلغ قسط', 'مبلغ مستند', 'تاریخ مستند', 'سررسید'])) {
            return 'installments_flat';
        }

        if ($hasColumn(['شماره انتظامی', 'نوع و سیستم مورد بیمه', 'سال های عدم خسارت', 'مدل خودرو'])) {
            return 'policies_flat';
        }

        if ($hasColumn(['policy_number', 'installments_count', 'total_amount'])) {
            return 'generic_csv';
        }

        if ($hasColumn(['شماره بیمه نامه', 'شماره بیمه‌نامه'])) {
            return 'policies_flat';
        }

        return 'unknown';
    }

    private function processIssuanceReport(array $rows, array $headers): void
    {
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
                if ($currentBlock === null) {
                    // اولین بلوک بیمه‌نامه
                } else {
                    $policyBlocks[] = $currentBlock;
                }
                $currentBlock = [
                    'main'         => $rowData,
                    'installments' => [],
                ];
            } elseif (is_array($currentBlock)) {
                $amount = $this->cleanInt($this->val($rowData, ['مبلغ', 'مبلغ قسط', 'amount']));
                if ($paymentRowType === 'قسط' || ($paymentRowType === '' && $amount > 0 && $policyNumber === '')) {
                    $currentBlock['installments'][] = $rowData;
                } elseif ($paymentRowType === 'نقد' && $amount > 0) {
                    $currentBlock['extra_cash'][] = $amount;
                }
            }
        }

        if (is_array($currentBlock)) {
            $policyBlocks[] = $currentBlock;
        }

        foreach ($policyBlocks as $block) {
            $main = $block['main'];
            $instRows = $block['installments'];

            $policyNumber = $this->val($main, ['شماره بیمه نامه', 'شماره بیمه‌نامه', 'policy_number']);
            $user = $this->findOrCreateUserPreservingExisting($main);
            $insurerId = $this->resolveInsurer($this->val($main, ['بیمه گر', 'شرکت بیمه', 'بیمه گر قبلی']) ?: 'بیمه ایران');

            [$category, $vehicle, $title] = $this->extractCategoryAndVehicle($main);

            $totalAmount = $this->cleanInt($this->val($main, ['مبلغ پرداختی', 'حق بیمه کل', 'مبلغ کل', 'total_amount']));
            $mainRowType = $this->val($main, ['نوع', 'نوع پرداخت']);
            $mainRowAmount = $this->cleanInt($this->val($main, ['مبلغ', 'پیش پرداخت']));
            $extraCash = array_sum($block['extra_cash'] ?? []);

            $initialCashPayment = 0;
            if ($mainRowType === 'نقد' && $mainRowAmount > 0) {
                $initialCashPayment = $mainRowAmount + $extraCash;
            } elseif ($mainRowType === 'قسط' && $mainRowAmount > 0) {
                array_unshift($instRows, $main);
                $initialCashPayment = $extraCash;
            }

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

            $isFullCash = ($installmentsCount === 0) || ($initialCashPayment >= $totalAmount && $totalAmount > 0 && $installmentsCount === 0);
            $paymentType = $isFullCash ? 'cash' : 'installment';

            if ($isFullCash && $initialCashPayment <= 0 && $totalAmount > 0) {
                $initialCashPayment = $totalAmount;
            }

            $prevPolicyNumber = $this->val($main, ['شماره بیمه نامه قبلی', 'بیمه نامه قبلی']);
            $inheritedPlate = null;
            $inheritedModel = null;
            $inheritedNoClaim = null;
            $inheritedDriverNoClaim = null;

            if ($prevPolicyNumber === '' || $prevPolicyNumber === '--') {
                // بیمه‌نامه قبلی ثبت نشده است
            } else {
                $prevPolicy = Policy::withTrashed()
                    ->where('policy_number', $prevPolicyNumber)
                    ->first();
                if ($prevPolicy instanceof Policy) {
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

            $hasPrevPolicyDesc = ($prevPolicyNumber === '' || $prevPolicyNumber === '--') ? false : true;

            $currentPolicy = Policy::withTrashed()->where('policy_number', $policyNumber)->first();

            if ($currentPolicy instanceof Policy) {
                if ($currentPolicy->trashed()) {
                    $currentPolicy->restore();
                }

                $policyUpdates = [];

                if (empty($currentPolicy->vehicle_name) && strlen($vehicle) > 0) {
                    $policyUpdates['vehicle_name'] = $vehicle;
                }
                if (
                    (empty($currentPolicy->title) || str_starts_with((string) $currentPolicy->title, 'ثالث - بیمه‌نامه')) &&
                    strlen($title) > 0
                ) {
                    $policyUpdates['title'] = $title;
                }
                if (empty($currentPolicy->insurance_category) && strlen($category) > 0) {
                    $policyUpdates['insurance_category'] = $category;
                }
                if (empty($currentPolicy->plate_number) && is_string($plateNumber) && strlen($plateNumber) > 0) {
                    $policyUpdates['plate_number'] = $plateNumber;
                }
                if (empty($currentPolicy->vehicle_model) && is_string($vehicleModel) && strlen($vehicleModel) > 0) {
                    $policyUpdates['vehicle_model'] = $vehicleModel;
                }
                if (empty($currentPolicy->profile_title) && is_string($profileTitle) && strlen($profileTitle) > 0) {
                    $policyUpdates['profile_title'] = $profileTitle;
                }
                if ((int) $currentPolicy->total_amount <= 0 && $totalAmount > 0) {
                    $policyUpdates['total_amount'] = $totalAmount;
                }
                if ((int) $currentPolicy->initial_payment <= 0 && $initialCashPayment > 0) {
                    $policyUpdates['initial_payment'] = $initialCashPayment;
                    $policyUpdates['down_payment'] = $initialCashPayment;
                }
                if (empty($currentPolicy->started_at_jalali) && is_string($startedJalali)) {
                    $policyUpdates['started_at_jalali'] = $startedJalali;
                    $policyUpdates['started_at'] = $this->convertToGregorian($startedJalali);
                }
                if (empty($currentPolicy->expires_at_jalali) && is_string($expiresJalali)) {
                    $policyUpdates['expires_at_jalali'] = $expiresJalali;
                    $policyUpdates['expires_at'] = $this->convertToGregorian($expiresJalali);
                }
                if (empty($currentPolicy->issued_at_jalali) && is_string($issuedJalali)) {
                    $policyUpdates['issued_at_jalali'] = $issuedJalali;
                    $policyUpdates['issued_at'] = $this->convertToGregorian($issuedJalali);
                }
                if (empty($currentPolicy->description) && $hasPrevPolicyDesc === true) {
                    $policyUpdates['description'] = "بیمه‌نامه قبلی: {$prevPolicyNumber}";
                }

                $policyUpdates['payment_type'] = $paymentType;
                $policyUpdates['installments_count'] = $installmentsCount;

                if ($policyUpdates === []) {
                    // بدون تغییر
                } else {
                    $currentPolicy->update($policyUpdates);
                }
            } else {
                $currentPolicy = Policy::create([
                    'user_id'               => $user->id,
                    'insurer_id'            => $insurerId,
                    'policy_number'         => $policyNumber,
                    'title'                 => $title,
                    'insurance_category'    => $category,
                    'policyholder_type'     => $this->val($main, ['نوع بیمه گذار', 'نوع بیمه‌گذار']) ?: ($user->policyholder_type ?: 'حقیقی'),
                    'vehicle_name'          => strlen($vehicle) > 0 ? $vehicle : null,
                    'plate_number'          => $plateNumber,
                    'vehicle_model'         => $vehicleModel,
                    'no_claim_years'        => $inheritedNoClaim,
                    'driver_no_claim_years' => $inheritedDriverNoClaim,
                    'profile_title'         => (is_string($profileTitle) && strlen($profileTitle) > 0) ? $profileTitle : null,
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
                    'description'           => $hasPrevPolicyDesc === true ? "بیمه‌نامه قبلی: {$prevPolicyNumber}" : null,
                ]);
            }

            $this->updatedPolicies++;

            if ($isFullCash) {
                $currentPolicy->installments()
                    ->whereIn('status', ['pending', 'overdue', 'awaiting_approval'])
                    ->where(function ($q): void {
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

                    $existingInst = null;
                    if (strlen($paymentIdentifier) > 0) {
                        $existingInst = Installment::withTrashed()
                            ->where('policy_id', $currentPolicy->id)
                            ->where('payment_identifier', $paymentIdentifier)
                            ->first();
                    }
                    if ($existingInst instanceof Installment) {
                        // با شناسه پرداخت پیدا شد
                    } else {
                        $existingInst = Installment::withTrashed()
                            ->where('policy_id', $currentPolicy->id)
                            ->where('installment_number', $instNumber)
                            ->first();
                    }

                    if ($existingInst instanceof Installment && $this->isInstallmentLockedAsPaid($existingInst)) {
                        if ($existingInst->trashed()) {
                            $existingInst->restore();
                        }
                        continue;
                    }

                    $status = 'pending';
                    if (is_string($dueDateGregorian) && $dueDateGregorian < now('Asia/Tehran')->toDateString()) {
                        $status = 'overdue';
                    }

                    if ($existingInst instanceof Installment) {
                        if ($existingInst->trashed()) {
                            $existingInst->restore();
                        }
                        $finalStatus = $existingInst->status === 'awaiting_approval' ? 'awaiting_approval' : $status;

                        $existingInst->update([
                            'installment_number' => $instNumber,
                            'amount'             => $amount,
                            'payment_identifier' => strlen($paymentIdentifier) > 0 ? $paymentIdentifier : $existingInst->payment_identifier,
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
                            'payment_identifier' => strlen($paymentIdentifier) > 0 ? $paymentIdentifier : null,
                            'profile_title'      => $profileTitle ?: null,
                            'due_date_jalali'    => $dueDateJalali,
                            'due_date'           => $dueDateGregorian ?? now('Asia/Tehran')->toDateString(),
                            'status'             => $status,
                            'payment_type'       => 'قسط',
                        ]);
                    }

                    $this->updatedInstallments++;
                }

                $paidInstSum = (int) $currentPolicy->installments()->where('status', 'paid')->sum('amount');
                $currentPolicy->update([
                    'paid_amount'        => $initialCashPayment + $paidInstSum,
                    'installments_count' => $currentPolicy->installments()->count(),
                ]);
            }
        }
    }

    private function processFlatPolicies(array $rows, array $headers): void
    {
        foreach ($rows as $row) {
            $rowData = $this->combineRowWithHeaders($headers, $row);
            if ($this->isSummaryOrEmptyRow($rowData)) {
                continue;
            }

            $policyNumber = $this->val($rowData, ['شماره بیمه نامه', 'شماره بیمه‌نامه', 'policy_number']);
            $rowIndexVal = $this->val($rowData, ['ردیف', 'row']);

            if ($this->isValidPolicyNumber($policyNumber, $rowIndexVal) === false) {
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

            if ($policy instanceof Policy) {
                if ($policy->trashed()) {
                    $policy->restore();
                }

                $updates = [];
                if (
                    (empty($policy->title) || str_starts_with((string) $policy->title, 'ثالث - بیمه‌نامه')) &&
                    strlen($title) > 0
                ) {
                    $updates['title'] = $title;
                }
                if (empty($policy->insurance_category) && strlen($category) > 0) {
                    $updates['insurance_category'] = $category;
                }
                if (empty($policy->vehicle_name) && strlen($vehicle) > 0) {
                    $updates['vehicle_name'] = $vehicle;
                }
                if (empty($policy->plate_number) && is_string($plateNumber)) {
                    $updates['plate_number'] = $plateNumber;
                }
                if (empty($policy->vehicle_model) && is_string($vehicleModel)) {
                    $updates['vehicle_model'] = $vehicleModel;
                }
                if (empty($policy->no_claim_years) && is_string($noClaimYears)) {
                    $updates['no_claim_years'] = $noClaimYears;
                }
                if (empty($policy->driver_no_claim_years) && is_string($driverNoClaim)) {
                    $updates['driver_no_claim_years'] = $driverNoClaim;
                }
                if (empty($policy->expires_at_jalali) && is_string($expiresJalali)) {
                    $updates['expires_at_jalali'] = $expiresJalali;
                    $updates['expires_at'] = $expiresGregorian;
                }

                if ($updates === []) {
                    // بدون تغییر
                } else {
                    $policy->update($updates);
                }
            } else {
                $policy = Policy::create([
                    'user_id'               => $user->id,
                    'insurer_id'            => $insurerId,
                    'policy_number'         => $policyNumber,
                    'title'                 => $title,
                    'insurance_category'    => $category,
                    'vehicle_name'          => strlen($vehicle) > 0 ? $vehicle : null,
                    'plate_number'          => $plateNumber,
                    'vehicle_model'         => $vehicleModel,
                    'no_claim_years'        => $noClaimYears,
                    'driver_no_claim_years' => $driverNoClaim,
                    'expires_at_jalali'     => $expiresJalali,
                    'expires_at'            => $expiresGregorian,
                ]);
            }

            if (is_string($plateNumber)) {
                Policy::where('user_id', $user->id)
                    ->whereNull('plate_number')
                    ->where(function ($q) use ($policyNumber, $vehicle): void {
                        $q->where('description', 'like', "%{$policyNumber}%");
                        if (strlen($vehicle) > 0) {
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

            if ($this->isValidPolicyNumber($policyNumber, $rowIndexVal) === false) {
                continue;
            }

            $user = $this->findOrCreateUserPreservingExisting($rowData);
            $insurerId = $this->resolveInsurer($this->val($rowData, ['بیمه گر', 'شرکت بیمه']) ?: 'بیمه ایران');
            $profileTitle = $this->val($rowData, ['پروفایل', 'profile_title']) ?: $user->profile_title;

            $policy = Policy::withTrashed()->where('policy_number', $policyNumber)->first();
            if ($policy instanceof Policy) {
                if ($policy->trashed()) {
                    $policy->restore();
                }
            } else {
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

            if ($documentedAmount > 0 || is_string($documentedDate)) {
                $newStatus = 'paid';
                if ($documentedAmount <= 0) {
                    $documentedAmount = $amount;
                }
            } elseif (is_string($dueDateGregorian) && $dueDateGregorian < now('Asia/Tehran')->toDateString()) {
                $newStatus = 'overdue';
            } else {
                $newStatus = 'pending';
            }

            $installment = null;
            if (strlen($paymentIdentifier) > 0) {
                $installment = Installment::withTrashed()
                    ->where('policy_id', $policy->id)
                    ->where('payment_identifier', $paymentIdentifier)
                    ->first();
            }
            if ($installment === null && is_string($dueDateJalali)) {
                $installment = Installment::withTrashed()
                    ->where('policy_id', $policy->id)
                    ->where('due_date_jalali', $dueDateJalali)
                    ->where('amount', $amount)
                    ->first();
            }
            if ($installment === null && is_string($dueDateJalali)) {
                $installment = Installment::withTrashed()
                    ->where('policy_id', $policy->id)
                    ->where('due_date_jalali', $dueDateJalali)
                    ->first();
            }

            if ($installment instanceof Installment) {
                if ($installment->trashed()) {
                    $installment->restore();
                }

                if ($this->isInstallmentLockedAsPaid($installment)) {
                    continue;
                }

                if ($installment->status === 'awaiting_approval' && in_array($newStatus, ['pending', 'overdue'], true)) {
                    $finalStatus = 'awaiting_approval';
                } else {
                    $finalStatus = $newStatus;
                }

                $installment->update([
                    'amount'             => $amount,
                    'documented_amount'  => $documentedAmount > 0 ? $documentedAmount : $installment->documented_amount,
                    'documented_date'    => $documentedDate ?? $installment->documented_date,
                    'payment_identifier' => strlen($paymentIdentifier) > 0 ? $paymentIdentifier : $installment->payment_identifier,
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
                    'payment_identifier' => strlen($paymentIdentifier) > 0 ? $paymentIdentifier : null,
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

        foreach (array_keys($touchedPolicyIds) as $pid) {
            $p = Policy::find($pid);
            if ($p instanceof Policy) {
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
    }

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

        if (strlen($nationalCode) > 0) {
            $user = User::withTrashed()->where('national_code', $nationalCode)->first();
        }
        if ($user === null && strlen($mobile) > 0) {
            $user = User::withTrashed()->where('mobile', $mobile)->first();
        }
        if ($user === null && strlen($name) > 0) {
            $user = User::withTrashed()->where('name', $name)->first();
        }

        if ($user instanceof User && $user->trashed()) {
            $user->restore();
        }

        if ($user instanceof User) {
            $updates = [];

            if ((empty($user->name) || $user->name === 'بیمه‌گذار') && strlen($name) > 0) {
                $updates['name'] = $name;
            }
            if (empty($user->national_code) && strlen($nationalCode) > 0) {
                $duplicateNational = User::withTrashed()
                    ->where('national_code', $nationalCode)
                    ->whereKeyNot($user->id)
                    ->exists();
                if ($duplicateNational === false) {
                    $updates['national_code'] = $nationalCode;
                }
            }
            if (strlen($mobile) > 0 && (empty($user->mobile) || $user->mobile === $user->national_code || strlen((string) $user->mobile) === 10)) {
                $duplicateMobile = User::withTrashed()
                    ->where('mobile', $mobile)
                    ->whereKeyNot($user->id)
                    ->exists();
                if ($duplicateMobile === false) {
                    $updates['mobile'] = $mobile;
                }
            }
            if (empty($user->phone) && strlen($phone) > 0) {
                $updates['phone'] = $phone;
            }
            if (empty($user->address) && strlen($rawAddress) > 0) {
                $updates['address'] = $rawAddress;
            }
            if (empty($user->policyholder_type) && strlen($rawHolderType) > 0) {
                $updates['policyholder_type'] = $rawHolderType;
            }
            if (empty($user->profile_title) && strlen($rawProfile) > 0) {
                $updates['profile_title'] = $rawProfile;
            }

            if ($updates === []) {
                // بدون تغییر
            } else {
                $user->update($updates);
                $this->updatedUsers++;
            }
        } else {
            $fallbackMobile = strlen($mobile) > 0
                ? $mobile
                : (strlen($nationalCode) > 0 ? $nationalCode : '09' . random_int(100000000, 999999999));

            if (User::withTrashed()->where('mobile', $fallbackMobile)->exists()) {
                $fallbackMobile = '09' . random_int(100000000, 999999999);
            }

            $user = User::create([
                'name'              => strlen($name) > 0 ? $name : 'بیمه‌گذار',
                'national_code'     => strlen($nationalCode) > 0 ? $nationalCode : null,
                'mobile'            => $fallbackMobile,
                'phone'             => strlen($phone) > 0 ? $phone : null,
                'address'           => strlen($rawAddress) > 0 ? $rawAddress : null,
                'policyholder_type' => strlen($rawHolderType) > 0 ? $rawHolderType : 'حقیقی',
                'profile_title'     => strlen($rawProfile) > 0 ? $rawProfile : null,
                'password'          => Hash::make($fallbackMobile),
                'role'              => 'customer',
            ]);
            $this->createdUsers++;
        }

        return $user;
    }

    private function extractCategoryAndVehicle(array $rowData): array
    {
        $explicitCategory = $this->val($rowData, ['رشته', 'رشته بیمه', 'نوع بیمه', 'موضوع بیمه', 'insurance_category']);

        $combinedVehicle = $this->val($rowData, ['نوع و سیستم مورد بیمه', 'مورد بیمه', 'vehicle_name']);
        if (strlen($combinedVehicle) > 0) {
            $combinedVehicle = trim(str_replace('--', '', $combinedVehicle));
            $combinedVehicle = (string) preg_replace('/\s+/u', ' ', $combinedVehicle);
        } else {
            $parts = array_filter([
                $this->val($rowData, ['نوع وسیله']),
                $this->val($rowData, ['نوع سیستم', 'سیستم']),
                $this->val($rowData, ['تیپ']),
            ], function (string $v): bool {
                if ($v === '' || $v === '--') {
                    return false;
                }

                return true;
            });

            $combinedVehicle = trim((string) preg_replace('/\s+/u', ' ', implode(' ', $parts)));
        }

        if ($explicitCategory === '' || $explicitCategory === '--') {
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
        } else {
            $category = $explicitCategory;
        }

        $title = strlen($combinedVehicle) > 0
            ? "{$category} - {$combinedVehicle}"
            : $category;

        return [$category, $combinedVehicle, $title];
    }

    private function isInstallmentLockedAsPaid(Installment $installment): bool
    {
        return $installment->status === 'paid'
            || ((int) $installment->documented_amount > 0)
            || ((int) $installment->paid_amount > 0 && $installment->status === 'paid');
    }

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
        if (Schema::hasTable('insurers') === false) {
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
            if ($str === '' || $str === '--') {
                // بدون مقدار معتبر
            } else {
                $hasData = true;
            }
            if (in_array($key, ['ردیف', 'تاریخ صدور', 'شماره بیمه نامه'], true)) {
                if (str_contains($str, 'جمع کل') || $str === 'مجموع' || $str === 'جمع') {
                    return true;
                }
            }
        }

        if ($hasData === true) {
            return false;
        }

        return true;
    }

    private function normalizeJalaliDateString(?string $jalaliDate): ?string
    {
        if ($jalaliDate === null) {
            return null;
        }
        $clean = trim(str_replace('-', '/', PersianNormalizer::normalizeText($jalaliDate)));
        if ($clean === '' || $clean === '--') {
            return null;
        }

        if (str_contains($clean, '/')) {
            $parts = explode('/', $clean);
            if (count($parts) === 3 && is_numeric($parts[0])) {
                return sprintf('%04d/%02d/%02d', (int) $parts[0], (int) $parts[1], (int) $parts[2]);
            }
        }

        return null;
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
            if (strlen($header) > 0) {
                $combined[$header] = $row[$index] ?? null;
            }
        }

        return $combined;
    }

    private function val(array $rowData, array $candidateKeys): string
    {
        foreach ($candidateKeys as $key) {
            $normKey = $this->normalizeHeaderKey($key);
            if (array_key_exists($normKey, $rowData) && isset($rowData[$normKey])) {
                $v = trim(PersianNormalizer::normalizeText((string) $rowData[$normKey]));
                $v = (string) preg_replace('/\s+/u', ' ', $v);
                if (strlen($v) > 0) {
                    return $v;
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

        return strlen($digits) > 0 ? (int) $digits : 0;
    }
}