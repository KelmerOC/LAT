<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'planta_id' => null,
            'name' => fake()->name(),
            'usuario' => strtolower(fake()->unique()->userName()),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'cargo' => 'recolector_ruta',
            'rango' => 'operador',
            'permisos' => User::PLANTILLAS_CARGO['recolector_ruta'],
            'activo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'activo' => false,
        ]);
    }
}
