<?php

namespace App\Filament\Resources\WelcomeContentResource\Pages;

use App\Filament\Resources\WelcomeContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWelcomeContent extends CreateRecord
{
    protected static string $resource = WelcomeContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['value'] = WelcomeContentResource::valueFromFormData($data);
        $data['type'] = WelcomeContentResource::typeForKey($data['key'] ?? null);

        unset($data['value_text'], $data['value_html'], $data['value_logo'], $data['value_email'], $data['value_phone']);

        return $data;
    }
}
