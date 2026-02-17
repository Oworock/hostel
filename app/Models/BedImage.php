<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedImage extends Model
{
    protected $fillable = ['bed_id', 'image_path', 'sort_order'];

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }
}

