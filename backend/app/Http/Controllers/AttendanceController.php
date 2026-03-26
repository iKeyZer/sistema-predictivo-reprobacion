<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Group;
use App\Services\AlertService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AlertService $alertService) {}

    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $groups  = Group::with('subject')->where('teacher_id', $teacher?->id)->get();

        $selectedGroup = $request->group_id
            ? Group::with(['subject', 'enrollments.student.user',
                'enrollments.attendance' => fn($q) => $q->where('date', $request->date ?? today())])
                ->find($request->group_id)
            : null;

        return view('attendance.index', compact('groups', 'selectedGroup'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'         => 'required|date|before_or_equal:today',
            'group_id'     => 'required|exists:groups,id',
            'attendance'   => 'required|array',
            'attendance.*' => 'in:presente,ausente,justificado',
        ]);

        foreach ($request->attendance as $enrollmentId => $status) {
            Attendance::updateOrCreate(
                ['enrollment_id' => $enrollmentId, 'date' => $request->date],
                ['status' => $status, 'recorded_by' => auth()->id()]
            );
        }

        // Check attendance alerts for each enrollment
        $enrollmentIds = array_keys($request->attendance);
        foreach ($enrollmentIds as $enrollmentId) {
            $enrollment = Enrollment::find($enrollmentId);
            if ($enrollment) {
                $this->alertService->generateAttendanceAlert($enrollment);
            }
        }

        AuditLog::record('attendance.register', null, ['group_id' => $request->group_id, 'date' => $request->date]);

        return back()->with('success', 'Asistencia registrada correctamente.');
    }
}
