<?php

declare(strict_types=1);

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

    public function test_paid_installment_is_never_reverted_during_reimport(): void
    {
        $user = User::factory()->create([
            'name'          => 'علی رضایی',
            'national_code' => '0012345678',
            'mobile'        => '09123456789',
        ]);

        $policy = Policy::factory()->create([
            'user_id'       => $user->id,
            'policy_number' => '1405/3116/55555',
        ]);

        $installment = Installment::factory()->create([
            'policy_id'          => $policy->id,
            'installment_number' => 1,
            'amount'             => 15000000,
            'paid_amount'        => 15000000,
            'documented_amount'  => 15000000,
            'due_date_jalali'    => '1405/02/15',
            'status'             => 'paid',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['ردیف', 'شماره بیمه نامه', 'نام بیمه گذار', 'کد ملی', 'شماره همراه', 'سررسید', 'مبلغ قسط', 'مبلغ مستند'],
            ['1', '1405/3116/55555', 'علی رضایی', '0012345678', '09123456789', '1405/02/15', '15000000', '0'],
        ]);

        $tempFile = storage_path('app/test_installments.xlsx');
        (new Xlsx($spreadsheet))->save($tempFile);

        $service = app(ExcelImportService::class);
        $service->import($tempFile);

        @unlink($tempFile);

        $this->assertSame('paid', $installment->fresh()->status);
    }
}