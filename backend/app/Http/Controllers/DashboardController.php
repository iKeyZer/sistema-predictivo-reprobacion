<?php

namespace App\Http\Controllers;

use App\Models\AcademicAlert;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\RiskPrediction;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return match($user->role) {
            'estudiante'  => $this->student(),
            'docente'     => $this->teacher(),
            'tutor'       => $this->tutor(),
            'coordinador' => $this->coordinator(),
            'admin'       => $this->admin(),
            default       => abort(403),
        };
    }

    public function student()
    {
        $student = auth()->user()->student()->with(['enrollments.group.subject', 'alerts'])->first();
        $activeAlerts = $student?->alerts()->where('status', 'activa')->count() ?? 0;
        $latestPrediction = $student?->latestPrediction();

        $gradesBySubject = collect();
        if ($student) {
            $gradesBySubject = $student->enrollments()
                ->with(['group.subject', 'partialGrades'])
                ->where('status', 'cursando')
                ->get()
                ->map(fn($e) => [
                    'subject' => $e->group->subject->name,
                    'grades'  => $e->partialGrades->sortBy('partial_number')->pluck('grade'),
                    'avg'     => $e->getAverageGrade(),
                    'attendance' => $e->getAttendancePercentage(),
                    'prediction' => $e->latestPrediction,
                ]);
        }

        return view('dashboard.student', compact('student', 'activeAlerts', 'latestPrediction', 'gradesBySubject'));
    }

    public function teacher()
    {
        $teacher = auth()->user()->teacher;
        $groups = Group::with(['subject', 'enrollments.student.user'])
            ->where('teacher_id', $teacher?->id)
            ->get();

        $riskStats = [
            'alto'  => RiskPrediction::whereIn('enrollment_id',
                Enrollment::whereIn('group_id', $groups->pluck('id'))->pluck('id')
            )->where('risk_level', 'alto')->count(),
            'medio' => RiskPrediction::whereIn('enrollment_id',
                Enrollment::whereIn('group_id', $groups->pluck('id'))->pluck('id')
            )->where('risk_level', 'medio')->count(),
            'bajo'  => RiskPrediction::whereIn('enrollment_id',
                Enrollment::whereIn('group_id', $groups->pluck('id'))->pluck('id')
            )->where('risk_level', 'bajo')->count(),
        ];

        return view('dashboard.teacher', compact('teacher', 'groups', 'riskStats'));
    }

    public function tutor()
    {
        $activeAlerts = AcademicAlert::with(['student.user'])
            ->where('status', 'activa')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $studentsAtRisk = Student::whereHas('enrollments.riskPredictions', function ($q) {
            $q->whereIn('risk_level', ['alto', 'medio']);
        })->with(['user', 'enrollments.riskPredictions'])->get();

        return view('dashboard.tutor', compact('activeAlerts', 'studentsAtRisk'));
    }

    public function coordinator()
    {
        $subjects = Subject::withCount(['academicHistory as total_students'])
            ->withCount(['academicHistory as failed_students' => fn($q) => $q->where('status', 'reprobado')])
            ->orderByDesc('historical_difficulty')
            ->get()
            ->map(function ($s) {
                $s->fail_rate = $s->total_students > 0
                    ? round(($s->failed_students / $s->total_students) * 100, 1)
                    : 0;
                return $s;
            });

        $totalStudents    = Student::where('status', 'activo')->count();
        $highRiskStudents = RiskPrediction::where('risk_level', 'alto')->distinct('enrollment_id')->count();
        $activeAlerts     = AcademicAlert::where('status', 'activa')->count();

        return view('dashboard.coordinator', compact('subjects', 'totalStudents', 'highRiskStudents', 'activeAlerts'));
    }

    public function admin()
    {
        $stats = [
            'users'    => User::count(),
            'students' => Student::count(),
            'active'   => Student::where('status', 'activo')->count(),
            'alerts'   => AcademicAlert::where('status', 'activa')->count(),
            'predictions' => RiskPrediction::count(),
        ];

        $usersByRole = User::selectRaw('role, count(*) as total')
            ->groupBy('role')->pluck('total', 'role');

        return view('dashboard.admin', compact('stats', 'usersByRole'));
    }

    public function apiCharts()
    {
        $riskDistribution = RiskPrediction::selectRaw('risk_level, count(*) as total')
            ->groupBy('risk_level')
            ->pluck('total', 'risk_level');

        return response()->json([
            'risk_distribution' => $riskDistribution,
        ]);
    }
}
