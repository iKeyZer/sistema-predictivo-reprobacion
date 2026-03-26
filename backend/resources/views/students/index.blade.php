@extends('layouts.app')
@section('title', 'Estudiantes')
@section('page-title', 'Gestión de Estudiantes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Total registrados: <strong>{{ $students->total() }}</strong></p>
    </div>
    @can('admin')
    <a href="{{ route('estudiantes.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Nuevo Estudiante
    </a>
    @endcan
</div>

{{-- Filtros --}}
<div class="card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Buscar por nombre o número de control..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="semester" class="form-select form-select-sm">
                <option value="">Semestre</option>
                @for($i = 1; $i <= 9; $i++)
                    <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>{{ $i }}°</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">Estado</option>
                <option value="activo" {{ request('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="baja" {{ request('status') == 'baja' ? 'selected' : '' }}>Baja</option>
                <option value="egresado" {{ request('status') == 'egresado' ? 'selected' : '' }}>Egresado</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill">
                <i class="bi bi-search me-1"></i>Buscar
            </button>
            <a href="{{ route('estudiantes.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x"></i>
            </a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th>No. Control</th>
                    <th>Semestre</th>
                    <th>Estado</th>
                    <th>Riesgo</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    @php $pred = $student->latestPrediction(); @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                                     style="width:36px;height:36px;font-size:0.85rem;flex-shrink:0">
                                    {{ strtoupper(substr($student->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold small">{{ $student->user->name }}</div>
                                    <div class="text-muted" style="font-size:0.75rem">{{ $student->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $student->control_number }}</code></td>
                        <td>{{ $student->semester }}°</td>
                        <td>
                            <span class="badge {{ $student->status === 'activo' ? 'bg-success' : ($student->status === 'baja' ? 'bg-danger' : 'bg-secondary') }}">
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td>
                            @if($pred)
                                <span class="badge {{ $pred->getRiskBadgeClass() }}">{{ $pred->getRiskLabel() }}</span>
                            @else
                                <span class="text-muted small">Sin datos</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('estudiantes.show', $student) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @auth
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('estudiantes.edit', $student) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @endauth
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No se encontraron estudiantes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
        <div class="p-3">{{ $students->links() }}</div>
    @endif
</div>
@endsection
