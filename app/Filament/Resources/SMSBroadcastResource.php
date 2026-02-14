<?php

namespace App\Filament\Resources;

use App\Models\SmsCampaign;
use Filament\Resources\Resource;

class SMSBroadcastResource extends Resource
{
    protected static ?string $model = SmsCampaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationLabel = 'Send SMS';

    protected static ?string $navigationGroup = 'Communication';

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SMSBroadcastResource\Pages\SendSMS::route('/'),
        ];
    }
}
