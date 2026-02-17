<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'hostel_id',
        'room_number',
        'type',
        'capacity',
        'price_per_month',
        'description',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price_per_month' => 'decimal:2',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class)->orderBy('sort_order');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function availableBeds()
    {
        return $this->beds()->where('is_occupied', false);
    }

    public function occupiedBeds()
    {
        return $this->beds()->where('is_occupied', true);
    }

    public function getOccupancyPercentage()
    {
        $totalBeds = $this->beds()->count();
        if ($totalBeds === 0) {
            return 0;
        }
        $occupiedBeds = $this->occupiedBeds()->count();
        return round(($occupiedBeds / $totalBeds) * 100, 2);
    }

    public function isBooked()
    {
        $totalBeds = $this->beds()->count();
        if ($totalBeds === 1) {
            return $this->occupiedBeds()->exists();
        }
        return $this->occupiedBeds()->count() === $totalBeds;
    }
}
