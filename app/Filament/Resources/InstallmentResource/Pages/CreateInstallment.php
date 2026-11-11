<?php

declare(strict_types=1);

namespace App\Filament\Resources\InstallmentResource\Pages;

use App\Casts\JalaliDateCast;
use App\Filament\Resources\InstallmentResource;
use App\Support\PersianNormalizer;
use Filament\Resources\Pages\CreateRecord;

class CreateInstallment extends CreateRecord
{
    protected static string $resource = InstallmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $dueJalali = PersianNormalizer::normalizeText((string) ($data['due_date_jalali'] ?? ''));
        $data['due_date_jalali'] = $dueJalali;
        $data['due_date'] = JalaliDateCast::toGregorian($dueJalali) ?? now('Asia/Tehran')->toDateString();

        return $data;
    }
}