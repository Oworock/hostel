<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomChangeRequest extends Model
{
    protected $fillable = [
        'student_id',
        'current_booking_id',
        'current_room_id',
        'requested_room_id',
        'requested_bed_id',
        'status',
        'reason',
        'manager_note',
        'manager_approved_by',
        'manager_approved_at',
    ];

    protected $casts = [
        'manager_approved_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function currentBooking()
    {
        return $this->belongsTo(Booking::class, 'current_booking_id');
    }

    public function currentRoom()
    {
        return $this->belongsTo(Room::class, 'current_room_id');
    }

    public function requestedRoom()
    {
        return $this->belongsTo(Room::class, 'requested_room_id');
    }

    public function requestedBed()
    {
        return $this->belongsTo(Bed::class, 'requested_bed_id');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }
}

