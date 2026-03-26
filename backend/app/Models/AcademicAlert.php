<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'prediction_id', 'student_id', 'type', 'message', 'status', 'notified_to',
    ];

    protected function casts(): array
    {
        return [
            'notified_to' => 'array',
        ];
    }

    public function prediction()
    {
        return $this->belongsTo(RiskPrediction::class, 'prediction_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function tutoringRecords()
    {
        return $this->hasMany(TutoringRecord::class, 'alert_id');
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'riesgo_alto' => 'Riesgo Alto de Reprobación',
            'riesgo_medio' => 'Riesgo Medio de Reprobación',
            'asistencia' => 'Problema de Asistencia',
            'calificacion' => 'Calificación Baja',
            default => 'Alerta Académica',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'activa' => 'bg-danger',
            'atendida' => 'bg-success',
            'descartada' => 'bg-secondary',
            default => 'bg-warning',
        };
    }
}
