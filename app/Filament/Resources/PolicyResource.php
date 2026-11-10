<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PolicyResource\Pages;
use App\Models\Policy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PolicyResource extends Resource
{
    protected static ?string $model = Policy::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $modelLabel = 'بیمه‌نامه';

    protected static ?string $pluralModelLabel = 'بیمه‌نامه‌ها';

    // حذف قطعی از هدر بالای سایت (مدیریت متمرکز در پرونده جامع بیمه‌گذاران)
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('مشخصات بیمه‌نامه و خودرو')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('بیمه‌گذار (مشتری)')
                            ->relationship('user', 'name')
                            ->searchable(['name', 'mobile', 'national_code'])
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('insurer_id')
                            ->label('شرکت بیمه')
                            ->relationship('insurer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('policy_number')
                            ->label('شماره بیمه‌نامه')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('title')
                            ->label('نوع بیمه‌نامه')
                            ->default('بیمه شخص ثالث')
                            ->required(),

                        Forms\Components\TextInput::make('vehicle_name')
                            ->label('نوع و سیستم مورد بیمه (خودرو)')
                            ->placeholder('مثال: سواری پژو 206TU3'),

                        Forms\Components\TextInput::make('plate_number')
                            ->label('شماره انتظامی (پلاک)')
                            ->placeholder('مثال: 31ط927 ایران52'),

                        Forms\Components\TextInput::make('vehicle_model')
                            ->label('مدل خودرو (سال ساخت)')
                            ->placeholder('مثال: 1394'),

                        Forms\Components\TextInput::make('profile_title')
                            ->label('پروفایل')
                            ->placeholder('مثال: اعتباری 50-50'),

                        Forms\Components\DatePicker::make('expires_at')
                            ->label('تاریخ انقضا')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('policy_number')
                    ->label('شماره بیمه‌نامه')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('نام بیمه‌گذار')
                    ->description(fn (Policy $record): string => $record->user?->mobile ?? '')
                    ->searchable(),

                Tables\Columns\TextColumn::make('vehicle_name')
                    ->label('مورد بیمه (خودرو)')
                    ->description(fn (Policy $record): string => $record->plate_number ?? '')
                    ->searchable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('تاریخ انقضا')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPolicies::route('/'),
            'create' => Pages\CreatePolicy::route('/create'),
            'edit' => Pages\EditPolicy::route('/{record}/edit'),
        ];
    }
}
