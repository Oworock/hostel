<?php

namespace App\Filament\Resources\WelcomeSectionResource\Pages;

use App\Filament\Resources\WelcomeSectionResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord as EditRecord;
use Illuminate\Support\Facades\Storage;

class EditWelcomeSection extends EditRecord
{
    protected static string $resource = WelcomeSectionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $previousPath = $this->record->image_path;
        $nextPath = $data['image_path'] ?? null;

        if (!empty($previousPath) && $previousPath !== $nextPath) {
            Storage::disk('public')->delete($previousPath);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
