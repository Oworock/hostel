<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'state',
        'postal_code',
        'phone',
        'email',
        'image_path',
        'owner_id',
        'price_per_month',
        'total_capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_per_month' => 'decimal:2',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function managers()
    {
        return $this->belongsToMany(User::class, 'hostel_manager', 'hostel_id', 'user_id')->withTimestamps();
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Room::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Booking::class);
    }

    public function incomingChangeRequests()
    {
        return $this->hasMany(HostelChangeRequest::class, 'requested_hostel_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function incomingAssetMovements()
    {
        return $this->hasMany(AssetMovement::class, 'to_hostel_id');
    }

    public function outgoingAssetMovements()
    {
        return $this->hasMany(AssetMovement::class, 'from_hostel_id');
    }

    public function assetSubscriptions()
    {
        return $this->hasMany(AssetSubscription::class);
    }
}
