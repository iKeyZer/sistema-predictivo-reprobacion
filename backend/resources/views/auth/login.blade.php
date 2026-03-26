@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center"
     style="background: linear-gradient(135deg, #1a3c6e 0%, #0d2b54 100%)">
    <div class="w-100" style="max-width: 420px; padding: 24px">
        <div class="card p-4 shadow-lg">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill text-primary" style="font-size: 3rem"></i>
                <h4 class="mt-2 fw-bold text-dark">Sistema Predictivo</h4>
                <p class="text-muted small mb-0">Carrera de Informática — ITSC</p>
                <p class="text-muted small">Prevención de Deserción Académica</p>
            </div>

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="usuario@sistema.edu.mx" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label small">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </button>
            </form>

            <hr class="my-4">
            <div class="bg-light rounded p-3">
                <p class="small text-muted mb-2 fw-semibold">Credenciales de prueba:</p>
                <div class="row g-1" style="font-size: 0.75rem">
                    <div class="col-6"><span class="badge bg-danger me-1">Admin</span>admin@sistema.edu.mx</div>
                    <div class="col-6"><span class="badge bg-primary me-1">Docente</span>docente1@sistema.edu.mx</div>
                    <div class="col-6"><span class="badge bg-success me-1">Tutor</span>tutor@sistema.edu.mx</div>
                    <div class="col-6"><span class="badge bg-info me-1">Coord.</span>coordinador@sistema.edu.mx</div>
                    <div class="col-12 mt-1"><span class="badge bg-secondary me-1">Alumno</span>juan@estudiante.edu.mx</div>
                    <div class="col-12 text-muted">Contraseña: <code>Admin123!</code> / <code>Docente123!</code> / etc.</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});
</script>
@endpush
@endsection
