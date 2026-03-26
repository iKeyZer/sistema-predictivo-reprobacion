<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'group_id', 'status', 'final_grade',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function partialGrades()
    {
        return $this->hasMany(PartialGrade::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function riskPredictions()
    {
        return $this->hasMany(RiskPrediction::class);
    }

    public function latestPrediction()
    {
        return $this->hasOne(RiskPrediction::class)->latestOfMany('generated_at');
    }

    public function getAverageGrade(): float
    {
        $avg = $this->partialGrades()->avg('grade');
        return round($avg ?? 0, 2);
    }

    public function getAttendancePercentage(): float
    {
        $total = $this->attendance()->count();
        if ($total === 0) return 100.0;
        $present = $this->attendance()->whereIn('status', ['presente', 'justificado'])->count();
        return round(($present / $total) * 100, 2);
    }
}
