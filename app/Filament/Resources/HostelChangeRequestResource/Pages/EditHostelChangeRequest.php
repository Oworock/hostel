<?php

namespace App\Filament\Resources\HostelChangeRequestResource\Pages;

use App\Filament\Resources\HostelChangeRequestResource;
use App\Filament\Resources\Pages\BaseEditRecord as EditRecord;

class EditHostelChangeRequest extends EditRecord
{
    protected static string $resource = HostelChangeRequestResource::class;
    protected string $previousStatus = '';

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->previousStatus = (string) $this->record->status;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $oldStatus = $this->record->status;
        $newStatus = $data['status'] ?? $oldStatus;

        if ($newStatus === 'approved' && $oldStatus !== 'approved') {
            $data['admin_approved_by'] = auth()->id();
            $data['admin_approved_at'] = now();
        }

        if ($newStatus === 'approved') {
            $this->record->student?->update(['hostel_id' => $data['requested_hostel_id'] ?? $this->record->requested_hostel_id]);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record->fresh(['student', 'requestedHostel.managers', 'currentHostel']);
        $admin = auth()->user();

        if ($this->previousStatus !== $record->status && $record->status === 'approved') {
            app(\App\Services\HostelChangeNotificationService::class)->adminApproved($record, $admin);
        } elseif ($this->previousStatus !== $record->status && $record->status === 'rejected') {
            app(\App\Services\HostelChangeNotificationService::class)->adminRejected($record, $admin);
        }
    }
}
