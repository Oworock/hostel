<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentAudienceService
{
    /**
     * @param array{
     *   hostel_id?: int|string|null,
     *   student_ids?: array<int|string>|null
     * } $filters
     */
    public function resolve(string $segment, array $filters = []): Collection
    {
        $query = User::query()->where('role', 'student');

        $hostelId = (int) ($filters['hostel_id'] ?? 0);
        if ($hostelId > 0) {
            $query->where(function (Builder $nested) use ($hostelId): void {
                $nested
                    ->where('hostel_id', $hostelId)
                    ->orWhereHas('bookings.room', fn (Builder $q) => $q->where('hostel_id', $hostelId));
            });
        }

        switch ($segment) {
            case 'active':
                $query
                    ->where('is_active', true)
                    ->whereDoesntHave('userManagement', fn (Builder $q) => $q->whereIn('status', ['inactive', 'suspended']));
                break;

            case 'inactive':
                $query->where(function (Builder $q): void {
                    $q->where('is_active', false)
                        ->orWhereHas('userManagement', fn (Builder $m) => $m->whereIn('status', ['inactive', 'suspended']));
                });
                break;

            case 'expired_booking':
                $today = now()->toDateString();
                $query
                    ->whereHas('bookings', function (Builder $q) use ($today): void {
                        $q->whereIn('status', ['approved', 'completed'])
                            ->whereDate('check_out_date', '<', $today);
                    })
                    ->whereDoesntHave('bookings', function (Builder $q) use ($today): void {
                        $q->whereIn('status', ['pending', 'approved'])
                            ->whereDate('check_out_date', '>=', $today);
                    });
                break;

            case 'specific':
                $ids = collect($filters['student_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0)
                    ->values();
                if ($ids->isEmpty()) {
                    return collect();
                }
                $query->whereIn('id', $ids->all());
                break;

            case 'hostel':
            case 'all':
            default:
                // Base query already applies.
                break;
        }

        return $query->get();
    }
}

