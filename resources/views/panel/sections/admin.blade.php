@php
  $etiquetasSeccion = [
    'dashboard'    => 'Dashboard',
    'acopio'       => 'Acopio',
    'calidad'      => 'Calidad',
    'tinas'        => 'Tinas',
    'finanzas'     => 'Liquidación',
    'estadisticas' => 'Estadísticas',
    'admin'        => 'Usuarios/Plantas',
  ];
@endphp

<section id="sec-admin" class="tab-panel" aria-label="Administración de plantas y usuarios" hidden>
  @if($usuario->tienePermiso('admin.plantas'))
    <div class="panel-card" style="margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <div>
          <h2 data-i18n="m6.plants_title">Administrador de plantas lácteas</h2>
          <p style="margin: 0;">Cree, edite y configure el precio por litro de cada planta comunitaria.</p>
        </div>
        <button type="button" class="btn btn-ember" data-open-modal="modal-nueva-planta" data-i18n="m6.add_plant">+ Nueva planta</button>
      </div>

      <div class="tabla-region" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombre de la Planta</th>
              <th>Ubicación</th>
              <th class="num">Precio/L</th>
              <th class="num">Tinas</th>
              <th class="num">Usuarios</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($plantas as $pl)
              <tr>
                <td class="num-tabular"><strong>{{ $pl->codigo }}</strong></td>
                <td>{{ $pl->nombre }}</td>
                <td>{{ $pl->ubicacion }}</td>
                <td class="num">S/ {{ number_format($pl->precio_litro, 2) }}</td>
                <td class="num">{{ $pl->tinas_count ?? $pl->tinas()->count() }}</td>
                <td class="num">{{ $pl->users_count ?? $pl->users()->count() }}</td>
                <td>
                  <span class="chip {{ $pl->activa ? 'chip-verde' : 'chip-rojo' }}">{{ $pl->activa ? 'Activa' : 'Inactiva' }}</span>
                </td>
                <td>
                  <button type="button" class="btn-micro js-edit-planta"
                    data-id="{{ $pl->id }}"
                    data-codigo="{{ $pl->codigo }}"
                    data-nombre="{{ $pl->nombre }}"
                    data-ubicacion="{{ $pl->ubicacion }}"
                    data-precio="{{ $pl->precio_litro }}"
                    data-activa="{{ $pl->activa ? '1' : '0' }}">
                    {!! $icoEditar !!} Editar
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  @if($usuario->tienePermiso('admin.usuarios'))
    <div class="panel-card">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <div>
          <h2 data-i18n="m6.users_title">Gestión de usuarios personalizados y credenciales</h2>
          <p style="margin: 0;">
            Cree usuarios con credenciales propias y elija exactamente qué <strong>secciones y controles</strong> pueden usar.
            @unless($usuario->esAdmin())
              <span class="chip chip-amarillo" style="margin-left: 0.35rem;">Tu rango permite crear cuentas para {{ $plantaActiva->nombre }} con tus mismos permisos o menos</span>
            @endunless
          </p>
        </div>
        <button type="button" class="btn btn-ocean" id="btn-abrir-nuevo-usuario" data-open-modal="modal-nuevo-usuario" data-i18n="m6.add_user">+ Crear usuario personalizado</button>
      </div>

      <div class="tabla-region" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Credencial</th>
              <th>Cargo y Rango</th>
              <th>Secciones y controles permitidos</th>
              <th>Planta</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @foreach($usuarios as $u)
              @php
                $permsU = $u->permisosEfectivos();
                $secsU = $u->seccionesPermitidas();
              @endphp
              <tr>
                <td>
                  <strong>{{ $u->name }}</strong>
                  <small>{{ $u->email ?? 'Sin correo (acceso por usuario)' }}</small>
                  @if($u->creador)
                    <small>Creado por: {{ $u->creador->name }}</small>
                  @endif
                </td>
                <td class="num-tabular">{{ $u->usuario }}</td>
                <td>
                  <span class="chip chip-celeste">{{ $u->etiquetaCargo() }}</span>
                  <small style="margin-top: 4px;">{{ $u->etiquetaRango() }}</small>
                </td>
                <td>
                  <div class="user-perms-chips">
                    @if($u->esAdmin())
                      <span class="chip chip-verde">Acceso Total ({{ count($permsU) }} controles)</span>
                    @else
                      @forelse($secsU as $secClave)
                        <span class="chip chip-fog">{{ $etiquetasSeccion[$secClave] ?? $secClave }}</span>
                      @empty
                        <span class="chip chip-rojo">Sin secciones</span>
                      @endforelse
                      <small style="width: 100%; margin-top: 2px;">{{ count($permsU) }} controles activos</small>
                    @endif
                  </div>
                </td>
                <td>{{ $u->planta?->nombre ?? 'Sin planta' }}</td>
                <td><span class="chip {{ $u->activo ? 'chip-verde' : 'chip-rojo' }}">{{ $u->activo ? 'Activo' : 'Inactivo' }}</span></td>
                <td>
                  @if($usuario->puedeGestionarUsuario($u))
                    <button type="button" class="btn-micro js-edit-usuario"
                      data-id="{{ $u->id }}"
                      data-name="{{ $u->name }}"
                      data-usuario="{{ $u->usuario }}"
                      data-email="{{ $u->email ?? '' }}"
                      data-cargo="{{ $u->cargo }}"
                      data-rango="{{ $u->rango }}"
                      data-planta="{{ $u->planta_id ?? $plantaActiva->id }}"
                      data-activo="{{ $u->activo ? '1' : '0' }}"
                      data-permisos="{{ json_encode($permsU) }}">
                      {!! $icoEditar !!} Editar
                    </button>
                  @else
                    <span class="chip chip-fog">Protegido</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
</section>
