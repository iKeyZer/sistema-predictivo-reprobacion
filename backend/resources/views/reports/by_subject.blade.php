@extends('layouts.app')
@section('title', 'Reporte por Asignatura')
@section('page-title', 'Índice de Reprobación por Asignatura')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Clave</th>
                            <th>Asignatura</th>
                            <th class="text-center">Sem.</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Reprobados</th>
                            <th class="text-center">% Rep.</th>
                            <th class="text-center">% Aprob.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subjects as $s)
                            <tr>
                                <td><code class="small">{{ $s->code }}</code></td>
                                <td class="small">{{ $s->name }}</td>
                                <td class="text-center">{{ $s->semester }}°</td>
                                <td class="text-center">{{ $s->total }}</td>
                                <td class="text-center text-danger">{{ $s->failed }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $s->fail_rate >= 40 ? 'bg-danger' : ($s->fail_rate >= 25 ? 'bg-warning text-dark' : 'bg-success') }}">
                                        {{ $s->fail_rate }}%
                                    </span>
                                </td>
                                <td class="text-center text-success small">{{ $s->pass_rate }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4">
            <h6 class="fw-bold mb-3">Distribución de Reprobación</h6>
            <canvas id="failDistChart" height="250"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const data = @json($subjects->sortByDesc('fail_rate')->take(8)->values());
new Chart(document.getElementById('failDistChart'), {
    type: 'bar',
    data: {
        labels: data.map(s => s.name.substring(0,20)),
        datasets: [{
            label: '% Reprobación',
            data: data.map(s => s.fail_rate),
            backgroundColor: data.map(s => s.fail_rate >= 40 ? '#dc3545' : s.fail_rate >= 25 ? '#ffc107' : '#198754'),
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { max: 100 } }
    }
});
</script>
@endpush
