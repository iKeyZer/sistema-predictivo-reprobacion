@extends('layouts.app')

@section('title', 'Reporte de Grupo')
@section('page-title', 'Reporte — ' . $group->group_name)

@section('content')
<div class="row g-4">

    {{-- Info del grupo --}}
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-muted small">Materia</div>
                        <div class="fw-semibold">{{ $group->subject->name }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small">Clave</div>
                        <div class="fw-semibold">{{ $group->subject->code }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small">Periodo</div>
                        <div class="fw-semibold">{{ $group->school_period }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Docente</div>
                        <div class="fw-semibold">{{ $group->teacher->user->name }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen estadístico --}}
    @php
        $enrollments = $group->enrollments;
        $total = $enrollments->count();
        $alto  = $enrollments->filter(fn($e) => $e->latestPrediction?->risk_level === 'alto')->count();
        $medio = $enrollments->filter(fn($e) => $e->latestPrediction?->risk_level === 'medio')->count();
        $bajo  = $enrollments->filter(fn($e) => $e->latestPrediction?->risk_level === 'bajo')->count();
        $avgGrades = $enrollments->map(function($e) {
            return $e->partialGrades->avg('grade');
        })->filter()->avg();
    @endphp
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <div class="display-6 fw-bold">{{ $total }}</div>
                <div class="text-muted small">Total alumnos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center" style="border-left:4px solid #dc3545">
            <div class="card-body">
                <div class="display-6 fw-bold text-danger">{{ $alto }}</div>
                <div class="text-muted small">Riesgo alto</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center" style="border-left:4px solid #ffc107">
            <div class="card-body">
                <div class="display-6 fw-bold text-warning">{{ $medio }}</div>
                <div class="text-muted small">Riesgo medio</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center" style="border-left:4px solid #198754">
            <div class="card-body">
                <div class="display-6 fw-bold text-success">{{ $avgGrades ? number_format($avgGrades,1) : '—' }}</div>
                <div class="text-muted small">Promedio grupal</div>
            </div>
        </div>
    </div>

    {{-- Tabla de alumnos --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-table me-2 text-primary"></i>Detalle por Alumno
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover datatable mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Control</th>
                                <th>Nombre</th>
                                <th>P1</th>
                                <th>P2</th>
                                <th>P3</th>
                                <th>Promedio</th>
                                <th>Asistencia</th>
                                <th>Riesgo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enrollments as $enrollment)
                            @php
                                $g = $enrollment->partialGrades->keyBy('partial_number');
                                $att = $enrollment->attendance;
                                $attPct = $att->count() > 0
                                    ? round($att->whereIn('status',['presente','justificado'])->count() / $att->count() * 100)
                                    : null;
                                $avg = $g->avg('grade');
                                $pred = $enrollment->latestPrediction;
                            @endphp
                            <tr>
                                <td>{{ $enrollment->student->control_number }}</td>
                                <td>{{ $enrollment->student->user->name }}</td>
                                <td>{{ $g->get(1)?->grade ?? '—' }}</td>
                                <td>{{ $g->get(2)?->grade ?? '—' }}</td>
                                <td>{{ $g->get(3)?->grade ?? '—' }}</td>
                                <td class="{{ $avg ? ($avg >= 70 ? 'text-success' : 'text-danger') : '' }} fw-semibold">
                                    {{ $avg ? number_format($avg, 1) : '—' }}
                                </td>
                                <td>
                                    @if($attPct !== null)
                                        <span class="badge {{ $attPct >= 80 ? 'bg-success' : ($attPct >= 70 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ $attPct }}%
                                        </span>
                                    @else —
                                    @endif
                                </td>
                                <td>
                                    @if($pred)
                                        <span class="badge
                                            {{ $pred->risk_level === 'alto' ? 'bg-danger' :
                                               ($pred->risk_level === 'medio' ? 'bg-warning text-dark' : 'bg-success') }}">
                                            {{ ucfirst($pred->risk_level) }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Sin predicción</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
