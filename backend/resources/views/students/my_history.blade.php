@extends('layouts.app')

@section('title', 'Mi Historial Académico')
@section('page-title', 'Mi Historial Académico')

@section('content')
<div class="row g-4">

    {{-- Encabezado del estudiante --}}
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:56px;height:56px;font-size:1.4rem">
                    {{ strtoupper(substr($student->user->name, 0, 1)) }}
                </div>
                <div>
                    <h5 class="mb-0">{{ $student->user->name }}</h5>
                    <div class="text-muted small">
                        No. Control: <strong>{{ $student->control_number }}</strong> &bull;
                        Semestre: <strong>{{ $student->semester }}</strong> &bull;
                        {{ $student->career }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Materias cursando actualmente --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-book-half me-2 text-primary"></i>Materias en Curso
            </div>
            <div class="card-body p-0">
                @php $active = $student->enrollments->where('status', 'cursando'); @endphp
                @if($active->isEmpty())
                    <p class="text-muted p-3 mb-0">No tienes materias activas en este momento.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Materia</th>
                                <th>Grupo</th>
                                <th>P1</th>
                                <th>P2</th>
                                <th>P3</th>
                                <th>Promedio</th>
                                <th>Asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($active as $enrollment)
                            @php
                                $grades = $enrollment->partialGrades->keyBy('partial_number');
                                $totalClasses = $enrollment->attendance->count();
                                $present = $enrollment->attendance->whereIn('status', ['presente','justificado'])->count();
                                $attPct = $totalClasses > 0 ? round($present / $totalClasses * 100) : null;
                                $avgGrade = $grades->avg('grade');
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $enrollment->group->subject->name }}</td>
                                <td>{{ $enrollment->group->group_name }}</td>
                                <td>{{ $grades->get(1)?->grade ?? '—' }}</td>
                                <td>{{ $grades->get(2)?->grade ?? '—' }}</td>
                                <td>{{ $grades->get(3)?->grade ?? '—' }}</td>
                                <td>
                                    @if($avgGrade !== null)
                                        <span class="fw-bold {{ $avgGrade >= 70 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($avgGrade, 1) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attPct !== null)
                                        <span class="badge {{ $attPct >= 80 ? 'bg-success' : ($attPct >= 70 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ $attPct }}%
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Historial académico --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-journal-text me-2 text-secondary"></i>Historial de Materias
            </div>
            <div class="card-body p-0">
                @if($student->academicHistory->isEmpty())
                    <p class="text-muted p-3 mb-0">Sin historial académico registrado.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-hover datatable mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Materia</th>
                                <th>Periodo</th>
                                <th>Calificación</th>
                                <th>Intento</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($student->academicHistory->sortByDesc('school_period') as $h)
                            <tr>
                                <td>{{ $h->subject->name }}</td>
                                <td>{{ $h->school_period }}</td>
                                <td class="{{ $h->grade >= 70 ? 'text-success' : 'text-danger' }} fw-bold">
                                    {{ number_format($h->grade, 1) }}
                                </td>
                                <td>{{ $h->attempt_number }}</td>
                                <td>
                                    @if($h->status === 'aprobado')
                                        <span class="badge bg-success">Aprobado</span>
                                    @else
                                        <span class="badge bg-danger">Reprobado</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
