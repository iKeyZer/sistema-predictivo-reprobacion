@extends('layouts.app')

@section('title', 'Importar Calificaciones')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">
                <i class="bi bi-file-earmark-arrow-up text-primary me-2"></i>
                Importar Calificaciones y Asistencia
            </h2>
            <p class="text-muted">Sube un archivo CSV o Excel con las calificaciones de tu grupo.</p>
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
                    <form action="{{ route('import.grades.post') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Grupo <span class="text-danger">*</span></label>
                            <select name="group_id" class="form-select @error('group_id') is-invalid @enderror" required>
                                <option value="">— Selecciona un grupo —</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->group_name }} — {{ $group->subject->name }}
                                        ({{ $group->school_period }})
                                    </option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Archivo <span class="text-danger">*</span></label>
                            <input type="file" name="file" accept=".csv,.xlsx,.xls"
                                   class="form-control @error('file') is-invalid @enderror" required>
                            <div class="form-text">Formatos aceptados: <strong>CSV, XLSX, XLS</strong>. Máximo 2 MB.</div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cloud-upload me-1"></i> Importar
                            </button>
                            <a href="{{ route('import.grades.template') }}" class="btn btn-outline-secondary">
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
                                    <td>Número de control del alumno</td>
                                </tr>
                                <tr>
                                    <td><code>parcial_1</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>Calificación parcial 1 (0–100)</td>
                                </tr>
                                <tr>
                                    <td><code>parcial_2</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>Calificación parcial 2 (0–100)</td>
                                </tr>
                                <tr>
                                    <td><code>parcial_3</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>Calificación parcial 3 (0–100)</td>
                                </tr>
                                <tr>
                                    <td><code>total_clases</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>Total de clases impartidas</td>
                                </tr>
                                <tr>
                                    <td><code>clases_asistidas</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                    <td>Clases a las que asistió el alumno</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info small mb-0 py-2">
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Tip:</strong> Si el alumno no está inscrito en el grupo seleccionado será omitido.
                        Puedes dejar vacías las columnas que no tengas aún.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
