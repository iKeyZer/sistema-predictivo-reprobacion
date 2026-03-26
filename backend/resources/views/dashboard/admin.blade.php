@extends('layouts.app')
@section('title', 'Dashboard Administrador')
@section('page-title', 'Panel de Administración del Sistema')

@section('content')
<div class="row g-4 mb-4">
    @foreach([
        ['icon'=>'bi-people-fill','color'=>'primary','label'=>'Total usuarios','value'=>$stats['users']],
        ['icon'=>'bi-person-vcard','color'=>'success','label'=>'Estudiantes','value'=>$stats['students']],
        ['icon'=>'bi-person-check','color'=>'info','label'=>'Activos','value'=>$stats['active']],
        ['icon'=>'bi-cpu','color'=>'warning','label'=>'Predicciones','value'=>$stats['predictions']],
    ] as $stat)
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-{{ $stat['color'] }} bg-opacity-10 text-{{ $stat['color'] }} d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="bi {{ $stat['icon'] }} fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">{{ $stat['label'] }}</div>
                    <div class="fw-bold fs-4">{{ $stat['value'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2 text-primary"></i>Usuarios por Rol</h6>
            <canvas id="roleChart" height="200"></canvas>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-gear me-2 text-secondary"></i>Acciones Rápidas</h6>
            <div class="list-group">
                <a href="{{ route('usuarios.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                    <i class="bi bi-person-plus text-primary fs-5"></i>
                    <div>
                        <div class="fw-semibold">Crear nuevo usuario</div>
                        <div class="text-muted small">Agregar docente, tutor o admin</div>
                    </div>
                </a>
                <a href="{{ route('estudiantes.create') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                    <i class="bi bi-person-vcard text-success fs-5"></i>
                    <div>
                        <div class="fw-semibold">Registrar estudiante</div>
                        <div class="text-muted small">Dar de alta a un nuevo alumno</div>
                    </div>
                </a>
                <form method="POST" action="{{ route('modelo.entrenar') }}" class="list-group-item list-group-item-action p-0">
                    @csrf
                    <button type="submit" class="btn w-100 text-start d-flex align-items-center gap-3 p-3 border-0 bg-transparent">
                        <i class="bi bi-cpu text-warning fs-5"></i>
                        <div>
                            <div class="fw-semibold">Entrenar modelo ML</div>
                            <div class="text-muted small">Reentrenar con datos históricos actuales</div>
                        </div>
                    </button>
                </form>
                <a href="{{ route('predicciones.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                    <i class="bi bi-graph-up-arrow text-danger fs-5"></i>
                    <div>
                        <div class="fw-semibold">Ver predicciones</div>
                        <div class="text-muted small">Historial de predicciones generadas</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const roleData = @json($usersByRole);
new Chart(document.getElementById('roleChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(roleData).map(r => r.charAt(0).toUpperCase() + r.slice(1)),
        datasets: [{
            data: Object.values(roleData),
            backgroundColor: ['#2e86de','#10ac84','#f39c12','#9b59b6','#e74c3c'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        cutout: '55%'
    }
});
</script>
@endpush
