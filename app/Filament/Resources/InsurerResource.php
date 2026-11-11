<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\InsurerResource\Pages;
use App\Models\Insurer;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InsurerResource extends Resource
{
    protected static ?string $model = Insurer::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'app_reports';

    protected static ?string $navigationLabel = 'شرکت‌های بیمه';

    protected static ?string $modelLabel = 'شرکت بیمه';

    protected static ?string $pluralModelLabel = 'شرکت‌های بیمه';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User ? ($user->isAdmin() || $user->hasPermission('manage_insurers')) : false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('نام شرکت بیمه')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('code')
                    ->label('کد نمایندگی / شرکت')
                    ->maxLength(50),

                Forms\Components\TextInput::make('service_type')
                    ->label('نوع سرویس / رشته اصلی')
                    ->default('ثالث و بدنه'),

                Forms\Components\TextInput::make('contact_phone')
                    ->label('تلفن پشتیبانی')
                    ->maxLength(50),

                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام شرکت بیمه')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('code')
                    ->label('کد')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('policies_count')
                    ->counts('policies')
                    ->label('تعداد بیمه‌نامه')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('وضعیت')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInsurers::route('/'),
        ];
    }
}