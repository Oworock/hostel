<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PopupAnnouncement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'target',
        'is_active',
        'start_at',
        'end_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function seenByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'popup_announcement_user')
            ->withPivot(['seen_at'])
            ->withTimestamps();
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            });
    }
}

