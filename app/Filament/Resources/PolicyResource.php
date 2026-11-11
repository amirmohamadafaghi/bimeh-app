<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PolicyResource\Pages;
use App\Models\Policy;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PolicyResource extends Resource
{
    protected static ?string $model = Policy::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'app_reports';

    protected static ?string $navigationLabel = 'بیمه‌نامه‌ها';

    protected static ?string $modelLabel = 'بیمه‌نامه';

    protected static ?string $pluralModelLabel = 'بیمه‌نامه‌ها';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User ? ($user->isAdmin() || $user->hasPermission('manage_policies')) : false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('مشخصات بیمه‌نامه')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('بیمه‌گذار')
                            ->relationship('user', 'name')
                            ->searchable()
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
                            ->maxLength(120)
                            ->extraInputAttributes(['dir' => 'ltr']),

                        Forms\Components\TextInput::make('insurance_category')
                            ->label('رشته بیمه')
                            ->default('ثالث')
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('عنوان بیمه‌نامه')
                            ->default('بیمه شخص ثالث')
                            ->required(),

                        Forms\Components\TextInput::make('vehicle_name')
                            ->label('نوع و سیستم خودرو / مورد بیمه'),

                        Forms\Components\TextInput::make('plate_number')
                            ->label('شماره انتظامی (پلاک)'),

                        Forms\Components\TextInput::make('vehicle_model')
                            ->label('سال ساخت / مدل'),

                        Forms\Components\TextInput::make('issued_at_jalali')
                            ->label('تاریخ صدور (شمسی)')
                            ->placeholder('1405/01/01')
                            ->extraInputAttributes(['dir' => 'ltr']),

                        Forms\Components\TextInput::make('expires_at_jalali')
                            ->label('تاریخ انقضا (شمسی)')
                            ->placeholder('1406/01/01')
                            ->extraInputAttributes(['dir' => 'ltr']),

                        Forms\Components\Select::make('payment_type')
                            ->label('شیوه پرداخت')
                            ->options([
                                'installment' => 'اقساطی',
                                'cash'        => 'نقدی',
                            ])
                            ->default('installment'),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('حق بیمه کل (ریال)')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('initial_payment')
                            ->label('پیش‌پرداخت نقد (ریال)')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('installments_count')
                            ->label('تعداد اقساط')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Textarea::make('description')
                            ->label('توضیحات')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['user', 'insurer']))
            ->columns([
                Tables\Columns\TextColumn::make('policy_number')
                    ->label('شماره بیمه‌نامه')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('بیمه‌گذار')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('insurance_category')
                    ->label('رشته')
                    ->badge(),

                Tables\Columns\TextColumn::make('vehicle_name')
                    ->label('مورد بیمه')
                    ->searchable(),

                Tables\Columns\TextColumn::make('plate_number')
                    ->label('پلاک')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('مبلغ کل (ریال)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at_jalali')
                    ->label('تاریخ انقضا'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPolicies::route('/'),
            'create' => Pages\CreatePolicy::route('/create'),
            'edit'   => Pages\EditPolicy::route('/{record}/edit'),
        ];
    }
}