<?php

namespace App\Filament\Resources\AddonResource\Pages;

use App\Filament\Resources\AddonResource;
use App\Models\Addon;
use App\Services\AddonPackageService;
use App\Services\AddonVisibilityService;
use Filament\Notifications\Notification;
use App\Filament\Resources\Pages\BaseCreateRecord as CreateRecord;

class CreateAddon extends CreateRecord
{
    protected static string $resource = AddonResource::class;

    protected function handleRecordCreation(array $data): Addon
    {
        $packagePath = (string) ($data['package_file'] ?? '');

        if ($packagePath === '') {
            $this->halt();
        }

        try {
            $addon = app(AddonPackageService::class)->registerFromStoredPackagePath($packagePath, auth()->id());
            app(AddonVisibilityService::class)->unignore($addon->slug);

            return $addon;
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Addon upload failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            'name' => 'Processing...',
            'version' => '1.0.0',
            'is_active' => false,
            'package_file' => $data['package_file'] ?? null,
        ];
    }
}
