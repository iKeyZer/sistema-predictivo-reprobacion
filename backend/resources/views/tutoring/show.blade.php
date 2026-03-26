@extends('layouts.app')
@section('title', 'Detalle de Tutoría')
@section('page-title', 'Detalle de Sesión de Tutoría')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="text-muted small fw-semibold">Estudiante</div>
                    <div class="fw-bold">{{ $tutoria->student->user->name }}</div>
                    <div class="text-muted small">{{ $tutoria->student->control_number }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small fw-semibold">Tutor</div>
                    <div class="fw-bold">{{ $tutoria->tutor->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small fw-semibold">Fecha</div>
                    <div class="fw-bold">{{ $tutoria->session_date->format('d/m/Y') }}</div>
                    <span class="badge bg-info text-dark">{{ $tutoria->getTypeLabel() }}</span>
                </div>
            </div>

            @if($tutoria->alert)
            <div class="alert alert-warning mb-4">
                <div class="fw-semibold small">Alerta vinculada:</div>
                <div class="small">{{ $tutoria->alert->message }}</div>
            </div>
            @endif

            <div class="mb-4">
                <h6 class="fw-bold text-muted small text-uppercase letter-spacing">Notas de la sesión</h6>
                <div class="bg-light rounded p-3">{{ $tutoria->notes }}</div>
            </div>

            @if($tutoria->outcome)
            <div class="mb-4">
                <h6 class="fw-bold text-muted small text-uppercase">Resultado / Compromisos</h6>
                <div class="bg-success bg-opacity-10 rounded p-3 small">
                    <i class="bi bi-check-circle text-success me-2"></i>{{ $tutoria->outcome }}
                </div>
            </div>
            @endif

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('tutorias.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
                <a href="{{ route('estudiantes.show', $tutoria->student) }}" class="btn btn-primary">
                    <i class="bi bi-person me-2"></i>Ver estudiante
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
