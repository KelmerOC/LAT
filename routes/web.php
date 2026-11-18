<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ENRUTAMIENTO PRINCIPAL DEL SISTEMA LÁCTEO COMPACTO (LAT)
|--------------------------------------------------------------------------
*/

// Zona Pública y Cambio de Idioma
Route::get('/', [HomeController::class, 'index'])->name('inicio');
Route::get('/idioma/{lang}', [AuthController::class, 'cambiarIdioma'])->name('idioma');

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/registrarse', [AuthController::class, 'showRegister'])->name('registro');
Route::post('/registrarse', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Zona Privada (Protegida por Autenticación y Permisos Granulares)
Route::middleware('auth')->prefix('panel')->group(function (): void {
    Route::get('/', [PanelController::class, 'index'])->name('panel');
    Route::post('/cambiar-planta', [PanelController::class, 'cambiarPlanta'])
        ->middleware('permiso:admin.plantas')
        ->name('panel.cambiar_planta');

    // Módulo 1: Acopio y Edición de datos
    Route::post('/acopio', [PanelController::class, 'guardarAcopio'])
        ->middleware('permiso:acopio.crear')
        ->name('panel.acopio');
    Route::put('/acopio/{id}', [PanelController::class, 'actualizarAcopio'])
        ->middleware('permiso:acopio.editar')
        ->name('panel.acopio.update');
    Route::delete('/acopio/{id}', [PanelController::class, 'eliminarAcopio'])
        ->middleware('permiso:acopio.editar')
        ->name('panel.acopio.delete');

    // Módulo 2: Calidad sanitaria
    Route::post('/calidad/{id}', [PanelController::class, 'evaluarCalidad'])
        ->middleware('permiso:calidad.evaluar')
        ->name('panel.calidad');

    // Módulo 3: Múltiples Tinas y Lotes
    Route::post('/tinas', [PanelController::class, 'crearTina'])
        ->middleware('permiso:tinas.gestionar')
        ->name('panel.tinas.store');
    Route::put('/tinas/{id}', [PanelController::class, 'editarTina'])
        ->middleware('permiso:tinas.gestionar')
        ->name('panel.tinas.update');
    Route::post('/tinas/{tinaId}/lote', [PanelController::class, 'iniciarLoteEnTina'])
        ->middleware('permiso:tinas.operar')
        ->name('panel.tinas.lote');
    Route::post('/tina/{id}', [PanelController::class, 'actualizarTina'])
        ->middleware('permiso:tinas.operar')
        ->name('panel.tina');

    // Módulo 4: Movimientos, Ventas y Proveedores
    Route::post('/movimiento', [PanelController::class, 'registrarMovimiento'])
        ->middleware('permiso:finanzas.registrar')
        ->name('panel.movimiento');
    Route::put('/movimiento/{id}', [PanelController::class, 'actualizarMovimiento'])
        ->middleware('permiso:finanzas.editar')
        ->name('panel.movimiento.update');
    Route::delete('/movimiento/{id}', [PanelController::class, 'eliminarMovimiento'])
        ->middleware('permiso:finanzas.editar')
        ->name('panel.movimiento.delete');
    Route::post('/proveedor', [PanelController::class, 'guardarProveedor'])
        ->middleware('permiso:acopio.proveedores')
        ->name('panel.proveedor.store');
    Route::put('/proveedor/{id}', [PanelController::class, 'actualizarProveedor'])
        ->middleware('permiso:acopio.proveedores')
        ->name('panel.proveedor.update');

    // Módulo 5: Exportación Excel (.xlsx)
    Route::get('/exportar-excel', [PanelController::class, 'exportarExcel'])
        ->middleware('permiso:finanzas.exportar')
        ->name('panel.exportar');
    Route::get('/exportar-csv', [PanelController::class, 'exportarExcel'])
        ->middleware('permiso:finanzas.exportar')
        ->name('panel.exportar_legacy');

    // Módulo 6: Administración de Plantas y Usuarios Personalizados
    Route::post('/admin/usuarios', [AdminController::class, 'crearUsuario'])
        ->middleware('permiso:admin.usuarios')
        ->name('admin.usuarios.store');
    Route::put('/admin/usuarios/{id}', [AdminController::class, 'actualizarUsuario'])
        ->middleware('permiso:admin.usuarios')
        ->name('admin.usuarios.update');
    Route::post('/admin/plantas', [AdminController::class, 'crearPlanta'])
        ->middleware('permiso:admin.plantas')
        ->name('admin.plantas.store');
    Route::put('/admin/plantas/{id}', [AdminController::class, 'actualizarPlanta'])
        ->middleware('permiso:admin.plantas')
        ->name('admin.plantas.update');
});
