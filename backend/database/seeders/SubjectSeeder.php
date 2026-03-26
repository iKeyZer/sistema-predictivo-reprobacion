<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Semestre 1
            ['code' => 'ISC-101', 'name' => 'Cálculo Diferencial', 'semester' => 1, 'credits' => 5, 'historical_difficulty' => 45.00],
            ['code' => 'ISC-102', 'name' => 'Fundamentos de Programación', 'semester' => 1, 'credits' => 5, 'historical_difficulty' => 28.00],
            ['code' => 'ISC-103', 'name' => 'Álgebra Lineal', 'semester' => 1, 'credits' => 4, 'historical_difficulty' => 38.00],
            ['code' => 'ISC-104', 'name' => 'Taller de Ética', 'semester' => 1, 'credits' => 4, 'historical_difficulty' => 10.00],
            // Semestre 2
            ['code' => 'ISC-201', 'name' => 'Cálculo Integral', 'semester' => 2, 'credits' => 5, 'historical_difficulty' => 48.00],
            ['code' => 'ISC-202', 'name' => 'Programación Orientada a Objetos', 'semester' => 2, 'credits' => 5, 'historical_difficulty' => 22.00],
            ['code' => 'ISC-203', 'name' => 'Matemáticas Discretas', 'semester' => 2, 'credits' => 4, 'historical_difficulty' => 42.00],
            // Semestre 3
            ['code' => 'ISC-301', 'name' => 'Cálculo Vectorial', 'semester' => 3, 'credits' => 5, 'historical_difficulty' => 50.00],
            ['code' => 'ISC-302', 'name' => 'Estructura de Datos', 'semester' => 3, 'credits' => 5, 'historical_difficulty' => 35.00],
            ['code' => 'ISC-303', 'name' => 'Probabilidad y Estadística', 'semester' => 3, 'credits' => 5, 'historical_difficulty' => 40.00],
            // Semestre 4
            ['code' => 'ISC-401', 'name' => 'Análisis y Diseño de Algoritmos', 'semester' => 4, 'credits' => 5, 'historical_difficulty' => 38.00],
            ['code' => 'ISC-402', 'name' => 'Bases de Datos', 'semester' => 4, 'credits' => 5, 'historical_difficulty' => 25.00],
            ['code' => 'ISC-403', 'name' => 'Sistemas Operativos', 'semester' => 4, 'credits' => 5, 'historical_difficulty' => 32.00],
            // Semestre 5
            ['code' => 'ISC-501', 'name' => 'Redes de Computadoras', 'semester' => 5, 'credits' => 5, 'historical_difficulty' => 30.00],
            ['code' => 'ISC-502', 'name' => 'Ingeniería de Software', 'semester' => 5, 'credits' => 5, 'historical_difficulty' => 18.00],
            ['code' => 'ISC-503', 'name' => 'Lenguajes y Autómatas I', 'semester' => 5, 'credits' => 5, 'historical_difficulty' => 44.00],
            // Semestre 6
            ['code' => 'ISC-601', 'name' => 'Programación Web', 'semester' => 6, 'credits' => 5, 'historical_difficulty' => 15.00],
            ['code' => 'ISC-602', 'name' => 'Inteligencia Artificial', 'semester' => 6, 'credits' => 5, 'historical_difficulty' => 35.00],
            ['code' => 'ISC-603', 'name' => 'Lenguajes y Autómatas II', 'semester' => 6, 'credits' => 5, 'historical_difficulty' => 42.00],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
