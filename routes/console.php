<?php

use App\Models\Planta;
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

Schedule::command('lat:purgar-plantas')->daily();
