<?php

namespace App\Filament\Resources\WelcomeContentResource\Pages;

use App\Filament\Resources\WelcomeContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditWelcomeContent extends EditRecord
{
    protected static string $resource = WelcomeContentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $input = WelcomeContentResource::inputForKey($data['key'] ?? null);

        $data['value_text'] = null;
        $data['value_html'] = null;
        $data['value_logo'] = null;
        $data['value_email'] = null;
        $data['value_phone'] = null;

        if ($input === 'html') {
            $data['value_html'] = $data['value'] ?? null;
        } elseif ($input === 'logo') {
            $data['value_logo'] = $data['value'] ?? null;
        } elseif ($input === 'email') {
            $data['value_email'] = $data['value'] ?? null;
        } elseif ($input === 'phone') {
            $data['value_phone'] = $data['value'] ?? null;
        } else {
            $data['value_text'] = $data['value'] ?? null;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $key = $this->record->key;
        $previousValue = $this->record->value;

        $data['key'] = $key;
        $data['value'] = WelcomeContentResource::valueFromFormData($data, $key);
        $data['type'] = WelcomeContentResource::typeForKey($key);

        if (WelcomeContentResource::inputForKey($key) === 'logo' && !empty($previousValue) && $previousValue !== ($data['value'] ?? null)) {
            Storage::disk('public')->delete($previousValue);
        }

        unset($data['value_text'], $data['value_html'], $data['value_logo'], $data['value_email'], $data['value_phone']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
