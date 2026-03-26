@extends('layouts.app')

@section('title', 'Reportes Predictivos')
@section('page-title', 'Reportes Predictivos')

@section('content')
<div class="row g-4">

    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <span class="fw-semibold"><i class="bi bi-file-earmark-bar-graph me-2 text-primary"></i>Historial de Predicciones</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover datatable mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Estudiante</th>
                                <th>No. Control</th>
                                <th>Materia</th>
                                <th>Riesgo</th>
                                <th>Probabilidad</th>
                                <th>Promedio</th>
                                <th>Asistencia</th>
                                <th>Generado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($predictions as $pred)
                            <tr>
                                <td>{{ $pred->enrollment->student->user->name ?? '—' }}</td>
                                <td>{{ $pred->enrollment->student->control_number ?? '—' }}</td>
                                <td>{{ $pred->enrollment->group->subject->name ?? '—' }}</td>
                                <td>
                                    <span class="badge
                                        {{ $pred->risk_level === 'alto' ? 'bg-danger' :
                                           ($pred->risk_level === 'medio' ? 'bg-warning text-dark' : 'bg-success') }}">
                                        {{ ucfirst($pred->risk_level) }}
                                    </span>
                                </td>
                                <td>{{ number_format($pred->risk_probability * 100, 1) }}%</td>
                                <td class="{{ $pred->avg_grade >= 70 ? 'text-success' : 'text-danger' }} fw-semibold">
                                    {{ number_format($pred->avg_grade, 1) }}
                                </td>
                                <td>
                                    <span class="badge {{ $pred->attendance_pct >= 80 ? 'bg-success' : ($pred->attendance_pct >= 70 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ number_format($pred->attendance_pct, 0) }}%
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $pred->generated_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No hay predicciones registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($predictions->hasPages())
            <div class="card-footer bg-white">
                {{ $predictions->links() }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
