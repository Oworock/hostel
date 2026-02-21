<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'bed_id',
        'check_in_date',
        'check_out_date',
        'semester_id',
        'academic_session_id',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function outstandingAmount(): float
    {
        $total = (float) ($this->total_amount ?? 0);
        return max(0, $total - $this->paidAmount());
    }

    public function isFullyPaid(): bool
    {
        return $this->outstandingAmount() <= 0;
    }

    public function isActiveNow(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $today = now()->toDateString();
        $checkIn = (string) optional($this->check_in_date)->toDateString();
        $checkOut = optional($this->check_out_date)?->toDateString();

        if ($checkIn === '' || $checkIn > $today) {
            return false;
        }

        if ($checkOut !== null && $checkOut < $today) {
            return false;
        }

        return true;
    }
}
