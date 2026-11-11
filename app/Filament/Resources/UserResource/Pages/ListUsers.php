<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\ManageAdminPermissions;
use App\Filament\Resources\UserResource;
use App\Services\ExcelImportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage_permissions')
                ->label('مدیریت اختیارات مدیران')
                ->icon('app_admin')
                ->color('gray')
                ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false)
                ->url(fn (): string => ManageAdminPermissions::getUrl()),

            Actions\Action::make('import_excel')
                ->label('آپلود و همگام‌سازی اکسل')
                ->icon('app_upload_file')
                ->color('info')
                ->visible(fn (): bool => auth()->user()?->isAdmin() || (auth()->user()?->hasPermission('import_excel') ?? false))
                ->form([
                    Forms\Components\FileUpload::make('excel_file')
                        ->label('فایل اکسل گزارش صدور، بیمه‌نامه‌ها یا اقساط (.xls / .xlsx / .csv)')
                        ->disk('local')
                        ->directory('imports')
                        ->required(),
                ])
                ->action(function (array $data, ExcelImportService $importService): void {
                    $filePath = (string) ($data['excel_file'] ?? '');
                    $fullPath = Storage::disk('local')->path($filePath);

                    if (empty($filePath) || file_exists($fullPath) === false) {
                        Notification::make()
                            ->title('فایل آپلودشده یافت نشد.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $result = $importService->import($fullPath);
                        @unlink($fullPath);

                        Notification::make()
                            ->title('همگام‌سازی اکسل با موفقیت انجام شد')
                            ->body("بیمه‌گذاران جدید: {$result['new_users_count']} | تکمیل‌شده: {$result['updated_users_count']} | بیمه‌نامه‌ها: {$result['policies_processed']} | اقساط: {$result['installments_processed']}")
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('خطا در پردازش فایل اکسل')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make()
                ->label('افزودن بیمه‌گذار جدید')
                ->icon('heroicon-o-plus'),
        ];
    }
}