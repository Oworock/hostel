<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Addon extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'package_path',
        'extracted_path',
        'is_active',
        'manifest',
        'uploaded_by',
        'installed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'manifest' => 'array',
        'installed_at' => 'datetime',
    ];

    public static function isActive(string $slug): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->exists();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    protected static function booted(): void
    {
        static::deleting(function (Addon $addon): void {
            if (!empty($addon->package_path)) {
                Storage::disk('local')->delete($addon->package_path);
            }

            if (!empty($addon->extracted_path)) {
                Storage::disk('local')->deleteDirectory($addon->extracted_path);
            }
        });
    }
}
