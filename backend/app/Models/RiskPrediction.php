<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id', 'risk_level', 'risk_probability',
        'avg_grade', 'attendance_pct', 'failed_subjects',
        'academic_load', 'model_version', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'risk_probability' => 'float',
            'avg_grade' => 'float',
            'attendance_pct' => 'float',
        ];
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function alerts()
    {
        return $this->hasMany(AcademicAlert::class, 'prediction_id');
    }

    public function getRiskBadgeClass(): string
    {
        return match($this->risk_level) {
            'alto' => 'bg-danger',
            'medio' => 'bg-warning text-dark',
            'bajo' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function getRiskLabel(): string
    {
        return match($this->risk_level) {
            'alto' => 'Riesgo Alto',
            'medio' => 'Riesgo Medio',
            'bajo' => 'Riesgo Bajo',
            default => 'Sin datos',
        };
    }

    public function getRiskPercentage(): int
    {
        return (int)round($this->risk_probability * 100);
    }
}
