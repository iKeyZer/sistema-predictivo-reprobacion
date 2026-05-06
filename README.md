# Sistema Predictivo de Reprobación — ITSC Informática

Sistema web para predecir el riesgo de reprobación de estudiantes de la carrera de Ingeniería en Sistemas Computacionales, permitiendo intervención académica temprana para reducir la deserción escolar.

---

## Características

- **Predicción de riesgo** (Bajo / Medio / Alto) por materia y estudiante
- **Dashboards personalizados** según el rol del usuario
- **Alertas automáticas** cuando un estudiante entra en riesgo
- **Registro de tutorías** vinculadas a alertas activas
- **Reportes institucionales** con exportación a PDF
- **Microservicio ML** con Random Forest + fallback heurístico
- **Importación de datos** desde CSV/Excel (calificaciones, asistencia y alumnos)
- **Acceso remoto** vía Cloudflare Tunnel sin configuración de red

## Stack

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.3 + Laravel 11 |
| Frontend | Blade + Bootstrap 5 + Chart.js + DataTables |
| Base de datos | MySQL 8 |
| Modelo predictivo | Python 3.12 + scikit-learn + Flask |

---

## Requisitos previos

- [Laragon](https://laragon.org/) (incluye PHP 8+, MySQL, Apache)
- [PHP 8.3](https://www.php.net/) en PATH
- [Composer](https://getcomposer.org/)
- [Python 3.10+](https://www.python.org/)
- Git

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/iKeyZer/sistema-predictivo-reprobacion.git
cd sistema-predictivo-reprobacion
```

### 2. Configurar el backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus credenciales de base de datos:

```env
DB_DATABASE=sistema_predictivo
DB_USERNAME=root
DB_PASSWORD=
ML_SERVICE_URL=http://127.0.0.1:5000
```

Crea la base de datos en MySQL y ejecuta las migraciones con datos demo:

```bash
php artisan migrate --seed
```

### 3. Configurar el microservicio Python

```bash
cd ../ml-service
pip install -r requirements.txt
```

---

## Iniciar el sistema

Desde la raíz del proyecto, ejecuta el archivo `iniciar.bat` (doble clic) o manualmente:

**Terminal 1 — Laravel:**
```bash
cd backend
php artisan serve
```

**Terminal 2 — Microservicio ML:**
```bash
cd ml-service
python app.py
```

Accede en: **http://127.0.0.1:8000**

> El archivo `iniciar.bat` inicia los tres servicios automáticamente con doble clic. Si `cloudflared.exe` está en la raíz del proyecto, también levanta el túnel remoto.

---

## Acceso remoto con Cloudflare Tunnel (opcional, gratis)

Para acceder al sistema desde cualquier lugar sin configurar el router:

1. Descarga [`cloudflared.exe`](https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe)
2. Colócalo en la raíz del proyecto (junto a `iniciar.bat`)
3. Ejecuta `iniciar.bat` — el túnel se inicia automáticamente

La URL pública aparece en la ventana **"Cloudflare Tunnel"**:
```
https://abc-def-123.trycloudflare.com
```

> La URL cambia cada vez que se reinicia el sistema. Para URL fija se requiere cuenta Cloudflare gratuita.

Si hay error de DNS al conectar, añade el flag `--protocol http2`:
```bat
cloudflared tunnel --url http://localhost:8000 --protocol http2
```

---

## Importación de datos desde Excel / CSV

### Docente — Calificaciones y Asistencia

Menú lateral → **Importar CSV/Excel**. Selecciona el grupo y sube el archivo con las columnas:

| Columna | Requerida | Descripción |
|---------|-----------|-------------|
| `numero_control` | Sí | Número de control del alumno |
| `parcial_1` | No | Calificación parcial 1 (0–100) |
| `parcial_2` | No | Calificación parcial 2 (0–100) |
| `parcial_3` | No | Calificación parcial 3 (0–100) |
| `total_clases` | No | Total de clases impartidas |
| `clases_asistidas` | No | Clases a las que asistió el alumno |

### Admin — Alumnos en lote

Menú lateral → **Importar Alumnos**. Columnas del archivo:

| Columna | Requerida | Descripción |
|---------|-----------|-------------|
| `numero_control` | Sí | Número de control único |
| `nombre` | Sí | Nombre(s) del alumno |
| `apellidos` | No | Apellidos |
| `email` | No | Si se omite se genera `nc@itsc.edu.mx` |
| `carrera` | No | Clave de carrera (default: ISC) |
| `semestre` | No | Semestre actual (default: 1) |

La contraseña por defecto de alumnos importados es su número de control.

Archivos de ejemplo en la carpeta [`ejemplos/`](ejemplos/).

---

## Usuarios demo

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@sistema.edu.mx | Admin123! |
| Coordinador | coordinador@sistema.edu.mx | Coord123! |
| Tutor | tutor@sistema.edu.mx | Tutor123! |
| Docente 1 | docente1@sistema.edu.mx | Docente123! |
| Docente 2 | docente2@sistema.edu.mx | Docente123! |
| Estudiante | juan@estudiante.edu.mx | Estudiante123! |

---

## Estructura del proyecto

```
sistema-predictivo-reprobacion/
├── backend/                  # Proyecto Laravel 11
│   ├── app/
│   │   ├── Http/Controllers/ # 11 controladores
│   │   ├── Models/           # 13 modelos Eloquent
│   │   └── Services/         # PredictionService, AlertService
│   ├── database/
│   │   ├── migrations/       # 14 migraciones
│   │   └── seeders/          # Datos demo incluidos
│   ├── resources/views/      # 30+ vistas Blade
│   └── routes/
│       ├── web.php           # Rutas por rol
│       └── api.php           # Endpoints AJAX internos
├── ml-service/               # Microservicio Python Flask
│   ├── app.py                # API REST (predict, train, health)
│   ├── train.py              # Random Forest Classifier
│   └── requirements.txt
├── ejemplos/                 # Archivos CSV de prueba para importación
│   ├── calificaciones_prueba.csv
│   └── alumnos_prueba.csv
├── iniciar.bat               # Lanzador del sistema (Windows)
└── cloudflared.exe           # Tunnel Cloudflare (descargar por separado)
```

## Roles del sistema

| Rol | Acceso |
|-----|--------|
| **Estudiante** | Ver su propio riesgo, calificaciones y alertas |
| **Docente** | Registrar calificaciones y asistencia, ver riesgo por grupo |
| **Tutor** | Ver estudiantes en riesgo, registrar tutorías |
| **Coordinador** | Reportes institucionales y por asignatura |
| **Admin** | Gestión completa de usuarios, estudiantes y predicciones |

## API del microservicio ML

```
GET  /health           Estado del servicio y versión del modelo
POST /predict          Genera predicción para un estudiante
POST /train            Entrena el modelo con datos históricos
```

Ejemplo de request a `/predict`:
```json
{
  "avg_grade": 52.5,
  "attendance_pct": 68.0,
  "failed_subjects": 2,
  "academic_load": 5,
  "subject_difficulty": 38.0,
  "partial1": 55,
  "partial2": 50,
  "partial3": null
}
```

Respuesta:
```json
{
  "risk_level": "alto",
  "probability": 0.85,
  "model_version": "heuristic"
}
```

---

## Licencia

MIT — libre para uso académico y educativo.
