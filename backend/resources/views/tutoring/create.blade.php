@extends('layouts.app')
@section('title', 'Nueva Tutoría')
@section('page-title', 'Registrar Sesión de Tutoría')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        @if($selectedAlert)
        <div class="alert alert-warning d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0"></i>
            <div>
                <div class="fw-semibold">Alerta vinculada: {{ $selectedAlert->student->user->name }}</div>
                <div class="small">{{ Str::limit($selectedAlert->message, 120) }}</div>
            </div>
        </div>
        @endif

        <div class="card p-4">
            <form method="POST" action="{{ route('tutorias.store') }}">
                @csrf
                @if($selectedAlert)
                    <input type="hidden" name="alert_id" value="{{ $selectedAlert->id }}">
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Estudiante *</label>
                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">— Seleccionar —</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}"
                                    {{ (old('student_id') == $student->id || $selectedAlert?->student_id == $student->id) ? 'selected' : '' }}>
                                    {{ $student->user->name }} ({{ $student->control_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Fecha *</label>
                        <input type="date" name="session_date" class="form-control @error('session_date') is-invalid @enderror"
                               value="{{ old('session_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                        @error('session_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Tipo *</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="tutoria" {{ old('type') == 'tutoria' ? 'selected' : '' }}>Tutoría</option>
                            <option value="asesoria" {{ old('type') == 'asesoria' ? 'selected' : '' }}>Asesoría</option>
                            <option value="seguimiento" {{ old('type') == 'seguimiento' ? 'selected' : '' }}>Seguimiento</option>
                        </select>
                    </div>

                    @if(!$selectedAlert)
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Vincular alerta (opcional)</label>
                        <select name="alert_id" class="form-select">
                            <option value="">— Sin alerta vinculada —</option>
                            @foreach($alerts as $alert)
                                <option value="{{ $alert->id }}" {{ old('alert_id') == $alert->id ? 'selected' : '' }}>
                                    {{ $alert->student->user->name }} — {{ $alert->getTypeLabel() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Notas de la sesión *</label>
                        <textarea name="notes" rows="5" class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="Describe los temas tratados, dificultades identificadas y estrategias acordadas..." required>{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Resultado / Compromisos del estudiante</label>
                        <input type="text" name="outcome" class="form-control"
                               value="{{ old('outcome') }}"
                               placeholder="Ej: El estudiante se comprometió a asistir a asesorías los martes...">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('tutorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2 me-2"></i>Registrar Tutoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
