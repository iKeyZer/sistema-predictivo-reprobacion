@extends('layouts.app')
@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nombre completo *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $usuario->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Correo electrónico *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $usuario->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Rol *</label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            @foreach(['estudiante','docente','tutor','coordinador','admin'] as $role)
                                <option value="{{ $role }}" {{ old('role', $usuario->role) == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Estado</label>
                        <select name="active" class="form-select">
                            <option value="1" {{ $usuario->active ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ !$usuario->active ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nueva contraseña <span class="text-muted">(dejar vacío para no cambiar)</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy me-2"></i>Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
