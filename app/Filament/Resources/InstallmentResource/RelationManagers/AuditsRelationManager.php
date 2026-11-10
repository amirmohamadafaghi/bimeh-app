<?php

namespace App\Filament\Resources\InstallmentResource\RelationManagers;

use App\Models\InstallmentAudit;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'audits';

    protected static ?string $title = 'تاریخچه تغییرات و لاگ حسابرسی (Audit Log)';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at_jalali')
                    ->label('زمان دقیق (شمسی)')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('actor.name')
                    ->label('انجام‌دهنده (Who)')
                    ->formatStateUsing(fn ($state, InstallmentAudit $record) => $state ?: "سیستم ({$record->actor_type})")
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('action')
                    ->label('عملیات (What)')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'created' => 'ایجاد قسط',
                        'paid' => 'تسویه قسط',
                        'status_changed' => 'تغییر وضعیت/مبلغ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('old_status')
                    ->label('وضعیت قبلی')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('new_status')
                    ->label('وضعیت جدید')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('description')
                    ->label('توضیحات'),
            ]);
    }
}
