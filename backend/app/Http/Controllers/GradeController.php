<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\PartialGrade;
use App\Services\AlertService;
use App\Services\PredictionService;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function __construct(
        private PredictionService $predictionService,
        private AlertService $alertService
    ) {}

    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $groups = Group::with('subject')
            ->where('teacher_id', $teacher?->id)
            ->get();

        $selectedGroup = $request->group_id
            ? Group::with(['subject', 'enrollments.student.user', 'enrollments.partialGrades'])->find($request->group_id)
            : $groups->first();

        return view('grades.index', compact('groups', 'selectedGroup'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'enrollment_id'    => 'required|exists:enrollments,id',
            'partial_number'   => 'required|integer|in:1,2,3',
            'grade'            => 'required|numeric|min:0|max:100',
            'activities_grade' => 'nullable|numeric|min:0|max:100',
            'participation_grade' => 'nullable|numeric|min:0|max:100',
        ]);

        $grade = PartialGrade::updateOrCreate(
            [
                'enrollment_id'  => $request->enrollment_id,
                'partial_number' => $request->partial_number,
            ],
            [
                'grade'               => $request->grade,
                'activities_grade'    => $request->activities_grade,
                'participation_grade' => $request->participation_grade,
                'recorded_by'         => auth()->id(),
            ]
        );

        $enrollment = Enrollment::with(['student.academicHistory', 'partialGrades', 'attendance', 'group.subject'])->find($request->enrollment_id);
        $prediction = $this->predictionService->predict($enrollment);
        $this->alertService->generateFromPrediction($prediction);

        AuditLog::record('grade.save', $grade);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'prediction' => $prediction]);
        }

        return back()->with('success', 'Calificación guardada. Predicción actualizada.');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'grades'               => 'required|array',
            'grades.*.enrollment_id' => 'required|exists:enrollments,id',
            'grades.*.partial_number' => 'required|integer|in:1,2,3',
            'grades.*.grade'       => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->grades as $gradeData) {
            PartialGrade::updateOrCreate(
                ['enrollment_id' => $gradeData['enrollment_id'], 'partial_number' => $gradeData['partial_number']],
                ['grade' => $gradeData['grade'], 'recorded_by' => auth()->id()]
            );
        }

        return back()->with('success', 'Calificaciones guardadas.');
    }
}
