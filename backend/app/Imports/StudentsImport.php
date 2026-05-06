<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToCollection, WithHeadingRow
{
    public array $errors  = [];
    public int   $created = 0;
    public int   $skipped = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum        = $index + 2;
            $controlNumber = trim($row['numero_control'] ?? '');
            $nombre        = trim($row['nombre']         ?? '');
            $apellidos     = trim($row['apellidos']      ?? '');

            if ($controlNumber === '' || $nombre === '') {
                $this->errors[] = "Fila {$rowNum}: numero_control y nombre son requeridos.";
                $this->skipped++;
                continue;
            }

            // Skip if student already exists
            if (Student::where('control_number', $controlNumber)->exists()) {
                $this->errors[] = "Fila {$rowNum}: El número de control '{$controlNumber}' ya existe (omitido).";
                $this->skipped++;
                continue;
            }

            $email    = trim($row['email'] ?? '') ?: "{$controlNumber}@itsc.edu.mx";
            $carrera  = trim($row['carrera']  ?? 'ISC');
            $semestre = isset($row['semestre']) && $row['semestre'] !== '' ? (int) $row['semestre'] : 1;

            // Skip if email already taken
            if (User::where('email', $email)->exists()) {
                $this->errors[] = "Fila {$rowNum}: El email '{$email}' ya está en uso (omitido).";
                $this->skipped++;
                continue;
            }

            $user = User::create([
                'name'     => "{$nombre} {$apellidos}",
                'email'    => $email,
                'password' => Hash::make($controlNumber), // default password = control number
                'role'     => 'estudiante',
                'active'   => true,
            ]);

            Student::create([
                'user_id'          => $user->id,
                'control_number'   => $controlNumber,
                'career'           => $carrera,
                'semester'         => $semestre,
                'enrollment_year'  => now()->year,
                'status'           => 'activo',
            ]);

            $this->created++;
        }
    }
}
