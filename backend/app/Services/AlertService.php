<?php

namespace App\Services;

use App\Models\AcademicAlert;
use App\Models\Enrollment;
use App\Models\RiskPrediction;

class AlertService
{
    public function generateFromPrediction(RiskPrediction $prediction): ?AcademicAlert
    {
        $enrollment = $prediction->enrollment()->with(['group.subject', 'group.teacher.user', 'student'])->first();

        if (!in_array($prediction->risk_level, ['alto', 'medio'])) {
            return null;
        }

        $type = $prediction->risk_level === 'alto' ? 'riesgo_alto' : 'riesgo_medio';
        $studentName = $enrollment->student->user->name;
        $subjectName = $enrollment->group->subject->name;

        $message = $this->buildMessage($prediction, $studentName, $subjectName);
        $notifiedTo = $this->getNotifyList($enrollment);

        return AcademicAlert::create([
            'prediction_id' => $prediction->id,
            'student_id'    => $enrollment->student_id,
            'type'          => $type,
            'message'       => $message,
            'status'        => 'activa',
            'notified_to'   => $notifiedTo,
        ]);
    }

    public function generateAttendanceAlert(Enrollment $enrollment): ?AcademicAlert
    {
        $attendancePct = $enrollment->getAttendancePercentage();
        if ($attendancePct >= 80) return null;

        $latestPrediction = $enrollment->latestPrediction;
        if (!$latestPrediction) return null;

        $studentName = $enrollment->student->user->name;
        $subjectName = $enrollment->group->subject->name;

        return AcademicAlert::create([
            'prediction_id' => $latestPrediction->id,
            'student_id'    => $enrollment->student_id,
            'type'          => 'asistencia',
            'message'       => "El estudiante {$studentName} tiene {$attendancePct}% de asistencia en {$subjectName}. Asistencia mínima requerida: 80%.",
            'status'        => 'activa',
            'notified_to'   => $this->getNotifyList($enrollment),
        ]);
    }

    private function buildMessage(RiskPrediction $prediction, string $studentName, string $subjectName): string
    {
        $pct = $prediction->getRiskPercentage();
        $level = strtoupper($prediction->risk_level);

        return "ALERTA {$level}: El estudiante {$studentName} presenta {$pct}% de probabilidad de reprobación en {$subjectName}. " .
               "Promedio actual: {$prediction->avg_grade}, Asistencia: {$prediction->attendance_pct}%, " .
               "Materias reprobadas históricamente: {$prediction->failed_subjects}. " .
               "Se recomienda intervención académica inmediata.";
    }

    private function getNotifyList(Enrollment $enrollment): array
    {
        $ids = [];
        $teacherUserId = $enrollment->group->teacher->user_id ?? null;
        if ($teacherUserId) $ids[] = $teacherUserId;
        return $ids;
    }
}
