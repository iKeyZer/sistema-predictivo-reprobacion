<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'control_number', 'career', 'semester',
        'enrollment_year', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function academicHistory()
    {
        return $this->hasMany(AcademicHistory::class);
    }

    public function riskPredictions()
    {
        return $this->hasManyThrough(RiskPrediction::class, Enrollment::class);
    }

    public function alerts()
    {
        return $this->hasMany(AcademicAlert::class);
    }

    public function tutoringRecords()
    {
        return $this->hasMany(TutoringRecord::class);
    }

    public function latestPrediction()
    {
        return $this->hasManyThrough(RiskPrediction::class, Enrollment::class)
            ->latest('generated_at')
            ->first();
    }

    public function getFailedSubjectsCount(): int
    {
        return $this->academicHistory()->where('status', 'reprobado')->count();
    }

    public function getCurrentEnrollmentsCount(): int
    {
        return $this->enrollments()->where('status', 'cursando')->count();
    }

    public function getGeneralAverage(): float
    {
        $history = $this->academicHistory()->where('status', 'aprobado')->avg('grade');
        return round($history ?? 0, 2);
    }
}
