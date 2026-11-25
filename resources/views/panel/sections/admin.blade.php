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
  $qrFlash = session('qr_generado');
@endphp

<section id="sec-admin" class="tab-panel" aria-label="Administración de plantas y usuarios" data-i18n-aria="tab.admin" hidden>
  @if($usuario->tienePermiso('admin.plantas'))
    <div class="panel-card" style="margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <div>
          <h2 data-i18n="m6.plants_title">Administrador de plantas lácteas</h2>
          <p style="margin: 0;" data-i18n="m6.plants_sub">Cree, edite, configure el precio por litro o envíe al tacho (con restauración de 30 días).</p>
        </div>
        <button type="button" class="btn btn-ember" data-open-modal="modal-nueva-planta" data-i18n="m6.add_plant">+ Nueva planta</button>
      </div>

      <div class="tabla-region" role="region" aria-label="Plantas lácteas" data-i18n-aria="m6.plants_title" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th data-i18n="m6.col_code">Código</th>
              <th data-i18n="m6.col_plant_name">Nombre de la Planta</th>
              <th data-i18n="m6.col_location">Ubicación</th>
              <th class="num" data-i18n="m6.col_price">Precio/L</th>
              <th class="num" data-i18n="m6.col_vats">Tinas</th>
              <th class="num" data-i18n="m6.col_users">Usuarios</th>
              <th data-i18n="m6.col_status">Estado</th>
              <th data-i18n="m1.col_actions">Acciones</th>
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
                  <span class="chip {{ $pl->activa ? 'chip-verde' : 'chip-rojo' }}" data-i18n="{{ $pl->activa ? 'm3.active' : 'm3.inactive' }}">{{ $pl->activa ? 'Activa' : 'Inactiva' }}</span>
                </td>
                <td>
                  <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                    <button type="button" class="btn-micro js-edit-planta"
                      data-id="{{ $pl->id }}"
                      data-codigo="{{ $pl->codigo }}"
                      data-nombre="{{ $pl->nombre }}"
                      data-ubicacion="{{ $pl->ubicacion }}"
                      data-precio="{{ $pl->precio_litro }}"
                      data-activa="{{ $pl->activa ? '1' : '0' }}">
                      {!! $icoEditar !!} <span data-i18n="common.edit">Editar</span>
                    </button>
                    @if($usuario->esAdmin() && $plantas->count() > 1)
                      <form method="POST" action="{{ route('admin.plantas.delete', $pl->id) }}"
                        class="js-confirm-form"
                        data-confirm-kind="trash_plant"
                        data-nombre="{{ $pl->nombre }}"
                        data-impacto="{{ ($pl->tinas_count ?? 0) }} tinas, {{ ($pl->users_count ?? 0) }} usuarios, {{ ($pl->proveedores_count ?? 0) }} productores">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-micro btn-micro-danger">Eliminar</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    @if($usuario->esAdmin() && $plantasPapelera->isNotEmpty())
      <div class="panel-card panel-papelera" style="margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.85rem;">
          <div>
            <h3 style="margin: 0;">Papelera de plantas (Tacho · retención {{ \App\Models\Planta::DIAS_RETENCION_PAPELERA }} días)</h3>
            <p style="margin: 0.2rem 0 0; font-size: 0.9rem;">Las plantas enviadas al tacho conservan sus registros durante 30 días antes de eliminarse definitivamente.</p>
          </div>
          <span class="chip chip-amarillo">{{ $plantasPapelera->count() }} en tacho</span>
        </div>
        <div class="tabla-region" role="region" aria-label="Papelera de plantas" tabindex="0">
          <table class="tabla-plana">
            <thead>
              <tr>
                <th>Código</th>
                <th>Planta</th>
                <th>Datos resguardados</th>
                <th>Tiempo restante</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($plantasPapelera as $trash)
                <tr>
                  <td class="num-tabular"><strong>{{ $trash->codigo }}</strong></td>
                  <td>
                    <strong>{{ $trash->nombre }}</strong>
                    <small>{{ $trash->ubicacion }}@if($trash->eliminador) · Eliminada por {{ $trash->eliminador->name }}@endif</small>
                  </td>
                  <td>{{ $trash->tinas_count }} tinas · {{ $trash->proveedores_count }} productores · {{ $trash->acopios_count }} acopios</td>
                  <td>
                    <span class="chip chip-amarillo">{{ $trash->diasRestantesPapelera() }} días</span>
                    <small>Vence: {{ $trash->expiraPapeleraEn()?->format('d/m/Y') }}</small>
                  </td>
                  <td>
                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                      <form method="POST" action="{{ route('admin.plantas.restore', $trash->id) }}"
                        class="js-confirm-form"
                        data-confirm-kind="restore_plant"
                        data-nombre="{{ $trash->nombre }}">
                        @csrf
                        <button type="submit" class="btn-micro">Restaurar</button>
                      </form>
                      <form method="POST" action="{{ route('admin.plantas.force_delete', $trash->id) }}"
                        class="js-confirm-form"
                        data-confirm-kind="force_delete_plant"
                        data-nombre="{{ $trash->nombre }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-micro btn-micro-danger">Eliminar definitivo</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif
  @endif

  @if($usuario->tieneAlgunPermiso(['admin.qr', 'admin.usuarios']))
    <div class="panel-card panel-card-celeste" style="margin-bottom: 1.5rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <div>
          <h2 style="margin-bottom: 0.25rem;">Acceso Móvil por Código QR</h2>
          <p style="margin: 0;">Genera un código QR para que un recolector o evaluador se registre/ingrese desde su celular y pruebe el acopio en campo.</p>
        </div>
      </div>

      <div class="panel-grid-2" style="align-items: start;">
        <form method="POST" action="{{ route('admin.qr.store') }}" class="panel-card" style="padding: 1.25rem;">
          @csrf
          <div class="form-grupo">
            <label for="qr-etiqueta" class="form-label">Nombre de la invitación / prueba</label>
            <input type="text" id="qr-etiqueta" name="etiqueta" class="input-lat" required value="Evaluación Móvil Campo" maxlength="120">
          </div>
          <div class="grid-form-2">
            <div class="form-grupo">
              <label for="qr-cargo" class="form-label">Rol asignado</label>
              <select id="qr-cargo" name="cargo_asignado" class="select-lat">
                <option value="recolector_ruta">Recolector en ruta (Acopio)</option>
                <option value="control_tina">Control de tina / calidad</option>
                <option value="encargado_planta">Encargado de planta</option>
                <option value="contador_externo">Contador externo</option>
              </select>
            </div>
            <div class="form-grupo">
              <label for="qr-planta-id" class="form-label">Planta destino</label>
              @if($usuario->esAdmin())
                <select id="qr-planta-id" name="planta_id" class="select-lat">
                  @foreach($plantas as $pl)
                    <option value="{{ $pl->id }}" @selected($pl->id === $plantaActiva->id)>{{ $pl->codigo }} · {{ $pl->nombre }}</option>
                  @endforeach
                </select>
              @else
                <input type="hidden" name="planta_id" value="{{ $plantaActiva->id }}">
                <input type="text" id="qr-planta-id" class="input-lat" value="{{ $plantaActiva->nombre }}" readonly>
              @endif
            </div>
          </div>
          <div class="grid-form-2">
            <div class="form-grupo">
              <label for="qr-horas" class="form-label">Vigencia (horas)</label>
              <input type="number" id="qr-horas" name="horas_vigencia" class="input-lat" min="1" max="168" value="24">
            </div>
            <div class="form-grupo">
              <label for="qr-usos" class="form-label">Máx. registros</label>
              <input type="number" id="qr-usos" name="max_usos" class="input-lat" min="1" max="50" value="10">
            </div>
          </div>
          <button type="submit" class="btn btn-ember btn-bloque">Generar Código QR Móvil</button>
        </form>

        <div class="panel-card" style="padding: 1.25rem;">
          @if($qrFlash)
            <div class="qr-preview-box">
              <div id="qr-svg-preview" data-qr-path="{{ $qrFlash['url_path'] }}"></div>
              <div style="flex: 1; min-width: 200px;">
                <span class="chip chip-verde" style="margin-bottom: 0.45rem;">QR listo · {{ $qrFlash['planta'] }}</span>
                <p style="margin: 0 0 0.5rem; font-size: 0.88rem;">Expira: <strong>{{ $qrFlash['expira_en'] }}</strong> · Máx. {{ $qrFlash['max_usos'] }} usos</p>
                <div class="form-grupo" style="margin-bottom: 0.65rem;">
                  <label for="qr-base-url" class="form-label" style="font-size: 0.8rem;">URL base / IP LAN para celular</label>
                  <input type="url" id="qr-base-url" class="input-lat" value="{{ url('/') }}" style="min-height: 38px; font-size: 0.85rem;">
                </div>
                <p id="qr-full-link-text" style="font-size: 0.78rem; word-break: break-all; color: var(--color-graphite); margin-bottom: 0.65rem;"></p>
                <div style="display: flex; gap: 0.45rem; flex-wrap: wrap;">
                  <button type="button" id="btn-download-qr-svg" class="btn-micro">Descargar SVG</button>
                  <a id="qr-test-link" href="{{ $qrFlash['url_path'] }}" target="_blank" rel="noopener" class="btn-micro">Probar enlace</a>
                </div>
              </div>
            </div>
          @else
            <div class="qr-preview-box">
              <div id="qr-svg-preview" data-qr-path="{{ route('login', [], false) }}"></div>
              <div style="flex: 1; min-width: 200px;">
                <span class="chip chip-celeste" style="margin-bottom: 0.45rem;">QR rápido a Login</span>
                <p style="margin: 0 0 0.5rem; font-size: 0.88rem;">Genera un enlace de invitación a la izquierda para registrar un usuario nuevo desde celular, o escanea este QR para abrir <strong>/login</strong>.</p>
                <div class="form-grupo" style="margin-bottom: 0.65rem;">
                  <label for="qr-base-url" class="form-label" style="font-size: 0.8rem;">URL base / IP LAN para celular</label>
                  <input type="url" id="qr-base-url" class="input-lat" value="{{ url('/') }}" style="min-height: 38px; font-size: 0.85rem;">
                </div>
                <p id="qr-full-link-text" style="font-size: 0.78rem; word-break: break-all; color: var(--color-graphite); margin-bottom: 0.65rem;"></p>
                <button type="button" id="btn-download-qr-svg" class="btn-micro">Descargar SVG</button>
              </div>
            </div>
          @endif

          @if($qrAccesos->isNotEmpty())
            <div style="margin-top: 1rem; border-top: 1px solid var(--color-hairline); padding-top: 0.85rem;">
              <strong style="font-size: 0.86rem;">Códigos QR recientes ({{ $plantaActiva->codigo }})</strong>
              <ul class="qr-history-list">
                @foreach($qrAccesos as $qra)
                  <li>
                    <div>
                      <strong>{{ $qra->etiqueta }}</strong>
                      <small>{{ $qra->cargo_asignado }} · Usos: {{ $qra->usos_actuales }}/{{ $qra->max_usos }} · Vence: {{ $qra->expira_en?->format('d/m H:i') }}</small>
                    </div>
                    <form method="POST" action="{{ route('admin.qr.delete', $qra->id) }}">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-micro btn-micro-danger">Revocar</button>
                    </form>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif

  @if($usuario->tienePermiso('admin.usuarios'))
    <div class="panel-card">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
        <div>
          <h2 data-i18n="m6.users_title">Gestión de usuarios personalizados y credenciales</h2>
          <p style="margin: 0;">
            <span data-i18n="m6.users_sub">Cree usuarios con credenciales propias y elija exactamente qué secciones y controles pueden usar.</span>
            @unless($usuario->esAdmin())
              <span class="chip chip-amarillo" style="margin-left: 0.35rem;">{{ $plantaActiva->nombre }}</span>
            @endunless
          </p>
        </div>
        <button type="button" class="btn btn-ocean" id="btn-abrir-nuevo-usuario" data-open-modal="modal-nuevo-usuario" data-i18n="m6.add_user">+ Crear usuario personalizado</button>
      </div>

      <div class="tabla-region" role="region" aria-label="Usuarios del sistema" data-i18n-aria="m6.users_aria" tabindex="0">
        <table class="tabla-plana">
          <thead>
            <tr>
              <th data-i18n="m6.col_name">Nombre</th>
              <th data-i18n="m6.col_cred">Credencial</th>
              <th data-i18n="m6.col_role_rank">Cargo y Rango</th>
              <th data-i18n="m6.col_perms">Secciones y controles permitidos</th>
              <th data-i18n="m6.col_plant">Planta</th>
              <th data-i18n="m6.col_status">Estado</th>
              <th data-i18n="m1.col_actions">Acciones</th>
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
                  @if($u->email)
                    <small>{{ $u->email }}</small>
                  @else
                    <small data-i18n="m6.no_email">Sin correo (acceso por usuario)</small>
                  @endif
                  @if($u->creador)
                    <small><span data-i18n="m6.created_by">Creado por:</span> {{ $u->creador->name }}</small>
                  @endif
                </td>
                <td class="num-tabular">{{ $u->usuario }}</td>
                <td>
                  <span class="chip chip-celeste" data-i18n="m6.role_{{ $u->cargo }}">{{ $u->etiquetaCargo() }}</span>
                  <small style="margin-top: 4px;" data-i18n="m6.rank_{{ $u->rango }}">{{ $u->etiquetaRango() }}</small>
                </td>
                <td>
                  <div class="user-perms-chips">
                    @if($u->esAdmin())
                      <span class="chip chip-verde"><span data-i18n="m6.full_access">Acceso Administrativo</span> ({{ count($permsU) }})</span>
                    @else
                      @forelse($secsU as $secClave)
                        <span class="chip chip-fog" data-i18n="perm.sec.{{ $secClave }}">{{ $etiquetasSeccion[$secClave] ?? $secClave }}</span>
                      @empty
                        <span class="chip chip-rojo">0</span>
                      @endforelse
                      <small style="width: 100%; margin-top: 2px;">{{ count($permsU) }} <span data-i18n="m6.controls_count">controles</span></small>
                    @endif
                  </div>
                </td>
                <td>{{ $u->planta?->nombre ?? '—' }}</td>
                <td><span class="chip {{ $u->activo ? 'chip-verde' : 'chip-rojo' }}" data-i18n="{{ $u->activo ? 'm3.active' : 'm3.inactive' }}">{{ $u->activo ? 'Activo' : 'Inactivo' }}</span></td>
                <td>
                  @if($usuario->puedeGestionarUsuario($u))
                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
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
                        {!! $icoEditar !!} <span data-i18n="common.edit">Editar</span>
                      </button>
                      @if($u->id !== $usuario->id)
                        <form method="POST" action="{{ route('admin.usuarios.delete', $u->id) }}"
                          class="js-confirm-form"
                          data-confirm-kind="delete"
                          data-detalle="usuario {{ $u->name }}">
                          @csrf @method('DELETE')
                          <button type="submit" class="btn-micro btn-micro-danger" data-i18n="common.delete">Eliminar</button>
                        </form>
                      @endif
                    </div>
                  @else
                    <span class="chip chip-fog" data-i18n="m6.protected">Protegido</span>
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
