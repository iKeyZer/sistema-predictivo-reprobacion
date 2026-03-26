@extends('layouts.app')
@section('title', 'Alertas Académicas')
@section('page-title', 'Alertas Tempranas')

@section('content')
<div class="card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">Todos los estados</option>
                <option value="activa" {{ request('status') == 'activa' ? 'selected' : '' }}>Activas</option>
                <option value="atendida" {{ request('status') == 'atendida' ? 'selected' : '' }}>Atendidas</option>
                <option value="descartada" {{ request('status') == 'descartada' ? 'selected' : '' }}>Descartadas</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select form-select-sm">
                <option value="">Todos los tipos</option>
                <option value="riesgo_alto" {{ request('type') == 'riesgo_alto' ? 'selected' : '' }}>Riesgo Alto</option>
                <option value="riesgo_medio" {{ request('type') == 'riesgo_medio' ? 'selected' : '' }}>Riesgo Medio</option>
                <option value="asistencia" {{ request('type') == 'asistencia' ? 'selected' : '' }}>Asistencia</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-primary w-100">Filtrar</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th>Tipo</th>
                    <th>Mensaje</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                    <tr>
                        <td class="small fw-semibold">{{ $alert->student->user->name }}</td>
                        <td>
                            <span class="badge {{ $alert->type === 'riesgo_alto' ? 'bg-danger' : ($alert->type === 'riesgo_medio' ? 'bg-warning text-dark' : 'bg-info text-dark') }}">
                                {{ $alert->getTypeLabel() }}
                            </span>
                        </td>
                        <td class="small text-muted" style="max-width:300px">{{ Str::limit($alert->message, 80) }}</td>
                        <td>
                            <span class="badge {{ $alert->getStatusBadgeClass() }}">{{ ucfirst($alert->status) }}</span>
                        </td>
                        <td class="small text-muted">{{ $alert->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($alert->status === 'activa')
                                <form method="POST" action="{{ route('alertas.atender', $alert) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success" title="Marcar atendida">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                                <a href="{{ route('tutorias.create', ['alert_id' => $alert->id]) }}"
                                   class="btn btn-sm btn-outline-primary" title="Registrar tutoría">
                                    <i class="bi bi-chat-square-text"></i>
                                </a>
                            @endif
                            <a href="{{ route('estudiantes.show', $alert->student) }}"
                               class="btn btn-sm btn-outline-secondary" title="Ver estudiante">
                                <i class="bi bi-person"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay alertas con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($alerts->hasPages())
        <div class="p-3">{{ $alerts->links() }}</div>
    @endif
</div>
@endsection
