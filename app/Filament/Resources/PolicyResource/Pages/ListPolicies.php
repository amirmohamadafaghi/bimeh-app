<?php

declare(strict_types=1);

namespace App\Filament\Resources\PolicyResource\Pages;

use App\Filament\Resources\PolicyResource;
use App\Services\ExcelImportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListPolicies extends ListRecords
{
    protected static string $resource = PolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('importExcel')
                ->label('ایمپورت هوشمند فایل اکسل')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('excel_file')
                        ->label('انتخاب فایل اکسل (.xls یا .xlsx یا .csv با هر نام و ساختاری)')
                        ->disk('local')
                        ->directory('imports')
                        ->required(),
                ])
                ->action(function (array $data, ExcelImportService $importService): void {
                    @set_time_limit(0);
                    @ini_set('memory_limit', '512M');

                    $relativePath = (string) ($data['excel_file'] ?? '');
                    $fullPath = Storage::disk('local')->path($relativePath);

                    try {
                        $stats = $importService->import($fullPath);

                        Notification::make()
                            ->title('ایمپورت اکسل با موفقیت انجام شد.')
                            ->body(sprintf(
                                'کاربران: %d | بیمه‌نامه‌ها: %d | اقساط: %d',
                                $stats['users_processed'] ?? 0,
                                $stats['policies_processed'] ?? 0,
                                $stats['installments_processed'] ?? 0
                            ))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('خطا در پردازش فایل اکسل')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if ($relativePath !== '' && Storage::disk('local')->exists($relativePath)) {
                            Storage::disk('local')->delete($relativePath);
                        }
                    }
                }),
        ];
    }
}