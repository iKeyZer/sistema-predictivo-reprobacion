@extends('layouts.app')
@section('title', 'Dashboard Tutor')
@section('page-title', 'Panel del Tutor')

@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Alertas Activas</h6>
                <a href="{{ route('alertas.tutor') }}" class="btn btn-sm btn-outline-danger">Ver todas</a>
            </div>
            @forelse($activeAlerts as $alert)
                <div class="d-flex gap-3 mb-3 p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <span class="badge bg-danger rounded-circle p-2">
                            <i class="bi bi-exclamation"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $alert->student->user->name }}</div>
                        <div class="text-muted" style="font-size:0.78rem">{{ Str::limit($alert->message, 90) }}</div>
                        <div class="mt-1">
                            <a href="{{ route('tutorias.create', ['alert_id' => $alert->id]) }}"
                               class="btn btn-xs btn-primary" style="font-size:0.75rem;padding:2px 8px">
                                <i class="bi bi-plus me-1"></i>Registrar tutoría
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted small text-center py-3">
                    <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                    No hay alertas activas
                </p>
            @endforelse
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-people me-2 text-warning"></i>Estudiantes en Riesgo</h6>
                <a href="{{ route('estudiantes.asignados') }}" class="btn btn-sm btn-outline-warning">Ver todos</a>
            </div>
            @forelse($studentsAtRisk->take(8) as $student)
                <div class="d-flex align-items-center gap-3 mb-2 p-2 border-bottom">
                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:36px;height:36px;font-size:0.85rem;flex-shrink:0">
                        {{ strtoupper(substr($student->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">{{ $student->user->name }}</div>
                        <div class="text-muted" style="font-size:0.75rem">Semestre {{ $student->semester }}</div>
                    </div>
                    @php $pred = $student->enrollments->flatMap->riskPredictions->sortByDesc('generated_at')->first(); @endphp
                    @if($pred)
                        <span class="badge {{ $pred->getRiskBadgeClass() }}">{{ $pred->getRiskLabel() }}</span>
                    @endif
                </div>
            @empty
                <p class="text-muted small text-center py-3">No hay estudiantes en riesgo.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
