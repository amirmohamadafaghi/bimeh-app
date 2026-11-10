<?php

namespace App\Filament\Resources\InsurerResource\Pages;

use App\Filament\Resources\InsurerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInsurers extends ListRecords
{
    protected static string $resource = InsurerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
