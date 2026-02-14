<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'guardian_name',
        'guardian_phone',
    ];

    public function allocations()
    {
        return $this->hasMany(Allocation::class);
    }
}
