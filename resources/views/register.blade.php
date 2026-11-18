@extends('layouts.app')

@section('titulo', 'LAT · Registro de Planta y Usuario')

@section('contenido')
  <section class="login-wrap lienzo-acuarela" aria-labelledby="titulo-registro">
    <div class="aguada aguada-cielo" aria-hidden="true"></div>
    <div class="contenedor">
      <div class="login-card on-fog" style="max-width: 600px;">
        <header>
          <h2 id="titulo-registro" data-i18n="register.title">Registro de Planta y Usuario Administrador</h2>
          <p data-i18n="register.sub">Cree su cuenta de administración junto con su planta quesera. Sin comisiones (S/ 0.00).</p>
        </header>

        <form method="POST" action="{{ route('registro') }}">
          @csrf
          <div class="form-grupo">
            <label for="reg-name" class="form-label" data-i18n="register.name">Nombre completo del responsable</label>
            <input type="text" id="reg-name" name="name" class="input-lat" value="{{ old('name') }}" required placeholder="Ej: Rosaura Quispe" data-i18n-placeholder="register.ph_name">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div class="form-grupo">
              <label for="reg-usuario" class="form-label" data-i18n="register.username">Usuario de acceso</label>
              <input type="text" id="reg-usuario" name="usuario" class="input-lat" value="{{ old('usuario') }}" required placeholder="rosaura.anta">
            </div>
            <div class="form-grupo">
              <label for="reg-email" class="form-label" data-i18n="register.email">Correo electrónico (opcional)</label>
              <input type="email" id="reg-email" name="email" class="input-lat" value="{{ old('email') }}" placeholder="contacto@planta.pe">
            </div>
          </div>

          <hr style="border: none; border-top: 1px dashed var(--color-panel-dashed); margin: 1rem 0;">

          <div class="form-grupo">
            <label for="reg-planta" class="form-label" data-i18n="register.plant_name">Nombre de la planta o quesería</label>
            <input type="text" id="reg-planta" name="planta_nombre" class="input-lat" value="{{ old('planta_nombre') }}" required placeholder="Ej: Quesería Familiar Santa Rosa" data-i18n-placeholder="register.ph_plant">
          </div>

          <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 0.75rem;">
            <div class="form-grupo">
              <label for="reg-ubicacion" class="form-label" data-i18n="register.plant_loc">Ubicación / Provincia</label>
              <input type="text" id="reg-ubicacion" name="planta_ubicacion" class="input-lat" value="{{ old('planta_ubicacion', 'Cusco Provincia') }}" required>
            </div>
            <div class="form-grupo">
              <label for="reg-precio" class="form-label" data-i18n="register.price">Precio por litro (S/)</label>
              <input type="number" step="0.05" min="0.5" max="20" id="reg-precio" name="precio_litro" class="input-lat" value="{{ old('precio_litro', '1.65') }}">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div class="form-grupo">
              <label for="reg-pass" class="form-label" data-i18n="register.pass">Contraseña (mín. 6 caracteres)</label>
              <input type="password" id="reg-pass" name="password" class="input-lat" required autocomplete="new-password">
            </div>
            <div class="form-grupo">
              <label for="reg-pass2" class="form-label" data-i18n="register.pass_confirm">Confirmar contraseña</label>
              <input type="password" id="reg-pass2" name="password_confirmation" class="input-lat" required autocomplete="new-password">
            </div>
          </div>

          <button type="submit" class="btn btn-ember btn-bloque btn-grande" data-i18n="register.submit">Crear planta y entrar al Panel</button>
        </form>

        <p style="margin-top: 1.25rem; font-size: 0.9rem; text-align: center; color: var(--color-graphite);">
          <span data-i18n="register.have_account">¿Ya tienes cuenta?</span>
          <a href="{{ route('login') }}" style="font-weight: 700; color: var(--color-celeste-action); text-decoration: underline;" data-i18n="register.go_login">Iniciar sesión</a>
        </p>
      </div>
    </div>
  </section>
@endsection
