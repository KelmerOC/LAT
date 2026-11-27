/**
 * ============================================================================
 * LAT · CUADRÍCULA EXCEL EN VIVO PARA LIQUIDACIÓN, VENTAS Y SUELDOS
 * ============================================================================
 * JS puro con manipulación segura de DOM. Recalcula montos en tiempo real y actualiza barras fx.
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

  function setFormulaBar(bar, text) {
    if (!bar) return;
    const target = bar.querySelector('.excel-formula-text, .js-formula-value, code')
      || (bar.children.length > 1 ? bar.lastElementChild : bar);
    target.textContent = text;
  }

  function setActiveRow(table, row) {
    if (!table || !row) return;
    table.querySelectorAll('tbody tr.is-row-active').forEach((tr) => {
      if (tr !== row) tr.classList.remove('is-row-active');
    });
    row.classList.add('is-row-active');
  }

  function initLiquidacionGrid() {
    const wrap = document.getElementById('liq-excel-wrap');
    const table = document.getElementById('tabla-excel-liquidacion');
    if (!wrap || !table) return;

    const btnToggle = document.getElementById('btn-toggle-edit-liq');
    const btnCancel = document.getElementById('btn-cancel-edit-liq');
    const editActions = document.getElementById('liq-edit-actions');
    const formulaBar = document.getElementById('liq-formula-bar');
    const sumLitrosEl = document.getElementById('liq-sum-litros');
    const sumBrutoEl = document.getElementById('liq-sum-bruto');
    const sumAdelantosEl = document.getElementById('liq-sum-adelantos');
    const sumNetoEl = document.getElementById('liq-sum-neto');
    const adelantosHasMinus = Boolean(sumAdelantosEl && sumAdelantosEl.textContent.trim().startsWith('-'));

    const editableControls = Array.from(
      table.querySelectorAll('.js-liq-precio, .js-liq-adelantos, .js-liq-estado')
    );

    editableControls.forEach((ctrl) => {
      ctrl.dataset.initialValue = ctrl.value;
    });

    function getDataRows() {
      return Array.from(table.querySelectorAll('tbody tr')).filter(
        (tr) => !tr.classList.contains('excel-totals-row')
          && (tr.querySelector('.js-liq-precio') || tr.querySelector('.js-liq-litros') || tr.dataset.litros !== undefined)
      );
    }

    function setEditing(editing) {
      wrap.classList.toggle('is-editing', editing);
      editableControls.forEach((ctrl) => {
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

      rows.forEach((tr) => {
        const litrosEl = tr.querySelector('.js-liq-litros');
        const precioEl = tr.querySelector('.js-liq-precio');
        const adelantoEl = tr.querySelector('.js-liq-adelantos');
        const brutoEl = tr.querySelector('.js-liq-bruto');
        const netoEl = tr.querySelector('.js-liq-neto');

        const litros = tr.dataset.litros !== undefined
          ? parseNum(tr.dataset.litros)
          : parseNum(litrosEl ? litrosEl.textContent : 0);
        const precio = precioEl ? Math.max(0, parseNum(precioEl.value)) : parseNum(tr.dataset.precio || 0);
        const adelantos = adelantoEl ? Math.max(0, parseNum(adelantoEl.value)) : parseNum(tr.dataset.adelantos || 0);

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

    function updateLiqFormula(target) {
      if (!formulaBar || !target) return;
      const tr = target.closest('tr');
      if (!tr) return;

      const dataRows = getDataRows();
      const last = Math.max(2, dataRows.length + 1);

      if (tr.classList.contains('excel-totals-row') || tr.closest('tfoot')) {
        setFormulaBar(formulaBar, `=SUM(I2:I${last})`);
        return;
      }

      const idx = dataRows.indexOf(tr);
      if (idx === -1) return;
      const r = Number(tr.dataset.excelRow || (idx + 2));

      if (target.closest('.js-liq-precio, .js-liq-bruto')) {
        setFormulaBar(formulaBar, `F${r}: =ROUND(D${r}*E${r},2)`);
      } else if (target.closest('.js-liq-adelantos, .js-liq-neto, .js-liq-estado')) {
        setFormulaBar(formulaBar, `I${r}: =MAX(0,F${r}-G${r}-H${r})`);
      } else {
        setFormulaBar(formulaBar, `F${r}: =ROUND(D${r}*E${r},2)  |  I${r}: =MAX(0,F${r}-G${r}-H${r})  |  =SUM(I2:I${last})`);
      }
    }

    if (btnToggle) {
      btnToggle.addEventListener('click', () => {
        const nextEditing = !wrap.classList.contains('is-editing');
        setEditing(nextEditing);
        if (nextEditing) {
          const firstInput = table.querySelector('.js-liq-precio:not(:disabled)');
          if (firstInput) firstInput.focus();
        }
      });
    }

    if (btnCancel) {
      btnCancel.addEventListener('click', () => {
        editableControls.forEach((ctrl) => {
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
      if (e.target.matches('.js-liq-precio, .js-liq-adelantos')) {
        recalcLiquidacion();
        updateLiqFormula(e.target);
      }
    });

    table.addEventListener('change', (e) => {
      if (e.target.matches('.js-liq-precio, .js-liq-adelantos, .js-liq-estado')) {
        recalcLiquidacion();
        updateLiqFormula(e.target);
      }
    });

    table.addEventListener('focusin', (e) => {
      const tr = e.target.closest('tr');
      if (tr && tr.closest('tbody')) setActiveRow(table, tr);
      updateLiqFormula(e.target);
    });

    table.addEventListener('click', (e) => {
      const tr = e.target.closest('tr');
      if (tr && tr.closest('tbody')) setActiveRow(table, tr);
      updateLiqFormula(e.target);
    });
  }

  function initConceptoTarifaCalc(opts) {
    const form = document.getElementById(opts.formId);
    const table = document.getElementById(opts.tableId);
    const formulaBar = document.getElementById(opts.formulaBarId);

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
        setFormulaBar(formulaBar, `=E*F → S/ ${fmtMoney(total)}`);
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
      const getDataRows = () => Array.from(table.querySelectorAll('tbody tr'))
        .filter((tr) => !tr.classList.contains('excel-totals-row') && !tr.querySelector('td[colspan]'));

      const updateRowFormula = (target) => {
        if (!formulaBar || !target) return;
        const tr = target.closest('tr');
        if (!tr) return;
        const rows = getDataRows();
        const last = Math.max(2, rows.length + 1);
        if (tr.classList.contains('excel-totals-row') || tr.closest('tfoot')) {
          setFormulaBar(formulaBar, `=SUM(G2:G${last})`);
          return;
        }
        const idx = rows.indexOf(tr);
        if (idx === -1) return;
        const r = Number(tr.dataset.excelRow || (idx + 2));
        const valTxt = tr.dataset.total ? ` → S/ ${fmtMoney(tr.dataset.total)}` : '';
        setFormulaBar(formulaBar, `G${r}: =E${r}*F${r}${valTxt}`);
      };

      table.addEventListener('focusin', (e) => {
        const tr = e.target.closest('tr');
        if (tr && tr.closest('tbody')) setActiveRow(table, tr);
        updateRowFormula(e.target);
      });

      table.addEventListener('click', (e) => {
        const tr = e.target.closest('tr');
        if (tr && tr.closest('tbody')) setActiveRow(table, tr);
        updateRowFormula(e.target);
      });
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    initLiquidacionGrid();

    initConceptoTarifaCalc({
      formId: 'form-nueva-venta',
      tableId: 'tabla-excel-ventas',
      formulaBarId: 'ventas-formula-bar',
      cantSelector: '.js-venta-cant',
      rateSelector: '.js-venta-punit',
      totalSelector: '.js-venta-total',
    });

    initConceptoTarifaCalc({
      formId: 'form-nuevo-sueldo',
      tableId: 'tabla-excel-sueldos',
      formulaBarId: 'sueldos-formula-bar',
      cantSelector: '.js-sueldo-cant',
      rateSelector: '.js-sueldo-tarifa',
      totalSelector: '.js-sueldo-total',
    });
  });
})();
