<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/predicciones/estudiante/{id}', [PredictionController::class, 'apiGetByStudent']);
    Route::post('/predicciones/generar/{enrollment}', [PredictionController::class, 'apiGenerate']);
    Route::get('/alertas/activas', [AlertController::class, 'apiActive']);
    Route::get('/reportes/estadisticas', [ReportController::class, 'apiStats']);
    Route::get('/dashboard/charts', [DashboardController::class, 'apiCharts']);
});
