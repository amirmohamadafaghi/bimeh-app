<?php

declare(strict_types=1);

namespace App\Filament\Resources\InstallmentResource\RelationManagers;

use App\Casts\JalaliDateCast;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'audits';

    protected static ?string $title = 'تاریخچه تغییرات و لاگ حسابرسی قسط';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
                Tables\Columns\TextColumn::make('actor.name')
                    ->label('اقدام‌کننده')
                    ->default('سیستم خودکار'),

                Tables\Columns\TextColumn::make('action')
                    ->label('عملیات')
                    ->badge(),

                Tables\Columns\TextColumn::make('old_status')
                    ->label('وضعیت قبلی')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('new_status')
                    ->label('وضعیت جدید')
                    ->badge(),

                Tables\Columns\TextColumn::make('new_amount')
                    ->label('مبلغ (ریال)')
                    ->numeric(),

                Tables\Columns\TextColumn::make('description')
                    ->label('توضیحات'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->formatStateUsing(fn ($state): string => JalaliDateCast::toJalali($state, 'Y/m/d H:i')),
            ])
            ->defaultSort('id', 'desc');
    }
}