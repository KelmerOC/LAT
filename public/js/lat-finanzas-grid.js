/**
 * ============================================================================
 * LAT · CUADRÍCULA INTERACTIVA PARA LIQUIDACIÓN, VENTAS Y SUELDOS
 * ============================================================================
 * JS puro con manipulación segura de DOM. Recalcula montos en tiempo real y sincroniza desde acopios.
 */
(function () {
  'use strict';

  function round2(val) {
    const n = Number(val);
    return Number.isFinite(n) ? Math.round(n * 100) / 100 : 0;
  }

  function parseNum(raw) {
    if (typeof raw === 'number') return Number.isFinite(raw) ? raw : 0;
    const cleaned = String(raw || '').replace(/[^0-9.-]/g, '');
    const n = parseFloat(cleaned);
    return Number.isFinite(n) ? n : 0;
  }

  function fmtMoney(val) {
    return round2(val).toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
  }

  function fmtLiters(val) {
    return (Math.round((Number(val) || 0) * 10) / 10).toLocaleString('en-US', {
      minimumFractionDigits: 1,
      maximumFractionDigits: 1,
    });
  }

  function tr(key, fallback) {
    return (window.LAT_I18N && window.LAT_I18N.t(key)) || fallback;
  }

  function setActiveRow(table, row) {
    if (!table || !row) return;
    table.querySelectorAll('tbody tr.is-row-active').forEach((trEl) => {
      if (trEl !== row) trEl.classList.remove('is-row-active');
    });
    row.classList.add('is-row-active');
  }

  function initLiquidacionGrid() {
    const wrap = document.getElementById('liq-excel-wrap');
    const table = document.getElementById('tabla-excel-liquidacion');
    if (!wrap || !table) return;

    const btnToggle = document.getElementById('btn-toggle-edit-liq');
    const btnSync = document.getElementById('btn-sync-liq-acopios');
    const btnCancel = document.getElementById('btn-cancel-edit-liq');
    const editActions = document.getElementById('liq-edit-actions');
    const sumLitrosEl = document.getElementById('liq-sum-litros');
    const sumBrutoEl = document.getElementById('liq-sum-bruto');
    const sumAdelantosEl = document.getElementById('liq-sum-adelantos');
    const sumNetoEl = document.getElementById('liq-sum-neto');
    const adelantosHasMinus = Boolean(sumAdelantosEl && sumAdelantosEl.textContent.trim().startsWith('-'));
    const datosUrl = wrap.dataset.datosUrl || '';
    const syncUrl = btnSync?.dataset.syncUrl || '';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let necesitaRefresh = false;

    function getEditableControls() {
      return Array.from(
        table.querySelectorAll('.js-liq-litros, .js-liq-precio, .js-liq-adelantos, .js-liq-estado')
      );
    }

    function snapshotInitialValues() {
      getEditableControls().forEach((ctrl) => {
        ctrl.dataset.initialValue = ctrl.value;
      });
    }

    snapshotInitialValues();

    function getDataRows() {
      return Array.from(table.querySelectorAll('tbody tr')).filter(
        (trEl) => !trEl.classList.contains('excel-totals-row')
          && (trEl.querySelector('.js-liq-precio') || trEl.querySelector('.js-liq-litros'))
      );
    }

    function setEditing(editing) {
      wrap.classList.toggle('is-editing', editing);
      getEditableControls().forEach((ctrl) => {
        ctrl.disabled = !editing;
      });
      if (editActions) editActions.hidden = !editing;
      if (btnToggle) btnToggle.setAttribute('aria-pressed', editing ? 'true' : 'false');
    }

    function recalcLiquidacion() {
      const rows = getDataRows();
      let sumLitros = 0;
      let sumBruto = 0;
      let sumAdelantos = 0;
      let sumNeto = 0;

      rows.forEach((trEl) => {
        const litrosEl = trEl.querySelector('.js-liq-litros');
        const precioEl = trEl.querySelector('.js-liq-precio');
        const adelantoEl = trEl.querySelector('.js-liq-adelantos');
        const brutoEl = trEl.querySelector('.js-liq-bruto');
        const netoEl = trEl.querySelector('.js-liq-neto');

        const litros = litrosEl
          ? Math.max(0, parseNum('value' in litrosEl ? litrosEl.value : litrosEl.textContent))
          : parseNum(trEl.dataset.litros || 0);
        const precio = precioEl ? Math.max(0, parseNum(precioEl.value)) : parseNum(trEl.dataset.precio || 0);
        const adelantos = adelantoEl ? Math.max(0, parseNum(adelantoEl.value)) : parseNum(trEl.dataset.adelantos || 0);

        const bruto = round2(litros * precio);
        const neto = Math.max(0, round2(bruto - adelantos));

        if (brutoEl) brutoEl.textContent = 'S/ ' + fmtMoney(bruto);
        if (netoEl) netoEl.textContent = 'S/ ' + fmtMoney(neto);

        sumLitros += litros;
        sumBruto += bruto;
        sumAdelantos += adelantos;
        sumNeto += neto;
      });

      if (sumLitrosEl) sumLitrosEl.textContent = fmtLiters(sumLitros) + ' L';
      if (sumBrutoEl) sumBrutoEl.textContent = 'S/ ' + fmtMoney(sumBruto);
      if (sumAdelantosEl) {
        sumAdelantosEl.textContent = (adelantosHasMinus ? '- S/ ' : 'S/ ') + fmtMoney(sumAdelantos);
      }
      if (sumNetoEl) sumNetoEl.textContent = 'S/ ' + fmtMoney(sumNeto);
    }

    function buildFilterParams() {
      const params = new URLSearchParams();
      const map = [
        ['f-liq-desde', 'liq_desde'],
        ['f-liq-hasta', 'liq_hasta'],
        ['f-liq-prov', 'liq_proveedor_id'],
        ['f-liq-sector', 'liq_sector'],
        ['f-liq-estado', 'liq_estado'],
      ];
      map.forEach(([elId, paramName]) => {
        const el = document.getElementById(elId);
        if (el && el.value !== '') params.set(paramName, el.value);
      });
      return params;
    }

    function createInputCell(id, name, val, step, min, max, cls, labelText, editing) {
      const td = document.createElement('td');
      td.className = 'num';
      const lbl = document.createElement('label');
      lbl.className = 'sr-only';
      lbl.setAttribute('for', id);
      lbl.textContent = labelText;
      const inp = document.createElement('input');
      inp.type = 'number';
      inp.step = step;
      inp.min = min;
      inp.max = max;
      inp.id = id;
      inp.name = name;
      inp.value = val;
      inp.defaultValue = val;
      inp.className = `excel-cell-input ${cls}`;
      inp.disabled = !editing;
      if (cls.includes('js-liq-precio')) inp.setAttribute('list', 'lista-precios-litro');
      td.appendChild(lbl);
      td.appendChild(inp);
      return td;
    }

    function renderLiquidacionRows(payload) {
      const tbody = table.querySelector('tbody');
      if (!tbody || !payload) return;
      const filas = Array.isArray(payload.filas) ? payload.filas : [];
      const editing = wrap.classList.contains('is-editing');
      tbody.textContent = '';

      if (filas.length === 0) {
        const trEmpty = document.createElement('tr');
        const tdEmpty = document.createElement('td');
        tdEmpty.colSpan = 10;
        tdEmpty.style.textAlign = 'center';
        tdEmpty.style.padding = '2rem';
        tdEmpty.style.color = 'var(--color-graphite)';
        tdEmpty.dataset.i18n = 'm4.empty_liq';
        tdEmpty.textContent = tr('m4.empty_liq', 'No hay liquidaciones en este período.');
        trEmpty.appendChild(tdEmpty);
        tbody.appendChild(trEmpty);
      } else {
        const estados = [
          ['pendiente', 'm4.pay_status_pendiente', 'Pendiente'],
          ['parcial', 'm4.pay_status_parcial', 'Parcial'],
          ['pagado', 'm4.pay_status_pagado', 'Pagado'],
        ];

        filas.forEach((liq, idx) => {
          const row = document.createElement('tr');
          row.className = 'js-liq-row';
          row.dataset.rowIdx = String(idx + 2);

          const tdCod = document.createElement('td');
          tdCod.className = 'num-tabular';
          tdCod.appendChild(document.createTextNode(String(liq.codigo || '')));
          const hidProv = document.createElement('input');
          hidProv.type = 'hidden';
          hidProv.name = `filas[${idx}][proveedor_id]`;
          hidProv.value = String(liq.proveedor_id || '');
          tdCod.appendChild(hidProv);
          row.appendChild(tdCod);

          const tdNom = document.createElement('td');
          const strongNom = document.createElement('strong');
          strongNom.textContent = String(liq.nombre || '');
          tdNom.appendChild(strongNom);
          row.appendChild(tdNom);

          const tdSec = document.createElement('td');
          tdSec.textContent = String(liq.sector || '');
          row.appendChild(tdSec);

          row.appendChild(createInputCell(
            `liq-litros-${idx}`,
            `filas[${idx}][litros]`,
            Number(liq.litros || 0).toFixed(1),
            '0.1',
            '0',
            '100000',
            'js-liq-litros',
            `Litros ${liq.nombre || ''}`,
            editing
          ));

          row.appendChild(createInputCell(
            `liq-precio-${idx}`,
            `filas[${idx}][precio_litro]`,
            Number(liq.precio_litro || 0).toFixed(2),
            '0.01',
            '0.5',
            '20',
            'js-liq-precio',
            `Precio/L ${liq.nombre || ''}`,
            editing
          ));

          const tdBruto = document.createElement('td');
          tdBruto.className = 'num num-tabular js-liq-bruto';
          tdBruto.textContent = 'S/ ' + fmtMoney(liq.bruto || 0);
          row.appendChild(tdBruto);

          row.appendChild(createInputCell(
            `liq-adel-${idx}`,
            `filas[${idx}][adelantos]`,
            Number(liq.adelantos || 0).toFixed(2),
            '0.01',
            '0',
            '100000',
            'js-liq-adelantos',
            `Adelantos ${liq.nombre || ''}`,
            editing
          ));

          const tdCom = document.createElement('td');
          tdCom.className = 'num num-tabular';
          tdCom.style.color = 'var(--verde-text)';
          tdCom.textContent = 'S/ 0.00';
          row.appendChild(tdCom);

          const tdNeto = document.createElement('td');
          tdNeto.className = 'num num-tabular js-liq-neto';
          tdNeto.style.fontWeight = '700';
          tdNeto.style.background = '#f7fbfe';
          tdNeto.textContent = 'S/ ' + fmtMoney(liq.neto || 0);
          row.appendChild(tdNeto);

          const tdEst = document.createElement('td');
          const lblEst = document.createElement('label');
          lblEst.className = 'sr-only';
          lblEst.setAttribute('for', `liq-est-${idx}`);
          lblEst.textContent = `Estado ${liq.nombre || ''}`;
          const selEst = document.createElement('select');
          selEst.id = `liq-est-${idx}`;
          selEst.name = `filas[${idx}][estado_pago]`;
          selEst.className = 'excel-cell-select js-liq-estado';
          selEst.disabled = !editing;
          estados.forEach(([val, key, fb]) => {
            const opt = document.createElement('option');
            opt.value = val;
            opt.dataset.i18n = key;
            opt.textContent = tr(key, fb);
            if ((liq.estado_pago || 'pendiente') === val) {
              opt.selected = true;
              opt.defaultSelected = true;
            }
            selEst.appendChild(opt);
          });
          tdEst.appendChild(lblEst);
          tdEst.appendChild(selEst);
          row.appendChild(tdEst);

          tbody.appendChild(row);
        });
      }

      snapshotInitialValues();
      recalcLiquidacion();
    }

    async function refreshLiquidacionDesdeServidor(url, method = 'GET') {
      if (!url || !navigator.onLine) return;
      const params = buildFilterParams();
      const targetUrl = method === 'GET' && params.toString()
        ? `${url}${url.includes('?') ? '&' : '?'}${params.toString()}`
        : url;

      try {
        const opts = {
          method,
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin',
        };
        if (method === 'POST') {
          opts.headers['Content-Type'] = 'application/json';
          opts.headers['X-CSRF-TOKEN'] = csrf;
          opts.body = JSON.stringify(Object.fromEntries(params.entries()));
        }
        const res = await fetch(targetUrl, opts);
        if (!res.ok) return;
        const data = await res.json();
        if (method === 'POST') setEditing(false);
        renderLiquidacionRows(data);
        necesitaRefresh = false;
      } catch (_) {}
    }

    if (btnSync && syncUrl) {
      btnSync.addEventListener('click', () => {
        btnSync.disabled = true;
        refreshLiquidacionDesdeServidor(syncUrl, 'POST').finally(() => {
          btnSync.disabled = false;
        });
      });
    }

    window.addEventListener('lat:acopios-updated', () => {
      necesitaRefresh = true;
      const sec = document.getElementById('sec-quincena');
      if (sec && !sec.hidden && !wrap.classList.contains('is-editing') && datosUrl) {
        refreshLiquidacionDesdeServidor(datosUrl, 'GET');
      }
    });

    window.addEventListener('lat:section-shown', (e) => {
      if (e.detail && e.detail.sectionId === 'sec-quincena' && necesitaRefresh && !wrap.classList.contains('is-editing') && datosUrl) {
        refreshLiquidacionDesdeServidor(datosUrl, 'GET');
      }
    });

    if (btnToggle) {
      btnToggle.addEventListener('click', () => {
        const nextEditing = !wrap.classList.contains('is-editing');
        setEditing(nextEditing);
        if (nextEditing) {
          const firstInput = table.querySelector('.js-liq-litros:not(:disabled), .js-liq-precio:not(:disabled)');
          if (firstInput) firstInput.focus();
        }
      });
    }

    if (btnCancel) {
      btnCancel.addEventListener('click', () => {
        getEditableControls().forEach((ctrl) => {
          if (ctrl.tagName === 'SELECT') {
            const defOpt = Array.from(ctrl.options).find((o) => o.defaultSelected);
            ctrl.value = defOpt ? defOpt.value : (ctrl.dataset.initialValue || '');
          } else {
            ctrl.value = ctrl.defaultValue !== undefined ? ctrl.defaultValue : (ctrl.dataset.initialValue || '');
          }
        });
        recalcLiquidacion();
        setEditing(false);
      });
    }

    table.addEventListener('input', (e) => {
      if (e.target.matches('.js-liq-litros, .js-liq-precio, .js-liq-adelantos')) {
        recalcLiquidacion();
      }
    });

    table.addEventListener('change', (e) => {
      if (e.target.matches('.js-liq-litros, .js-liq-precio, .js-liq-adelantos, .js-liq-estado')) {
        recalcLiquidacion();
      }
    });

    table.addEventListener('focusin', (e) => {
      const trEl = e.target.closest('tr');
      if (trEl && trEl.closest('tbody')) setActiveRow(table, trEl);
    });

    table.addEventListener('click', (e) => {
      const trEl = e.target.closest('tr');
      if (trEl && trEl.closest('tbody')) setActiveRow(table, trEl);
    });
  }

  function initConceptoTarifaCalc(opts) {
    const form = document.getElementById(opts.formId);
    const table = document.getElementById(opts.tableId);

    if (form) {
      const cantEl = form.querySelector(opts.cantSelector);
      const rateEl = form.querySelector(opts.rateSelector);
      const totalEl = form.querySelector(opts.totalSelector);

      const syncTotal = () => {
        const cant = cantEl ? Math.max(0, parseNum(cantEl.value)) : 0;
        const rate = rateEl ? Math.max(0, parseNum(rateEl.value)) : 0;
        const total = round2(cant * rate);

        if (totalEl) {
          if ('value' in totalEl && (totalEl.tagName === 'INPUT' || totalEl.tagName === 'OUTPUT')) {
            totalEl.value = total.toFixed(2);
          } else {
            totalEl.textContent = 'S/ ' + fmtMoney(total);
          }
        }
      };

      if (cantEl) {
        cantEl.addEventListener('input', syncTotal);
        cantEl.addEventListener('change', syncTotal);
      }
      if (rateEl) {
        rateEl.addEventListener('input', syncTotal);
        rateEl.addEventListener('change', syncTotal);
      }
      syncTotal();
    }

    if (table) {
      table.addEventListener('focusin', (e) => {
        const trEl = e.target.closest('tr');
        if (trEl && trEl.closest('tbody')) setActiveRow(table, trEl);
      });

      table.addEventListener('click', (e) => {
        const trEl = e.target.closest('tr');
        if (trEl && trEl.closest('tbody')) setActiveRow(table, trEl);
      });
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    initLiquidacionGrid();

    initConceptoTarifaCalc({
      formId: 'form-nueva-venta',
      tableId: 'tabla-excel-ventas',
      cantSelector: '.js-venta-cant',
      rateSelector: '.js-venta-punit',
      totalSelector: '.js-venta-total',
    });

    initConceptoTarifaCalc({
      formId: 'form-nuevo-sueldo',
      tableId: 'tabla-excel-sueldos',
      cantSelector: '.js-sueldo-cant',
      rateSelector: '.js-sueldo-tarifa',
      totalSelector: '.js-sueldo-total',
    });
  });
})();
