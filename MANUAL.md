# Manual Técnico — Sistema Predictivo de Reprobación
### ITSC — Ingeniería en Sistemas Computacionales

---

## Índice

1. [Programación](#1-programación)
2. [Base de Datos](#2-base-de-datos)
3. [Tester](#3-tester)
4. [Diseñador de Interfaces](#4-diseñador-de-interfaces)
5. [Analista de Sistemas](#5-analista-de-sistemas)

---

# 1. Programación

## 1.1 Arquitectura general

El sistema está dividido en dos servicios independientes que se comunican por HTTP:

```
Navegador
    │
    ▼
Laravel 11 (PHP 8.3) ── puerto 8000
    │  Blade + Bootstrap 5
    │  Eloquent ORM
    │
    ├── MySQL 8 ── base de datos principal
    │
    └── Flask (Python 3.12) ── puerto 5000
           scikit-learn (Random Forest)
```

Laravel actúa como backend principal: gestiona sesiones, autenticación, lógica de negocio y vistas.  
Flask actúa como microservicio de predicción: recibe features y devuelve el nivel de riesgo.

---

## 1.2 Estructura de carpetas relevante

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Un controlador por módulo
│   │   └── Middleware/
│   │       └── RoleMiddleware.php   # Middleware de roles
│   ├── Imports/
│   │   ├── GradesImport.php   # Importación CSV/Excel de calificaciones
│   │   └── StudentsImport.php # Importación CSV/Excel de alumnos
│   ├── Models/                # 13 modelos Eloquent
│   └── Services/
│       ├── PredictionService.php  # Llama a Flask, guarda predicción
│       └── AlertService.php       # Genera alertas desde predicciones
├── bootstrap/
│   └── app.php                # Registro de middleware (TrustProxies, roles)
├── database/
│   ├── migrations/            # 14 migraciones en orden cronológico
│   └── seeders/
│       ├── UserSeeder.php     # Usuarios base (admin, coordinador, etc.)
│       ├── SubjectSeeder.php  # Materias del plan de estudios ISC
│       └── DemoDataSeeder.php # Grupos, inscripciones, calificaciones, predicciones demo
└── routes/
    ├── web.php                # Rutas organizadas por rol con middleware
    └── api.php                # Endpoints AJAX internos

ml-service/
├── app.py     # Flask: endpoints /health, /predict, /train
└── train.py   # Clase RiskModel: Random Forest + heurístico de respaldo
```

---

## 1.3 Controladores

| Controlador | Responsabilidad |
|-------------|----------------|
| `LoginController` | Autenticación, logout, redirección por rol |
| `DashboardController` | Estadísticas personalizadas por rol |
| `GradeController` | Registro de calificaciones parciales |
| `AttendanceController` | Registro de asistencia por fecha |
| `PredictionController` | Generar predicciones, ver riesgo por grupo/alumno |
| `AlertController` | Listar, atender y descartar alertas |
| `TutoringController` | CRUD de sesiones de tutoría |
| `StudentController` | CRUD de alumnos, historial académico |
| `UserController` | CRUD de usuarios (admin) |
| `ReportController` | Reportes institucionales, por materia, PDF |
| `ImportController` | Importación de calificaciones y alumnos desde CSV/Excel |

---

## 1.4 Servicios

### PredictionService

Flujo al llamar `predict(Enrollment $enrollment)`:

1. Extrae features del enrollment: promedio parcial, % asistencia, materias reprobadas, carga académica, dificultad de la materia, calificaciones por parcial.
2. Hace POST a `http://127.0.0.1:5000/predict` con las features.
3. Si Flask no responde → usa `heuristicFallback()` internamente.
4. Guarda el resultado en `risk_predictions`.

### AlertService

Flujo al llamar `generateFromPrediction(RiskPrediction $prediction)`:

1. Lee el nivel de riesgo de la predicción.
2. Si es `alto` o `medio` → crea registro en `academic_alerts`.
3. Si asistencia < 70% → crea alerta adicional de tipo `asistencia`.
4. Evita duplicar alertas activas para el mismo enrollment.

---

## 1.5 Middleware de roles

`RoleMiddleware` verifica que `auth()->user()->role` esté en la lista permitida. Soporta múltiples roles separados por coma:

```php
Route::middleware(['auth', 'role:docente,tutor'])->group(...)
```

Registro en `bootstrap/app.php`:

```php
$middleware->alias(['role' => RoleMiddleware::class]);
```

---

## 1.6 Importación CSV/Excel

Usa el paquete `maatwebsite/excel`. Las clases de importación implementan:
- `ToCollection` — procesa todas las filas como colección
- `WithHeadingRow` — usa la primera fila como nombre de columnas

**GradesImport**: recibe `group_id` y `teacher_id`. Por cada fila busca al alumno por `numero_control`, encuentra su enrollment en el grupo, crea/actualiza `PartialGrade` y genera registros de `Attendance` sintéticos proporcionales a `clases_asistidas / total_clases`.

**StudentsImport**: por cada fila crea `User` (rol estudiante, contraseña = número de control) y `Student`. Omite filas con número de control ya existente.

---

## 1.7 Microservicio Flask (ml-service)

Endpoints disponibles:

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/health` | Estado del servicio y versión del modelo |
| POST | `/predict` | Predicción para un estudiante |
| POST | `/train` | Entrenar el modelo con datos históricos |

**Modos de predicción:**

- **Heurístico** (por defecto si no hay modelo entrenado): calcula score ponderado con pesos asignados a promedio (40%), asistencia (30%), materias reprobadas (15%), dificultad (10%) y tendencia de parciales (5%).
- **Random Forest** (si se entrenó con `/train`): usa `sklearn.ensemble.RandomForestClassifier` con 100 estimadores. El modelo se guarda en `ml-service/model/risk_model.pkl`.

Para entrenar el modelo se necesitan mínimo 10 registros históricos en `academic_history`. Se activa desde el panel admin → Predicciones → Entrenar modelo.

---

## 1.8 Proxy y acceso remoto

Para exponer el sistema con Cloudflare Tunnel, `bootstrap/app.php` configura:

```php
$middleware->trustProxies(at: '*');
```

Esto le indica a Laravel que confíe en los headers `X-Forwarded-Proto` y `X-Forwarded-For` enviados por Cloudflare, generando URLs HTTPS correctas aunque el servidor corra en HTTP local.

---

# 2. Base de Datos

## 2.1 Motor y configuración

- **Motor:** MySQL 8
- **Nombre de la base:** `sistema_predictivo` (configurable en `.env`)
- **Charset:** utf8mb4
- **Colación:** utf8mb4_unicode_ci

---

## 2.2 Diagrama de relaciones (simplificado)

```
users ──────────── students ──────────── enrollments
  │                    │                     │
  │                 academic_history      ┌──┴──────────────┐
  │                                       │                 │
teachers ───── groups ────────────── partial_grades     attendance
                  │
               subjects
                  
enrollments ── risk_predictions ── academic_alerts ── tutoring_records
```

---

## 2.3 Tablas

### `users`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | Identificador |
| name | VARCHAR(100) | Nombre completo |
| email | VARCHAR(150) UNIQUE | Correo electrónico |
| password | VARCHAR | Hash bcrypt |
| role | ENUM | estudiante, docente, tutor, coordinador, admin |
| active | BOOLEAN | Cuenta activa/inactiva |

### `students`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| user_id | FK → users | |
| control_number | VARCHAR UNIQUE | Número de control |
| career | VARCHAR | Clave de carrera (ISC, IIA...) |
| semester | TINYINT | Semestre actual |
| enrollment_year | YEAR | Año de ingreso |
| status | ENUM | activo, baja, egresado |

### `teachers`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| user_id | FK → users | |
| employee_number | VARCHAR UNIQUE | Número de empleado |
| department | VARCHAR | Departamento académico |

### `subjects`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| name | VARCHAR | Nombre de la materia |
| code | VARCHAR UNIQUE | Clave (ej. ISC-401) |
| credits | TINYINT | Créditos |
| semester | TINYINT | Semestre al que pertenece |
| historical_difficulty | DECIMAL(5,2) | % histórico de reprobación |

### `groups`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| subject_id | FK → subjects | |
| teacher_id | FK → teachers | |
| school_period | VARCHAR | Ej: "2024-A" |
| group_name | VARCHAR | Ej: "ISC-4A" |

### `enrollments`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| student_id | FK → students | |
| group_id | FK → groups | |
| status | ENUM | cursando, aprobado, reprobado, baja |
| final_grade | DECIMAL(5,2) | Calificación final (nullable) |
| UNIQUE | (student_id, group_id) | Un alumno no puede estar inscrito dos veces |

### `partial_grades`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| enrollment_id | FK → enrollments | |
| partial_number | TINYINT | 1, 2 o 3 |
| grade | DECIMAL(5,2) | Calificación (0–100) |
| activities_grade | DECIMAL(5,2) | Actividades (nullable) |
| participation_grade | DECIMAL(5,2) | Participación (nullable) |
| recorded_by | FK → users | Docente que registró |

### `attendance`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| enrollment_id | FK → enrollments | |
| date | DATE | Fecha de la clase |
| status | ENUM | presente, ausente, justificado |
| recorded_by | FK → users | Docente que registró |

> **Nota:** El nombre de la tabla es `attendance` (singular), no `attendances`. El modelo Eloquent declara `protected $table = 'attendance'` explícitamente.

### `academic_history`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| student_id | FK → students | |
| subject_id | FK → subjects | |
| school_period | VARCHAR | Periodo cursado |
| grade | DECIMAL(5,2) | Calificación final |
| status | ENUM | aprobado, reprobado |

### `risk_predictions`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| enrollment_id | FK → enrollments | |
| risk_level | ENUM | bajo, medio, alto |
| risk_probability | DECIMAL(5,4) | Probabilidad 0.0000–1.0000 |
| avg_grade | DECIMAL(5,2) | Snapshot del promedio al generar |
| attendance_pct | DECIMAL(5,2) | Snapshot de asistencia al generar |
| failed_subjects | TINYINT | Materias reprobadas históricas |
| academic_load | TINYINT | Materias cursando al momento |
| model_version | VARCHAR(20) | "heuristic" o "vYYYYMMDDHHmm" |
| generated_at | TIMESTAMP | Fecha/hora de generación |

### `academic_alerts`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| prediction_id | FK → risk_predictions | |
| student_id | FK → students | |
| type | ENUM | riesgo_alto, riesgo_medio, asistencia, calificacion |
| message | TEXT | Mensaje descriptivo de la alerta |
| status | ENUM | activa, atendida, descartada |
| notified_to | JSON | Array de user_ids notificados |

### `tutoring_records`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| student_id | FK → students | |
| tutor_id | FK → users | |
| alert_id | FK → academic_alerts (nullable) | Alerta que originó la tutoría |
| session_date | DATE | Fecha de la sesión |
| type | ENUM | tutoria, asesoria, seguimiento |
| notes | TEXT | Notas del tutor |
| outcome | VARCHAR(255) | Resultado/acuerdo (nullable) |

### `audit_logs`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | |
| user_id | FK → users | |
| action | VARCHAR | Acción realizada |
| model | VARCHAR | Modelo afectado |
| model_id | BIGINT | ID del registro afectado |
| changes | JSON | Datos antes/después |

---

## 2.4 Comandos de base de datos

```bash
# Crear tablas y cargar datos demo
php artisan migrate --seed

# Reiniciar base de datos completa
php artisan migrate:fresh --seed

# Solo ejecutar seeders
php artisan db:seed

# Solo datos demo (sin recrear usuarios base)
php artisan db:seed --class=DemoDataSeeder
```

---

## 2.5 Convenciones importantes

- Las migraciones están numeradas con prefijo `2024_01_01_0000XX` en orden de dependencia (primero `users`, luego `students`, luego `enrollments`, etc.).
- Las llaves foráneas usan `onDelete('cascade')` en la mayoría de relaciones.
- `enrollment_id` + `partial_number` tienen índice único en `partial_grades` (un parcial por enrollment).
- `student_id` + `group_id` tienen índice único en `enrollments`.

---

# 3. Tester

## 3.1 Credenciales de prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@sistema.edu.mx | Admin123! |
| Coordinador | coordinador@sistema.edu.mx | Coord123! |
| Tutor | tutor@sistema.edu.mx | Tutor123! |
| Docente 1 | docente1@sistema.edu.mx | Docente123! |
| Docente 2 | docente2@sistema.edu.mx | Docente123! |
| Docente 3 | docente3@sistema.edu.mx | Docente123! |
| Estudiante (riesgo alto) | juan@estudiante.edu.mx | Estudiante123! |
| Estudiante (riesgo bajo) | pedro@estudiante.edu.mx | Estudiante123! |

---

## 3.2 Flujos de prueba por rol

### Administrador
- [ ] Iniciar sesión → redirige a dashboard admin
- [ ] Crear usuario nuevo (Usuarios → Nuevo)
- [ ] Editar usuario existente → cambiar rol
- [ ] Desactivar usuario → verificar que no puede iniciar sesión
- [ ] Ver lista de predicciones (Predicciones)
- [ ] Entrenar modelo ML (requiere al menos 10 registros en historial)
- [ ] Importar alumnos: subir `ejemplos/alumnos_prueba.csv`
- [ ] Verificar alumnos creados en la lista de Estudiantes

### Docente
- [ ] Iniciar sesión → redirige a dashboard docente con sus grupos
- [ ] Calificaciones → registrar nota parcial para un alumno
- [ ] Asistencia → marcar presente/ausente para fecha de hoy
- [ ] Grupos → Riesgo del grupo → ver tabla con colores de riesgo
- [ ] Grupos → Generar predicciones → confirmar que aparecen en la tabla
- [ ] Alertas → ver alertas generadas, marcar como atendida
- [ ] Importar CSV/Excel → seleccionar grupo → subir `ejemplos/calificaciones_prueba.csv`
- [ ] Verificar que las calificaciones y asistencias se actualizaron

### Tutor
- [ ] Iniciar sesión → ver dashboard con estudiantes en riesgo
- [ ] Estudiantes en Riesgo → lista de alumnos asignados
- [ ] Tutorías → Nueva tutoría → llenar formulario vinculado a una alerta
- [ ] Ver detalle de tutoría registrada
- [ ] Reportes Predictivos → tabla paginada de predicciones
- [ ] Alertas → marcar alerta como descartada

### Coordinador
- [ ] Reportes Institucionales → ver métricas globales y gráfica de riesgo
- [ ] Por Asignatura → ver tabla de materias con promedio y % riesgo
- [ ] Exportar PDF → descargar reporte institucional

### Estudiante
- [ ] Iniciar sesión → ver dashboard con sus materias activas
- [ ] Mi Historial → ver tabla de historial académico con DataTables (búsqueda, ordenamiento)
- [ ] Mi Nivel de Riesgo → ver predicción por materia con badge de color y recomendaciones
- [ ] Mis Alertas → ver alertas propias

---

## 3.3 Casos límite a probar

| Caso | Pasos | Resultado esperado |
|------|-------|--------------------|
| Login con credenciales incorrectas | Email/password erróneo | Mensaje de error, no redirige |
| Acceso directo a ruta de otro rol | Entrar a `/reportes` como docente | Redirige o muestra 403 |
| Importar CSV con número de control inexistente | `numero_control` que no existe en DB | Fila omitida, mensaje de advertencia |
| Importar CSV con clases_asistidas > total_clases | 30 asistidas sobre 20 totales | Error por fila, resto importa bien |
| Parcial 3 vacío en CSV | Columna `parcial_3` en blanco | Se importan parcial 1 y 2, parcial 3 ignorado |
| Generar predicción sin calificaciones | Grupo con enrollments sin notas | Predicción generada con promedio 0 → riesgo alto |
| Alumno duplicado en import | `numero_control` ya existente | Fila omitida, no falla todo el archivo |
| Flask apagado | Apagar ml-service y generar predicción | Laravel usa heurístico de respaldo, sistema sigue funcionando |

---

## 3.4 Verificación del microservicio ML

Abre en el navegador: `http://127.0.0.1:5000/health`

Respuesta esperada:
```json
{
  "status": "ok",
  "model_loaded": false,
  "model_version": "heuristic",
  "trained_at": null
}
```

Probar predicción manual con curl o Postman:
```http
POST http://127.0.0.1:5000/predict
Content-Type: application/json

{
  "avg_grade": 45,
  "attendance_pct": 65,
  "failed_subjects": 2,
  "academic_load": 5,
  "subject_difficulty": 40
}
```
Respuesta esperada: `"risk_level": "alto"`

---

## 3.5 Verificar importación paso a paso

1. Iniciar sesión como **docente1@sistema.edu.mx**
2. Menú → **Importar CSV/Excel**
3. Seleccionar grupo **ISC-4A — Programación Orientada a Objetos**
4. Subir `ejemplos/calificaciones_prueba.csv`
5. Verificar mensaje: *"Importación completada: 8 alumno(s) actualizado(s)"*
6. Ir a **Calificaciones** y confirmar que los parciales están registrados
7. Ir a **Grupos → Riesgo del grupo** y generar predicciones → verificar que Juan aparece en rojo (alto)

---

# 4. Diseñador de Interfaces

## 4.1 Stack de UI

| Herramienta | Versión | Uso |
|-------------|---------|-----|
| Bootstrap 5 | 5.3.3 | Grid, componentes, formularios |
| Bootstrap Icons | 1.11.3 | Iconografía |
| DataTables | 1.13.8 | Tablas interactivas con búsqueda y paginación |
| Chart.js | 4.x | Gráficas en dashboards |

Todos se cargan desde CDN en `layouts/app.blade.php`, sin necesidad de compilación local.

---

## 4.2 Variables de color (CSS custom properties)

Definidas en `<style>` dentro de `layouts/app.blade.php`:

```css
:root {
    --sidebar-width: 250px;
    --primary-color: #1a3c6e;   /* Azul marino institucional */
    --secondary-color: #2e86de; /* Azul claro (acentos, bordes activos) */
}
```

Para cambiar el tema principal, solo modificar estos dos valores.

---

## 4.3 Layout general

```
┌─────────────┬────────────────────────────────────────┐
│             │  TOPBAR (fecha, título de página)       │
│   SIDEBAR   ├────────────────────────────────────────┤
│  250px fijo │                                        │
│             │         CONTENIDO PRINCIPAL             │
│  Logo       │         @yield('content')               │
│  Navegación │                                        │
│  por rol    │                                        │
│             │                                        │
│  Usuario    │                                        │
│  + Logout   │                                        │
└─────────────┴────────────────────────────────────────┘
```

En móvil (< 768px): el sidebar se oculta con `transform: translateX(-100%)` y se activa con el botón hamburguesa del topbar.

---

## 4.4 Estructura de una vista típica

```blade
@extends('layouts.app')

@section('title', 'Título de la página')

@section('content')
<div class="container-fluid">

    {{-- Encabezado --}}
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">
                <i class="bi bi-icon-name text-primary me-2"></i>
                Título
            </h2>
        </div>
    </div>

    {{-- Alertas flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Contenido --}}
    <div class="card shadow-sm">
        <div class="card-body">
            ...
        </div>
    </div>

</div>
@endsection
```

---

## 4.5 Badges de riesgo

Se usan en toda la aplicación para mostrar el nivel de riesgo. Las clases CSS están definidas en el layout:

```html
<!-- Alto riesgo -->
<span class="badge risk-badge-alto">Alto</span>

<!-- Riesgo medio -->
<span class="badge risk-badge-medio">Medio</span>

<!-- Bajo riesgo -->
<span class="badge risk-badge-bajo">Bajo</span>
```

```css
.risk-badge-alto  { background-color: #dc3545 !important; }
.risk-badge-medio { background-color: #ffc107 !important; color: #212529 !important; }
.risk-badge-bajo  { background-color: #198754 !important; }
```

---

## 4.6 Tarjetas de estadísticas (stat-card)

Usadas en los dashboards para mostrar métricas:

```html
<div class="card stat-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small">Etiqueta</div>
                <div class="fs-3 fw-bold">42</div>
            </div>
            <i class="bi bi-people fs-1 text-primary opacity-25"></i>
        </div>
    </div>
</div>
```

La clase `stat-card` agrega un borde izquierdo azul: `border-left: 4px solid var(--secondary-color)`.

---

## 4.7 Tablas con DataTables

Para activar DataTables en cualquier tabla:

```html
<table id="mi-tabla" class="table table-hover">
    <thead>...</thead>
    <tbody>...</tbody>
</table>

@push('scripts')
<script>
    $('#mi-tabla').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/es-MX.json' }
    });
</script>
@endpush
```

---

## 4.8 Vistas existentes

```
resources/views/
├── auth/login.blade.php
├── layouts/app.blade.php
├── dashboard/
│   ├── admin.blade.php
│   ├── teacher.blade.php
│   ├── tutor.blade.php
│   ├── coordinator.blade.php
│   └── student.blade.php
├── grades/index.blade.php
├── attendance/index.blade.php
├── alerts/
│   ├── index.blade.php        (docente/tutor)
│   └── my_alerts.blade.php    (estudiante)
├── predictions/
│   ├── index.blade.php        (admin)
│   ├── group_risk.blade.php   (docente)
│   └── my_risk.blade.php      (estudiante)
├── reports/
│   ├── institutional.blade.php
│   ├── by_subject.blade.php
│   ├── group.blade.php
│   ├── predictive.blade.php
│   └── pdf/institutional.blade.php
├── imports/
│   ├── grades.blade.php
│   └── students.blade.php
├── students/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── assigned.blade.php
│   └── my_history.blade.php
├── tutoring/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── show.blade.php
└── users/
    ├── index.blade.php
    ├── create.blade.php
    └── edit.blade.php
```

---

# 5. Analista de Sistemas

## 5.1 Descripción del problema

El ITSC enfrenta tasas de reprobación que impactan la retención escolar. Los docentes no tienen visibilidad temprana de qué alumnos están en riesgo, y los tutores actúan de forma reactiva (cuando el alumno ya reprobó). El sistema busca identificar el riesgo **durante el periodo académico** para permitir intervención oportuna.

---

## 5.2 Roles y permisos

| Rol | Puede hacer |
|-----|-------------|
| **Estudiante** | Ver su propio historial, riesgo actual y alertas |
| **Docente** | Registrar/importar calificaciones y asistencia, ver riesgo de su grupo, gestionar alertas de su grupo |
| **Tutor** | Ver alumnos en riesgo asignados, registrar tutorías, ver reportes predictivos |
| **Coordinador** | Ver reportes institucionales y por asignatura, exportar PDF |
| **Admin** | Todo lo anterior + gestión de usuarios y entrenamiento del modelo ML |

---

## 5.3 Flujo principal del sistema

```
1. DOCENTE registra calificaciones parciales y/o asistencia
         │
         ▼
2. DOCENTE genera predicciones para su grupo
         │
         ▼
3. Sistema llama al microservicio Flask → obtiene nivel de riesgo
         │
         ▼
4. AlertService evalúa predicción:
   ├── Si riesgo = alto  → crea alerta tipo "riesgo_alto"
   ├── Si riesgo = medio → crea alerta tipo "riesgo_medio"
   └── Si asistencia < 70% → crea alerta adicional tipo "asistencia"
         │
         ▼
5. TUTOR ve las alertas → registra sesión de tutoría
         │
         ▼
6. DOCENTE/TUTOR marca la alerta como "atendida" o "descartada"
         │
         ▼
7. COORDINADOR consulta reportes institucionales con métricas agregadas
```

---

## 5.4 Algoritmo de predicción

El sistema tiene dos modos:

### Modo heurístico (activo por defecto)

Calcula un score ponderado:

| Factor | Peso | Escala |
|--------|------|--------|
| Promedio de calificaciones | 40% | <55 = máximo peso |
| Porcentaje de asistencia | 30% | <70% = máximo peso |
| Materias reprobadas (historial) | 15% | ≥3 = máximo peso |
| Dificultad histórica de la materia | 10% | % histórico reprobación |
| Tendencia entre parcial 1 y 2 | 5% | Caída >10 puntos = penalización |

Resultado:
- Score ≥ 0.55 → **Alto**
- Score ≥ 0.30 → **Medio**
- Score < 0.30 → **Bajo**

### Modo ML (requiere entrenamiento)

Random Forest Classifier con 100 árboles, entrenado con el historial académico de la institución. Usa las mismas 8 features del heurístico. Se activa automáticamente cuando hay modelo entrenado disponible (`risk_model.pkl`).

---

## 5.5 Ciclo de vida de una alerta

```
[activa] ──── Docente/Tutor atiende ──▶ [atendida]
    │
    └─────── Docente/Tutor descarta ──▶ [descartada]
```

Solo se genera una alerta si no hay otra alerta activa del mismo tipo para el mismo enrollment. Esto evita duplicados cuando el docente genera predicciones múltiples veces.

---

## 5.6 Importación de datos externos

Permite migrar datos de sistemas anteriores (Excel) sin necesidad de captura manual:

**Para docentes:** importan calificaciones y asistencia de sus grupos actuales. El sistema encuentra al alumno por número de control y actualiza sus registros.

**Para administradores:** importan listas de alumnos en lote. Se crea automáticamente el usuario con contraseña igual al número de control.

Ambos procesos son tolerantes a errores: una fila inválida no cancela la importación completa, sino que reporta advertencias por fila al finalizar.

---

## 5.7 Acceso remoto

Para demos o uso fuera de la institución se usa Cloudflare Tunnel (gratuito), que crea un túnel cifrado entre el servidor local y la red de Cloudflare sin necesidad de abrir puertos ni contratar hosting. La URL pública cambia al reiniciar el sistema.

---

## 5.8 Consideraciones de seguridad

- Contraseñas almacenadas con bcrypt (factor por defecto de Laravel).
- Middleware `role` verifica el rol en cada ruta antes de ejecutar el controlador.
- CSRF token requerido en todos los formularios POST/PATCH/DELETE.
- `TrustProxies` configurado para confiar en Cloudflare sin exponer headers internos arbitrarios.
- Las contraseñas de alumnos importados por CSV son su número de control — se recomienda que el administrador solicite cambio al primer acceso.

---

*Manual generado para el Sistema Predictivo de Reprobación — ITSC Informática*
