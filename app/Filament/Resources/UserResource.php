<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Pages\NotificationSettingsPage;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'app_policyholders';

    protected static ?string $navigationLabel = 'بیمه‌گذاران';

    protected static ?string $modelLabel = 'بیمه‌گذار';

    protected static ?string $pluralModelLabel = 'بیمه‌گذاران';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات هویتی و تماس بیمه‌گذار')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('نام و نام خانوادگی')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('national_code')
                            ->label('کد ملی / شناسه ملی')
                            ->maxLength(20)
                            ->extraInputAttributes(['dir' => 'ltr']),

                        Forms\Components\TextInput::make('mobile')
                            ->label('شماره تلفن همراه')
                            ->required()
                            ->maxLength(20)
                            ->extraInputAttributes(['dir' => 'ltr']),

                        Forms\Components\TextInput::make('phone')
                            ->label('تلفن ثابت')
                            ->maxLength(20)
                            ->extraInputAttributes(['dir' => 'ltr']),

                        Forms\Components\TextInput::make('birth_date')
                            ->label('تاریخ تولد (شمسی، مثال: 1365/04/15)')
                            ->placeholder('1365/04/15')
                            ->maxLength(20)
                            ->extraInputAttributes(['dir' => 'ltr']),

                        Forms\Components\Select::make('policyholder_type')
                            ->label('نوع بیمه‌گذار')
                            ->options([
                                'حقیقی' => 'حقیقی',
                                'حقوقی' => 'حقوقی',
                            ])
                            ->default('حقیقی'),

                        Forms\Components\TextInput::make('profile_title')
                            ->label('پروفایل / معرف')
                            ->maxLength(255),

                        Forms\Components\Select::make('payment_badge')
                            ->label('وضعیت خوش‌حسابی')
                            ->options([
                                'good_payer' => 'خوش‌حساب',
                                'normal'     => 'عادی',
                                'bad_payer'  => 'بدحساب (دارای معوقه)',
                            ])
                            ->default('normal'),

                        Forms\Components\Select::make('role')
                            ->label('نقش کاربری در سامانه')
                            ->options([
                                'customer' => 'بیمه‌گذار (مشتری)',
                                'manager'  => 'منجر / کارشناس پنل',
                                'admin'    => 'مدیر کل (Admin)',
                            ])
                            ->default('customer')
                            ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),

                        Forms\Components\TextInput::make('bale_chat_id')
                            ->label('شناسه چت ربات بله (bale_chat_id)')
                            ->maxLength(100)
                            ->extraInputAttributes(['dir' => 'ltr']),

                        Forms\Components\TextInput::make('password')
                            ->label('رمز عبور (در صورت خالی بودن در ویرایش، تغییر نمی‌کند)')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state): string => Hash::make((string) $state))
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\Textarea::make('address')
                            ->label('نشانی محل سکونت / محل کار')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('نام بیمه‌گذار')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('national_code')
                    ->label('کد ملی')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('شماره موبایل')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('profile_title')
                    ->label('پروفایل')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('policies_count')
                    ->counts('policies')
                    ->label('تعداد بیمه‌نامه')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_badge')
                    ->label('وضعیت پرداخت')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'good_payer' => 'خوش‌حساب',
                        'bad_payer'  => 'دارای معوقه',
                        default      => 'عادی',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'good_payer' => 'success',
                        'bad_payer'  => 'danger',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('bale_chat_id')
                    ->label('ربات بله')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => filled($state) ? 'متصل' : 'غیرمتصل')
                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray'),
            ])
            ->defaultSort('id', 'desc')
            ->recordUrl(fn (User $record): string => static::getUrl('portfolio', ['record' => $record]))
            ->filters([
                Tables\Filters\SelectFilter::make('payment_badge')
                    ->label('وضعیت خوش‌حسابی')
                    ->options([
                        'good_payer' => 'خوش‌حساب',
                        'normal'     => 'عادی',
                        'bad_payer'  => 'دارای معوقه',
                    ]),
                Tables\Filters\SelectFilter::make('role')
                    ->label('نقش کاربری')
                    ->options([
                        'customer' => 'بیمه‌گذار',
                        'manager'  => 'منجر',
                        'admin'    => 'مدیر کل',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('portfolio')
                    ->label('پرونده جامع')
                    ->icon('app_more_details')
                    ->color('primary')
                    ->url(fn (User $record): string => static::getUrl('portfolio', ['record' => $record])),

                Tables\Actions\EditAction::make()
                    ->icon('app_edit'),

                Tables\Actions\DeleteAction::make()
                    ->icon('app_delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('send_custom_notification')
                        ->label('ارسال پیام اختصاصی به انتخاب‌شده‌ها')
                        ->icon('app_notifications')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $ids = $records->pluck('id')->implode(',');

                            return redirect()->to(NotificationSettingsPage::getUrl(['users' => $ids]) . '#custom-message-section');
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'     => Pages\ListUsers::route('/'),
            'create'    => Pages\CreateUser::route('/create'),
            'edit'      => Pages\EditUser::route('/{record}/edit'),
            'portfolio' => Pages\ViewUserPortfolio::route('/{record}/portfolio'),
        ];
    }
}