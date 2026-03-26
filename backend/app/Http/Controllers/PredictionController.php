<?php

namespace App\Http\Controllers;

use App\Models\AcademicHistory;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\RiskPrediction;
use App\Services\AlertService;
use App\Services\PredictionService;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function __construct(
        private PredictionService $predictionService,
        private AlertService $alertService
    ) {}

    public function index()
    {
        $predictions = RiskPrediction::with(['enrollment.student.user', 'enrollment.group.subject'])
            ->latest('generated_at')
            ->paginate(20);

        return view('predictions.index', compact('predictions'));
    }

    public function myRisk()
    {
        $student = auth()->user()->student;
        $enrollments = Enrollment::with(['group.subject', 'latestPrediction', 'partialGrades'])
            ->where('student_id', $student->id)
            ->where('status', 'cursando')
            ->get();

        $recommendations = $this->buildRecommendations($enrollments);

        return view('predictions.my_risk', compact('enrollments', 'recommendations'));
    }

    public function groupRisk(Group $group)
    {
        $enrollments = Enrollment::with(['student.user', 'latestPrediction', 'partialGrades'])
            ->where('group_id', $group->id)
            ->where('status', 'cursando')
            ->get()
            ->sortByDesc(fn($e) => $e->latestPrediction?->risk_probability ?? 0);

        return view('predictions.group_risk', compact('group', 'enrollments'));
    }

    public function generateForGroup(Request $request, Group $group)
    {
        $predictions = $this->predictionService->predictAll($group->id);
        foreach ($predictions as $prediction) {
            $this->alertService->generateFromPrediction($prediction);
        }

        return back()->with('success', count($predictions) . ' predicciones generadas.');
    }

    public function trainModel()
    {
        $trainingData = $this->buildTrainingData();

        if (count($trainingData['features']) < 10) {
            return back()->with('error', 'Se necesitan al menos 10 registros históricos para entrenar el modelo.');
        }

        $result = $this->predictionService->trainModel($trainingData);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? "Modelo entrenado. Precisión: " . round($result['accuracy'] * 100, 1) . "%"
                : 'Error: ' . ($result['error'] ?? 'No se pudo conectar al servicio ML')
        );
    }

    public function apiGenerate(Enrollment $enrollment)
    {
        $prediction = $this->predictionService->predict($enrollment);
        $this->alertService->generateFromPrediction($prediction);
        return response()->json($prediction->load('enrollment.student.user'));
    }

    public function apiGetByStudent(int $id)
    {
        $predictions = RiskPrediction::whereHas('enrollment', fn($q) => $q->where('student_id', $id))
            ->with('enrollment.group.subject')
            ->latest('generated_at')
            ->get();
        return response()->json($predictions);
    }

    private function buildRecommendations($enrollments): array
    {
        $recommendations = [];
        foreach ($enrollments as $enrollment) {
            $prediction = $enrollment->latestPrediction;
            if (!$prediction) continue;

            $recs = [];
            if ($enrollment->getAttendancePercentage() < 80) {
                $recs[] = 'Mejora tu asistencia a clases (mínimo 80% para no reprobar).';
            }
            if ($enrollment->getAverageGrade() < 70) {
                $recs[] = 'Busca asesoría con tu docente o tutor para mejorar tus calificaciones.';
            }
            if ($prediction->risk_level === 'alto') {
                $recs[] = 'Asiste a tutoría académica lo antes posible.';
                $recs[] = 'Dedica más tiempo de estudio a esta materia.';
            }
            if ($prediction->risk_level === 'medio') {
                $recs[] = 'Realiza todas las actividades y tareas asignadas.';
            }

            $recommendations[$enrollment->id] = $recs;
        }
        return $recommendations;
    }

    private function buildTrainingData(): array
    {
        $history = AcademicHistory::with(['student', 'subject'])->get();
        $features = [];
        $labels = [];

        foreach ($history as $record) {
            $student = $record->student;
            $features[] = [
                $student->getGeneralAverage(),
                80.0, // placeholder attendance
                $student->getFailedSubjectsCount(),
                $student->getCurrentEnrollmentsCount(),
                $record->subject->historical_difficulty ?? 0,
                $record->grade,
                0, 0,
            ];
            $labels[] = $record->status === 'reprobado' ? 2 : 0;
        }

        return ['features' => $features, 'labels' => $labels];
    }
}
