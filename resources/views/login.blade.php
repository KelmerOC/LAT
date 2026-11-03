@extends('layouts.app')

@section('titulo', 'LAT · Acceso a Mi Panel')

@section('contenido')
  <div style="max-width: 500px; margin: 2rem auto;">
    <div style="background-color: var(--color-fog); border-radius: 12px; padding: 2.5rem 2rem;">
      <div style="text-align: center; margin-bottom: 2rem;">
        <span style="font-size: 0.8rem; font-weight: 600; color: var(--color-celeste); text-transform: uppercase; letter-spacing: 0.5px;">Acceso Seguro</span>
        <h2 style="font-family: var(--font-display); font-size: 1.85rem; color: var(--color-slate-deep); margin-top: 0.25rem;">
          Ingreso a Mi Panel Lácteo
        </h2>
        <p style="color: var(--color-graphite); font-size: 0.88rem; margin-top: 0.5rem;">
          Ingrese con su identificador de operario o correo de la microempresa.
        </p>
      </div>

      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div style="margin-bottom: 1.25rem;">
          <label for="login" style="display: block; font-size: 0.85rem; font-weight: 500; color: var(--color-slate-deep); margin-bottom: 0.35rem;">
            Usuario o Correo
          </label>
          <input type="text" id="login" name="login" value="{{ old('login', 'donjusto@sanborreal.pe') }}" required autofocus
                 style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 8px; background-color: #ffffff; color: var(--color-slate-deep); font-size: 0.95rem; outline: none;">
        </div>

        <div style="margin-bottom: 1.5rem;">
          <label for="password" style="display: block; font-size: 0.85rem; font-weight: 500; color: var(--color-slate-deep); margin-bottom: 0.35rem;">
            Contraseña
          </label>
          <input type="password" id="password" name="password" value="secreto123" required
                 style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 8px; background-color: #ffffff; color: var(--color-slate-deep); font-size: 0.95rem; outline: none;">
        </div>

        <button type="submit" class="btn-lat btn-ember" style="width: 100%; padding: 0.85rem; font-size: 1rem; font-weight: 600; cursor: pointer; border: none; border-radius: 8px;">
          Entrar a Mi Panel
        </button>
      </form>

      <!-- Selector Rápido para Demostración Universitaria -->
      <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-sky-wash);">
        <strong style="display: block; font-size: 0.78rem; text-transform: uppercase; color: var(--color-pewter); margin-bottom: 0.75rem; letter-spacing: 0.5px;">
          Perfiles de Prueba Sembrados (1 Clic)
        </strong>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
          <button type="button" onclick="cargar('donjusto@sanborreal.pe')" style="background: #ffffff; border: none; padding: 0.5rem; border-radius: 6px; font-size: 0.78rem; cursor: pointer; text-align: left; color: var(--color-slate-deep);">
            <strong>Don Justo</strong><br><span style="color: var(--color-pewter); font-size: 0.72rem;">Administración</span>
          </button>
          <button type="button" onclick="cargar('carlos.anta')" style="background: #ffffff; border: none; padding: 0.5rem; border-radius: 6px; font-size: 0.78rem; cursor: pointer; text-align: left; color: var(--color-slate-deep);">
            <strong>Carlos</strong><br><span style="color: var(--color-pewter); font-size: 0.72rem;">Encargado Planta</span>
          </button>
          <button type="button" onclick="cargar('sabina.tina')" style="background: #ffffff; border: none; padding: 0.5rem; border-radius: 6px; font-size: 0.78rem; cursor: pointer; text-align: left; color: var(--color-slate-deep);">
            <strong>Sabina</strong><br><span style="color: var(--color-pewter); font-size: 0.72rem;">Tina / Calidad</span>
          </button>
          <button type="button" onclick="cargar('contador.marco')" style="background: #ffffff; border: none; padding: 0.5rem; border-radius: 6px; font-size: 0.78rem; cursor: pointer; text-align: left; color: var(--color-slate-deep);">
            <strong>Marco</strong><br><span style="color: var(--color-pewter); font-size: 0.72rem;">Contador</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function cargar(usuario) {
      document.getElementById('login').value = usuario;
      document.getElementById('password').value = 'secreto123';
    }
  </script>
@endsection
