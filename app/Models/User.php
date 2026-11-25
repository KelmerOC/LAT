<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario del sistema con cargo, rango y permisos granulares por planta.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var list<string> */
    public const CARGOS_VALIDOS = [
        'administracion',
        'encargado_planta',
        'recolector_ruta',
        'control_tina',
        'contador_externo',
        'personalizado',
    ];

    /** @var list<string> */
    public const RANGOS_VALIDOS = [
        'administrador',
        'supervisor',
        'operador',
        'personalizado',
    ];

    /**
     * Catálogo estructurado de secciones y controles granulares del panel.
     *
     * @var array<string, array{titulo: string, descripcion: string, permisos: array<string, array{label: string, desc: string}>}>
     */
    public const PERMISOS_CATALOGO = [
        'dashboard' => [
            'titulo' => 'Dashboard y Gráficas',
            'descripcion' => 'Panel principal con KPIs, tendencias diarias y actividad reciente.',
            'permisos' => [
                'dashboard.ver' => ['label' => 'Ver dashboard general', 'desc' => 'Acceso al resumen visual y gráficas de su planta.'],
                'dashboard.ver_red' => ['label' => 'Ver comparativa de red', 'desc' => 'Ver el ranking multi-planta "¿Quién vende más?".'],
            ],
        ],
        'acopio' => [
            'titulo' => '1. Acopio por voz y tacto',
            'descripcion' => 'Recepción de leche en planta o ruta y gestión de ganaderos.',
            'permisos' => [
                'acopio.ver' => ['label' => 'Ver entregas de acopio', 'desc' => 'Consultar la lista de entregas diarias.'],
                'acopio.crear' => ['label' => 'Registrar acopio', 'desc' => 'Capturar entregas por voz o botonera táctil.'],
                'acopio.editar' => ['label' => 'Editar / eliminar acopio', 'desc' => 'Corregir litros o borrar registros erróneos.'],
                'acopio.proveedores' => ['label' => 'Gestionar ganaderos', 'desc' => 'Agregar y editar productores de la planta.'],
            ],
        ],
        'calidad' => [
            'titulo' => '2. Calidad sanitaria (74°)',
            'descripcion' => 'Prueba de alcohol y desvío ético a quesillos.',
            'permisos' => [
                'calidad.ver' => ['label' => 'Ver control sanitario', 'desc' => 'Consultar estado sanitario de entregas del día.'],
                'calidad.evaluar' => ['label' => 'Aprobar o desviar leche', 'desc' => 'Clasificar leche óptima o ácida hacia quesillo.'],
            ],
        ],
        'tinas' => [
            'titulo' => '3. Tinas y receta',
            'descripcion' => 'Monitoreo térmico, dosificación automática y lotes.',
            'permisos' => [
                'tinas.ver' => ['label' => 'Ver tinas y receta', 'desc' => 'Consultar temperatura, lotes activos e insumos.'],
                'tinas.operar' => ['label' => 'Operar lotes en tina', 'desc' => 'Iniciar lotes, actualizar temperatura/litros y finalizar.'],
                'tinas.gestionar' => ['label' => 'Crear y editar tinas', 'desc' => 'Agregar nuevas tinas y editar capacidad o estado.'],
            ],
        ],
        'finanzas' => [
            'titulo' => '4. Liquidación y ventas',
            'descripcion' => 'Pagos quincenales, movimientos financieros y Excel.',
            'permisos' => [
                'finanzas.ver' => ['label' => 'Ver liquidaciones y movimientos', 'desc' => 'Consultar tabla quincenal y listado de ventas/gastos.'],
                'finanzas.registrar' => ['label' => 'Registrar movimientos y ventas', 'desc' => 'Asentar ventas, adelantos, ayuda del día y gastos.'],
                'finanzas.editar' => ['label' => 'Editar / eliminar movimientos', 'desc' => 'Corregir o borrar registros financieros.'],
                'finanzas.exportar' => ['label' => 'Exportar balance Excel (.xlsx)', 'desc' => 'Descargar reportes financieros de la planta.'],
                'finanzas.enviar_excel' => ['label' => 'Enviar reportes Excel al contador', 'desc' => 'Generar y remitir reportes Excel ordenados a la bandeja del contador.'],
            ],
        ],
        'estadisticas' => [
            'titulo' => '5. Estadísticas avanzadas',
            'descripcion' => 'Desempeño por planta, top productores y métodos.',
            'permisos' => [
                'estadisticas.ver' => ['label' => 'Ver estadísticas y rankings', 'desc' => 'Acceso completo al módulo de analítica.'],
            ],
        ],
        'admin' => [
            'titulo' => '6. Administración y accesos',
            'descripcion' => 'Gestión de cuentas personalizadas y configuración de plantas.',
            'permisos' => [
                'admin.usuarios' => ['label' => 'Gestionar usuarios (Rango)', 'desc' => 'Crear/editar usuarios con credenciales y permisos propios.'],
                'admin.plantas' => ['label' => 'Gestionar y cambiar plantas', 'desc' => 'Crear/editar/eliminar plantas lácteas y alternar planta activa.'],
                'admin.qr' => ['label' => 'Generar códigos QR de acceso móvil', 'desc' => 'Crear enlaces y códigos QR para registro y acceso desde celular.'],
            ],
        ],
    ];

    /**
     * Plantillas base por cargo para prellenar permisos rápidamente.
     *
     * @var array<string, list<string>>
     */
    public const PLANTILLAS_CARGO = [
        'administracion' => [
            'dashboard.ver', 'dashboard.ver_red',
            'acopio.ver', 'acopio.editar', 'acopio.proveedores',
            'calidad.ver', 'calidad.evaluar',
            'tinas.ver', 'tinas.gestionar',
            'finanzas.ver', 'finanzas.registrar', 'finanzas.editar', 'finanzas.exportar', 'finanzas.enviar_excel',
            'estadisticas.ver',
            'admin.usuarios', 'admin.plantas', 'admin.qr',
        ],
        'encargado_planta' => [
            'dashboard.ver',
            'acopio.ver', 'acopio.crear', 'acopio.editar', 'acopio.proveedores',
            'calidad.ver', 'calidad.evaluar',
            'tinas.ver', 'tinas.operar', 'tinas.gestionar',
            'finanzas.ver', 'finanzas.registrar', 'finanzas.enviar_excel',
            'estadisticas.ver',
        ],
        'recolector_ruta' => [
            'acopio.ver', 'acopio.crear', 'acopio.proveedores',
        ],
        'control_tina' => [
            'calidad.ver', 'calidad.evaluar',
            'tinas.ver', 'tinas.operar',
        ],
        'contador_externo' => [
            'dashboard.ver',
            'finanzas.ver', 'finanzas.exportar',
            'estadisticas.ver',
        ],
        'personalizado' => [],
    ];

    protected $fillable = [
        'planta_id',
        'name',
        'usuario',
        'email',
        'password',
        'cargo',
        'rango',
        'permisos',
        'activo',
        'creado_por',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'permisos' => 'array',
            'activo' => 'boolean',
        ];
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(self::class, 'creado_por');
    }

    /** Solo usuarios autorizados y activos. */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Devuelve todas las claves de permisos existentes en el catálogo.
     *
     * @return list<string>
     */
    public static function todasLasClavesPermisos(): array
    {
        $claves = [];
        foreach (self::PERMISOS_CATALOGO as $seccion) {
            foreach (array_keys($seccion['permisos']) as $clave) {
                $claves[] = $clave;
            }
        }

        return $claves;
    }

    /**
     * Resuelve la lista efectiva de permisos activos del usuario.
     *
     * @return list<string>
     */
    public function permisosEfectivos(): array
    {
        if (! $this->activo) {
            return [];
        }

        if ($this->cargo === 'administracion') {
            return self::PLANTILLAS_CARGO['administracion'];
        }

        $validos = self::todasLasClavesPermisos();

        if (is_array($this->permisos)) {
            return array_values(array_intersect($validos, $this->permisos));
        }

        return self::PLANTILLAS_CARGO[$this->cargo] ?? [];
    }

    public function tienePermiso(string $permiso): bool
    {
        if (! $this->activo) {
            return false;
        }

        return in_array($permiso, $this->permisosEfectivos(), true);
    }

    /**
     * @param  array<int, string>  $permisos
     */
    public function tieneAlgunPermiso(array $permisos): bool
    {
        if (! $this->activo) {
            return false;
        }

        $efectivos = $this->permisosEfectivos();
        foreach ($permisos as $permiso) {
            if (in_array($permiso, $efectivos, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determina si el usuario puede visualizar una sección del panel lateral.
     */
    public function puedeVerSeccion(string $seccion): bool
    {
        $mapaSeccion = [
            'dashboard' => ['dashboard.ver'],
            'acopio' => ['acopio.ver', 'acopio.crear', 'acopio.editar', 'acopio.proveedores'],
            'calidad' => ['calidad.ver', 'calidad.evaluar'],
            'tinas' => ['tinas.ver', 'tinas.operar', 'tinas.gestionar'],
            'finanzas' => ['finanzas.ver', 'finanzas.registrar', 'finanzas.editar', 'finanzas.exportar', 'finanzas.enviar_excel'],
            'estadisticas' => ['estadisticas.ver'],
            'admin' => ['admin.usuarios', 'admin.plantas', 'admin.qr'],
        ];

        if (! isset($mapaSeccion[$seccion])) {
            return false;
        }

        return $this->tieneAlgunPermiso($mapaSeccion[$seccion]);
    }

    /**
     * Lista ordenada de secciones visibles para este usuario.
     *
     * @return list<string>
     */
    public function seccionesPermitidas(): array
    {
        $orden = ['dashboard', 'acopio', 'calidad', 'tinas', 'finanzas', 'estadisticas', 'admin'];
        $permitidas = [];
        foreach ($orden as $sec) {
            if ($this->puedeVerSeccion($sec)) {
                $permitidas[] = $sec;
            }
        }

        return $permitidas;
    }

    /**
     * Retorna la primera sección accesible al iniciar sesión.
     */
    public function primeraSeccionPermitida(): ?string
    {
        $permitidas = $this->seccionesPermitidas();

        return $permitidas[0] ?? null;
    }

    /**
     * Permisos que este usuario está autorizado a otorgar a otros usuarios.
     *
     * @return list<string>
     */
    public function permisosAsignables(): array
    {
        if ($this->esAdmin()) {
            return self::todasLasClavesPermisos();
        }

        if (! $this->tienePermiso('admin.usuarios')) {
            return [];
        }

        return $this->permisosEfectivos();
    }

    /**
     * Verifica jerarquía para evitar escalamiento de privilegios o edición horizontal entre supervisores.
     */
    public function puedeGestionarUsuario(self $objetivo): bool
    {
        if (! $this->activo) {
            return false;
        }

        if ($this->esAdmin()) {
            return true;
        }

        if (! $this->tienePermiso('admin.usuarios')) {
            return false;
        }

        if ($objetivo->id === $this->id || $objetivo->esAdmin()) {
            return false;
        }

        if (! in_array($objetivo->rango, ['operador', 'personalizado'], true)) {
            return false;
        }

        if ($objetivo->tienePermiso('admin.usuarios')) {
            return false;
        }

        return (int) $objetivo->planta_id === (int) $this->planta_id;
    }

    public function etiquetaCargo(): string
    {
        $clave = 'lat.cargo_'.$this->cargo;
        $trad = __($clave);

        return $trad !== $clave
            ? $trad
            : ucfirst(str_replace('_', ' ', (string) $this->cargo));
    }

    public function etiquetaRango(): string
    {
        $clave = 'lat.rango_'.$this->rango;
        $trad = __($clave);

        return $trad !== $clave
            ? $trad
            : __('lat.rango_operador');
    }

    public function esAdmin(): bool
    {
        return $this->cargo === 'administracion';
    }
}
