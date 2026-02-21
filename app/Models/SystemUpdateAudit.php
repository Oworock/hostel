<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemUpdateAudit extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'package_name',
        'package_path',
        'version',
        'files_total',
        'files_applied',
        'details',
        'applied_at',
    ];

    protected $casts = [
        'details' => 'array',
        'applied_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

