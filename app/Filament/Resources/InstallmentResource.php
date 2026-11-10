<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Pages\NotificationSettingsPage;
use App\Filament\Resources\InstallmentResource\Pages;
use App\Filament\Resources\InstallmentResource\RelationManagers\AuditsRelationManager;
use App\Models\Installment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class InstallmentResource extends Resource
{
    protected static ?string $model = Installment::class;

    protected static ?string $navigationIcon = 'app_installments';

    protected static ?string $navigationLabel = 'مدیریت اقساط';

    protected static ?string $modelLabel = 'قسط';

    protected static ?string $pluralModelLabel = 'مدیریت اقساط';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('status', ['overdue', 'awaiting_approval'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات مالی و وضعیت قسط')
                    ->schema([
                        Forms\Components\Select::make('policy_id')
                            ->label('بیمه‌نامه مرتبط')
                            ->relationship('policy', 'policy_number')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('installment_number')
                            ->label('شماره قسط')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('مبلغ قسط (ریال)')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('documented_amount')
                            ->label('مبلغ مستند / وصولی (ریال)')
                            ->numeric(),

                        Forms\Components\TextInput::make('due_date_jalali')
                            ->label('تاریخ سررسید (شمسی)')
                            ->placeholder('1405/08/01'),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('تاریخ سررسید (میلادی/سیستمی)')
                            ->required(),

                        Forms\Components\TextInput::make('payment_identifier')
                            ->label('شناسه پرداخت'),

                        Forms\Components\TextInput::make('documented_date')
                            ->label('تاریخ مستند شمسی')
                            ->placeholder('1405/08/01'),

                        Forms\Components\Select::make('status')
                            ->label('وضعیت قسط')
                            ->options([
                                'pending'           => 'در انتظار پرداخت',
                                'awaiting_approval' => 'در انتظار تایید ادمین',
                                'paid'              => 'وصول شده (تسویه)',
                                'overdue'           => 'معوق (سررسید گذشته)',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['policy.user']))
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('ردیف')
                    ->rowIndex()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('policy.policy_number')
                    ->label('شماره بیمه‌نامه')
                    ->searchable()
                    ->weight('bold')
                    ->limit(20)
                    ->tooltip(fn (Installment $record): string => (string) ($record->policy?->policy_number ?? ''))
                    ->description(fn (Installment $record): string => (string) ($record->policy?->vehicle_name ?: '—')),

                Tables\Columns\TextColumn::make('policy.user.name')
                    ->label('نام بیمه‌گذار')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(22)
                    ->tooltip(fn (Installment $record): string => (string) ($record->policy?->user?->name ?? '')),

                Tables\Columns\TextColumn::make('policy.user.mobile')
                    ->label('موبایل')
                    ->searchable()
                    ->copyable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('due_date_jalali')
                    ->label('سررسید')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('due_date', $direction))
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('مبلغ (ریال)')
                    ->formatStateUsing(fn ($state): string => number_format((int) $state))
                    ->sortable()
                    ->weight('bold')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('payment_identifier')
                    ->label('شناسه پرداخت')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->limit(14)
                    ->tooltip(fn (Installment $record): ?string => $record->payment_identifier)
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'paid'              => 'وصول شده',
                        'awaiting_approval' => 'منتظر تایید',
                        'overdue'           => 'معوق',
                        default             => 'در انتظار',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'paid'              => 'success',
                        'awaiting_approval' => 'info',
                        'overdue'           => 'danger',
                        default             => 'warning',
                    })
                    ->alignCenter(),
            ])
            ->defaultSort('due_date', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت قسط')
                    ->options([
                        'pending'           => 'در انتظار پرداخت',
                        'awaiting_approval' => 'در انتظار تایید ادمین',
                        'overdue'           => 'معوق (سررسید گذشته)',
                        'paid'              => 'وصول شده',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approvePayment')
                    ->label('تایید')
                    ->tooltip('تایید وصولی و تسویه این قسط')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->size('xs')
                    ->requiresConfirmation()
                    ->visible(fn (Installment $record): bool => $record->status !== 'paid' && (auth()->user()?->hasFinancialAccess() ?? false))
                    ->action(function (Installment $record): void {
                        $record->update([
                            'status'            => 'paid',
                            'paid_amount'       => $record->amount,
                            'documented_amount' => $record->amount,
                            'paid_at'           => now(),
                        ]);

                        Notification::make()
                            ->title("قسط شماره {$record->installment_number} با موفقیت تسویه شد.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('sendCustomNotification')
                        ->label('ارسال اعلان اختصاصی')
                        ->icon('app_notifications')
                        ->color('info')
                        ->visible(fn (): bool => (auth()->user()?->isAdmin() ?? false) || (auth()->user()?->hasPermission('manage_notifications') ?? false))
                        ->url(fn (Installment $record): ?string => $record->policy?->user_id
                            ? NotificationSettingsPage::getUrl(['users' => (string) $record->policy->user_id]) . '#custom-message-section'
                            : null
                        ),

                    Tables\Actions\EditAction::make()
                        ->label('ویرایش قسط')
                        ->icon('app_edit'),

                    Tables\Actions\DeleteAction::make()
                        ->label('حذف قسط')
                        ->icon('app_delete'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->tooltip('عملیات قسط'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('sendBulkNotification')
                        ->label('ارسال اعلان گروهی به انتخاب‌شده‌ها')
                        ->icon('app_notifications')
                        ->color('info')
                        ->visible(fn (): bool => (auth()->user()?->isAdmin() ?? false) || (auth()->user()?->hasPermission('manage_notifications') ?? false))
                        ->action(function (Collection $records, Component $livewire): void {
                            $records->loadMissing('policy');

                            $userIds = $records
                                ->pluck('policy.user_id')
                                ->filter()
                                ->map(fn ($id): int => (int) $id)
                                ->unique()
                                ->values()
                                ->all();

                            if (empty($userIds)) {
                                Notification::make()
                                    ->title('هیچ بیمه‌گذاری برای رکوردهای انتخاب‌شده یافت نشد.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $targetUrl = NotificationSettingsPage::getUrl([
                                'users' => implode(',', $userIds),
                            ]) . '#custom-message-section';

                            $livewire->redirect($targetUrl);
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(10);
    }

    public static function getRelations(): array
    {
        return [
            AuditsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInstallments::route('/'),
            'create' => Pages\CreateInstallment::route('/create'),
            'edit'   => Pages\EditInstallment::route('/{record}/edit'),
        ];
    }
}