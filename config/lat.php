<?php

/*
 * Parametros operativos de una planta quesera familiar (Cusco Provincia).
 * Los coeficientes de receta provienen del levantamiento de campo (pw_pp.txt)
 * usado en la version OLA. Ajustables por planta si el maestro quesero lo indica.
 */
return [
    'comision' => (float) env('LAT_COMISION', 0.00),

    'tina' => [
        'pasteurizacion' => (float) env('LAT_TEMP_PASTEURIZACION', 72),
        'inoculacion_min' => (float) env('LAT_TEMP_INOCULACION_MIN', 36),
        'inoculacion_max' => (float) env('LAT_TEMP_INOCULACION_MAX', 40),
        // Minutos estimados de enfriamiento con agua de manantial en doble chaqueta.
        'minutos_enfriado' => 105,
    ],

    'receta' => [
        'cuajo_ml_por_litro' => 0.025,
        'sal_kg_por_litro' => 0.018,
        'cultivo_g_por_litro' => 0.010,
        'litros_por_molde' => 9,
    ],

    'quesillo_dias' => (int) env('LAT_DIAS_QUESILLO', 4),

    'politica_privacidad_version' => '2026-10',
];
