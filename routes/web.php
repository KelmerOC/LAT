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
Route::post('/registrarse', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Zona Privada (Protegida por Autenticación y Permisos Granulares)
Route::middleware('auth')->prefix('panel')->group(function (): void {
    Route::get('/', [PanelController::class, 'index'])->name('panel');
    Route::post('/cambiar-planta', [PanelController::class, 'cambiarPlanta'])
        ->middleware('permiso:admin.plantas')
        ->name('panel.cambiar_planta');

    // Módulo 1: Acopio, Sincronización Offline y Edición de datos
    Route::post('/acopio', [PanelController::class, 'guardarAcopio'])
        ->middleware('permiso:acopio.crear')
        ->name('panel.acopio');
    Route::post('/acopio/sync', [PanelController::class, 'sincronizarAcopiosOffline'])
        ->middleware('permiso:acopio.crear')
        ->name('panel.acopio.sync');
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
    Route::put('/tinas/recetas-base', [PanelController::class, 'actualizarRecetasBase'])
        ->middleware('permiso:tinas.gestionar')
        ->name('panel.tinas.recetas_base');
    Route::post('/tinas', [PanelController::class, 'crearTina'])
        ->middleware('permiso:tinas.gestionar')
        ->name('panel.tinas.store');
    Route::put('/tinas/{id}', [PanelController::class, 'editarTina'])
        ->middleware('permiso:tinas.gestionar')
        ->name('panel.tinas.update');
    Route::delete('/tinas/{id}', [PanelController::class, 'eliminarTina'])
        ->middleware('permiso:tinas.gestionar')
        ->name('panel.tinas.delete');
    Route::post('/tinas/{tinaId}/lote', [PanelController::class, 'iniciarLoteEnTina'])
        ->middleware('permiso:tinas.operar')
        ->name('panel.tinas.lote');
    Route::post('/tina/{id}', [PanelController::class, 'actualizarTina'])
        ->middleware('permiso:tinas.operar')
        ->name('panel.tina');

    // Notificaciones internas del panel y comunicación contable
    Route::get('/notificaciones/poll', [PanelController::class, 'consultarNotificaciones'])
        ->name('panel.notificaciones.poll');
    Route::post('/notificaciones/leer', [PanelController::class, 'marcarNotificacionesLeidas'])
        ->name('panel.notificaciones.leer');
    Route::post('/notificaciones/contable', [PanelController::class, 'enviarNotificacionContable'])
        ->name('panel.notificaciones.contable');

    // Módulo 4: Movimientos, Ventas, Liquidación editable y Proveedores
    Route::put('/finanzas/liquidacion-grid', [PanelController::class, 'guardarLiquidacionGrid'])
        ->middleware('permiso:finanzas.editar')
        ->name('panel.finanzas.liquidacion_grid');
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
    Route::delete('/proveedor/{id}', [PanelController::class, 'eliminarProveedor'])
        ->middleware('permiso:acopio.proveedores')
        ->name('panel.proveedor.delete');

    // Módulo 5: Exportación Excel (.xlsx) e Intercambio con Contador
    Route::get('/exportar-excel', [PanelController::class, 'exportarExcel'])
        ->middleware('permiso:finanzas.exportar')
        ->name('panel.exportar');
    Route::get('/exportar-csv', [PanelController::class, 'exportarExcel'])
        ->middleware('permiso:finanzas.exportar')
        ->name('panel.exportar_legacy');
    Route::post('/reportes-excel/enviar', [PanelController::class, 'enviarReporteExcel'])
        ->middleware('permiso:finanzas.enviar_excel,finanzas.registrar')
        ->name('panel.reportes_excel.send');
    Route::get('/reportes-excel/{id}/descargar', [PanelController::class, 'descargarReporteExcel'])
        ->middleware('permiso:finanzas.ver,finanzas.exportar,finanzas.enviar_excel')
        ->name('panel.reportes_excel.download');
    Route::post('/reportes-excel/{id}/revisar', [PanelController::class, 'marcarReporteRevisado'])
        ->middleware('permiso:finanzas.exportar,finanzas.ver')
        ->name('panel.reportes_excel.review');

    // Módulo 5: Estadísticas e Informes de Actividad por Planta
    Route::post('/informes-planta/enviar', [PanelController::class, 'enviarInformePlantaManual'])
        ->name('panel.informes_planta.enviar');
    Route::get('/estadisticas/informes/{id}/descargar', [PanelController::class, 'descargarInformePlantaTxt'])
        ->name('panel.estadisticas.informes.download');

    // Módulo 6: Administración de Plantas (con Papelera), Usuarios y Modo Operativo
    Route::post('/admin/modo-operativo', [PanelController::class, 'alternarModoOperativo'])
        ->name('panel.admin.modo_operativo');
    Route::post('/admin/usuarios', [AdminController::class, 'crearUsuario'])
        ->middleware('permiso:admin.usuarios')
        ->name('admin.usuarios.store');
    Route::put('/admin/usuarios/{id}', [AdminController::class, 'actualizarUsuario'])
        ->middleware('permiso:admin.usuarios')
        ->name('admin.usuarios.update');
    Route::delete('/admin/usuarios/{id}', [AdminController::class, 'eliminarUsuario'])
        ->middleware('permiso:admin.usuarios')
        ->name('admin.usuarios.delete');
    Route::post('/admin/plantas', [AdminController::class, 'crearPlanta'])
        ->middleware('permiso:admin.plantas')
        ->name('admin.plantas.store');
    Route::put('/admin/plantas/{id}', [AdminController::class, 'actualizarPlanta'])
        ->middleware('permiso:admin.plantas')
        ->name('admin.plantas.update');
    Route::delete('/admin/plantas/{id}', [AdminController::class, 'eliminarPlanta'])
        ->middleware('permiso:admin.plantas')
        ->name('admin.plantas.delete');
    Route::post('/admin/plantas/{id}/restaurar', [AdminController::class, 'restaurarPlanta'])
        ->middleware('permiso:admin.plantas')
        ->name('admin.plantas.restore');
    Route::delete('/admin/plantas/{id}/definitivo', [AdminController::class, 'eliminarPlantaDefinitivo'])
        ->middleware('permiso:admin.plantas')
        ->name('admin.plantas.force_delete');
});
