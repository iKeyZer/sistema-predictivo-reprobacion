@extends('layouts.app')
@section('title', 'Dashboard Docente')
@section('page-title', 'Panel del Docente')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-collection fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Grupos asignados</div>
                    <div class="fw-bold fs-4">{{ $groups->count() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card p-3" style="border-left-color: #dc3545">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-exclamation-octagon fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Riesgo alto</div>
                    <div class="fw-bold fs-4">{{ $riskStats['alto'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card p-3" style="border-left-color: #ffc107">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-exclamation-triangle fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Riesgo medio</div>
                    <div class="fw-bold fs-4">{{ $riskStats['medio'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-collection me-2 text-primary"></i>Mis Grupos</h6>
            @forelse($groups as $group)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $group->subject->name }}</div>
                            <div class="text-muted small">{{ $group->group_name }} — {{ $group->school_period }}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('calificaciones.index', ['group_id' => $group->id]) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-clipboard2-check me-1"></i>Calificaciones
                            </a>
                            <a href="{{ route('grupos.riesgo', $group) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-graph-up-arrow me-1"></i>Riesgo
                            </a>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="bi bi-people me-1"></i>{{ $group->enrollments->count() }} estudiantes
                    </div>
                </div>
            @empty
                <p class="text-muted">No tienes grupos asignados.</p>
            @endforelse
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2 text-warning"></i>Distribución de Riesgo</h6>
            <canvas id="riskChart" height="200"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('riskChart'), {
    type: 'doughnut',
    data: {
        labels: ['Riesgo Alto', 'Riesgo Medio', 'Riesgo Bajo'],
        datasets: [{
            data: [{{ $riskStats['alto'] }}, {{ $riskStats['medio'] }}, {{ $riskStats['bajo'] }}],
            backgroundColor: ['#dc3545', '#ffc107', '#198754'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        cutout: '65%'
    }
});
</script>
@endpush
