@extends('layouts.app')

@section('titulo', 'LAT · Acceso a Mi Panel')

@section('contenido')
  <section class="login-wrap lienzo-acuarela" aria-labelledby="titulo-login">
    <div class="aguada aguada-cielo" aria-hidden="true"></div>
    <div class="contenedor">
      <div class="login-card on-fog">
        <header>
          <h2 id="titulo-login">Ingreso a Mi Panel Lácteo</h2>
          <p>Acceso seguro · Cusco Provincia. Ingrese con su identificador de operario o el correo de la microempresa.</p>
        </header>

        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="form-grupo">
            <label for="login" class="form-label">Usuario o correo</label>
            <input type="text" id="login" name="login" class="input-lat" value="{{ old('login', 'donjusto@sanborreal.pe') }}" required autofocus autocomplete="username" @error('login') aria-invalid="true" @enderror>
          </div>

          <div class="form-grupo">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" id="password" name="password" class="input-lat" value="secreto123" required autocomplete="current-password">
          </div>

          <button type="submit" class="btn btn-ember btn-bloque btn-grande">Entrar a Mi Panel</button>
        </form>

        <div class="perfiles">
          <h3>Perfiles de prueba sembrados</h3>
          <p class="ayuda">Rellenan el formulario con un clic. Solo para demostración universitaria.</p>
          <div class="perfiles-grid" role="group" aria-label="Perfiles de prueba">
            <button type="button" class="perfil-btn" data-usuario="donjusto@sanborreal.pe" aria-pressed="true"><strong>Don Justo</strong><span>Administración</span></button>
            <button type="button" class="perfil-btn" data-usuario="carlos.anta" aria-pressed="false"><strong>Carlos</strong><span>Encargado de planta</span></button>
            <button type="button" class="perfil-btn" data-usuario="sabina.tina" aria-pressed="false"><strong>Sabina</strong><span>Tina / Calidad</span></button>
            <button type="button" class="perfil-btn" data-usuario="contador.marco" aria-pressed="false"><strong>Marco</strong><span>Contador externo</span></button>
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
