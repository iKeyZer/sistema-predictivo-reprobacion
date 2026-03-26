<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutoringRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'tutor_id', 'alert_id', 'session_date',
        'type', 'notes', 'outcome',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function alert()
    {
        return $this->belongsTo(AcademicAlert::class, 'alert_id');
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'tutoria' => 'Tutoría',
            'asesoria' => 'Asesoría Académica',
            'seguimiento' => 'Seguimiento',
            default => 'Sesión',
        };
    }
}
