<?php

namespace App\Filament\Resources\SmsProviderResource\Pages;

use App\Filament\Resources\SmsProviderResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseCreateRecord as CreateRecord;

class CreateSmsProvider extends CreateRecord
{
    protected static string $resource = SmsProviderResource::class;
}
