<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Support\PersianNormalizer;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['name'] = PersianNormalizer::normalizeText($data['name'] ?? '');
        $data['national_code'] = PersianNormalizer::cleanNationalCode($data['national_code'] ?? '') ?: null;
        $data['mobile'] = PersianNormalizer::cleanMobile($data['mobile'] ?? '');

        return $data;
    }
}