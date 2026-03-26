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
└── iniciar.bat               # Lanzador del sistema (Windows)
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
