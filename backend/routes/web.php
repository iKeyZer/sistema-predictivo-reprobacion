<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TutoringController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Dashboard (redirige según rol)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// --- ESTUDIANTE ---
Route::middleware(['auth', 'role:estudiante'])->group(function () {
    Route::get('/mi-historial', [StudentController::class, 'myHistory'])->name('mi.historial');
    Route::get('/mi-riesgo', [PredictionController::class, 'myRisk'])->name('mi.riesgo');
    Route::get('/mis-alertas', [AlertController::class, 'myAlerts'])->name('mis.alertas');
});

// --- DOCENTE ---
Route::middleware(['auth', 'role:docente'])->group(function () {
    Route::get('/calificaciones', [GradeController::class, 'index'])->name('calificaciones.index');
    Route::post('/calificaciones', [GradeController::class, 'store'])->name('calificaciones.store');
    Route::post('/calificaciones/lote', [GradeController::class, 'bulkStore'])->name('calificaciones.bulk');
    Route::get('/asistencia', [AttendanceController::class, 'index'])->name('asistencia.index');
    Route::post('/asistencia', [AttendanceController::class, 'store'])->name('asistencia.store');
    Route::get('/grupos/{group}/riesgo', [PredictionController::class, 'groupRisk'])->name('grupos.riesgo');
    Route::post('/grupos/{group}/predecir', [PredictionController::class, 'generateForGroup'])->name('grupos.predecir');
    Route::get('/grupos/{group}/reporte', [ReportController::class, 'group'])->name('grupos.reporte');
    Route::get('/alertas', [AlertController::class, 'index'])->name('alertas.docente');
});

// --- TUTOR ---
Route::middleware(['auth', 'role:tutor'])->group(function () {
    Route::get('/estudiantes-en-riesgo', [StudentController::class, 'assigned'])->name('estudiantes.asignados');
    Route::get('/tutorias', [TutoringController::class, 'index'])->name('tutorias.index');
    Route::get('/tutorias/nueva', [TutoringController::class, 'create'])->name('tutorias.create');
    Route::post('/tutorias', [TutoringController::class, 'store'])->name('tutorias.store');
    Route::get('/tutorias/{tutoria}', [TutoringController::class, 'show'])->name('tutorias.show');
    Route::get('/reportes-predictivos', [ReportController::class, 'predictive'])->name('reportes.predictivos');
    Route::get('/alertas-tutor', [AlertController::class, 'index'])->name('alertas.tutor');
});

// Acciones de alertas compartidas entre docente y tutor
Route::middleware(['auth', 'role:docente,tutor'])->group(function () {
    Route::patch('/alertas/{alert}/atender', [AlertController::class, 'markAttended'])->name('alertas.atender');
    Route::patch('/alertas/{alert}/descartar', [AlertController::class, 'markDiscarded'])->name('alertas.descartar');
});

// --- COORDINADOR ---
Route::middleware(['auth', 'role:coordinador'])->group(function () {
    Route::get('/reportes', [ReportController::class, 'institutional'])->name('reportes.institucional');
    Route::get('/reportes/asignaturas', [ReportController::class, 'bySubject'])->name('reportes.asignaturas');
    Route::get('/reportes/exportar/{type}', [ReportController::class, 'export'])->name('reportes.exportar');
});

// --- ADMIN ---
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('usuarios', UserController::class);
    Route::resource('estudiantes', StudentController::class);
    Route::get('/predicciones', [PredictionController::class, 'index'])->name('predicciones.index');
    Route::post('/modelo/entrenar', [PredictionController::class, 'trainModel'])->name('modelo.entrenar');
});
