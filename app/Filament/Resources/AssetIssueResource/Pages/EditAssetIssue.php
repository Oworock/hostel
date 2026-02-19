<?php

namespace App\Filament\Resources\AssetIssueResource\Pages;

use App\Filament\Resources\AssetIssueResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseEditRecord as EditRecord;

class EditAssetIssue extends EditRecord
{
    protected static string $resource = AssetIssueResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'resolved' && empty($data['resolved_at'])) {
            $data['resolved_at'] = now();
        }

        if (($data['status'] ?? null) !== 'resolved') {
            $data['resolved_at'] = null;
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
