<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelChangeRequest extends Model
{
    protected $fillable = [
        'student_id',
        'current_hostel_id',
        'requested_hostel_id',
        'status',
        'reason',
        'manager_approved_by',
        'manager_approved_at',
        'manager_note',
        'admin_approved_by',
        'admin_approved_at',
        'admin_note',
    ];

    protected $casts = [
        'manager_approved_at' => 'datetime',
        'admin_approved_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function currentHostel()
    {
        return $this->belongsTo(Hostel::class, 'current_hostel_id');
    }

    public function requestedHostel()
    {
        return $this->belongsTo(Hostel::class, 'requested_hostel_id');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }
}
