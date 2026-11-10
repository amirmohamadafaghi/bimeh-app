<?php

namespace App\Filament\Resources\InsurerResource\Pages;

use App\Filament\Resources\InsurerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInsurer extends EditRecord
{
    protected static string $resource = InsurerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
