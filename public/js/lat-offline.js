(function () {
  const QUEUE_KEY = 'lat_offline_acopios_v1';
  const CHUNK_SIZE = 50;
  let syncing = false;

  if ('serviceWorker' in navigator && (window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
  }

  function obtenerContextoPlanta() {
    const form = document.getElementById('form-acopio');
    const plantaId = form?.dataset.plantaId ? parseInt(form.dataset.plantaId, 10) : null;
    const userId = form?.dataset.userId ? parseInt(form.dataset.userId, 10) : null;
    return {
      plantaId: Number.isFinite(plantaId) ? plantaId : null,
      userId: Number.isFinite(userId) ? userId : null,
    };
  }

  function leerCola() {
    try {
      const raw = localStorage.getItem(QUEUE_KEY);
      const parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (_) {
      return [];
    }
  }

  function leerColaPlantaActual() {
    const { plantaId } = obtenerContextoPlanta();
    const cola = leerCola();
    if (plantaId === null) return cola;
    return cola.filter((item) => item && (item.planta_id === undefined || item.planta_id === null || Number(item.planta_id) === plantaId));
  }

  function guardarCola(items) {
    try {
      localStorage.setItem(QUEUE_KEY, JSON.stringify(items));
    } catch (_) {}
  }

  function generarUuid() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
      return window.crypto.randomUUID();
    }
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
      const r = (Math.random() * 16) | 0;
      const v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  }

  function actualizarBadge(mensajeError = '') {
    const badge = document.getElementById('lat-net-badge');
    const txt = document.getElementById('lat-net-text');
    const btnSync = document.getElementById('btn-sync-offline');
    if (!badge || !txt) return;

    const cola = leerColaPlantaActual();
    const online = navigator.onLine;

    badge.classList.remove('is-online', 'is-offline', 'is-pending');
    if (!online) {
      badge.classList.add('is-offline');
      txt.textContent = cola.length > 0
        ? `Sin conexión · ${cola.length} pendiente(s)`
        : 'Sin conexión (Modo local)';
    } else if (mensajeError) {
      badge.classList.add('is-pending');
      txt.textContent = mensajeError;
    } else if (cola.length > 0) {
      badge.classList.add('is-pending');
      txt.textContent = `En línea · ${cola.length} por sincronizar`;
    } else {
      badge.classList.add('is-online');
      txt.textContent = 'En línea';
    }

    if (btnSync) {
      btnSync.hidden = !(online && cola.length > 0);
    }
  }

  function renderPendientesEnTabla() {
    const tbody = document.getElementById('tbody-acopios-hoy');
    if (!tbody) return;

    tbody.querySelectorAll('tr.is-offline-row').forEach((tr) => tr.remove());
    const cola = leerColaPlantaActual();
    const emptyRow = tbody.querySelector('tr.is-empty-acopios');
    if (!cola.length) {
      if (emptyRow) {
        const hayOtrasFilas = tbody.querySelectorAll('tr:not(.is-empty-acopios)').length > 0;
        emptyRow.hidden = hayOtrasFilas;
      }
      return;
    }

    if (emptyRow) emptyRow.hidden = true;

    cola.slice().reverse().forEach((item) => {
      const tr = document.createElement('tr');
      tr.className = 'is-offline-row';

      const tdFecha = document.createElement('td');
      tdFecha.className = 'num-tabular';
      tdFecha.textContent = item.fecha ? item.fecha.split('-').reverse().join('/') : '-';

      const tdHora = document.createElement('td');
      tdHora.className = 'num-tabular';
      tdHora.textContent = (item.hora || '').slice(0, 5);

      const tdProv = document.createElement('td');
      const strong = document.createElement('strong');
      strong.textContent = item.proveedor_nombre || 'Productor';
      const small = document.createElement('small');
      small.textContent = item.error_sync ? `Error sync: ${item.error_sync}` : 'Guardado localmente (Offline)';
      tdProv.appendChild(strong);
      tdProv.appendChild(small);

      const tdLitros = document.createElement('td');
      tdLitros.className = 'num';
      tdLitros.style.fontSize = '1.1rem';
      tdLitros.textContent = Number(item.litros).toFixed(1);

      const tdMetodo = document.createElement('td');
      const spanMet = document.createElement('span');
      spanMet.className = 'chip chip-fog';
      spanMet.textContent = item.metodo === 'voz' ? 'Voz' : 'Manual';
      tdMetodo.appendChild(spanMet);

      const tdEstado = document.createElement('td');
      const spanEst = document.createElement('span');
      spanEst.className = item.error_sync ? 'chip chip-rojo' : 'chip chip-amarillo';
      spanEst.textContent = item.error_sync ? 'Revisar' : 'Pendiente sync';
      tdEstado.appendChild(spanEst);

      tr.append(tdFecha, tdHora, tdProv, tdLitros, tdMetodo, tdEstado);

      const cols = tbody.closest('table')?.querySelectorAll('thead th').length || 6;
      if (cols > 6) {
        const tdAcc = document.createElement('td');
        tdAcc.textContent = 'En cola';
        tr.appendChild(tdAcc);
      }

      tbody.prepend(tr);
    });
  }

  function encolarAcopioDesdeFormulario(form) {
    const selectProv = form.querySelector('#select-proveedor');
    const inputLitros = form.querySelector('#input-litros');
    const inputFecha = form.querySelector('#input-fecha');
    const inputMetodo = form.querySelector('#input-metodo');
    const inputFrase = form.querySelector('#input-frase');

    const provId = parseInt(selectProv?.value || '0', 10);
    const litros = parseFloat(inputLitros?.value || '0');
    if (!provId || Number.isNaN(litros) || litros < 1 || litros > 5000) {
      return false;
    }

    const { plantaId, userId } = obtenerContextoPlanta();
    const ahora = new Date();
    const hora = `${String(ahora.getHours()).padStart(2, '0')}:${String(ahora.getMinutes()).padStart(2, '0')}:00`;
    const fechaDefault = `${ahora.getFullYear()}-${String(ahora.getMonth() + 1).padStart(2, '0')}-${String(ahora.getDate()).padStart(2, '0')}`;
    const provLabel = selectProv?.selectedOptions?.[0]?.textContent || 'Productor';
    const esVoz = inputMetodo?.value === 'voz';
    const frase = inputFrase?.value || '';

    const cola = leerCola();
    cola.push({
      uuid_offline: generarUuid(),
      planta_id: plantaId,
      user_id: userId,
      proveedor_id: provId,
      proveedor_nombre: provLabel,
      litros: Number(litros.toFixed(2)),
      fecha: inputFecha?.value || fechaDefault,
      hora,
      metodo: esVoz ? 'voz' : 'manual',
      frase_dictada: frase || null
    });
    guardarCola(cola);

    if (inputLitros) inputLitros.value = '';
    if (inputFrase) inputFrase.value = '';
    if (inputMetodo) inputMetodo.value = 'manual';

    if (esVoz) {
      const aviso = document.getElementById('aviso-acopio-voz');
      const avisoTitulo = document.getElementById('aviso-acopio-voz-titulo');
      const avisoDetalle = document.getElementById('aviso-acopio-voz-detalle');
      const avisoAcciones = document.getElementById('aviso-acopio-voz-acciones');
      if (aviso) {
        aviso.hidden = false;
        if (avisoTitulo) {
          avisoTitulo.setAttribute('data-i18n', 'm1.voice_auto_saved_offline');
          avisoTitulo.textContent = window.LAT_I18N
            ? window.LAT_I18N.t('m1.voice_auto_saved_offline')
            : 'Acopio por voz guardado automáticamente (modo offline)';
        }
        if (avisoDetalle) {
          avisoDetalle.textContent = `${provLabel} · ${Number(litros).toFixed(1)} L${frase ? ' — “' + frase + '”' : ''}`;
        }
        if (avisoAcciones) {
          avisoAcciones.hidden = true;
        }
      }
    }

    actualizarBadge();
    renderPendientesEnTabla();
    return true;
  }

  async function sincronizarCola(recarregarSiExito = true) {
    const colaPlanta = leerColaPlantaActual();
    if (!colaPlanta.length || !navigator.onLine || syncing) return;

    const form = document.getElementById('form-acopio');
    const syncUrl = form?.dataset.syncUrl || '/panel/acopio/sync';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    syncing = true;
    let totalSincronizados = 0;
    let avisoError = '';

    try {
      for (let i = 0; i < colaPlanta.length; i += CHUNK_SIZE) {
        const lote = colaPlanta.slice(i, i + CHUNK_SIZE);
        const res = await fetch(syncUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          credentials: 'same-origin',
          body: JSON.stringify({ acopios: lote })
        });

        if (!res.ok) {
          avisoError = res.status === 419
            ? 'Sesión expirada · Recargue para sincronizar'
            : `Error al sincronizar (${res.status})`;
          break;
        }

        const data = await res.json();
        const procesados = new Set([...(data.sincronizados || []), ...(data.duplicados || [])]);
        totalSincronizados += (data.sincronizados || []).length;

        const erroresMap = new Map();
        if (Array.isArray(data.errores)) {
          data.errores.forEach((err) => {
            if (err && err.uuid_offline) {
              erroresMap.set(err.uuid_offline, err.error || 'Registro inválido');
            }
          });
        }

        const colaCompleta = leerCola();
        const actualizados = colaCompleta
          .filter((item) => !procesados.has(item.uuid_offline))
          .map((item) => (erroresMap.has(item.uuid_offline)
            ? Object.assign({}, item, { error_sync: erroresMap.get(item.uuid_offline) })
            : item));
        guardarCola(actualizados);

        if (erroresMap.size > 0) {
          avisoError = `${erroresMap.size} pendiente(s) con error de validación`;
        }
      }

      actualizarBadge(avisoError);
      renderPendientesEnTabla();

      if (totalSincronizados > 0) {
        window.dispatchEvent(new CustomEvent('lat:acopios-updated'));
        if (recarregarSiExito || leerColaPlantaActual().length === 0) {
          window.location.hash = '#sec-acopio';
          window.location.reload();
        }
      }
    } catch (_) {
      // Sigue sin conectividad real; se reintentará al recuperar red
    } finally {
      syncing = false;
    }
  }

  function limpiarCacheAutenticado() {
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
      navigator.serviceWorker.controller.postMessage({ type: 'LAT_CLEAR_AUTH_CACHE' });
    }
    if ('caches' in window) {
      caches.keys().then((keys) => Promise.all(keys.map((k) => caches.open(k).then((c) => c.delete('/panel'))))).catch(() => {});
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    actualizarBadge();
    renderPendientesEnTabla();

    const form = document.getElementById('form-acopio');
    if (form) {
      form.addEventListener('submit', (e) => {
        if (!navigator.onLine) {
          e.preventDefault();
          encolarAcopioDesdeFormulario(form);
        }
      });
    }

    document.querySelectorAll('.js-logout-form').forEach((logoutForm) => {
      logoutForm.addEventListener('submit', limpiarCacheAutenticado);
    });

    const btnSync = document.getElementById('btn-sync-offline');
    if (btnSync) {
      btnSync.addEventListener('click', () => sincronizarCola(true));
    }

    if (navigator.onLine && leerColaPlantaActual().length > 0) {
      sincronizarCola(false);
    }
  });

  window.addEventListener('online', () => {
    actualizarBadge();
    sincronizarCola(true);
  });

  window.addEventListener('offline', () => {
    actualizarBadge();
  });
})();
