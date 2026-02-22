<?php

namespace App\Filament\Resources\StaffMemberResource\Pages;

use App\Filament\Resources\StaffMemberResource;
use App\Models\User;
use App\Services\StaffIdCardService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Schema;
use Throwable;

class EditStaffMember extends EditRecord
{
    protected static string $resource = StaffMemberResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['user_id'])) {
            $user = User::find($data['user_id']);
            if ($user) {
                $data['source_role'] = (string) $user->role;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (!Schema::hasColumn('staff_members', 'id_card_path')) {
            return;
        }

        try {
            $path = app(StaffIdCardService::class)->generate($this->record);
            $this->record->update(['id_card_path' => $path]);
        } catch (Throwable $e) {
            // Keep update flow successful even if ID card generation fails.
        }
    }
}
