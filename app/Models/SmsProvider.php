<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsProvider extends Model
{
    protected $fillable = ['name', 'api_key', 'api_secret', 'sender_id', 'is_active', 'config'];
    
    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'json',
    ];
}
