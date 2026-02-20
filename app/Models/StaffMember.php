<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffMember extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'employee_code',
        'department',
        'category',
        'job_title',
        'source_role',
        'registered_via_link',
        'base_salary',
        'joined_on',
        'status',
        'is_general_staff',
        'assigned_hostel_id',
        'approved_by',
        'approved_at',
        'address',
        'profile_image',
        'id_card_path',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'joined_on' => 'date',
        'registered_via_link' => 'boolean',
        'is_general_staff' => 'boolean',
        'approved_at' => 'datetime',
        'meta' => 'array',
    ];

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedHostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class, 'assigned_hostel_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
