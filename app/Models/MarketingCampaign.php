<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingCampaign extends Model
{
    protected $fillable = ['admin_id', 'name', 'description', 'type', 'content', 'status', 'starts_at', 'ends_at', 'impressions', 'clicks'];
    
    protected $casts = [
        'content' => 'json',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
