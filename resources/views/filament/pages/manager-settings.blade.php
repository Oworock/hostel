<x-filament-panels::page>
    <div class="max-w-4xl">
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-3">
                <x-filament::button type="submit" color="primary">
                    Save Changes
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
