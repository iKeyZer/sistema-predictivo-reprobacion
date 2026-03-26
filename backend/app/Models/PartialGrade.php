<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartialGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id', 'partial_number', 'grade',
        'activities_grade', 'participation_grade', 'recorded_by',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
