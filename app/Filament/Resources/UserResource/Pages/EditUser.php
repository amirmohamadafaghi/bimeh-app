<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Support\PersianNormalizer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = PersianNormalizer::normalizeText($data['name'] ?? '');
        $data['national_code'] = PersianNormalizer::cleanNationalCode($data['national_code'] ?? '') ?: null;
        $data['mobile'] = PersianNormalizer::cleanMobile($data['mobile'] ?? '');

        return $data;
    }
}