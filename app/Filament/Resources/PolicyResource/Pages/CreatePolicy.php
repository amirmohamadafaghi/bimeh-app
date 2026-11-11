<?php

declare(strict_types=1);

namespace App\Filament\Resources\PolicyResource\Pages;

use App\Casts\JalaliDateCast;
use App\Filament\Resources\PolicyResource;
use App\Support\PersianNormalizer;
use Filament\Resources\Pages\CreateRecord;

class CreatePolicy extends CreateRecord
{
    protected static string $resource = PolicyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['issued_at_jalali'] ?? null)) {
            $issuedJalali = PersianNormalizer::normalizeText((string) $data['issued_at_jalali']);
            $data['issued_at_jalali'] = $issuedJalali;
            $data['issued_at'] = JalaliDateCast::toGregorian($issuedJalali);
            $data['started_at_jalali'] = $issuedJalali;
            $data['started_at'] = $data['issued_at'];
        }

        if (filled($data['expires_at_jalali'] ?? null)) {
            $expiresJalali = PersianNormalizer::normalizeText((string) $data['expires_at_jalali']);
            $data['expires_at_jalali'] = $expiresJalali;
            $data['expires_at'] = JalaliDateCast::toGregorian($expiresJalali);
        }

        return $data;
    }
}