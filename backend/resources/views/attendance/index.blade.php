@extends('layouts.app')
@section('title', 'Registro de Asistencia')
@section('page-title', 'Registro de Asistencia')

@section('content')
<div class="card p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Grupo</label>
            <select name="group_id" class="form-select" onchange="this.form.submit()">
                <option value="">— Selecciona un grupo —</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" {{ request('group_id') == $g->id ? 'selected' : '' }}>
                        {{ $g->subject->name }} — {{ $g->group_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Fecha</label>
            <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}"
                   max="{{ date('Y-m-d') }}" onchange="this.form.submit()">
        </div>
    </form>
</div>

@if($selectedGroup)
<div class="card">
    <div class="p-3 border-bottom">
        <h6 class="fw-bold mb-0">
            {{ $selectedGroup->subject->name }} — {{ $selectedGroup->group_name }}
            <span class="text-muted fw-normal ms-2 small">{{ request('date', date('Y-m-d')) }}</span>
        </h6>
    </div>
    <form method="POST" action="{{ route('asistencia.store') }}">
        @csrf
        <input type="hidden" name="group_id" value="{{ $selectedGroup->id }}">
        <input type="hidden" name="date" value="{{ request('date', date('Y-m-d')) }}">

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Estudiante</th>
                        <th class="text-center">Presente</th>
                        <th class="text-center">Ausente</th>
                        <th class="text-center">Justificado</th>
                        <th class="text-center">% Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($selectedGroup->enrollments as $enrollment)
                        @php
                            $currentAttendance = $enrollment->attendance->first();
                            $currentStatus = $currentAttendance?->status ?? 'presente';
                            $totalPct = $enrollment->getAttendancePercentage();
                        @endphp
                        <tr>
                            <td class="small fw-semibold">{{ $enrollment->student->user->name }}</td>
                            @foreach(['presente', 'ausente', 'justificado'] as $status)
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input type="radio"
                                               class="form-check-input"
                                               name="attendance[{{ $enrollment->id }}]"
                                               value="{{ $status }}"
                                               {{ $currentStatus === $status ? 'checked' : '' }}>
                                    </div>
                                </td>
                            @endforeach
                            <td class="text-center small {{ $totalPct < 80 ? 'text-danger fw-bold' : 'text-success' }}">
                                {{ $totalPct }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-person-check me-2"></i>Guardar Asistencia
            </button>
        </div>
    </form>
</div>
@endif
@endsection
