<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function ownedHostels()
    {
        return $this->hasMany(Hostel::class, 'owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
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
}
