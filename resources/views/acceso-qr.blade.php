@extends('layouts.app')

@section('titulo', 'LAT · Acceso Rápido Móvil por QR')

@section('contenido')
  <section class="login-wrap lienzo-acuarela" aria-labelledby="titulo-qr">
    <div class="aguada aguada-cielo" aria-hidden="true"></div>
    <div class="contenedor">
      <div class="login-card on-fog" style="max-width: 500px;">
        @if(! $vigente)
          <header>
            <span class="chip chip-rojo" style="margin-bottom: 0.75rem;">Enlace no disponible</span>
            <h2 id="titulo-qr">Código QR expirado o revocado</h2>
            <p>Este código de invitación ya caducó, alcanzó el límite de registros o fue desactivado por el administrador de la planta.</p>
          </header>
          <a href="{{ route('login') }}" class="btn btn-ocean btn-bloque btn-grande">Ir a Iniciar Sesión</a>
        @else
          <header>
            <span class="chip chip-celeste" style="margin-bottom: 0.65rem;">{{ $qr->planta->codigo }} · {{ $qr->planta->nombre }}</span>
            <h2 id="titulo-qr">Acceso Móvil Rápido</h2>
            <p>Invitación: <strong>{{ $qr->etiqueta }}</strong> · Rol asignado: <strong>{{ ucfirst(str_replace('_', ' ', $qr->cargo_asignado)) }}</strong>. Registra tus datos para ingresar directo a Acopio desde tu celular.</p>
          </header>

          <form method="POST" action="{{ route('qr.register', ['token' => $token]) }}">
            @csrf
            <div class="form-grupo">
              <label for="qr-name" class="form-label">Nombre completo</label>
              <input type="text" id="qr-name" name="name" class="input-lat" value="{{ old('name') }}" required placeholder="Ej: Prof. Evaluador LAT">
            </div>
            <div class="form-grupo">
              <label for="qr-usuario" class="form-label">Usuario de acceso</label>
              <input type="text" id="qr-usuario" name="usuario" class="input-lat" value="{{ old('usuario') }}" required minlength="3" pattern="[a-zA-Z0-9._-]+" placeholder="ej: profe.acopio">
            </div>
            <div class="grid-form-2">
              <div class="form-grupo">
                <label for="qr-password" class="form-label">Contraseña (mín. 8)</label>
                <input type="password" id="qr-password" name="password" class="input-lat" required minlength="8" autocomplete="new-password">
              </div>
              <div class="form-grupo">
                <label for="qr-password-conf" class="form-label">Confirmar</label>
                <input type="password" id="qr-password-conf" name="password_confirmation" class="input-lat" required minlength="8" autocomplete="new-password">
              </div>
            </div>
            <button type="submit" class="btn btn-ember btn-bloque btn-grande">Crear acceso y entrar a Acopio</button>
          </form>

          <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--color-hairline); text-align: center;">
            <p style="margin-bottom: 0.65rem; font-size: 0.9rem; color: var(--color-graphite);">¿Ya tienes usuario en esta planta?</p>
            <a href="{{ route('login') }}" class="btn btn-outline btn-bloque">Iniciar sesión con mi cuenta</a>
          </div>
        @endif
      </div>
    </div>
  </section>
@endsection
