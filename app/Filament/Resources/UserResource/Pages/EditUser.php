<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string|Htmlable
    {
        return 'ویرایش پرونده بیمه‌گذار: '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label('تأیید')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('تأیید ثبت تغییرات')
            ->modalDescription('این اطلاعات قرار است ثبت شود؛ آیا از ثبت اطلاعات فعلی مطمئن هستید؟')
            ->modalSubmitActionLabel('تأیید')
            ->modalCancelActionLabel('لغو')
            ->action(fn () => $this->save());
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('لغو و بازگشت')
            ->icon('heroicon-o-x-mark')
            ->color('gray')
            ->url($this->getResource()::getUrl('index'));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
