@extends('layouts.app')
@section('title', 'Reportes Institucionales')
@section('page-title', 'Reportes Institucionales')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="period" class="form-control form-control-sm" style="width:120px"
               value="{{ $period }}" placeholder="2024-A">
        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
    </form>
    <div class="d-flex gap-2">
        <a href="{{ route('reportes.exportar', 'pdf') }}" class="btn btn-sm btn-danger">
            <i class="bi bi-file-pdf me-1"></i>PDF
        </a>
        <a href="{{ route('reportes.exportar', 'excel') }}" class="btn btn-sm btn-success">
            <i class="bi bi-file-excel me-1"></i>Excel
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card p-4 text-center">
            <div class="fs-2 fw-bold text-primary">{{ $riskSummary['alto'] }}</div>
            <div class="text-danger fw-semibold">En Riesgo Alto</div>
            <div class="progress mt-2" style="height:6px">
                @php $total = array_sum($riskSummary); @endphp
                <div class="progress-bar bg-danger" style="width:{{ $total > 0 ? ($riskSummary['alto']/$total*100) : 0 }}%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 text-center">
            <div class="fs-2 fw-bold text-warning">{{ $riskSummary['medio'] }}</div>
            <div class="text-warning fw-semibold">En Riesgo Medio</div>
            <div class="progress mt-2" style="height:6px">
                <div class="progress-bar bg-warning" style="width:{{ $total > 0 ? ($riskSummary['medio']/$total*100) : 0 }}%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 text-center">
            <div class="fs-2 fw-bold text-success">{{ $riskSummary['bajo'] }}</div>
            <div class="text-success fw-semibold">Riesgo Bajo</div>
            <div class="progress mt-2" style="height:6px">
                <div class="progress-bar bg-success" style="width:{{ $total > 0 ? ($riskSummary['bajo']/$total*100) : 0 }}%"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-table me-2"></i>Estadísticas por Asignatura</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Asignatura</th>
                            <th>Sem.</th>
                            <th class="text-center">Inscritos</th>
                            <th class="text-center">Reprobados</th>
                            <th class="text-center">% Reprobación</th>
                            <th class="text-center">Dificultad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjectStats as $s)
                            <tr>
                                <td class="small">{{ $s->name }}</td>
                                <td class="text-center small">{{ $s->semester }}°</td>
                                <td class="text-center">{{ $s->total_enrollments }}</td>
                                <td class="text-center text-danger">{{ $s->failed_count }}</td>
                                <td class="text-center">
                                    @php $rate = $s->total_enrollments > 0 ? round(($s->failed_count/$s->total_enrollments)*100,1) : 0; @endphp
                                    <span class="badge {{ $rate >= 40 ? 'bg-danger' : ($rate >= 25 ? 'bg-warning text-dark' : 'bg-success') }}">
                                        {{ $rate }}%
                                    </span>
                                </td>
                                <td class="text-center small text-muted">{{ $s->historical_difficulty }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-x me-2 text-danger"></i>Estudiantes con Mayor Riesgo</h6>
            @foreach($topRiskStudents as $student)
                @php $pred = $student->enrollments->flatMap->riskPredictions->sortByDesc('generated_at')->first(); @endphp
                <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-danger bg-opacity-10 rounded">
                    <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:32px;height:32px;font-size:0.8rem;flex-shrink:0">
                        {{ strtoupper(substr($student->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="small fw-semibold text-truncate">{{ $student->user->name }}</div>
                        <div class="text-muted" style="font-size:0.72rem">Sem. {{ $student->semester }}</div>
                    </div>
                    @if($pred)
                        <span class="badge bg-danger">{{ $pred->getRiskPercentage() }}%</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Load chart via API
fetch('/api/reportes/estadisticas')
    .then(r => r.json())
    .then(data => {
        console.log('Stats loaded:', data);
    });
</script>
@endpush
