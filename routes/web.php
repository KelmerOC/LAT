<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ENRUTAMIENTO PRINCIPAL DEL SISTEMA LÁCTEO COMPACTO (LAT)
|--------------------------------------------------------------------------
| Rutas limpias, expresivas y agrupadas según la zona de acceso.
*/

// Zona Pública
Route::get('/', [HomeController::class, 'index'])->name('inicio');
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Zona Privada (Protegida por Autenticación)
Route::middleware('auth')->prefix('panel')->group(function () {
    Route::get('/', [PanelController::class, 'index'])->name('panel');
    Route::post('/acopio', [PanelController::class, 'guardarAcopio'])->name('panel.acopio');
    Route::post('/calidad/{id}', [PanelController::class, 'evaluarCalidad'])->name('panel.calidad');
    Route::post('/tina/{id}', [PanelController::class, 'actualizarTina'])->name('panel.tina');
    Route::post('/movimiento', [PanelController::class, 'registrarMovimiento'])->name('panel.movimiento');
    Route::get('/exportar-csv', [PanelController::class, 'exportarCsv'])->name('panel.exportar');
});
