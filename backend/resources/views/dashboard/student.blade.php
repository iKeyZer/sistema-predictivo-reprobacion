@extends('layouts.app')
@section('title', 'Mi Dashboard')
@section('page-title', 'Mi Panel Académico')

@section('content')
<div class="row g-4 mb-4">
    {{-- Tarjetas de estadísticas --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width:48px;height:48px">
                    <i class="bi bi-book fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Materias cursando</div>
                    <div class="fw-bold fs-4">{{ $gradesBySubject->count() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3" style="border-left-color: #dc3545">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger" style="width:48px;height:48px">
                    <i class="bi bi-bell fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Alertas activas</div>
                    <div class="fw-bold fs-4">{{ $activeAlerts }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3" style="border-left-color: #198754">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width:48px;height:48px">
                    <i class="bi bi-mortarboard fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Semestre actual</div>
                    <div class="fw-bold fs-4">{{ $student?->semester ?? '—' }}°</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        @php $pred = $latestPrediction; @endphp
        <div class="card stat-card p-3" style="border-left-color: {{ $pred?->risk_level === 'alto' ? '#dc3545' : ($pred?->risk_level === 'medio' ? '#ffc107' : '#198754') }}">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width:48px;height:48px">
                    <i class="bi bi-graph-up-arrow fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Nivel de riesgo</div>
                    @if($pred)
                        <span class="badge {{ $pred->getRiskBadgeClass() }} fs-6">{{ $pred->getRiskLabel() }}</span>
                    @else
                        <span class="text-muted small">Sin datos</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Gráfica de calificaciones --}}
    <div class="col-lg-8">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2 text-primary"></i>Calificaciones por Materia</h6>
            <canvas id="gradesChart" height="120"></canvas>
        </div>
    </div>

    {{-- Riesgo actual --}}
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-shield-exclamation me-2 text-warning"></i>Estado de Riesgo</h6>
            @forelse($gradesBySubject as $item)
                <div class="mb-3 p-3 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="small fw-semibold text-truncate pe-2" style="max-width:65%">{{ $item['subject'] }}</div>
                        @if($item['prediction'])
                            <span class="badge {{ $item['prediction']->getRiskBadgeClass() }}">
                                {{ $item['prediction']->getRiskLabel() }}
                            </span>
                        @else
                            <span class="badge bg-secondary">Sin datos</span>
                        @endif
                    </div>
                    <div class="d-flex gap-3 text-muted" style="font-size:0.78rem">
                        <span><i class="bi bi-clipboard2-check me-1"></i>Prom: <strong>{{ $item['avg'] }}</strong></span>
                        <span><i class="bi bi-person-check me-1"></i>Asist: <strong>{{ $item['attendance'] }}%</strong></span>
                    </div>
                    @if($item['prediction'])
                    <div class="progress mt-2" style="height:6px">
                        <div class="progress-bar {{ $item['prediction']->risk_level === 'alto' ? 'bg-danger' : ($item['prediction']->risk_level === 'medio' ? 'bg-warning' : 'bg-success') }}"
                             style="width:{{ $item['prediction']->getRiskPercentage() }}%"></div>
                    </div>
                    @endif
                </div>
            @empty
                <p class="text-muted small">No tienes materias activas.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const labels = @json($gradesBySubject->pluck('subject'));
const datasets = [];
const colors = ['#2e86de', '#10ac84', '#ee5a24', '#f39c12'];
const allPartials = @json($gradesBySubject->map(fn($i) => $i['grades']));

for (let p = 0; p < 3; p++) {
    const data = allPartials.map(g => g[p] ?? null);
    datasets.push({
        label: `Parcial ${p+1}`,
        data,
        backgroundColor: colors[p] + '33',
        borderColor: colors[p],
        borderWidth: 2,
        borderRadius: 6,
    });
}

new Chart(document.getElementById('gradesChart'), {
    type: 'bar',
    data: { labels, datasets },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { min: 0, max: 100, grid: { color: '#f0f0f0' } }
        }
    }
});
</script>
@endpush
