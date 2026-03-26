@extends('layouts.app')
@section('title', 'Predicciones')
@section('page-title', 'Historial de Predicciones de Riesgo')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 datatable">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th>Asignatura</th>
                    <th class="text-center">Promedio</th>
                    <th class="text-center">Asistencia</th>
                    <th class="text-center">Prob.</th>
                    <th class="text-center">Riesgo</th>
                    <th>Modelo</th>
                    <th>Generada</th>
                </tr>
            </thead>
            <tbody>
                @forelse($predictions as $pred)
                    <tr>
                        <td class="small fw-semibold">{{ $pred->enrollment->student->user->name }}</td>
                        <td class="small">{{ $pred->enrollment->group->subject->name }}</td>
                        <td class="text-center small">{{ $pred->avg_grade }}</td>
                        <td class="text-center small {{ $pred->attendance_pct < 80 ? 'text-danger' : '' }}">{{ $pred->attendance_pct }}%</td>
                        <td class="text-center small">{{ $pred->getRiskPercentage() }}%</td>
                        <td class="text-center">
                            <span class="badge {{ $pred->getRiskBadgeClass() }}">{{ $pred->getRiskLabel() }}</span>
                        </td>
                        <td class="small text-muted"><code>{{ $pred->model_version }}</code></td>
                        <td class="small text-muted">{{ $pred->generated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay predicciones generadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($predictions->hasPages())
        <div class="p-3">{{ $predictions->links() }}</div>
    @endif
</div>
@endsection
