<?php

namespace App\Http\Controllers;

use App\Models\AcademicAlert;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicAlert::with(['student.user', 'prediction.enrollment.group.subject'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest();

        // Filter by teacher's groups if role is docente
        if (auth()->user()->isDocente()) {
            $query->whereHas('student.enrollments.group', fn($q) =>
                $q->where('teacher_id', auth()->user()->teacher?->id)
            );
        }

        $alerts = $query->paginate(15)->withQueryString();

        return view('alerts.index', compact('alerts'));
    }

    public function myAlerts()
    {
        $student = auth()->user()->student;
        $alerts = AcademicAlert::with(['prediction'])
            ->where('student_id', $student->id)
            ->latest()
            ->paginate(10);

        return view('alerts.my_alerts', compact('alerts'));
    }

    public function markAttended(AcademicAlert $alert)
    {
        $alert->update(['status' => 'atendida']);
        AuditLog::record('alert.attended', $alert);
        return back()->with('success', 'Alerta marcada como atendida.');
    }

    public function markDiscarded(AcademicAlert $alert)
    {
        $alert->update(['status' => 'descartada']);
        AuditLog::record('alert.discarded', $alert);
        return back()->with('success', 'Alerta descartada.');
    }

    public function apiActive()
    {
        $user = auth()->user();
        $query = AcademicAlert::with(['student.user'])->where('status', 'activa');

        if ($user->isEstudiante()) {
            $query->where('student_id', $user->student->id);
        } elseif ($user->isDocente()) {
            $query->whereHas('student.enrollments.group', fn($q) =>
                $q->where('teacher_id', $user->teacher->id)
            );
        }

        return response()->json($query->latest()->take(10)->get());
    }
}
