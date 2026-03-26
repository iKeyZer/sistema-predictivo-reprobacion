<?php

namespace App\Http\Controllers;

use App\Models\AcademicHistory;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\RiskPrediction;
use App\Models\Student;
use App\Models\Subject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function institutional(Request $request)
    {
        $period = $request->period ?? date('Y') . '-A';

        $subjectStats = Subject::withCount([
            'academicHistory as total_enrollments' => fn($q) => $q->where('school_period', $period),
            'academicHistory as failed_count' => fn($q) => $q->where('school_period', $period)->where('status', 'reprobado'),
        ])->orderByDesc('historical_difficulty')->get();

        $riskSummary = [
            'alto'  => RiskPrediction::where('risk_level', 'alto')->distinct('enrollment_id')->count(),
            'medio' => RiskPrediction::where('risk_level', 'medio')->distinct('enrollment_id')->count(),
            'bajo'  => RiskPrediction::where('risk_level', 'bajo')->distinct('enrollment_id')->count(),
        ];

        $topRiskStudents = Student::with(['user', 'enrollments.latestPrediction'])
            ->whereHas('enrollments.latestPrediction', fn($q) => $q->where('risk_level', 'alto'))
            ->take(10)
            ->get();

        return view('reports.institutional', compact('subjectStats', 'riskSummary', 'topRiskStudents', 'period'));
    }

    public function bySubject(Request $request)
    {
        $subjects = Subject::with([
            'academicHistory' => fn($q) => $q->when($request->period, fn($q) => $q->where('school_period', $request->period)),
        ])->get()->map(function ($subject) {
            $total = $subject->academicHistory->count();
            $failed = $subject->academicHistory->where('status', 'reprobado')->count();
            $subject->total = $total;
            $subject->failed = $failed;
            $subject->pass_rate = $total > 0 ? round((($total - $failed) / $total) * 100, 1) : 0;
            $subject->fail_rate = $total > 0 ? round(($failed / $total) * 100, 1) : 0;
            return $subject;
        })->sortByDesc('fail_rate');

        return view('reports.by_subject', compact('subjects'));
    }

    public function group(Group $group)
    {
        $group->load(['subject', 'teacher.user', 'enrollments.student.user',
            'enrollments.partialGrades', 'enrollments.latestPrediction']);

        return view('reports.group', compact('group'));
    }

    public function predictive()
    {
        $predictions = RiskPrediction::with(['enrollment.student.user', 'enrollment.group.subject'])
            ->latest('generated_at')
            ->paginate(20);

        return view('reports.predictive', compact('predictions'));
    }

    public function export(Request $request, string $type)
    {
        if ($type === 'pdf') {
            $data = $this->getInstitutionalData($request->period ?? date('Y') . '-A');
            $pdf = Pdf::loadView('reports.pdf.institutional', $data);
            return $pdf->download('reporte_institucional_' . date('Y-m-d') . '.pdf');
        }

        if ($type === 'excel') {
            // Excel export would require Maatwebsite package - returning JSON for now
            return response()->json(['message' => 'Excel export not implemented yet']);
        }

        abort(404);
    }

    public function apiStats()
    {
        $bySubject = Subject::withCount([
            'academicHistory as total',
            'academicHistory as failed' => fn($q) => $q->where('status', 'reprobado'),
        ])->get()->map(fn($s) => [
            'name'      => $s->name,
            'total'     => $s->total,
            'failed'    => $s->failed,
            'fail_rate' => $s->total > 0 ? round(($s->failed / $s->total) * 100, 1) : 0,
        ]);

        $riskByLevel = RiskPrediction::selectRaw('risk_level, count(*) as total')
            ->groupBy('risk_level')->pluck('total', 'risk_level');

        return response()->json([
            'by_subject'  => $bySubject,
            'risk_levels' => $riskByLevel,
        ]);
    }

    private function getInstitutionalData(string $period): array
    {
        return [
            'period'       => $period,
            'totalStudents'=> Student::where('status', 'activo')->count(),
            'riskSummary'  => [
                'alto'  => RiskPrediction::where('risk_level', 'alto')->count(),
                'medio' => RiskPrediction::where('risk_level', 'medio')->count(),
                'bajo'  => RiskPrediction::where('risk_level', 'bajo')->count(),
            ],
            'subjectStats' => Subject::all(),
        ];
    }
}
