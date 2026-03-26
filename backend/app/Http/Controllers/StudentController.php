<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('user')
            ->when($request->search, fn($q) => $q->whereHas('user', fn($u) =>
                $u->where('name', 'like', "%{$request->search}%")
            )->orWhere('control_number', 'like', "%{$request->search}%"))
            ->when($request->semester, fn($q) => $q->where('semester', $request->semester))
            ->when($request->status, fn($q) => $q->where('status', $request->status));

        $students = $query->paginate(15)->withQueryString();
        return view('students.index', compact('students'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email|unique:users,email',
            'control_number' => 'required|string|max:20|unique:students,control_number',
            'semester'       => 'required|integer|min:1|max:9',
            'enrollment_year'=> 'required|integer|min:2000|max:' . date('Y'),
            'password'       => 'required|string|min:8',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'estudiante',
            ]);

            $student = Student::create([
                'user_id'        => $user->id,
                'control_number' => $request->control_number,
                'career'         => $request->career ?? 'Ingeniería en Sistemas Computacionales',
                'semester'       => $request->semester,
                'enrollment_year'=> $request->enrollment_year,
                'status'         => 'activo',
            ]);

            AuditLog::record('student.create', $student);
        });

        return redirect()->route('estudiantes.index')->with('success', 'Estudiante registrado correctamente.');
    }

    public function show(Student $estudiante)
    {
        $estudiante->load([
            'user',
            'enrollments.group.subject',
            'enrollments.partialGrades',
            'enrollments.attendance',
            'enrollments.latestPrediction',
            'academicHistory.subject',
            'alerts' => fn($q) => $q->latest()->take(5),
            'tutoringRecords.tutor',
        ]);

        return view('students.show', ['student' => $estudiante]);
    }

    public function edit(Student $estudiante)
    {
        return view('students.edit', ['student' => $estudiante]);
    }

    public function update(Request $request, Student $estudiante)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|unique:users,email,' . $estudiante->user_id,
            'semester'=> 'required|integer|min:1|max:9',
            'status'  => 'required|in:activo,baja,egresado',
        ]);

        DB::transaction(function () use ($request, $estudiante) {
            $old = $estudiante->toArray();
            $estudiante->user->update([
                'name'  => $request->name,
                'email' => $request->email,
            ]);
            $estudiante->update([
                'semester' => $request->semester,
                'status'   => $request->status,
            ]);
            AuditLog::record('student.update', $estudiante, ['old' => $old, 'new' => $estudiante->fresh()->toArray()]);
        });

        return redirect()->route('estudiantes.show', $estudiante)->with('success', 'Estudiante actualizado.');
    }

    public function destroy(Student $estudiante)
    {
        AuditLog::record('student.delete', $estudiante);
        $estudiante->user->delete();
        return redirect()->route('estudiantes.index')->with('success', 'Estudiante eliminado.');
    }

    public function myHistory()
    {
        $student = auth()->user()->student()->with([
            'academicHistory.subject',
            'enrollments.group.subject',
            'enrollments.partialGrades',
            'enrollments.attendance',
        ])->firstOrFail();

        return view('students.my_history', compact('student'));
    }

    public function assigned()
    {
        $students = Student::with(['user', 'enrollments.latestPrediction', 'alerts'])
            ->whereHas('alerts', fn($q) => $q->where('status', 'activa'))
            ->orWhereHas('enrollments.riskPredictions', fn($q) => $q->whereIn('risk_level', ['alto', 'medio']))
            ->get();

        return view('students.assigned', compact('students'));
    }
}
