<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /** @var array<string, array{name: string, usuario: string, email: string}> */
    private const MAPA_USUARIOS = [
        'donjusto' => [
            'name' => 'Ing. Rodrigo Quispe',
            'usuario' => 'rodrigo',
            'email' => 'rodrigo@sanborreal.pe',
        ],
        'carlos.anta' => [
            'name' => 'Ing. Andrés Huamán',
            'usuario' => 'andres.anta',
            'email' => 'andres@sanborreal.pe',
        ],
        'sabina.tina' => [
            'name' => 'Téc. Valeria Cárdenas',
            'usuario' => 'valeria.tina',
            'email' => 'valeria@sanborreal.pe',
        ],
        'contador.marco' => [
            'name' => 'CPC Ricardo Paredes',
            'usuario' => 'contador.ricardo',
            'email' => 'contador@sanborreal.pe',
        ],
        'mateo.ruta' => [
            'name' => 'Téc. Javier Condori',
            'usuario' => 'javier.ruta',
            'email' => 'javier@sanborreal.pe',
        ],
    ];

    public function up(): void
    {
        $ahora = Carbon::now();
        $passwordHash = Hash::make('secreto123');

        DB::transaction(function () use ($ahora, $passwordHash): void {
            foreach (self::MAPA_USUARIOS as $anterior => $nuevo) {
                $fila = DB::table('users')->where('usuario', $anterior)->first();
                if ($fila === null) {
                    continue;
                }

                $colisionUsuario = DB::table('users')
                    ->where('usuario', $nuevo['usuario'])
                    ->where('id', '!=', $fila->id)
                    ->exists();
                $colisionEmail = DB::table('users')
                    ->where('email', $nuevo['email'])
                    ->where('id', '!=', $fila->id)
                    ->exists();

                if ($colisionUsuario || $colisionEmail) {
                    continue;
                }

                DB::table('users')->where('id', $fila->id)->update([
                    'name' => $nuevo['name'],
                    'usuario' => $nuevo['usuario'],
                    'email' => $nuevo['email'],
                    'password' => $passwordHash,
                    'activo' => true,
                    'updated_at' => $ahora,
                ]);
            }

            DB::table('users')->where('usuario', 'lucia.personal')->delete();
        });
    }

    public function down(): void
    {
        $ahora = Carbon::now();
        $reverso = [
            'rodrigo' => [
                'name' => 'Don Justo Quispe',
                'usuario' => 'donjusto',
                'email' => 'donjusto@sanborreal.pe',
            ],
            'andres.anta' => [
                'name' => 'Carlos Quispe (Hijo Encargado)',
                'usuario' => 'carlos.anta',
                'email' => 'carlos@sanborreal.pe',
            ],
            'valeria.tina' => [
                'name' => 'Sabina Huillca',
                'usuario' => 'sabina.tina',
                'email' => 'sabina@sanborreal.pe',
            ],
            'contador.ricardo' => [
                'name' => 'Marco Antonio (Contador)',
                'usuario' => 'contador.marco',
                'email' => 'contador@sanborreal.pe',
            ],
            'javier.ruta' => [
                'name' => 'Mateo Triveño',
                'usuario' => 'mateo.ruta',
                'email' => 'mateo@sanborreal.pe',
            ],
        ];

        DB::transaction(function () use ($ahora, $reverso): void {
            foreach ($reverso as $actual => $original) {
                $fila = DB::table('users')->where('usuario', $actual)->first();
                if ($fila === null) {
                    continue;
                }

                $colisionUsuario = DB::table('users')
                    ->where('usuario', $original['usuario'])
                    ->where('id', '!=', $fila->id)
                    ->exists();
                $colisionEmail = DB::table('users')
                    ->where('email', $original['email'])
                    ->where('id', '!=', $fila->id)
                    ->exists();

                if ($colisionUsuario || $colisionEmail) {
                    continue;
                }

                DB::table('users')->where('id', $fila->id)->update([
                    'name' => $original['name'],
                    'usuario' => $original['usuario'],
                    'email' => $original['email'],
                    'updated_at' => $ahora,
                ]);
            }
        });
    }
};
