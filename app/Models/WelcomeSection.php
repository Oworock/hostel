<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WelcomeSection extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_path',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (WelcomeSection $section): void {
            if (!empty($section->image_path)) {
                Storage::disk('public')->delete($section->image_path);
            }
        });
    }
}
