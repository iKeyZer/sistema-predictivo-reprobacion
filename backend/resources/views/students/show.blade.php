@extends('layouts.app')
@section('title', $student->user->name)
@section('page-title', 'Perfil del Estudiante')

@section('content')
<div class="row g-4">
    {{-- Info del estudiante --}}
    <div class="col-lg-4">
        <div class="card p-4 text-center">
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3"
                 style="width:80px;height:80px;font-size:2rem">
                {{ strtoupper(substr($student->user->name, 0, 1)) }}
            </div>
            <h5 class="fw-bold">{{ $student->user->name }}</h5>
            <p class="text-muted small mb-1">{{ $student->user->email }}</p>
            <p class="text-muted small mb-3">No. Control: <strong>{{ $student->control_number }}</strong></p>

            <div class="row text-center g-0 border-top pt-3">
                <div class="col-4">
                    <div class="fw-bold">{{ $student->semester }}°</div>
                    <div class="text-muted" style="font-size:0.75rem">Semestre</div>
                </div>
                <div class="col-4 border-start border-end">
                    <div class="fw-bold">{{ $student->enrollment_year }}</div>
                    <div class="text-muted" style="font-size:0.75rem">Ingreso</div>
                </div>
                <div class="col-4">
                    <span class="badge {{ $student->status === 'activo' ? 'bg-success' : 'bg-danger' }}">
                        {{ ucfirst($student->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Nivel de riesgo global --}}
        @php $latestPred = $student->latestPrediction(); @endphp
        @if($latestPred)
        <div class="card p-3 mt-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-shield me-2"></i>Nivel de Riesgo Actual</h6>
            <div class="text-center">
                <span class="badge {{ $latestPred->getRiskBadgeClass() }} fs-6 px-4 py-2 d-block mb-2">
                    {{ $latestPred->getRiskLabel() }}
                </span>
                <div class="progress mb-2" style="height:10px">
                    <div class="progress-bar {{ $latestPred->getRiskBadgeClass() }}"
                         style="width:{{ $latestPred->getRiskPercentage() }}%"></div>
                </div>
                <small class="text-muted">{{ $latestPred->getRiskPercentage() }}% probabilidad de reprobación</small>
            </div>
            <div class="mt-3 text-muted small">
                <div><i class="bi bi-calculator me-1"></i>Promedio: <strong>{{ $latestPred->avg_grade }}</strong></div>
                <div><i class="bi bi-person-check me-1"></i>Asistencia: <strong>{{ $latestPred->attendance_pct }}%</strong></div>
                <div><i class="bi bi-x-circle me-1"></i>Reprobadas: <strong>{{ $latestPred->failed_subjects }}</strong></div>
            </div>
        </div>
        @endif
    </div>

    {{-- Detalle académico --}}
    <div class="col-lg-8">
        {{-- Materias actuales --}}
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-book me-2 text-primary"></i>Materias Actuales</h6>
            @forelse($student->enrollments->where('status', 'cursando') as $enrollment)
                <div class="border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold small">{{ $enrollment->group->subject->name }}</div>
                            <div class="text-muted" style="font-size:0.75rem">
                                {{ $enrollment->group->group_name }} — {{ $enrollment->group->school_period }}
                            </div>
                        </div>
                        @if($enrollment->latestPrediction)
                            <span class="badge {{ $enrollment->latestPrediction->getRiskBadgeClass() }}">
                                {{ $enrollment->latestPrediction->getRiskLabel() }}
                            </span>
                        @endif
                    </div>
                    <div class="d-flex gap-3 mt-2 text-muted small">
                        <span>Prom: <strong>{{ $enrollment->getAverageGrade() }}</strong></span>
                        <span>Asist: <strong>{{ $enrollment->getAttendancePercentage() }}%</strong></span>
                        @foreach($enrollment->partialGrades->sortBy('partial_number') as $pg)
                            <span>P{{ $pg->partial_number }}: <strong>{{ $pg->grade }}</strong></span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-muted small">No hay materias activas.</p>
            @endforelse
        </div>

        {{-- Historial académico --}}
        <div class="card p-4 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-secondary"></i>Historial Académico</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Asignatura</th>
                            <th>Período</th>
                            <th>Calificación</th>
                            <th>Estado</th>
                            <th>Intento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student->academicHistory->sortByDesc('school_period') as $record)
                            <tr>
                                <td class="small">{{ $record->subject->name }}</td>
                                <td class="small text-muted">{{ $record->school_period }}</td>
                                <td><strong>{{ $record->grade }}</strong></td>
                                <td>
                                    <span class="badge {{ $record->status === 'aprobado' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $record->attempt_number }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted small text-center">Sin historial previo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Últimas tutorías --}}
        @if($student->tutoringRecords->count() > 0)
        <div class="card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-chat-square-text me-2 text-info"></i>Últimas Tutorías</h6>
            @foreach($student->tutoringRecords->sortByDesc('session_date')->take(3) as $record)
                <div class="d-flex gap-3 mb-2 p-2 bg-light rounded">
                    <div class="text-muted small" style="min-width:80px">{{ $record->session_date->format('d/m/Y') }}</div>
                    <div>
                        <span class="badge bg-info text-dark me-2">{{ $record->getTypeLabel() }}</span>
                        <span class="small">{{ $record->tutor->name }}</span>
                        <div class="text-muted" style="font-size:0.78rem">{{ Str::limit($record->notes, 80) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
