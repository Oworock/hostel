<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetIssue extends Model
{
    protected $fillable = [
        'asset_id',
        'hostel_id',
        'reported_by',
        'title',
        'description',
        'priority',
        'status',
        'resolved_at',
        'resolution_note',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
