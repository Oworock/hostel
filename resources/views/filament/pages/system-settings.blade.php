<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-3">
            <x-filament::button type="submit" color="primary">
                Save Settings
            </x-filament::button>

            @if($this->form->getState()['sms_provider'] === 'custom')
                <x-filament::button wire:click="testSMS" type="button" color="info">
                    Test SMS
                </x-filament::button>
            @endif
        </div>
    </form>
</x-filament-panels::page>
