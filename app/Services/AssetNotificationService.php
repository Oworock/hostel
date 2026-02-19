<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\User;
use App\Notifications\SystemEventNotification;

class AssetNotificationService
{
    public function notifyManagersAssetCreated(Asset $asset, ?User $actor = null): void
    {
        if (!$asset->hostel_id) {
            return;
        }

        $asset->loadMissing('hostel');

        $managers = User::query()
            ->where('role', 'manager')
            ->where(function ($query) use ($asset) {
                $query->whereHas('managedHostels', fn ($managed) => $managed->where('hostels.id', $asset->hostel_id))
                    ->orWhere('hostel_id', $asset->hostel_id);
            })
            ->get();

        if ($managers->isEmpty()) {
            return;
        }

        $actorName = $actor?->name ?: 'System';
        $hostelName = $asset->hostel?->name ?: 'Assigned Hostel';

        foreach ($managers as $manager) {
            $manager->notify(new SystemEventNotification(
                event: 'asset_created',
                title: 'New Asset Added',
                message: sprintf(
                    'A new asset "%s" was added to %s by %s.',
                    $asset->name,
                    $hostelName,
                    $actorName
                ),
                payload: [
                    'asset_id' => $asset->id,
                    'hostel_id' => $asset->hostel_id,
                    'created_by' => $actor?->id,
                ],
            ));
        }
    }
}
