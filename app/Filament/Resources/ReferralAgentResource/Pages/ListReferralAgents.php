<?php

namespace App\Filament\Resources\ReferralAgentResource\Pages;

use App\Filament\Resources\ReferralAgentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReferralAgents extends ListRecords
{
    protected static string $resource = ReferralAgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
