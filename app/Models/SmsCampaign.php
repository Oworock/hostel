<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsCampaign extends Model
{
    protected $fillable = ['admin_id', 'name', 'message', 'target', 'target_users', 'status', 'scheduled_at', 'sent_at', 'total_recipients', 'successful', 'failed'];
    
    protected $casts = [
        'target_users' => 'json',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
