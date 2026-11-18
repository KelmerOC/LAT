@extends('layouts.app')

@section('titulo', 'LAT · Acceso a Mi Panel')

@section('contenido')
  <section class="login-wrap lienzo-acuarela" aria-labelledby="titulo-login">
    <div class="aguada aguada-cielo" aria-hidden="true"></div>
    <div class="contenedor">
      <div class="login-card on-fog">
        <header>
          <h2 id="titulo-login" data-i18n="login.title">Ingreso a Mi Panel Lácteo</h2>
          <p data-i18n="login.sub">Acceso seguro · Cusco Provincia. Ingrese con su identificador de operario o el correo de la microempresa.</p>
        </header>

        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="form-grupo">
            <label for="login" class="form-label" data-i18n="login.user_label">Usuario o correo</label>
            <input type="text" id="login" name="login" class="input-lat" value="{{ old('login', 'donjusto@sanborreal.pe') }}" required autofocus autocomplete="username" @error('login') aria-invalid="true" @enderror>
          </div>

          <div class="form-grupo">
            <label for="password" class="form-label" data-i18n="login.pass_label">Contraseña</label>
            <input type="password" id="password" name="password" class="input-lat" value="secreto123" required autocomplete="current-password">
          </div>

          <button type="submit" class="btn btn-ember btn-bloque btn-grande" data-i18n="login.submit">Entrar a Mi Panel</button>
        </form>

        <p style="margin-top: 1.1rem; font-size: 0.9rem; text-align: center; color: var(--color-graphite);">
          <span data-i18n="login.no_account">¿Nueva planta o asociación?</span>
          <a href="{{ route('registro') }}" style="font-weight: 700; color: var(--color-celeste-action); text-decoration: underline;" data-i18n="login.go_register">Registrar nueva planta aquí</a>
        </p>

        <div class="perfiles">
          <h3 data-i18n="login.profiles_title">Perfiles de prueba sembrados</h3>
          <p class="ayuda" data-i18n="login.profiles_help">Rellenan el formulario con un clic. Solo para demostración universitaria.</p>
          <div class="perfiles-grid" role="group" aria-label="Perfiles de prueba">
            <button type="button" class="perfil-btn" data-usuario="donjusto@sanborreal.pe" aria-pressed="true"><strong>Don Justo</strong><span data-i18n="login.role_admin">Administración total</span></button>
            <button type="button" class="perfil-btn" data-usuario="carlos.anta" aria-pressed="false"><strong>Carlos</strong><span data-i18n="login.role_manager">Encargado de planta</span></button>
            <button type="button" class="perfil-btn" data-usuario="sabina.tina" aria-pressed="false"><strong>Sabina</strong><span data-i18n="login.role_vat">Tina / Calidad</span></button>
            <button type="button" class="perfil-btn" data-usuario="contador.marco" aria-pressed="false"><strong>Marco</strong><span data-i18n="login.role_accountant">Contador externo</span></button>
            <button type="button" class="perfil-btn" data-usuario="mateo.ruta" aria-pressed="false"><strong>Mateo</strong><span data-i18n="login.role_collector">Solo Acopio en ruta</span></button>
            <button type="button" class="perfil-btn" data-usuario="lucia.personal" aria-pressed="false"><strong>Lucía</strong><span data-i18n="login.role_custom">Personalizado (Calidad + Tinas)</span></button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    document.querySelectorAll('.perfil-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('login').value = btn.dataset.usuario;
        document.getElementById('password').value = 'secreto123';
        document.querySelectorAll('.perfil-btn').forEach(b => b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'));
      });
    });
  </script>
@endsection
