@extends('layouts.app')
@section('title', 'Calificaciones')
@section('page-title', 'Registro de Calificaciones')

@section('content')
{{-- Selector de grupo --}}
<div class="card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small fw-semibold">Seleccionar grupo</label>
            <select name="group_id" class="form-select" onchange="this.form.submit()">
                <option value="">— Selecciona un grupo —</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" {{ $selectedGroup?->id == $g->id ? 'selected' : '' }}>
                        {{ $g->subject->name }} — {{ $g->group_name }} ({{ $g->school_period }})
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>

@if($selectedGroup)
<div class="card p-0">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0">{{ $selectedGroup->subject->name }}</h6>
            <small class="text-muted">{{ $selectedGroup->group_name }} — {{ $selectedGroup->school_period }}</small>
        </div>
        <form method="POST" action="{{ route('grupos.predecir', $selectedGroup) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning">
                <i class="bi bi-cpu me-1"></i>Generar Predicciones
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Estudiante</th>
                    <th class="text-center">Parcial 1</th>
                    <th class="text-center">Parcial 2</th>
                    <th class="text-center">Parcial 3</th>
                    <th class="text-center">Promedio</th>
                    <th class="text-center">Asistencia</th>
                    <th class="text-center">Riesgo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($selectedGroup->enrollments as $enrollment)
                    <tr>
                        <td class="small fw-semibold">{{ $enrollment->student->user->name }}</td>
                        @for($p = 1; $p <= 3; $p++)
                            @php $grade = $enrollment->partialGrades->where('partial_number', $p)->first(); @endphp
                            <td class="text-center">
                                <input type="number" step="0.01" min="0" max="100"
                                       class="form-control form-control-sm text-center grade-input"
                                       style="width:70px;margin:0 auto"
                                       data-enrollment="{{ $enrollment->id }}"
                                       data-partial="{{ $p }}"
                                       value="{{ $grade?->grade ?? '' }}"
                                       placeholder="—">
                            </td>
                        @endfor
                        <td class="text-center">
                            <strong id="avg-{{ $enrollment->id }}">{{ $enrollment->getAverageGrade() ?: '—' }}</strong>
                        </td>
                        <td class="text-center small {{ $enrollment->getAttendancePercentage() < 80 ? 'text-danger fw-bold' : '' }}">
                            {{ $enrollment->getAttendancePercentage() }}%
                        </td>
                        <td class="text-center">
                            @if($enrollment->latestPrediction)
                                <span class="badge {{ $enrollment->latestPrediction->getRiskBadgeClass() }}">
                                    {{ ucfirst($enrollment->latestPrediction->risk_level) }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 border-top d-flex justify-content-end">
        <button class="btn btn-primary" id="saveAllGrades">
            <i class="bi bi-floppy me-2"></i>Guardar todas las calificaciones
        </button>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.getElementById('saveAllGrades')?.addEventListener('click', async function() {
    const inputs = document.querySelectorAll('.grade-input');
    const grades = [];
    inputs.forEach(input => {
        if (input.value !== '') {
            grades.push({
                enrollment_id: input.dataset.enrollment,
                partial_number: input.dataset.partial,
                grade: input.value
            });
        }
    });

    if (!grades.length) {
        alert('No hay calificaciones para guardar.');
        return;
    }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

    const resp = await fetch('{{ route("calificaciones.bulk") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ grades })
    });

    if (resp.ok) {
        window.location.reload();
    } else {
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-floppy me-2"></i>Guardar todas las calificaciones';
        alert('Error al guardar. Intenta de nuevo.');
    }
});
</script>
@endpush
