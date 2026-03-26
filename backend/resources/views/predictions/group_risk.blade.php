@extends('layouts.app')
@section('title', 'Riesgo del Grupo')
@section('page-title', 'Indicadores de Riesgo — ' . $group->group_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h6 class="mb-0">{{ $group->subject->name }}</h6>
        <small class="text-muted">{{ $group->group_name }} — {{ $group->school_period }}</small>
    </div>
    <form method="POST" action="{{ route('grupos.predecir', $group) }}">
        @csrf
        <button type="submit" class="btn btn-warning">
            <i class="bi bi-arrow-clockwise me-2"></i>Actualizar Predicciones
        </button>
    </form>
</div>

{{-- Resumen estadístico --}}
@php
    $alto = $enrollments->filter(fn($e) => $e->latestPrediction?->risk_level === 'alto')->count();
    $medio = $enrollments->filter(fn($e) => $e->latestPrediction?->risk_level === 'medio')->count();
    $bajo = $enrollments->filter(fn($e) => $e->latestPrediction?->risk_level === 'bajo')->count();
    $total = $enrollments->count();
@endphp
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card p-3 text-center border-danger">
            <div class="fw-bold fs-3 text-danger">{{ $alto }}</div>
            <div class="small text-muted">Riesgo Alto</div>
            <div class="progress mt-2" style="height:4px">
                <div class="progress-bar bg-danger" style="width:{{ $total > 0 ? ($alto/$total*100) : 0 }}%"></div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card p-3 text-center border-warning">
            <div class="fw-bold fs-3 text-warning">{{ $medio }}</div>
            <div class="small text-muted">Riesgo Medio</div>
            <div class="progress mt-2" style="height:4px">
                <div class="progress-bar bg-warning" style="width:{{ $total > 0 ? ($medio/$total*100) : 0 }}%"></div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card p-3 text-center border-success">
            <div class="fw-bold fs-3 text-success">{{ $bajo }}</div>
            <div class="small text-muted">Riesgo Bajo</div>
            <div class="progress mt-2" style="height:4px">
                <div class="progress-bar bg-success" style="width:{{ $total > 0 ? ($bajo/$total*100) : 0 }}%"></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 datatable">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th class="text-center">Promedio</th>
                    <th class="text-center">Asistencia</th>
                    <th class="text-center">Prob. Reprobación</th>
                    <th class="text-center">Nivel de Riesgo</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrollments as $enrollment)
                    @php $pred = $enrollment->latestPrediction; @endphp
                    <tr class="{{ $pred?->risk_level === 'alto' ? 'table-danger' : ($pred?->risk_level === 'medio' ? 'table-warning' : '') }}">
                        <td class="fw-semibold small">{{ $enrollment->student->user->name }}</td>
                        <td class="text-center small">{{ $enrollment->getAverageGrade() }}</td>
                        <td class="text-center small {{ $enrollment->getAttendancePercentage() < 80 ? 'text-danger fw-bold' : '' }}">
                            {{ $enrollment->getAttendancePercentage() }}%
                        </td>
                        <td class="text-center">
                            @if($pred)
                                <div class="progress" style="height:8px">
                                    <div class="progress-bar {{ $pred->getRiskBadgeClass() }}"
                                         style="width:{{ $pred->getRiskPercentage() }}%" title="{{ $pred->getRiskPercentage() }}%"></div>
                                </div>
                                <small>{{ $pred->getRiskPercentage() }}%</small>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($pred)
                                <span class="badge {{ $pred->getRiskBadgeClass() }}">{{ $pred->getRiskLabel() }}</span>
                            @else
                                <span class="badge bg-secondary">Sin datos</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('estudiantes.show', $enrollment->student) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($pred?->risk_level !== 'bajo')
                                <a href="{{ route('tutorias.create', ['alert_id' => $enrollment->student->alerts->where('status','activa')->first()?->id]) }}"
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-chat-square-text"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
