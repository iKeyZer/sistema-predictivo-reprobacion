<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema Predictivo') — ITSC Informática</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #1a3c6e;
            --secondary-color: #2e86de;
        }
        body { background-color: #f0f2f5; }
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #0d2b54 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .sidebar .brand {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 10px 20px;
            border-radius: 0;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
            border-left: 3px solid var(--secondary-color);
        }
        .sidebar .nav-link i { width: 20px; }
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        .topbar {
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .risk-badge-alto   { background-color: #dc3545 !important; }
        .risk-badge-medio  { background-color: #ffc107 !important; color: #212529 !important; }
        .risk-badge-bajo   { background-color: #198754 !important; }
        .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border-radius: 12px; }
        .stat-card { border-left: 4px solid var(--secondary-color); }
        .page-content { padding: 24px; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

@auth
<!-- Sidebar -->
<nav class="sidebar d-flex flex-column">
    <div class="brand text-white">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-mortarboard-fill fs-4 text-warning"></i>
            <div>
                <div class="fw-bold small">ITSC - Informática</div>
                <div class="text-white-50" style="font-size:0.7rem">Sistema Predictivo</div>
            </div>
        </div>
    </div>

    <div class="py-2 flex-grow-1 overflow-auto">
        <div class="px-3 py-2 text-white-50" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px">
            Principal
        </div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>

        @if(auth()->user()->isEstudiante())
            <div class="px-3 py-2 text-white-50" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px">Mi Académico</div>
            <a href="{{ route('mi.historial') }}" class="nav-link {{ request()->routeIs('mi.historial') ? 'active' : '' }}">
                <i class="bi bi-journal-text me-2"></i> Mi Historial
            </a>
            <a href="{{ route('mi.riesgo') }}" class="nav-link {{ request()->routeIs('mi.riesgo') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow me-2"></i> Mi Nivel de Riesgo
            </a>
            <a href="{{ route('mis.alertas') }}" class="nav-link {{ request()->routeIs('mis.alertas') ? 'active' : '' }}">
                <i class="bi bi-bell me-2"></i> Mis Alertas
                @php $alertCount = auth()->user()->student?->alerts()->where('status','activa')->count(); @endphp
                @if($alertCount > 0)
                    <span class="badge bg-danger ms-1">{{ $alertCount }}</span>
                @endif
            </a>
        @endif

        @if(auth()->user()->isDocente())
            <div class="px-3 py-2 text-white-50" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px">Docente</div>
            <a href="{{ route('calificaciones.index') }}" class="nav-link">
                <i class="bi bi-clipboard2-check me-2"></i> Calificaciones
            </a>
            <a href="{{ route('asistencia.index') }}" class="nav-link">
                <i class="bi bi-person-check me-2"></i> Asistencia
            </a>
            <a href="{{ route('alertas.docente') }}" class="nav-link">
                <i class="bi bi-exclamation-triangle me-2"></i> Alertas
            </a>
        @endif

        @if(auth()->user()->isTutor())
            <div class="px-3 py-2 text-white-50" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px">Tutoría</div>
            <a href="{{ route('estudiantes.asignados') }}" class="nav-link">
                <i class="bi bi-people me-2"></i> Estudiantes en Riesgo
            </a>
            <a href="{{ route('tutorias.index') }}" class="nav-link">
                <i class="bi bi-chat-square-text me-2"></i> Tutorías
            </a>
            <a href="{{ route('tutorias.create') }}" class="nav-link">
                <i class="bi bi-plus-circle me-2"></i> Nueva Tutoría
            </a>
            <a href="{{ route('reportes.predictivos') }}" class="nav-link">
                <i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes
            </a>
        @endif

        @if(auth()->user()->isCoordinador())
            <div class="px-3 py-2 text-white-50" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px">Coordinación</div>
            <a href="{{ route('reportes.institucional') }}" class="nav-link">
                <i class="bi bi-bar-chart-line me-2"></i> Reportes Institucionales
            </a>
            <a href="{{ route('reportes.asignaturas') }}" class="nav-link">
                <i class="bi bi-book me-2"></i> Por Asignatura
            </a>
        @endif

        @if(auth()->user()->isAdmin())
            <div class="px-3 py-2 text-white-50" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px">Administración</div>
            <a href="{{ route('usuarios.index') }}" class="nav-link">
                <i class="bi bi-people-fill me-2"></i> Usuarios
            </a>
            <a href="{{ route('estudiantes.index') }}" class="nav-link">
                <i class="bi bi-person-vcard me-2"></i> Estudiantes
            </a>
            <a href="{{ route('predicciones.index') }}" class="nav-link">
                <i class="bi bi-cpu me-2"></i> Predicciones
            </a>
        @endif
    </div>

    <div class="p-3 border-top border-secondary">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:36px;height:36px;font-size:0.85rem">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="text-white small fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                <div class="text-white-50" style="font-size:0.7rem">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary text-white border-0" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- Main content -->
<div class="main-content">
    <!-- Topbar -->
    <div class="topbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm d-md-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h6 class="mb-0 fw-semibold text-dark">@yield('page-title', 'Dashboard')</h6>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border">{{ date('d M Y') }}</span>
        </div>
    </div>

    <!-- Alerts / Flashes -->
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Page content -->
    <div class="page-content">
        @yield('content')
    </div>
</div>
@else
    @yield('content')
@endauth

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<script>
    // Sidebar toggle (mobile)
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.querySelector('.sidebar')?.classList.toggle('show');
    });

    // Auto-initialize DataTables (suppress column count warnings)
    $.fn.dataTable.ext.errMode = 'none';

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.datatable').forEach(table => {
            // Skip tables with no thead or mismatched columns
            const thead = table.querySelector('thead tr');
            if (!thead) return;
            const colCount = thead.querySelectorAll('th, td').length;

            try {
                $(table).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
                    },
                    responsive: true,
                    columnDefs: [{ targets: '_all', defaultContent: '' }],
                    drawCallback: function() {
                        // Re-align columns after draw
                    }
                });
            } catch(e) {
                console.warn('DataTable init skipped for table:', table.id, e.message);
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>
