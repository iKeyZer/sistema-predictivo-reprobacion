@extends('layouts.app')
@section('title', 'Dashboard Coordinador')
@section('page-title', 'Panel de Coordinación Académica')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-sm-4">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-people fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Estudiantes activos</div>
                    <div class="fw-bold fs-4">{{ $totalStudents }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card p-3" style="border-left-color: #dc3545">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-person-x fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">En riesgo alto</div>
                    <div class="fw-bold fs-4">{{ $highRiskStudents }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card stat-card p-3" style="border-left-color: #ffc107">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi bi-bell fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Alertas activas</div>
                    <div class="fw-bold fs-4">{{ $activeAlerts }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Índice de Reprobación por Asignatura</h6>
                <a href="{{ route('reportes.asignaturas') }}" class="btn btn-sm btn-outline-primary">Ver detalle</a>
            </div>
            <canvas id="subjectChart" height="100"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-danger"></i>Top Materias con Mayor Reprobación</h6>
            </div>
            @foreach($subjects->sortByDesc('fail_rate')->take(6) as $s)
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-truncate pe-2" style="max-width:70%">{{ $s->name }}</span>
                        <span class="fw-semibold {{ $s->fail_rate >= 40 ? 'text-danger' : ($s->fail_rate >= 25 ? 'text-warning' : 'text-success') }}">
                            {{ $s->fail_rate }}%
                        </span>
                    </div>
                    <div class="progress" style="height:6px">
                        <div class="progress-bar {{ $s->fail_rate >= 40 ? 'bg-danger' : ($s->fail_rate >= 25 ? 'bg-warning' : 'bg-success') }}"
                             style="width:{{ min($s->fail_rate, 100) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const topSubjects = @json($subjects->sortByDesc('fail_rate')->take(10)->values());
new Chart(document.getElementById('subjectChart'), {
    type: 'bar',
    data: {
        labels: topSubjects.map(s => s.name.length > 25 ? s.name.substring(0,25)+'…' : s.name),
        datasets: [{
            label: '% Reprobación',
            data: topSubjects.map(s => s.fail_rate),
            backgroundColor: topSubjects.map(s => s.fail_rate >= 40 ? '#dc3545' : s.fail_rate >= 25 ? '#ffc107' : '#198754'),
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { max: 100, ticks: { callback: v => v+'%' } }
        }
    }
});
</script>
@endpush
