@extends('layouts.app')
@section('title', 'Tutorías')
@section('page-title', 'Registro de Tutorías')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Total sesiones: <strong>{{ $records->total() }}</strong></p>
    <a href="{{ route('tutorias.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nueva Tutoría
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 datatable">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Estudiante</th>
                    <th>Tipo</th>
                    <th>Notas</th>
                    <th>Resultado</th>
                    <th>Alerta</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td class="small text-muted">{{ $record->session_date->format('d/m/Y') }}</td>
                        <td class="small fw-semibold">{{ $record->student->user->name }}</td>
                        <td><span class="badge bg-info text-dark">{{ $record->getTypeLabel() }}</span></td>
                        <td class="small text-muted" style="max-width:200px">{{ Str::limit($record->notes, 60) }}</td>
                        <td class="small" style="max-width:150px">{{ Str::limit($record->outcome, 50) }}</td>
                        <td>
                            @if($record->alert)
                                <span class="badge bg-warning text-dark small">Vinculada</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tutorias.show', $record) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay tutorías registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
        <div class="p-3">{{ $records->links() }}</div>
    @endif
</div>
@endsection
