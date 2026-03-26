<?php

namespace App\Http\Controllers;

use App\Models\AcademicAlert;
use App\Models\AuditLog;
use App\Models\Student;
use App\Models\TutoringRecord;
use Illuminate\Http\Request;

class TutoringController extends Controller
{
    public function index()
    {
        $records = TutoringRecord::with(['student.user', 'alert'])
            ->where('tutor_id', auth()->id())
            ->latest('session_date')
            ->paginate(15);

        return view('tutoring.index', compact('records'));
    }

    public function create(Request $request)
    {
        $students = Student::with('user')->where('status', 'activo')->get();
        $alerts = AcademicAlert::with(['student.user'])->where('status', 'activa')->get();
        $selectedAlert = $request->alert_id ? AcademicAlert::with('student.user')->find($request->alert_id) : null;

        return view('tutoring.create', compact('students', 'alerts', 'selectedAlert'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'   => 'required|exists:students,id',
            'session_date' => 'required|date|before_or_equal:today',
            'type'         => 'required|in:tutoria,asesoria,seguimiento',
            'notes'        => 'required|string|max:2000',
            'outcome'      => 'nullable|string|max:255',
            'alert_id'     => 'nullable|exists:academic_alerts,id',
        ]);

        $record = TutoringRecord::create([
            'student_id'   => $request->student_id,
            'tutor_id'     => auth()->id(),
            'alert_id'     => $request->alert_id,
            'session_date' => $request->session_date,
            'type'         => $request->type,
            'notes'        => $request->notes,
            'outcome'      => $request->outcome,
        ]);

        if ($request->alert_id) {
            AcademicAlert::find($request->alert_id)?->update(['status' => 'atendida']);
        }

        AuditLog::record('tutoring.create', $record);

        return redirect()->route('tutorias.index')->with('success', 'Sesión de tutoría registrada.');
    }

    public function show(TutoringRecord $tutoria)
    {
        $tutoria->load(['student.user', 'tutor', 'alert.prediction']);
        return view('tutoring.show', compact('tutoria'));
    }
}
