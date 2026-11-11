<?php

declare(strict_types=1);

namespace App\Filament\Resources\PolicyResource\Pages;

use App\Casts\JalaliDateCast;
use App\Filament\Resources\PolicyResource;
use App\Support\PersianNormalizer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPolicy extends EditRecord
{
    protected static string $resource = PolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['issued_at_jalali'] ?? null)) {
            $issuedJalali = PersianNormalizer::normalizeText((string) $data['issued_at_jalali']);
            $data['issued_at_jalali'] = $issuedJalali;
            $data['issued_at'] = JalaliDateCast::toGregorian($issuedJalali);
        }

        if (filled($data['expires_at_jalali'] ?? null)) {
            $expiresJalali = PersianNormalizer::normalizeText((string) $data['expires_at_jalali']);
            $data['expires_at_jalali'] = $expiresJalali;
            $data['expires_at'] = JalaliDateCast::toGregorian($expiresJalali);
        }

        return $data;
    }
}