<?php

declare(strict_types=1);

namespace App\Filament\Resources\InstallmentResource\Pages;

use App\Filament\Resources\InstallmentResource;
use App\Models\Installment;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInstallments extends ListRecords
{
    protected static string $resource = InstallmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('ثبت قسط جدید')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('همه اقساط'),

            'awaiting_approval' => Tab::make('منتظر تایید ادمین')
                ->badge(Installment::where('status', 'awaiting_approval')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'awaiting_approval')),

            'overdue' => Tab::make('اقساط معوق')
                ->badge(Installment::where('status', 'overdue')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'overdue')),

            'pending' => Tab::make('در انتظار پرداخت')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'pending')),

            'paid' => Tab::make('پرداخت شده')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'paid')),
        ];
    }
}