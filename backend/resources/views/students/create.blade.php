@extends('layouts.app')
@section('title', 'Registrar Estudiante')
@section('page-title', 'Registrar Nuevo Estudiante')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <form method="POST" action="{{ route('estudiantes.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nombre completo *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Correo electrónico *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Número de control *</label>
                        <input type="text" name="control_number" class="form-control @error('control_number') is-invalid @enderror"
                               value="{{ old('control_number') }}" placeholder="20240001" required>
                        @error('control_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Semestre *</label>
                        <select name="semester" class="form-select @error('semester') is-invalid @enderror" required>
                            <option value="">Seleccionar</option>
                            @for($i = 1; $i <= 9; $i++)
                                <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>{{ $i }}° Semestre</option>
                            @endfor
                        </select>
                        @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Año de ingreso *</label>
                        <input type="number" name="enrollment_year" class="form-control @error('enrollment_year') is-invalid @enderror"
                               value="{{ old('enrollment_year', date('Y')) }}" min="2000" max="{{ date('Y') }}" required>
                        @error('enrollment_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Contraseña *</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               placeholder="Mínimo 8 caracteres" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Carrera</label>
                        <input type="text" name="career" class="form-control"
                               value="{{ old('career', 'Ingeniería en Sistemas Computacionales') }}">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Registrar Estudiante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
