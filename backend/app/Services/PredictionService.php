<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\RiskPrediction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PredictionService
{
    private string $mlServiceUrl;

    public function __construct()
    {
        $this->mlServiceUrl = config('services.ml_service.url', 'http://127.0.0.1:5000');
    }

    public function predict(Enrollment $enrollment): RiskPrediction
    {
        $features = $this->extractFeatures($enrollment);
        $result = $this->callMlService($features);

        return RiskPrediction::create([
            'enrollment_id'    => $enrollment->id,
            'risk_level'       => $result['risk_level'],
            'risk_probability' => $result['probability'],
            'avg_grade'        => $features['avg_grade'],
            'attendance_pct'   => $features['attendance_pct'],
            'failed_subjects'  => $features['failed_subjects'],
            'academic_load'    => $features['academic_load'],
            'model_version'    => $result['model_version'] ?? 'heuristic',
            'generated_at'     => now(),
        ]);
    }

    public function predictAll(int $groupId): array
    {
        $enrollments = Enrollment::with(['student', 'partialGrades', 'attendance'])
            ->where('group_id', $groupId)
            ->where('status', 'cursando')
            ->get();

        $predictions = [];
        foreach ($enrollments as $enrollment) {
            $predictions[] = $this->predict($enrollment);
        }
        return $predictions;
    }

    public function trainModel(array $trainingData): array
    {
        try {
            $response = Http::timeout(120)
                ->post("{$this->mlServiceUrl}/train", [
                    'data'   => $trainingData['features'],
                    'labels' => $trainingData['labels'],
                ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('ML train error: ' . $e->getMessage());
        }

        return ['success' => false, 'error' => 'No se pudo conectar al servicio ML'];
    }

    private function extractFeatures(Enrollment $enrollment): array
    {
        $student = $enrollment->student;
        $partialGrades = $enrollment->partialGrades()->orderBy('partial_number')->get();
        $avgGrade = $enrollment->getAverageGrade();
        $attendancePct = $enrollment->getAttendancePercentage();
        $failedSubjects = $student->getFailedSubjectsCount();
        $academicLoad = $student->getCurrentEnrollmentsCount();
        $subjectDifficulty = $enrollment->group->subject->historical_difficulty ?? 0;

        return [
            'avg_grade'          => $avgGrade,
            'attendance_pct'     => $attendancePct,
            'failed_subjects'    => $failedSubjects,
            'academic_load'      => $academicLoad,
            'subject_difficulty' => $subjectDifficulty,
            'partial1'           => $partialGrades->where('partial_number', 1)->first()?->grade ?? null,
            'partial2'           => $partialGrades->where('partial_number', 2)->first()?->grade ?? null,
            'partial3'           => $partialGrades->where('partial_number', 3)->first()?->grade ?? null,
        ];
    }

    private function callMlService(array $features): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->mlServiceUrl}/predict", $features);
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('ML service unavailable, using heuristic: ' . $e->getMessage());
        }

        return $this->heuristicFallback($features);
    }

    private function heuristicFallback(array $features): array
    {
        $avg = $features['avg_grade'];
        $att = $features['attendance_pct'];
        $failed = $features['failed_subjects'];

        if (($avg < 60 && $att < 70) || $failed >= 3 || ($avg < 55)) {
            return ['risk_level' => 'alto', 'probability' => 0.85, 'model_version' => 'heuristic'];
        }

        if ($avg < 70 || $att < 80 || $failed >= 1) {
            return ['risk_level' => 'medio', 'probability' => 0.50, 'model_version' => 'heuristic'];
        }

        return ['risk_level' => 'bajo', 'probability' => 0.15, 'model_version' => 'heuristic'];
    }
}
