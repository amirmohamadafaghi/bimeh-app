<?php

namespace Tests\Feature;

use App\Models\Installment;
use App\Models\Policy;
use App\Models\User;
use App\Services\ExcelImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createTempExcelFile(array $rows, string $randomFileName): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($rows);

        $filePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$randomFileName.'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    public function test_imports_hierarchical_issuance_report_and_stores_cash_in_initial_payment(): void
    {
        $rows = [
            ['ردیف', 'شماره بیمه نامه', 'نام بیمه گزار', 'بیمه گر قبلی', 'نوع وسیله', 'نوع سیستم', 'تیپ', 'نوع بیمه گذار', 'کد ملی/شناسه ملی/پاسپورت', 'شماره همراه', 'آدرس', 'تاریخ شروع', 'تاریخ انقضا', 'تاریخ صدور', 'مبلغ پرداختی', 'پروفایل', 'نوع', 'مبلغ', 'شناسه پرداخت', 'تاریخ'],
            [1, '1405/314/60020/1600', 'علی رضایی', 'بیمه ایران', 'سواری', 'پژو', '405', 'حقیقی', '859042642', '9151371442', 'بشرویه خیابان امام', '1405/08/01', '1406/08/01', '1405/08/01', '50,000,000', 'اعتباری 50-50', 'نقد', '20,000,000', '31450000001', '1405/08/01'],
            [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, 'قسط', '15,000,000', '31450000002', '1405/09/01'],
            [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, 'قسط', '15,000,000', '31450000003', '1405/10/01'],
            [null, null, null, null, null, null, null, null, null, null, null, null, null, 'مجموع', '50,000,000', null, null, null, null, null],
        ];

        $filePath = $this->createTempExcelFile($rows, 'arbitrary_name_1');

        $service = app(ExcelImportService::class);
        $result = $service->processFile($filePath);
        @unlink($filePath);

        $this->assertTrue($result['success']);

        // ۱. بررسی ذخیره کاربر با صفر اول کد ملی و موبایل
        $user = User::where('national_code', '0859042642')->first();
        $this->assertNotNull($user);
        $this->assertSame('09151371442', $user->mobile);

        // ۲. بررسی ذخیره مبلغ نقد در initial_payment بیمه‌نامه
        $policy = Policy::where('policy_number', '1405/314/60020/1600')->first();
        $this->assertNotNull($policy);
        $this->assertSame(50000000, $policy->total_amount);
        $this->assertSame(20000000, $policy->initial_payment);

        // ۳. بررسی اینکه اقساط دقیقاً از شماره ۱ برای مابقی بدهی (۲ قسط ۱۵ میلیونی) شروع شده‌اند
        $installments = $policy->installments()->orderBy('installment_number')->get();
        $this->assertCount(2, $installments);
        $this->assertSame(1, $installments[0]->installment_number);
        $this->assertSame(15000000, $installments[0]->amount);
        $this->assertSame(2, $installments[1]->installment_number);
    }

    public function test_imports_flat_policies_and_updates_plate_and_no_claim_years(): void
    {
        $rows = [
            ['ردیف', 'شماره بیمه نامه', 'نام بیمه گزار', 'نوع و سیستم مورد بیمه', 'شماره انتظامی', 'مدل خودرو', 'سال های عدم خسارت', 'سال های عدم خسارت حوادث راننده', 'موبایل', 'تلفن', 'کد ملی', 'آدرس', 'تاریخ انقضا'],
            [1, '1405/314/60020/1600', 'علی رضایی', 'سواری پژو 405', '82ط867 ایران52', '1396', '6', '6', '9151371442', null, '859042642', 'بشرویه بلوار قائم', '1406/08/01'],
        ];

        $filePath = $this->createTempExcelFile($rows, 'random_file_xyz');

        $service = app(ExcelImportService::class);
        $service->processFile($filePath);
        @unlink($filePath);

        $policy = Policy::where('policy_number', '1405/314/60020/1600')->first();
        $this->assertNotNull($policy);
        $this->assertSame('82ط867 ایران52', $policy->plate_number);
        $this->assertSame('1396', (string) $policy->vehicle_model);
        $this->assertSame('6', (string) $policy->no_claim_years);
    }

    public function test_imports_flat_installments_and_marks_documented_rows_as_paid(): void
    {
        $rows = [
            ['ردیف', 'شماره بیمه نامه', 'نام بیمه گذار', 'کد ملی', 'موبایل', 'سررسید', 'مبلغ قسط', 'شناسه پرداخت', 'مبلغ مستند', 'تاریخ مستند', 'پروفایل', 'بازاریاب'],
            [1, '1405/314/60020/1600', 'علی رضایی', '859042642', '9151371442', '1405/09/01', '15,000,000', '31450000002', '15,000,000', '1405/08/29', 'اعتباری 50-50', 'نامشخص'],
            ['جمع کل:', '15,000,000', null, null, null, null, null, null, null, null, null, null],
        ];

        $filePath = $this->createTempExcelFile($rows, 'installments_any_name');

        $service = app(ExcelImportService::class);
        $service->processFile($filePath);
        @unlink($filePath);

        $installment = Installment::first();
        $this->assertNotNull($installment);
        $this->assertSame('paid', $installment->status);
        $this->assertSame(15000000, $installment->documented_amount);
    }
}
