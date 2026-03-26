<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin del sistema
        User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@sistema.edu.mx',
            'password' => Hash::make('Admin123!'),
            'role' => 'admin',
        ]);

        // Coordinador académico
        User::create([
            'name' => 'Dr. Coordinador Académico',
            'email' => 'coordinador@sistema.edu.mx',
            'password' => Hash::make('Coord123!'),
            'role' => 'coordinador',
        ]);

        // Tutor académico
        User::create([
            'name' => 'Lic. Ana Tutor González',
            'email' => 'tutor@sistema.edu.mx',
            'password' => Hash::make('Tutor123!'),
            'role' => 'tutor',
        ]);

        // Docentes
        $docentes = [
            ['name' => 'Dr. Carlos Ramírez López', 'email' => 'docente1@sistema.edu.mx', 'employee' => 'DOC001'],
            ['name' => 'Mtra. Laura Sánchez Pérez', 'email' => 'docente2@sistema.edu.mx', 'employee' => 'DOC002'],
            ['name' => 'Ing. Roberto Torres Cruz', 'email' => 'docente3@sistema.edu.mx', 'employee' => 'DOC003'],
        ];

        foreach ($docentes as $d) {
            $user = User::create([
                'name' => $d['name'],
                'email' => $d['email'],
                'password' => Hash::make('Docente123!'),
                'role' => 'docente',
            ]);
            Teacher::create([
                'user_id' => $user->id,
                'employee_number' => $d['employee'],
                'department' => 'Departamento de Sistemas y Computación',
            ]);
        }

        // Estudiantes demo
        $estudiantes = [
            ['name' => 'Juan García Mendoza', 'email' => 'juan@estudiante.edu.mx', 'control' => '20240001', 'semester' => 4],
            ['name' => 'María López Hernández', 'email' => 'maria@estudiante.edu.mx', 'control' => '20240002', 'semester' => 3],
            ['name' => 'Pedro Martínez Ruiz', 'email' => 'pedro@estudiante.edu.mx', 'control' => '20240003', 'semester' => 5],
            ['name' => 'Ana Torres Flores', 'email' => 'ana@estudiante.edu.mx', 'control' => '20240004', 'semester' => 2],
            ['name' => 'Carlos Jiménez Vega', 'email' => 'carlos@estudiante.edu.mx', 'control' => '20230001', 'semester' => 6],
            ['name' => 'Laura Rodríguez Castillo', 'email' => 'laura@estudiante.edu.mx', 'control' => '20230002', 'semester' => 5],
            ['name' => 'Miguel Sánchez Ortiz', 'email' => 'miguel@estudiante.edu.mx', 'control' => '20230003', 'semester' => 4],
            ['name' => 'Sofia Pérez Morales', 'email' => 'sofia@estudiante.edu.mx', 'control' => '20230004', 'semester' => 3],
        ];

        foreach ($estudiantes as $e) {
            $user = User::create([
                'name' => $e['name'],
                'email' => $e['email'],
                'password' => Hash::make('Estudiante123!'),
                'role' => 'estudiante',
            ]);
            Student::create([
                'user_id' => $user->id,
                'control_number' => $e['control'],
                'career' => 'Ingeniería en Sistemas Computacionales',
                'semester' => $e['semester'],
                'enrollment_year' => (int)substr($e['control'], 0, 4),
                'status' => 'activo',
            ]);
        }
    }
}
