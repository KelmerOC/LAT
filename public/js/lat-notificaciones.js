/**
 * ============================================================================
 * LAT · NOTIFICACIONES EN VIVO, ALERTAS DE CALIDAD Y TEMPORIZADORES DE TINA
 * ============================================================================
 */
(function () {
  const POLL_INTERVAL_MS = 12000;
  const ALERT_TTL_MS = 12000;
  const SEEN_KEY = 'lat_seen_live_notif_id';

  const tr = (key, fallback, vars) => {
    if (window.LAT_I18N) return window.LAT_I18N.t(key, vars);
    let out = fallback || key;
    if (vars) {
      Object.keys(vars).forEach((k) => {
        out = out.replace(new RegExp('\\{' + k + '\\}', 'g'), vars[k]);
      });
    }
    return out;
  };

  function safeSessionGet(key) {
    try { return sessionStorage.getItem(key); } catch (_) { return null; }
  }

  function safeSessionSet(key, val) {
    try { sessionStorage.setItem(key, val); } catch (_) {}
  }

  function sanitizeTipo(tipo) {
    return String(tipo || 'info').replace(/[^a-zA-Z0-9_-]/g, '') || 'info';
  }

  function reproducirChime() {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      if (ctx.state === 'suspended' && typeof ctx.resume === 'function') {
        ctx.resume().catch(() => {});
      }
      const now = ctx.currentTime;
      [587.33, 880].forEach((freq, idx, arr) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        const t0 = now + idx * 0.11;
        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq, t0);
        gain.gain.setValueAtTime(0.06, t0);
        gain.gain.exponentialRampToValueAtTime(0.001, t0 + 0.18);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(t0);
        osc.stop(t0 + 0.19);
        if (idx === arr.length - 1) {
          osc.onended = () => { try { ctx.close(); } catch (_) {} };
        }
      });
    } catch (_) {}
  }

  document.addEventListener('DOMContentLoaded', () => {
    const btnNotif = document.getElementById('btn-notificaciones');
    const tray = document.getElementById('tray-notificaciones');
    const listEl = document.getElementById('notif-tray-list');
    const badgeEl = document.getElementById('notif-badge-count');
    const btnMarkAll = document.getElementById('btn-notif-marcar-todas');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const pollUrl = btnNotif?.dataset.pollUrl || tray?.dataset.pollUrl || listEl?.dataset.pollUrl || '';
    const readUrl = btnNotif?.dataset.readUrl || tray?.dataset.readUrl || listEl?.dataset.readUrl || btnMarkAll?.dataset.readUrl || '';

    let lastId = Number(tray?.dataset.lastId || listEl?.dataset.lastId || btnNotif?.dataset.lastId || 0) || 0;
    if (listEl) {
      listEl.querySelectorAll('.lat-notif-item[data-notif-id], .lat-notif-item[data-id]').forEach((el) => {
        const idNum = Number(el.dataset.notifId || el.dataset.id || 0);
        if (idNum > lastId) lastId = idNum;
      });
    }

    let polling = false;
    const expiredPolledLotes = new Set();

    function actualizarBadge(explicitCount) {
      if (!badgeEl) return;
      const count = typeof explicitCount === 'number'
        ? explicitCount
        : (listEl ? listEl.querySelectorAll('.lat-notif-item.is-unread').length : 0);
      badgeEl.textContent = String(count);
      badgeEl.hidden = count <= 0;
    }

    function irASeccionTinas() {
      const btnTina = document.querySelector('#dash-nav-list .dash-nav-btn[data-section="sec-tina"]');
      if (btnTina) btnTina.click();
    }

    function posicionarTrayMovil() {
      if (!tray || !btnNotif) return;
      if (window.innerWidth <= 680) {
        const rect = btnNotif.getBoundingClientRect();
        const topPx = Math.max(12, Math.round(rect.bottom + 6));
        tray.style.top = `${topPx}px`;
        tray.style.maxHeight = `${Math.max(180, window.innerHeight - topPx - 12)}px`;
      } else {
        tray.style.top = '';
        tray.style.maxHeight = '';
      }
    }

    function cerrarTray() {
      if (!tray) return;
      tray.hidden = true;
      tray.style.top = '';
      tray.style.maxHeight = '';
      if (btnNotif) btnNotif.setAttribute('aria-expanded', 'false');
    }

    async function marcarLeidaEnServidor(payload) {
      if (!readUrl) return;
      try {
        await fetch(readUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          credentials: 'same-origin',
          body: JSON.stringify(payload)
        });
      } catch (_) {}
    }

    function extractNotifFromEl(el) {
      const tipoCls = Array.from(el.classList).find((c) => c.startsWith('is-') && c !== 'is-unread' && c !== 'is-read');
      return {
        id: Number(el.dataset.notifId || el.dataset.id || 0),
        tipo: el.dataset.tipo || (tipoCls ? tipoCls.slice(3) : 'info'),
        modulo_destino: el.dataset.modulo || el.dataset.moduloDestino || '',
        titulo: el.dataset.titulo || el.querySelector('.lat-notif-title, strong')?.textContent?.trim() || '',
        mensaje: el.dataset.mensaje || el.querySelector('.lat-notif-msg, p')?.textContent?.trim() || '',
        tiempo: el.dataset.tiempo || el.dataset.hora || el.querySelector('.lat-notif-time, small, time')?.textContent?.trim() || ''
      };
    }

    function crearElementoTray(notif) {
      const li = document.createElement('li');
      li.className = 'lat-notif-item ' + (notif.leida ? 'is-read' : 'is-unread') + ' is-' + sanitizeTipo(notif.tipo);
      li.setAttribute('role', 'button');
      li.tabIndex = 0;
      li.dataset.notifId = String(notif.id || '');
      li.dataset.id = String(notif.id || '');
      li.dataset.tipo = String(notif.tipo || 'info');
      if (notif.modulo_destino) li.dataset.modulo = String(notif.modulo_destino);
      if (notif.titulo) li.dataset.titulo = String(notif.titulo);
      if (notif.mensaje) li.dataset.mensaje = String(notif.mensaje);
      if (notif.tiempo) li.dataset.tiempo = String(notif.tiempo);

      const head = document.createElement('div');
      head.className = 'lat-notif-item-head';
      const titleEl = document.createElement('strong');
      titleEl.className = 'lat-notif-title';
      titleEl.textContent = notif.titulo || '';
      head.appendChild(titleEl);
      if (notif.tiempo) {
        const timeEl = document.createElement('small');
        timeEl.className = 'lat-notif-time num-tabular';
        timeEl.textContent = notif.tiempo;
        head.appendChild(timeEl);
      }
      li.appendChild(head);

      if (notif.mensaje) {
        const msgEl = document.createElement('p');
        msgEl.className = 'lat-notif-msg';
        msgEl.textContent = notif.mensaje;
        li.appendChild(msgEl);
      }
      return li;
    }

    function obtenerStackAlertas() {
      let stack = document.getElementById('lat-live-alert-stack');
      if (!stack && document.body) {
        stack = document.createElement('div');
        stack.id = 'lat-live-alert-stack';
        stack.className = 'lat-live-alert-stack';
        stack.setAttribute('aria-live', 'polite');
        document.body.appendChild(stack);
      }
      return stack;
    }

    function mostrarAlertaViva(notif, shouldChime = false) {
      const stack = obtenerStackAlertas();
      if (!stack || !notif) return;

      const card = document.createElement('div');
      card.className = 'lat-live-alert is-' + sanitizeTipo(notif.tipo);
      card.setAttribute('role', 'status');

      const body = document.createElement('div');
      body.className = 'lat-live-alert-body';

      const head = document.createElement('div');
      head.className = 'lat-live-alert-head';
      const titleEl = document.createElement('strong');
      titleEl.textContent = notif.titulo || '';
      head.appendChild(titleEl);
      if (notif.tiempo) {
        const timeEl = document.createElement('small');
        timeEl.textContent = notif.tiempo;
        head.appendChild(timeEl);
      }
      body.appendChild(head);

      if (notif.mensaje) {
        const msgEl = document.createElement('p');
        msgEl.textContent = notif.mensaje;
        body.appendChild(msgEl);
      }
      card.appendChild(body);

      const actions = document.createElement('div');
      actions.className = 'lat-live-alert-actions';
      actions.style.display = 'flex';
      actions.style.alignItems = 'center';
      actions.style.gap = '0.35rem';
      actions.style.flexShrink = '0';
      if ((notif.modulo_destino || notif.modulo) === 'tinas') {
        const btnVer = document.createElement('button');
        btnVer.type = 'button';
        btnVer.className = 'btn-micro';
        btnVer.textContent = tr('notif.view_section', 'Ver');
        btnVer.addEventListener('click', () => {
          if (notif.id) {
            const esc = CSS.escape(String(notif.id));
            const trayEl = listEl?.querySelector(`.lat-notif-item[data-notif-id="${esc}"], .lat-notif-item[data-id="${esc}"]`);
            if (trayEl && !trayEl.classList.contains('is-read')) {
              trayEl.classList.remove('is-unread');
              trayEl.classList.add('is-read');
              actualizarBadge();
            }
            marcarLeidaEnServidor({ notificacion_id: Number(notif.id) });
          }
          irASeccionTinas();
          card.remove();
        });
        actions.appendChild(btnVer);
      }

      const btnClose = document.createElement('button');
      btnClose.type = 'button';
      btnClose.className = 'btn-micro lat-live-alert-close';
      btnClose.setAttribute('aria-label', tr('modal.close_aria', 'Cerrar'));
      btnClose.textContent = '×';
      btnClose.addEventListener('click', () => card.remove());
      actions.appendChild(btnClose);
      card.appendChild(actions);

      stack.prepend(card);
      while (stack.children.length > 4) {
        stack.lastElementChild.remove();
      }
      setTimeout(() => { if (card.isConnected) card.remove(); }, ALERT_TTL_MS);
      if (shouldChime) reproducirChime();
    }

    function agregarAlertaCalidadEnTinas(notif) {
      const target = document.getElementById('tinas-calidad-alertas-list');
      if (!target || !String(notif.tipo || '').startsWith('calidad_')) return;
      const nid = String(notif.id || '');
      if (nid && target.querySelector(`[data-notif-id="${CSS.escape(nid)}"]`)) return;

      const alertaCls = notif.tipo === 'calidad_aprobada'
        ? 'alerta-verde'
        : (notif.tipo === 'calidad_descartada' ? 'alerta-roja' : 'alerta-celeste');
      const strip = document.createElement('div');
      strip.className = `alerta ${alertaCls} tina-calidad-alerta-item is-${sanitizeTipo(notif.tipo)}`;
      strip.style.marginBottom = '0';
      strip.style.padding = '0.55rem 0.8rem';
      if (nid) strip.dataset.notifId = nid;

      const titleEl = document.createElement('strong');
      titleEl.textContent = notif.titulo || '';
      strip.appendChild(titleEl);

      if (notif.mensaje) {
        const msgEl = document.createElement('span');
        msgEl.textContent = ' · ' + notif.mensaje;
        strip.appendChild(msgEl);
      }
      if (notif.tiempo) {
        const timeEl = document.createElement('small');
        timeEl.textContent = ' (' + notif.tiempo + ')';
        strip.appendChild(timeEl);
      }

      target.hidden = false;
      target.prepend(strip);
    }

    async function pollNotificaciones() {
      if (!pollUrl || polling || !navigator.onLine) return;
      polling = true;
      try {
        const sep = pollUrl.includes('?') ? '&' : '?';
        const res = await fetch(`${pollUrl}${sep}after_id=${encodeURIComponent(String(lastId))}`, {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin'
        });
        if (!res.ok) return;
        const data = await res.json();
        const rawItems = Array.isArray(data.items)
          ? data.items
          : (Array.isArray(data.notificaciones) ? data.notificaciones : []);

        if (rawItems.length > 0) {
          if (listEl) {
            listEl.querySelectorAll('.lat-notif-empty').forEach((el) => el.remove());
          }
          const ordered = [...rawItems].sort((a, b) => Number(a.id || 0) - Number(b.id || 0));
          ordered.forEach((raw) => {
            const notif = {
              id: Number(raw.id || 0),
              tipo: raw.tipo || 'info',
              titulo: raw.titulo || raw.title || '',
              mensaje: raw.mensaje || raw.message || '',
              modulo_destino: raw.modulo_destino || raw.modulo || '',
              leida: Boolean(raw.leida || raw.leida_at || raw.read),
              tiempo: raw.creada_en || raw.tiempo || raw.hora || raw.created_at_human || ''
            };
            if (notif.id > lastId) lastId = notif.id;
            const escId = notif.id ? CSS.escape(String(notif.id)) : '';
            const existsInTray = escId && listEl?.querySelector(`.lat-notif-item[data-notif-id="${escId}"], .lat-notif-item[data-id="${escId}"]`);
            if (listEl && !existsInTray) {
              listEl.prepend(crearElementoTray(notif));
            }
            if (!notif.leida) {
              mostrarAlertaViva(notif, true);
              if (notif.id) safeSessionSet(SEEN_KEY, String(notif.id));
            }
            agregarAlertaCalidadEnTinas(notif);
          });
          if (typeof data.ultimo_id === 'number' && data.ultimo_id > lastId) {
            lastId = data.ultimo_id;
          }
          const countFromServer = typeof data.unread_count === 'number'
            ? data.unread_count
            : (typeof data.no_leidas === 'number' ? data.no_leidas : undefined);
          actualizarBadge(countFromServer);
        }
      } catch (_) {
      } finally {
        polling = false;
      }
    }

    if (btnNotif && tray) {
      btnNotif.addEventListener('click', (e) => {
        e.stopPropagation();
        const nextOpen = tray.hidden;
        if (nextOpen) {
          posicionarTrayMovil();
        }
        tray.hidden = !nextOpen;
        if (!nextOpen) {
          tray.style.top = '';
          tray.style.maxHeight = '';
        }
        btnNotif.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
      });
      window.addEventListener('resize', () => {
        if (!tray.hidden) posicionarTrayMovil();
      });
      document.addEventListener('click', (e) => {
        if (!tray.hidden && !e.target.closest('#tray-notificaciones') && !e.target.closest('#btn-notificaciones')) {
          cerrarTray();
        }
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !tray.hidden) cerrarTray();
      });
    }

    if (listEl) {
      listEl.addEventListener('click', (e) => {
        const item = e.target.closest('.lat-notif-item');
        if (!item) return;
        const id = Number(item.dataset.id || item.dataset.notifId || 0);
        const modulo = item.dataset.modulo || item.dataset.moduloDestino || '';
        if (id && !item.classList.contains('is-read')) {
          item.classList.remove('is-unread');
          item.classList.add('is-read');
          actualizarBadge();
          marcarLeidaEnServidor({ notificacion_id: id });
        }
        if (modulo === 'tinas') {
          irASeccionTinas();
          cerrarTray();
        }
      });

      const unreadItems = Array.from(listEl.querySelectorAll('.lat-notif-item.is-unread'));
      if (unreadItems.length > 0) {
        let newestEl = unreadItems[0];
        let maxUnreadId = Number(newestEl.dataset.notifId || newestEl.dataset.id || 0);
        unreadItems.forEach((el) => {
          const val = Number(el.dataset.notifId || el.dataset.id || 0);
          if (val > maxUnreadId) {
            maxUnreadId = val;
            newestEl = el;
          }
        });
        const newest = extractNotifFromEl(newestEl);
        const seenId = safeSessionGet(SEEN_KEY);
        if (newest.id && seenId !== String(newest.id)) {
          safeSessionSet(SEEN_KEY, String(newest.id));
          mostrarAlertaViva(newest, false);
        }
      }
    }

    if (btnMarkAll) {
      btnMarkAll.addEventListener('click', () => {
        if (listEl) {
          listEl.querySelectorAll('.lat-notif-item.is-unread').forEach((el) => {
            el.classList.remove('is-unread');
            el.classList.add('is-read');
          });
        }
        actualizarBadge(0);
        marcarLeidaEnServidor({ todas: true });
      });
    }

    if (pollUrl && (btnNotif || tray || listEl)) {
      setInterval(() => {
        if (document.visibilityState === 'visible' && navigator.onLine) {
          pollNotificaciones();
        }
      }, POLL_INTERVAL_MS);
    }

    const timerBoxes = Array.from(document.querySelectorAll('.tina-timer-box[data-fin-iso]'));
    function tickTimers() {
      const now = Date.now();
      const pad2 = (n) => String(n).padStart(2, '0');
      timerBoxes.forEach((box, idx) => {
        const finMs = Date.parse(box.dataset.finIso || '');
        if (Number.isNaN(finMs)) return;
        const diffSec = Math.floor((finMs - now) / 1000);
        const valueEl = box.querySelector('.js-tina-countdown, .tina-timer-valor, .tina-timer-value, .tina-timer-countdown, strong') || box;
        const statusEl = box.querySelector('.tina-timer-estado');

        if (diffSec > 0) {
          box.classList.remove('is-expired');
          const hrs = Math.floor(diffSec / 3600);
          const mins = Math.floor((diffSec % 3600) / 60);
          const secs = diffSec % 60;
          valueEl.textContent = diffSec >= 3600
            ? `${pad2(hrs)}:${pad2(mins)}:${pad2(secs)}`
            : `${pad2(mins)}:${pad2(secs)}`;
          if (statusEl) statusEl.textContent = '';
        } else {
          box.classList.add('is-expired');
          const doneLabel = tr('m3.timer_done', 'Tiempo cumplido');
          if (statusEl) {
            valueEl.textContent = '00:00';
            statusEl.textContent = doneLabel;
          } else {
            valueEl.textContent = `00:00 · ${doneLabel}`;
          }
          const loteKey = box.dataset.loteId || box.dataset.lote || `timer-${idx}`;
          if (!expiredPolledLotes.has(loteKey)) {
            expiredPolledLotes.add(loteKey);
            pollNotificaciones();
          }
        }
      });
    }

    if (timerBoxes.length > 0) {
      tickTimers();
      setInterval(tickTimers, 1000);
      if (window.LAT_I18N && window.LAT_I18N.onChange) {
        window.LAT_I18N.onChange(tickTimers);
      }
    }
  });
})();
