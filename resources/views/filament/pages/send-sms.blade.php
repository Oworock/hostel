<x-filament-panels::page>
    <div class="max-w-2xl">
        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-3">
                <x-filament::button type="submit" color="success">
                    Send SMS
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
