<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetSubscriptionNotificationLog extends Model
{
    protected $fillable = [
        'asset_subscription_id',
        'user_id',
        'days_remaining',
        'notified_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(AssetSubscription::class, 'asset_subscription_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
