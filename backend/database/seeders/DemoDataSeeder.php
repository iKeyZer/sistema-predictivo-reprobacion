<?php

namespace Database\Seeders;

use App\Models\AcademicAlert;
use App\Models\AcademicHistory;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\PartialGrade;
use App\Models\RiskPrediction;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TutoringRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    private string $period = '2026-A';

    public function run(): void
    {
        // ── Recuperar usuarios ya seedeados ──────────────────────────────
        $doc1 = Teacher::whereHas('user', fn($q) => $q->where('email', 'docente1@sistema.edu.mx'))->first();
        $doc2 = Teacher::whereHas('user', fn($q) => $q->where('email', 'docente2@sistema.edu.mx'))->first();
        $doc3 = Teacher::whereHas('user', fn($q) => $q->where('email', 'docente3@sistema.edu.mx'))->first();
        $tutor = User::where('email', 'tutor@sistema.edu.mx')->first();

        // ── Recuperar asignaturas por código ─────────────────────────────
        $isc201 = Subject::where('code', 'ISC-201')->first(); // Cálculo Integral       (sem 2)
        $isc301 = Subject::where('code', 'ISC-301')->first(); // Cálculo Vectorial      (sem 3)
        $isc302 = Subject::where('code', 'ISC-302')->first(); // Estructura de Datos    (sem 3)
        $isc401 = Subject::where('code', 'ISC-401')->first(); // Análisis de Algoritmos (sem 4)
        $isc402 = Subject::where('code', 'ISC-402')->first(); // Bases de Datos         (sem 4)
        $isc501 = Subject::where('code', 'ISC-501')->first(); // Redes de Computadoras  (sem 5)
        $isc601 = Subject::where('code', 'ISC-601')->first(); // Programación Web       (sem 6)

        // ── Recuperar estudiantes ────────────────────────────────────────
        $juan    = Student::whereHas('user', fn($q) => $q->where('email', 'juan@estudiante.edu.mx'))->first();
        $maria   = Student::whereHas('user', fn($q) => $q->where('email', 'maria@estudiante.edu.mx'))->first();
        $pedro   = Student::whereHas('user', fn($q) => $q->where('email', 'pedro@estudiante.edu.mx'))->first();
        $ana     = Student::whereHas('user', fn($q) => $q->where('email', 'ana@estudiante.edu.mx'))->first();
        $carlos  = Student::whereHas('user', fn($q) => $q->where('email', 'carlos@estudiante.edu.mx'))->first();
        $laura   = Student::whereHas('user', fn($q) => $q->where('email', 'laura@estudiante.edu.mx'))->first();
        $miguel  = Student::whereHas('user', fn($q) => $q->where('email', 'miguel@estudiante.edu.mx'))->first();
        $sofia   = Student::whereHas('user', fn($q) => $q->where('email', 'sofia@estudiante.edu.mx'))->first();

        // ── GRUPOS ───────────────────────────────────────────────────────
        $grpAlg  = Group::create(['subject_id' => $isc401->id, 'teacher_id' => $doc1->id, 'school_period' => $this->period, 'group_name' => 'ISC-4A']);
        $grpBD   = Group::create(['subject_id' => $isc402->id, 'teacher_id' => $doc1->id, 'school_period' => $this->period, 'group_name' => 'ISC-4B']);
        $grpVec  = Group::create(['subject_id' => $isc301->id, 'teacher_id' => $doc2->id, 'school_period' => $this->period, 'group_name' => 'ISC-3A']);
        $grpED   = Group::create(['subject_id' => $isc302->id, 'teacher_id' => $doc2->id, 'school_period' => $this->period, 'group_name' => 'ISC-3B']);
        $grpRed  = Group::create(['subject_id' => $isc501->id, 'teacher_id' => $doc3->id, 'school_period' => $this->period, 'group_name' => 'ISC-5A']);
        $grpWeb  = Group::create(['subject_id' => $isc601->id, 'teacher_id' => $doc3->id, 'school_period' => $this->period, 'group_name' => 'ISC-6A']);
        $grpCalc = Group::create(['subject_id' => $isc201->id, 'teacher_id' => $doc1->id, 'school_period' => $this->period, 'group_name' => 'ISC-2A']);

        // ── INSCRIPCIONES + CALIFICACIONES + ASISTENCIA + PREDICCIONES ──
        // Perfiles:
        //   Juan   → RIESGO ALTO  (promedio bajo, asistencia baja, hist. reprobado)
        //   Miguel → RIESGO ALTO  (promedio bajo, asistencia baja)
        //   Carlos → RIESGO ALTO  (promedio muy bajo, asistencia muy baja)
        //   María  → RIESGO MEDIO (promedio regular, asistencia aceptable)
        //   Sofia  → RIESGO MEDIO (promedio regular)
        //   Ana    → RIESGO MEDIO (asistencia < 80%)
        //   Pedro  → RIESGO BAJO  (excelente)
        //   Laura  → RIESGO BAJO  (bueno)

        $enrollments = [
            // [student, group, recordedBy(teacher user_id), [p1,p2,p3], attPct]
            [$juan,   $grpAlg,  $doc1->user_id, [45, 38, 42], 65],
            [$juan,   $grpBD,   $doc1->user_id, [50, 42, null], 70],
            [$miguel, $grpAlg,  $doc1->user_id, [52, 48, 45], 68],
            [$miguel, $grpBD,   $doc1->user_id, [55, 48, null], 72],
            [$maria,  $grpVec,  $doc2->user_id, [62, 58, 65], 78],
            [$maria,  $grpED,   $doc2->user_id, [68, 72, null], 82],
            [$sofia,  $grpVec,  $doc2->user_id, [65, 70, 60], 80],
            [$sofia,  $grpED,   $doc2->user_id, [72, 75, null], 85],
            [$pedro,  $grpRed,  $doc3->user_id, [85, 88, 90], 95],
            [$laura,  $grpRed,  $doc3->user_id, [80, 78, 82], 92],
            [$carlos, $grpWeb,  $doc3->user_id, [40, 52, null], 60],
            [$ana,    $grpCalc, $doc1->user_id, [68, 72, null], 78],
        ];

        $createdEnrollments = [];
        foreach ($enrollments as $data) {
            [$student, $group, $recordedBy, $grades, $attPct] = $data;
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'group_id'   => $group->id,
                'status'     => 'cursando',
            ]);

            // Calificaciones parciales
            foreach ($grades as $i => $grade) {
                if ($grade !== null) {
                    PartialGrade::create([
                        'enrollment_id'  => $enrollment->id,
                        'partial_number' => $i + 1,
                        'grade'          => $grade,
                        'recorded_by'    => $recordedBy,
                    ]);
                }
            }

            // Asistencia (18 clases simuladas, últimas 6 semanas)
            $this->createAttendance($enrollment, $attPct, $recordedBy);

            $createdEnrollments[] = [$enrollment, $student, $grades, $attPct];
        }

        // ── HISTORIAL ACADÉMICO (periodos anteriores) ────────────────────
        $history = [
            // [student, subject_code, period, grade, attempt]
            [$juan,   'ISC-101', '2024-A', 55.0, 1],  // reprobado
            [$juan,   'ISC-201', '2025-A', 48.0, 1],  // reprobado
            [$juan,   'ISC-101', '2024-B', 72.0, 2],  // aprobado 2do intento
            [$miguel, 'ISC-201', '2025-A', 52.0, 1],  // reprobado
            [$miguel, 'ISC-201', '2025-B', 70.0, 2],  // aprobado 2do intento
            [$carlos, 'ISC-401', '2025-B', 60.0, 1],  // reprobado
            [$carlos, 'ISC-501', '2025-B', 58.0, 1],  // reprobado
            [$maria,  'ISC-101', '2024-A', 75.0, 1],  // aprobado
            [$maria,  'ISC-201', '2024-B', 78.0, 1],  // aprobado
            [$pedro,  'ISC-101', '2024-A', 92.0, 1],  // aprobado
            [$pedro,  'ISC-201', '2024-B', 88.0, 1],  // aprobado
            [$pedro,  'ISC-301', '2025-A', 85.0, 1],  // aprobado
            [$laura,  'ISC-101', '2024-A', 82.0, 1],  // aprobado
            [$laura,  'ISC-201', '2024-B', 80.0, 1],  // aprobado
            [$sofia,  'ISC-101', '2024-A', 70.0, 1],  // aprobado
            [$ana,    'ISC-101', '2024-A', 68.0, 1],  // aprobado por mínimo
        ];

        foreach ($history as [$student, $code, $period, $grade, $attempt]) {
            $subject = Subject::where('code', $code)->first();
            AcademicHistory::create([
                'student_id'    => $student->id,
                'subject_id'    => $subject->id,
                'school_period' => $period,
                'grade'         => $grade,
                'status'        => $grade >= 70 ? 'aprobado' : 'reprobado',
                'attempt_number'=> $attempt,
            ]);
        }

        // ── PREDICCIONES Y ALERTAS ───────────────────────────────────────
        foreach ($createdEnrollments as [$enrollment, $student, $grades, $attPct]) {
            $validGrades = array_filter($grades, fn($g) => $g !== null);
            $avgGrade = count($validGrades) > 0 ? array_sum($validGrades) / count($validGrades) : 0;
            $failedCount = AcademicHistory::where('student_id', $student->id)->where('status', 'reprobado')->count();

            [$riskLevel, $probability] = $this->heuristic($avgGrade, $attPct, $failedCount);

            $prediction = RiskPrediction::create([
                'enrollment_id'    => $enrollment->id,
                'risk_level'       => $riskLevel,
                'risk_probability' => $probability,
                'avg_grade'        => round($avgGrade, 2),
                'attendance_pct'   => $attPct,
                'failed_subjects'  => $failedCount,
                'academic_load'    => Enrollment::where('student_id', $student->id)->where('status', 'cursando')->count(),
                'model_version'    => 'heuristic-v1',
                'generated_at'     => now(),
            ]);

            // Crear alerta si riesgo alto o medio
            if (in_array($riskLevel, ['alto', 'medio'])) {
                $type = $riskLevel === 'alto' ? 'riesgo_alto' : 'riesgo_medio';
                $pct  = (int)round($probability * 100);
                $subjectName = $enrollment->group->subject->name;
                $studentName = $student->user->name;
                $teacherUserId = $enrollment->group->teacher->user_id;

                AcademicAlert::create([
                    'prediction_id' => $prediction->id,
                    'student_id'    => $student->id,
                    'type'          => $type,
                    'message'       => "ALERTA " . strtoupper($riskLevel) . ": {$studentName} tiene {$pct}% de probabilidad de reprobar {$subjectName}. Promedio: " . round($avgGrade, 1) . ", Asistencia: {$attPct}%, Materias reprobadas: {$failedCount}.",
                    'status'        => 'activa',
                    'notified_to'   => [$teacherUserId],
                ]);
            }

            // Alerta de asistencia adicional si < 70%
            if ($attPct < 70) {
                $subjectName = $enrollment->group->subject->name;
                $studentName = $student->user->name;
                $teacherUserId = $enrollment->group->teacher->user_id;

                AcademicAlert::create([
                    'prediction_id' => $prediction->id,
                    'student_id'    => $student->id,
                    'type'          => 'asistencia',
                    'message'       => "{$studentName} tiene solo {$attPct}% de asistencia en {$subjectName}. Riesgo de reprobar por inasistencias (mínimo requerido: 80%).",
                    'status'        => 'activa',
                    'notified_to'   => [$teacherUserId],
                ]);
            }
        }

        // ── TUTORÍAS ─────────────────────────────────────────────────────
        $alertJuan   = AcademicAlert::where('student_id', $juan->id)->where('type', 'riesgo_alto')->first();
        $alertMiguel = AcademicAlert::where('student_id', $miguel->id)->where('type', 'riesgo_alto')->first();
        $alertCarlos = AcademicAlert::where('student_id', $carlos->id)->where('type', 'riesgo_alto')->first();

        TutoringRecord::create([
            'student_id'   => $juan->id,
            'tutor_id'     => $tutor->id,
            'alert_id'     => $alertJuan?->id,
            'session_date' => Carbon::now()->subDays(10)->toDateString(),
            'type'         => 'tutoria',
            'notes'        => 'El estudiante presenta dificultades en comprensión de algoritmos recursivos y análisis de complejidad. Se trabajó con ejemplos prácticos y se asignaron ejercicios adicionales.',
            'outcome'      => 'Estudiante comprometido a repasar material. Próxima sesión en 1 semana.',
        ]);

        TutoringRecord::create([
            'student_id'   => $miguel->id,
            'tutor_id'     => $tutor->id,
            'alert_id'     => $alertMiguel?->id,
            'session_date' => Carbon::now()->subDays(5)->toDateString(),
            'type'         => 'asesoria',
            'notes'        => 'Se identificaron problemas de organización en el estudio. El estudiante trabaja de tiempo parcial, lo que afecta su dedicación académica. Se estableció un plan de estudio semanal.',
            'outcome'      => 'Se recomienda reducción de carga laboral. Seguimiento en 2 semanas.',
        ]);

        TutoringRecord::create([
            'student_id'   => $carlos->id,
            'tutor_id'     => $tutor->id,
            'alert_id'     => $alertCarlos?->id,
            'session_date' => Carbon::now()->subDays(3)->toDateString(),
            'type'         => 'seguimiento',
            'notes'        => 'Estudiante en riesgo crítico. Múltiples materias reprobadas en periodos anteriores. Se analizaron opciones: baja temporal, cambio de horario o apoyo psicopedagógico.',
            'outcome'      => 'Canalizado a departamento de psicopedagogía. Alerta activa bajo seguimiento.',
        ]);

        // Marcar alerta de Juan como atendida tras la tutoría
        if ($alertJuan) {
            $alertJuan->update(['status' => 'atendida']);
        }

        $this->command->info('✓ Datos demo cargados: grupos, inscripciones, calificaciones, asistencia, predicciones, alertas y tutorías.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function heuristic(float $avg, float $att, int $failed): array
    {
        if ($avg < 55 || ($avg < 60 && $att < 70) || $failed >= 3) {
            return ['alto', 0.85];
        }
        if ($avg < 70 || $att < 80 || $failed >= 1) {
            return ['medio', 0.52];
        }
        return ['bajo', 0.14];
    }

    private function createAttendance(Enrollment $enrollment, int $pct, int $recordedBy): void
    {
        // Generar 18 fechas de clase (lunes, miércoles, viernes de las últimas 6 semanas)
        $classDays = [];
        $date = Carbon::now()->startOfWeek()->subWeeks(6);
        while (count($classDays) < 18) {
            if (in_array($date->dayOfWeek, [1, 3, 5])) { // lun, mié, vie
                $classDays[] = $date->toDateString();
            }
            $date->addDay();
        }

        $present = (int)round(count($classDays) * $pct / 100);
        foreach ($classDays as $i => $day) {
            Attendance::create([
                'enrollment_id' => $enrollment->id,
                'date'          => $day,
                'status'        => $i < $present ? 'presente' : 'ausente',
                'recorded_by'   => $recordedBy,
            ]);
        }
    }
}
