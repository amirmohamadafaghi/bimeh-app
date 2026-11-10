<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\SystemActivity;
use App\Services\ExcelImportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('افزودن بیمه‌گذار جدید')
                ->icon('heroicon-o-user-plus'),

            Actions\Action::make('importExcel')
                ->label('ایمپورت فایل اکسل جامع')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('excel_file')
                        ->label('انتخاب فایل اکسل (.xls یا .xlsx یا .csv)')
                        ->disk('local')
                        ->directory('imports')
                        ->required(),
                ])
                ->action(function (array $data, ExcelImportService $importService): void {
                    // افزایش محدودیت زمان و حافظه برای فایل‌های بسیار حجیم روی cPanel
                    @set_time_limit(0);
                    @ini_set('memory_limit', '512M');

                    $relativePath = (string) ($data['excel_file'] ?? '');
                    $fullPath = Storage::disk('local')->path($relativePath);

                    try {
                        $stats = $importService->import($fullPath);

                        SystemActivity::create([
                            'actor_id'   => auth()->id(),
                            'event_type' => 'excel_imported',
                            'title'      => 'ایمپورت موفق فایل اکسل بیمه‌گذاران و اقساط',
                            'icon'       => 'app_upload_file',
                            'color'      => 'emerald',
                            'details'    => $stats,
                        ]);

                        Notification::make()
                            ->title('ایمپورت اکسل با موفقیت انجام شد.')
                            ->body(sprintf(
                                'کاربران: %d | بیمه‌نامه‌ها: %d | اقساط: %d',
                                $stats['users_processed'] ?? ($stats['users'] ?? 0),
                                $stats['policies_processed'] ?? ($stats['policies'] ?? 0),
                                $stats['installments_processed'] ?? ($stats['installments'] ?? 0)
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
                        // حذف قطعی فایل اکسل از دیسک سرور برای حفظ امنیت اطلاعات مشتریان
                        if ($relativePath !== '' && Storage::disk('local')->exists($relativePath)) {
                            Storage::disk('local')->delete($relativePath);
                        }
                    }
                }),
        ];
    }
}