<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id')->where('role', 'student');
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }
}
