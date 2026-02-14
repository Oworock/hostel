<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserManagement extends Model
{
    protected $fillable = ['user_id', 'status', 'notes', 'last_login', 'last_activity'];
    
    protected $casts = [
        'last_login' => 'datetime',
        'last_activity' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
