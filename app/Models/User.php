<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
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
        'is_active',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'extra_data' => 'array',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
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

    public function hostelChangeRequests()
    {
        return $this->hasMany(HostelChangeRequest::class, 'student_id');
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
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
}
