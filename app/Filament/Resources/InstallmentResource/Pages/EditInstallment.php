<?php

declare(strict_types=1);

namespace App\Filament\Resources\InstallmentResource\Pages;

use App\Casts\JalaliDateCast;
use App\Filament\Resources\InstallmentResource;
use App\Support\PersianNormalizer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstallment extends EditRecord
{
    protected static string $resource = InstallmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $dueJalali = PersianNormalizer::normalizeText((string) ($data['due_date_jalali'] ?? ''));
        if (strlen($dueJalali) > 0) {
            $data['due_date_jalali'] = $dueJalali;
            $data['due_date'] = JalaliDateCast::toGregorian($dueJalali) ?? $this->record->due_date;
        }

        return $data;
    }
}