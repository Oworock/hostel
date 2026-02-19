<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Bed extends Model
{
    protected $fillable = [
        'room_id',
        'bed_number',
        'name',
        'is_occupied',
        'is_approved',
        'approved_by',
        'approved_at',
        'created_by',
        'user_id',
        'occupied_from',
    ];

    protected $casts = [
        'is_occupied' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'occupied_from' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Bed $bed): void {
            $bed->images()->get()->each(function ($image): void {
                if (!empty($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
            });
        });
    }

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
