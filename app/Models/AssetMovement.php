<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetMovement extends Model
{
    protected $fillable = [
        'asset_id',
        'from_hostel_id',
        'to_hostel_id',
        'requested_by',
        'receiving_manager_id',
        'request_note',
        'status',
        'receiving_manager_decided_at',
        'receiving_manager_decided_by',
        'receiving_manager_note',
        'admin_decided_at',
        'admin_decided_by',
        'admin_note',
        'moved_at',
    ];

    protected $casts = [
        'receiving_manager_decided_at' => 'datetime',
        'admin_decided_at' => 'datetime',
        'moved_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function fromHostel()
    {
        return $this->belongsTo(Hostel::class, 'from_hostel_id');
    }

    public function toHostel()
    {
        return $this->belongsTo(Hostel::class, 'to_hostel_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function receivingManager()
    {
        return $this->belongsTo(User::class, 'receiving_manager_id');
    }

    public function receivingDecisionBy()
    {
        return $this->belongsTo(User::class, 'receiving_manager_decided_by');
    }

    public function adminDecisionBy()
    {
        return $this->belongsTo(User::class, 'admin_decided_by');
    }
}
