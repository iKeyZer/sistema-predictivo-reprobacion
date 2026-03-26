@extends('layouts.app')
@section('title', 'Estudiantes en Riesgo')
@section('page-title', 'Estudiantes en Riesgo — Seguimiento')

@section('content')
@if($students->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-check-circle fs-1 text-success d-block mb-3"></i>
        <h5 class="text-muted">No hay estudiantes con alertas activas</h5>
    </div>
@else
<div class="row g-4">
    @foreach($students as $student)
        @php
            $pred = $student->enrollments->flatMap->riskPredictions->sortByDesc('generated_at')->first();
            $activeAlerts = $student->alerts->where('status', 'activa')->count();
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 {{ $pred?->risk_level === 'alto' ? 'border-danger' : ($pred?->risk_level === 'medio' ? 'border-warning' : '') }}">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width:44px;height:44px;font-size:1rem;flex-shrink:0">
                            {{ strtoupper(substr($student->user->name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $student->user->name }}</div>
                            <div class="text-muted small">{{ $student->control_number }} — Sem. {{ $student->semester }}</div>
                        </div>
                        @if($pred)
                            <span class="badge {{ $pred->getRiskBadgeClass() }}">{{ $pred->getRiskLabel() }}</span>
                        @endif
                    </div>

                    @if($pred)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Probabilidad de reprobación</span>
                            <span>{{ $pred->getRiskPercentage() }}%</span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar {{ $pred->getRiskBadgeClass() }}"
                                 style="width:{{ $pred->getRiskPercentage() }}%"></div>
                        </div>
                    </div>
                    @endif

                    @if($activeAlerts > 0)
                        <div class="alert alert-danger py-2 px-3 small mb-3">
                            <i class="bi bi-bell me-1"></i>{{ $activeAlerts }} alerta(s) activa(s)
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('estudiantes.show', $student) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-eye me-1"></i>Ver perfil
                        </a>
                        <a href="{{ route('tutorias.create') }}?student_id={{ $student->id }}"
                           class="btn btn-sm btn-primary flex-fill">
                            <i class="bi bi-chat-square-text me-1"></i>Tutoría
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
@endsection
