@extends('layouts.app')
@section('title', 'Mi Nivel de Riesgo')
@section('page-title', 'Mi Nivel de Riesgo Académico')

@section('content')
@forelse($enrollments as $enrollment)
    @php $pred = $enrollment->latestPrediction; @endphp
    <div class="card mb-4 {{ $pred?->risk_level === 'alto' ? 'border-danger' : ($pred?->risk_level === 'medio' ? 'border-warning' : '') }}">
        <div class="card-header d-flex justify-content-between align-items-center
            {{ $pred?->risk_level === 'alto' ? 'bg-danger text-white' : ($pred?->risk_level === 'medio' ? 'bg-warning' : 'bg-success text-white') }}">
            <div class="fw-bold">{{ $enrollment->group->subject->name }}</div>
            @if($pred)
                <span class="badge bg-white text-dark">{{ $pred->getRiskLabel() }}</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3 text-center">
                    <div class="fs-3 fw-bold">{{ $enrollment->getAverageGrade() ?: '—' }}</div>
                    <div class="text-muted small">Promedio actual</div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="fs-3 fw-bold {{ $enrollment->getAttendancePercentage() < 80 ? 'text-danger' : 'text-success' }}">
                        {{ $enrollment->getAttendancePercentage() }}%
                    </div>
                    <div class="text-muted small">Asistencia</div>
                </div>
                <div class="col-md-3 text-center">
                    @if($pred)
                        <div class="fs-3 fw-bold">{{ $pred->getRiskPercentage() }}%</div>
                        <div class="text-muted small">Prob. reprobación</div>
                    @endif
                </div>
                <div class="col-md-3 text-center">
                    <div class="fs-3 fw-bold">{{ $enrollment->partialGrades->count() }}/3</div>
                    <div class="text-muted small">Parciales registrados</div>
                </div>
            </div>

            {{-- Recomendaciones --}}
            @if(isset($recommendations[$enrollment->id]) && count($recommendations[$enrollment->id]) > 0)
                <hr>
                <h6 class="fw-bold"><i class="bi bi-lightbulb me-2 text-warning"></i>Recomendaciones para ti:</h6>
                <ul class="mb-0">
                    @foreach($recommendations[$enrollment->id] as $rec)
                        <li class="small">{{ $rec }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@empty
    <div class="text-center py-5">
        <i class="bi bi-book fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">No tienes materias activas</h5>
        <p class="text-muted">Cuando estés inscrito en materias, podrás ver tu nivel de riesgo aquí.</p>
    </div>
@endforelse
@endsection
