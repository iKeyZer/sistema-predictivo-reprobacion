<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\PartialGrade;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GradesImport implements ToCollection, WithHeadingRow
{
    public array $errors  = [];
    public int   $updated = 0;
    public int   $skipped = 0;

    public function __construct(
        private int $groupId,
        private int $teacherId
    ) {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 because row 1 is header

            $controlNumber = trim($row['numero_control'] ?? '');
            if ($controlNumber === '') {
                continue; // skip blank rows
            }

            // Find student
            $student = Student::where('control_number', $controlNumber)->first();
            if (!$student) {
                $this->errors[] = "Fila {$rowNum}: No se encontró el alumno con número de control '{$controlNumber}'.";
                $this->skipped++;
                continue;
            }

            // Find enrollment in this group
            $enrollment = Enrollment::where('student_id', $student->id)
                ->where('group_id', $this->groupId)
                ->first();

            if (!$enrollment) {
                $this->errors[] = "Fila {$rowNum}: El alumno '{$controlNumber}' no está inscrito en este grupo.";
                $this->skipped++;
                continue;
            }

            // Import partial grades
            foreach ([1, 2, 3] as $partial) {
                $key = "parcial_{$partial}";
                if (isset($row[$key]) && $row[$key] !== '' && $row[$key] !== null) {
                    $grade = (float) $row[$key];
                    if ($grade < 0 || $grade > 100) {
                        $this->errors[] = "Fila {$rowNum}: La calificación del parcial {$partial} debe estar entre 0 y 100.";
                        continue;
                    }
                    PartialGrade::updateOrCreate(
                        ['enrollment_id' => $enrollment->id, 'partial_number' => $partial],
                        ['grade' => $grade, 'recorded_by' => $this->teacherId]
                    );
                }
            }

            // Import attendance
            $totalClases   = isset($row['total_clases'])    ? (int) $row['total_clases']    : null;
            $clasesAsist   = isset($row['clases_asistidas']) ? (int) $row['clases_asistidas'] : null;

            if ($totalClases !== null && $clasesAsist !== null && $totalClases > 0) {
                if ($clasesAsist > $totalClases) {
                    $this->errors[] = "Fila {$rowNum}: Las clases asistidas ({$clasesAsist}) no pueden ser mayores al total ({$totalClases}).";
                } else {
                    // Remove old synthetic import records and recreate
                    $enrollment->attendance()->delete();

                    $ausentes  = $totalClases - $clasesAsist;
                    $baseDate  = Carbon::now()->subWeeks((int) ceil($totalClases / 3));

                    $presentes = 0;
                    $ausCount  = 0;
                    for ($i = 0; $i < $totalClases; $i++) {
                        $date   = $baseDate->copy()->addDays($i * 2); // every 2 days
                        $status = ($presentes < $clasesAsist) ? 'presente' : 'ausente';

                        if ($status === 'presente') $presentes++;
                        else                        $ausCount++;

                        Attendance::create([
                            'enrollment_id' => $enrollment->id,
                            'date'          => $date->toDateString(),
                            'status'        => $status,
                            'recorded_by'   => $this->teacherId,
                        ]);
                    }
                }
            }

            $this->updated++;
        }
    }
}
