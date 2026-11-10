<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'بیمه‌گذاران';

    protected static ?string $modelLabel = 'بیمه‌گذار';

    protected static ?string $pluralModelLabel = 'بیمه‌گذاران';

    protected static ?int $navigationSort = 1;

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('نقش کاربری و سطح دسترسی در سامانه')
                ->visible(fn (): bool => auth()->user()?->isAdmin() ?? false)
                ->schema([
                    Forms\Components\Select::make('role')
                        ->label('نقش کاربری')
                        ->options([
                            'customer' => 'بیمه‌گذار (مشتری)',
                            'manager'  => 'منجر (مدیر با دسترسی انتخابی)',
                            'admin'    => 'ادمین (مدیر ارشد کل سامانه)',
                        ])
                        ->default('customer')
                        ->required()
                        ->live(),

                    Forms\Components\CheckboxList::make('permissions')
                        ->label('مجوزهای دسترسی منجر')
                        ->options(User::availableManagerPermissions())
                        ->columns(2)
                        ->visible(fn (Get $get): bool => $get('role') === 'manager')
                        ->helperText('مشخص کنید این منجر به کدام بخش‌ها و عملیات‌ها دسترسی داشته باشد.'),
                ])->columns(2),

            Forms\Components\Section::make('مشخصات هویتی و اطلاعات تماس بیمه‌گذار')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('نام و نام خانوادگی / نام شرکت')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('national_code')
                        ->label('کد ملی / شناسه ملی')
                        ->maxLength(15)
                        ->unique(table: User::class, column: 'national_code', ignoreRecord: true),

                    Forms\Components\TextInput::make('mobile')
                        ->label('شماره موبایل')
                        ->required()
                        ->maxLength(20)
                        ->unique(table: User::class, column: 'mobile', ignoreRecord: true),

                    Forms\Components\TextInput::make('phone')
                        ->label('تلفن ثابت')
                        ->maxLength(20),

                    Forms\Components\Select::make('policyholder_type')
                        ->label('نوع بیمه‌گذار')
                        ->options([
                            'حقیقی' => 'حقیقی',
                            'حقوقی' => 'حقوقی',
                        ])
                        ->default('حقیقی'),

                    Forms\Components\TextInput::make('profile_title')
                        ->label('پروفایل فروش / طرح اقساط')
                        ->placeholder('مثال: اعتباری 50-50'),

                    Forms\Components\TextInput::make('birth_date')
                        ->label('تاریخ تولد شمسی')
                        ->placeholder('1365/04/15'),

                    Forms\Components\TextInput::make('bale_chat_id')
                        ->label('شناسه چت بله (bale_chat_id)')
                        ->placeholder('اتصال خودکار یا ثبت دستی'),

                    Forms\Components\Select::make('payment_badge')
                        ->label('وضعیت خوش‌حسابی')
                        ->options([
                            'none'       => 'عادی',
                            'good_payer' => 'خوش‌حساب (طلایی)',
                            'bad_payer'  => 'بدحساب (قرمز)',
                        ])
                        ->default('none'),

                    Forms\Components\TextInput::make('password')
                        ->label('رمز عبور جدید (اختیاری)')
                        ->password()
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(false),

                    Forms\Components\Textarea::make('address')
                        ->label('آدرس کامل')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(3),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (User $record): string => static::getUrl('portfolio', ['record' => $record->getKey()]))
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('ردیف')
                    ->rowIndex()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('name')
                    ->label('نام بیمه‌گذار')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(28)
                    ->tooltip(fn (User $record): string => (string) $record->name),

                Tables\Columns\TextColumn::make('national_code')
                    ->label('کد ملی')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('موبایل')
                    ->searchable()
                    ->copyable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('policyholder_type')
                    ->label('نوع بیمه‌گذار')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'حقوقی' ? 'warning' : 'info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('profile_title')
                    ->label('پروفایل')
                    ->placeholder('—')
                    ->limit(18)
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('policies_count')
                    ->label('تعداد بیمه‌نامه')
                    ->counts('policies')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('payment_badge')
                    ->label('وضعیت خوش‌حسابی')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'good_payer' => '★ خوش‌حساب',
                        'bad_payer'  => '▲ بدحساب',
                        default      => 'عادی',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'good_payer' => 'warning',
                        'bad_payer'  => 'danger',
                        default      => 'gray',
                    })
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('policyholder_type')
                    ->label('نوع بیمه‌گذار')
                    ->options([
                        'حقیقی' => 'حقیقی',
                        'حقوقی' => 'حقوقی',
                    ]),
                Tables\Filters\SelectFilter::make('payment_badge')
                    ->label('وضعیت خوش‌حسابی')
                    ->options([
                        'good_payer' => 'خوش‌حساب',
                        'bad_payer'  => 'بدحساب',
                        'none'       => 'عادی',
                    ]),
                Tables\Filters\TrashedFilter::make()
                    ->label('سطل زباله (حذف‌شده‌ها)'),
            ])
            ->actions([
                Tables\Actions\Action::make('portfolio')
                    ->label('مشاهده پرونده')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->iconButton()
                    ->tooltip('مشاهده و ویرایش پرونده بیمه‌گذار')
                    ->url(fn (User $record): string => static::getUrl('portfolio', ['record' => $record->getKey()])),

                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->tooltip('انتقال به سطل زباله'),

                Tables\Actions\RestoreAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(10);
    }

    public static function getPages(): array
    {
        return [
            'index'     => Pages\ListUsers::route('/'),
            'create'    => Pages\CreateUser::route('/create'),
            'portfolio' => Pages\ViewUserPortfolio::route('/{record}/portfolio'),
            'edit'      => Pages\ViewUserPortfolio::route('/{record}/edit'),
        ];
    }
}