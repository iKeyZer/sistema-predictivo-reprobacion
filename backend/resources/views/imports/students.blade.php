@extends('layouts.app')

@section('title', 'Importar Alumnos')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">
                <i class="bi bi-people text-primary me-2"></i>
                Importar Alumnos en Lote
            </h2>
            <p class="text-muted">Crea múltiples alumnos desde un archivo CSV o Excel.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="alert alert-warning alert-dismissible fade show">
            <strong><i class="bi bi-exclamation-triangle me-2"></i>Advertencias durante la importación:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Upload form --}}
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-upload me-2 text-primary"></i>Subir archivo</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('import.students.post') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Archivo <span class="text-danger">*</span></label>
                            <input type="file" name="file" accept=".csv,.xlsx,.xls"
                                   class="form-control @error('file') is-invalid @enderror" required>
                            <div class="form-text">Formatos aceptados: <strong>CSV, XLSX, XLS</strong>. Máximo 5 MB.</div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning small py-2">
                            <i class="bi bi-shield-lock me-1"></i>
                            <strong>Contraseña por defecto:</strong> El número de control del alumno.
                            Pide a los alumnos que la cambien al primer inicio de sesión.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-upload me-1"></i> Importar
                            </button>
                            <a href="{{ route('import.students.template') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-download me-1"></i> Descargar plantilla
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Instructions --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Formato del archivo</h6>
                    <p class="text-muted small">El archivo debe tener las siguientes columnas (la primera fila es el encabezado):</p>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered small">
                            <thead class="table-dark">
                                <tr>
                                    <th>Columna</th>
                                    <th>Requerida</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>numero_control</code></td>
                                    <td><span class="badge bg-danger">Sí</span></td>
                                    <td>Número de control único</td>
                                </tr>
                                <tr>
                                    <td><code>nombre</code></td>
                                    <td><span class="badge bg-danger">Sí</span></td>
                                    <td>Nombre(s) del alumno</td>
                                </tr>
                                <tr>
                                    <td><code>apellidos</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>Apellidos del alumno</td>
                                </tr>
                                <tr>
                                    <td><code>email</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>Si se omite se genera: <code>nc@itsc.edu.mx</code></td>
                                </tr>
                                <tr>
                                    <td><code>carrera</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>Clave de carrera (default: ISC)</td>
                                </tr>
                                <tr>
                                    <td><code>semestre</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>Semestre actual (default: 1)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info small mb-0 py-2">
                        <i class="bi bi-lightbulb me-1"></i>
                        Los alumnos con número de control duplicado serán omitidos sin causar error.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
