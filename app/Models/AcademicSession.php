<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSession extends Model
{
    protected $fillable = [
        'session_name',
        'start_year',
        'end_year',
        'is_active',
    ];

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }
}
