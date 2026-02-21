<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReferralAgent extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'password',
        'referral_code',
        'is_active',
        'commission_type',
        'commission_value',
        'total_earned',
        'total_paid',
        'balance',
        'last_referred_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'commission_value' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'last_referred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ReferralAgent $agent): void {
            if (trim((string) $agent->referral_code) === '') {
                do {
                    $code = strtoupper(Str::random(8));
                } while (self::where('referral_code', $code)->exists());

                $agent->referral_code = $code;
            }
        });
    }

    public function referredStudents(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_referral_agent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(ReferralPayoutRequest::class);
    }

    public function referralUrl(): string
    {
        return route('referrals.capture', ['code' => $this->referral_code]);
    }
}
