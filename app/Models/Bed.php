<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $fillable = [
        'room_id',
        'bed_number',
        'name',
        'is_occupied',
        'user_id',
        'occupied_from',
    ];

    protected $casts = [
        'is_occupied' => 'boolean',
        'occupied_from' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(BedImage::class)->orderBy('sort_order');
    }

    public function booking()
    {
        return $this->hasOne(Booking::class);
    }
}
