<?php

use App\Models\Planta;
use App\Services\InformePlantaService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Muestra una cita inspiradora');

Artisan::command('lat:purgar-plantas', function (): void {
    $cantidad = Planta::purgarExpiradas();
    $this->info("Plantas expiradas purgadas de papelera: {$cantidad}");
})->purpose('Elimina definitivamente las plantas que superaron 30 días en papelera.');

Artisan::command('lat:generar-informes-planta', function (): void {
    $generados = app(InformePlantaService::class)->verificarYGenerarInformes24h(true);
    $this->info("Informes de actividad de planta generados: {$generados}");
})->purpose('Genera informes automáticos de actividad de las últimas 24 horas por planta.');

Schedule::command('lat:purgar-plantas')->daily();
Schedule::command('lat:generar-informes-planta')->daily();
