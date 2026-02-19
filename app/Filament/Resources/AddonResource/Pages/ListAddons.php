<?php

namespace App\Filament\Resources\AddonResource\Pages;

use App\Filament\Resources\AddonResource;
use App\Services\AddonDiscoveryService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAddons extends ListRecords
{
    protected static string $resource = AddonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('developer_guide')
                ->label('Developer Guide')
                ->icon('heroicon-o-book-open')
                ->color('gray')
                ->url(route('admin.addons.development-guide'), shouldOpenInNewTab: true),
            Actions\Action::make('discover')
                ->label('Discover Folder Addons')
                ->icon('heroicon-o-magnifying-glass')
                ->action(function (): void {
                    app(AddonDiscoveryService::class)->discover();

                    Notification::make()
                        ->success()
                        ->title('Discovery completed')
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
