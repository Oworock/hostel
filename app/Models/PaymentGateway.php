<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = ['name', 'public_key', 'secret_key', 'is_active', 'transaction_fee'];
    
    protected $casts = [
        'is_active' => 'boolean',
        'transaction_fee' => 'decimal:2',
    ];
}
