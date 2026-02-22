<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralPayoutRequest extends Model
{
    protected $fillable = [
        'referral_agent_id',
        'amount',
        'bank_name',
        'account_name',
        'account_number',
        'status',
        'note',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(ReferralAgent::class, 'referral_agent_id');
    }
}

