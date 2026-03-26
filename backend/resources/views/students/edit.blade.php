@extends('layouts.app')
@section('title', 'Editar Estudiante')
@section('page-title', 'Editar Estudiante')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <form method="POST" action="{{ route('estudiantes.update', $student) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nombre completo *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $student->user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Correo electrónico *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $student->user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Semestre *</label>
                        <select name="semester" class="form-select" required>
                            @for($i = 1; $i <= 9; $i++)
                                <option value="{{ $i }}" {{ old('semester', $student->semester) == $i ? 'selected' : '' }}>{{ $i }}°</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Estado *</label>
                        <select name="status" class="form-select" required>
                            @foreach(['activo','baja','egresado'] as $s)
                                <option value="{{ $s }}" {{ old('status', $student->status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('estudiantes.show', $student) }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy me-2"></i>Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
