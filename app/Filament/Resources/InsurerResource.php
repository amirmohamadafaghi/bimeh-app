<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InsurerResource\Pages;
use App\Models\Insurer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InsurerResource extends Resource
{
    protected static ?string $model = Insurer::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $modelLabel = 'شرکت بیمه';

    protected static ?string $pluralModelLabel = 'شرکت‌های بیمه';

    // چون فقط با شرکت بیمه ایران کار می‌کنیم، نیازی به نمایش در هدر نیست
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('نام شرکت بیمه')
                    ->default('بیمه ایران')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام شرکت بیمه'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInsurers::route('/'),
        ];
    }
}
