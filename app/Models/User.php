<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'phone',
        'id_number',
        'address',
        'guardian_name',
        'guardian_phone',
        'extra_data',
        'hostel_id',
        'referred_by_referral_agent_id',
        'is_active',
        'is_admin_uploaded',
        'must_change_password',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_admin_uploaded' => 'boolean',
        'must_change_password' => 'boolean',
        'extra_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $first = trim((string) ($user->first_name ?? ''));
            $last = trim((string) ($user->last_name ?? ''));

            if ($first !== '' && $last !== '') {
                $user->name = trim($first . ' ' . $last);
                return;
            }

            $name = trim((string) ($user->name ?? ''));
            if ($name === '') {
                return;
            }

            $parts = preg_split('/\s+/', $name) ?: [];
            $user->first_name = $first !== '' ? $first : (string) ($parts[0] ?? '');
            $user->last_name = $last !== '' ? $last : (count($parts) > 1
                ? trim(implode(' ', array_slice($parts, 1)))
                : (string) ($parts[0] ?? ''));
        });
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function referralAgent()
    {
        return $this->belongsTo(ReferralAgent::class, 'referred_by_referral_agent_id');
    }

    public function userManagement()
    {
        return $this->hasOne(UserManagement::class);
    }

    public function ownedHostels()
    {
        return $this->hasMany(Hostel::class, 'owner_id');
    }

    public function managedHostels()
    {
        return $this->belongsToMany(Hostel::class, 'hostel_manager', 'user_id', 'hostel_id')->withTimestamps();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function allocations()
    {
        return $this->hasMany(Allocation::class, 'student_id');
    }

    public function hostelChangeRequests()
    {
        return $this->hasMany(HostelChangeRequest::class, 'student_id');
    }

    public function roomChangeRequests()
    {
        return $this->hasMany(RoomChangeRequest::class, 'student_id');
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class, 'uploader_id');
    }

    public function uploadedAddons()
    {
        return $this->hasMany(Addon::class, 'uploaded_by');
    }

    public function createdAssets()
    {
        return $this->hasMany(Asset::class, 'created_by');
    }

    public function requestedAssetMovements()
    {
        return $this->hasMany(AssetMovement::class, 'requested_by');
    }

    public function createdAssetSubscriptions()
    {
        return $this->hasMany(AssetSubscription::class, 'created_by');
    }

    public function isStudent()
    {
        return strtolower(trim((string) $this->role)) === 'student';
    }

    public function isManager()
    {
        return strtolower(trim((string) $this->role)) === 'manager';
    }

    public function isAdmin()
    {
        $role = strtolower(trim((string) $this->role));

        return in_array($role, ['admin', 'super_admin'], true);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        return true;
    }

    public function managedHostelIds(): Collection
    {
        if (!$this->isManager()) {
            return collect();
        }

        $ids = $this->managedHostels()->pluck('hostels.id');
        if ($ids->isEmpty() && $this->hostel_id) {
            $ids = collect([$this->hostel_id]);
        }

        return $ids->unique()->values();
    }

    public function initials(): string
    {
        $name = trim((string) ($this->name ?? ''));
        if ($name === '') {
            return 'U';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $initials = collect($parts)
            ->filter(fn ($part) => $part !== '')
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'U';
    }
}
