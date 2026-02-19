<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AssetSubscription extends Model
{
    protected $fillable = [
        'hostel_id',
        'name',
        'service_type',
        'provider',
        'reference',
        'start_date',
        'expires_at',
        'billing_cycle',
        'cost',
        'status',
        'auto_renew',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expires_at' => 'date',
        'cost' => 'decimal:2',
        'auto_renew' => 'boolean',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notificationLogs()
    {
        return $this->hasMany(AssetSubscriptionNotificationLog::class);
    }

    public function daysRemaining(): int
    {
        return Carbon::now()->startOfDay()->diffInDays($this->expires_at, false);
    }
}
