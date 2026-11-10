<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit" color="primary" icon="heroicon-o-shield-check">
                ذخیره تغییرات اختیارات
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>