const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const OUT = path.resolve(__dirname, '../../scratch/e2e');
fs.mkdirSync(OUT, { recursive: true });
const BASE = process.env.E2E_BASE_URL || 'http://127.0.0.1:8000';

async function checkVisibleFormLabels(page) {
  return page.evaluate(() => {
    const isVisible = el => {
      if (!el) return false;
      if (el.closest('[hidden]')) return false;
      const st = window.getComputedStyle(el);
      if (st.display === 'none' || st.visibility === 'hidden') return false;
      const rect = el.getBoundingClientRect();
      return rect.width > 0 && rect.height > 0;
    };
    return [...document.querySelectorAll('input:not([type=hidden]), select')]
      .filter(isVisible)
      .filter(el => {
        const hasFor = el.id && document.querySelector(`label[for="${CSS.escape(el.id)}"]`);
        const hasWrap = !!el.closest('label');
        return !hasFor && !hasWrap;
      })
      .map(el => el.id || el.name || el.tagName);
  });
}

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const problemas = [];
  const check = (n, ok, d = '') => {
    console.log((ok ? 'PASS ' : 'FAIL ') + n + (ok ? '' : ' -> ' + d));
    if (!ok) problemas.push(n + (d ? ` (${d})` : ''));
  };

  const viewports = [
    ['desktop', { width: 1280, height: 800 }],
    ['movil', { width: 390, height: 844 }],
    ['movil-compacto', { width: 320, height: 568 }],
  ];

  for (const [nombre, vp] of viewports) {
    const ctx = await browser.newContext({ viewport: vp });
    const page = await ctx.newPage();
    const errores = [];
    page.on('pageerror', e => errores.push('pageerror: ' + e.message));
    page.on('console', m => {
      if (m.type() === 'error' && !m.text().includes('unsplash') && !m.text().includes('ERR_')) {
        errores.push('console: ' + m.text());
      }
    });

    // 1. Página de inicio (/) + anuncio QR de registro
    const rInicio = await page.goto(BASE + '/', { waitUntil: 'networkidle' });
    check(`${nombre} / status 200`, rInicio.status() === 200, rInicio.status());
    const desbordeInicio = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    check(`${nombre} / sin desborde horizontal`, !desbordeInicio);

    const sinLabelInicio = await checkVisibleFormLabels(page);
    check(`${nombre} / campos visibles con label accesible`, sinLabelInicio.length === 0, sinLabelInicio.join(', '));

    const landingQrState = await page.evaluate(() => {
      const banner = document.getElementById('anuncio-qr-registro');
      const svg = document.querySelector('#landing-qr-svg svg');
      const bannerRect = banner ? banner.getBoundingClientRect() : null;
      const svgRect = svg ? svg.getBoundingClientRect() : null;
      return {
        bannerVisible: !!banner && !!bannerRect && bannerRect.width > 0 && bannerRect.height > 0,
        svgRendered: !!svg && !!svgRect && svgRect.width > 0 && svgRect.height > 0,
      };
    });
    check(`${nombre} / #anuncio-qr-registro y #landing-qr-svg svg renderizan correctamente`,
      landingQrState.bannerVisible && landingQrState.svgRendered,
      JSON.stringify(landingQrState));

    // Cambio de idioma es -> qu -> en -> es en /
    const switchHomeLang = async (lang) => {
      if (nombre === 'desktop') {
        await page.click('#btn-lang-trigger');
        await page.waitForTimeout(100);
        await page.click(`#lang-dropdown-menu [data-lang="${lang}"]`);
      } else {
        await page.click('#btn-lang-trigger-mobile');
        await page.waitForTimeout(100);
        await page.click(`#lang-dropdown-menu-mobile [data-lang="${lang}"]`);
      }
      await page.waitForTimeout(150);
    };

    if (nombre === 'desktop') {
      const navAlto = await page.$eval('.nav-principal-inner', el => el.getBoundingClientRect().height);
      check('desktop nav bar altura 64-72px', navAlto >= 64 && navAlto <= 72, navAlto);
    }

    const homeLangTexts = [];
    for (const lang of ['qu', 'en', 'es']) {
      await switchHomeLang(lang);
      const state = await page.evaluate(() => ({
        htmlLang: document.documentElement.lang,
        heroSub: document.querySelector('#hero-sub')?.textContent?.trim() || '',
      }));
      homeLangTexts.push(state);
      check(`${nombre} / idioma -> ${lang} actualiza html[lang]`, state.htmlLang === lang, state.htmlLang);
    }
    const homeTranslationsDistinct = homeLangTexts[0].heroSub !== homeLangTexts[1].heroSub
      && homeLangTexts[1].heroSub !== homeLangTexts[2].heroSub;
    check(`${nombre} / traducción es->qu->en->es modifica contenido`, homeTranslationsDistinct);

    await page.screenshot({ path: path.join(OUT, `${nombre}_inicio.png`), fullPage: false });

    // 2. Página /registrarse
    const rReg = await page.goto(BASE + '/registrarse', { waitUntil: 'networkidle' });
    check(`${nombre} /registrarse status 200`, rReg.status() === 200, rReg.status());
    const desbordeReg = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    check(`${nombre} /registrarse sin desborde horizontal`, !desbordeReg);
    const sinLabelReg = await checkVisibleFormLabels(page);
    check(`${nombre} /registrarse campos visibles con label accesible`, sinLabelReg.length === 0, sinLabelReg.join(', '));

    // 3. Página /login
    const rLogin = await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
    check(`${nombre} /login status 200`, rLogin.status() === 200, rLogin.status());
    const desbordeLogin = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    check(`${nombre} /login sin desborde horizontal`, !desbordeLogin);
    const sinLabelLogin = await checkVisibleFormLabels(page);
    check(`${nombre} /login campos visibles con label accesible`, sinLabelLogin.length === 0, sinLabelLogin.join(', '));

    await page.screenshot({ path: path.join(OUT, `${nombre}_login.png`), fullPage: false });

    if (nombre === 'desktop') {
      const perfilesEsperados = ['rodrigo@sanborreal.pe', 'andres.anta', 'valeria.tina', 'contador.ricardo', 'javier.ruta'];
      const botonesCount = await page.locator('.perfil-btn').count();
      check(`${nombre} /login tiene exactamente 5 accesos rápidos profesionales`, botonesCount === 5, `count=${botonesCount}`);

      for (const usr of perfilesEsperados) {
        await page.click(`.perfil-btn[data-usuario="${usr}"]`);
        const vals = await page.evaluate(() => ({
          login: document.getElementById('login').value,
          pass: document.getElementById('password').value,
        }));
        check(`${nombre} /login perfil ${usr} rellena credenciales`, vals.login === usr && vals.pass === 'secreto123', JSON.stringify(vals));
        await Promise.all([
          page.waitForURL('**/panel**'),
          page.click('button[type=submit].btn-ember'),
        ]);
        await page.waitForLoadState('networkidle');
        check(`${nombre} /login perfil ${usr} accede a /panel`, page.url().includes('/panel'), page.url());
        await page.click('form.js-logout-form button[type="submit"]');
        await page.waitForLoadState('networkidle');
        await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
      }
      await page.click('.perfil-btn[data-usuario="rodrigo@sanborreal.pe"]');
    }

    // Ingresar al panel como rodrigo
    await Promise.all([
      page.waitForURL('**/panel**'),
      page.click('button[type=submit].btn-ember'),
    ]);
    await page.waitForLoadState('networkidle');
    check(`${nombre} /panel status y URL`, page.url().includes('/panel'), page.url());

    const desbordePanel = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    check(`${nombre} /panel sin desborde horizontal`, !desbordePanel);

    const navToPanelSection = async (sec) => {
      if (nombre !== 'desktop') {
        await page.click('#btn-sidebar-open');
        await page.waitForTimeout(220);
      }
      await page.click(`#dash-nav-list .dash-nav-btn[data-section="${sec}"]`);
      await page.waitForTimeout(150);
    };

    const switchPanelLangOn = async (targetPage, lang) => {
      const selector = `.dash-lang-row .btn-lang-choice[data-lang="${lang}"]`;
      const isSidebarBtnVisible = await targetPage.evaluate((sel) => {
        const el = document.querySelector(sel);
        if (!el) return false;
        const cs = window.getComputedStyle(el);
        if (cs.display === 'none' || cs.visibility === 'hidden') return false;
        const r = el.getBoundingClientRect();
        return r.width > 0 && r.height > 0 && r.right > 0 && r.left < window.innerWidth;
      }, selector);

      if (isSidebarBtnVisible) {
        await targetPage.click(selector);
      } else {
        await targetPage.click('#btn-sidebar-open');
        await targetPage.waitForTimeout(260);
        await targetPage.click(selector);
        await targetPage.keyboard.press('Escape');
        await targetPage.waitForTimeout(260);
      }
      await targetPage.waitForTimeout(150);
    };
    const switchPanelLang = (lang) => switchPanelLangOn(page, lang);

    // 4. Arquitectura sidebar + topbar + DESIGN.md + interruptor modo operativo en /panel
    const dashStructure = await page.evaluate(() => {
      const sidebar = document.querySelector('#dash-sidebar');
      const topbar = document.querySelector('.dash-topbar');
      const title = document.querySelector('#topbar-section-title');
      const activeNav = document.querySelector('#dash-nav-list .dash-nav-btn[data-section="sec-dashboard"]');
      const rangePills = document.querySelector('#dash-range-pills');
      const toggleForm = document.getElementById('form-toggle-modo-operativo');
      const toggleBtn = document.getElementById('btn-toggle-modo-operativo');
      return {
        hasSidebar: !!sidebar,
        hasTopbar: !!topbar,
        hasTitle: !!title && title.textContent.trim().length > 0,
        dashActive: !!activeNav && activeNav.classList.contains('activo'),
        rangePillsVisible: !!rangePills && !rangePills.hidden && window.getComputedStyle(rangePills).display !== 'none',
        topbarLangCount: document.querySelectorAll('.dash-topbar .dash-topbar-lang').length,
        anyTopbarLangCount: document.querySelectorAll('.dash-topbar-lang').length,
        sidebarLangRowExists: !!document.querySelector('#dash-sidebar .dash-lang-row'),
        sidebarLangBtnCount: document.querySelectorAll('#dash-sidebar .dash-lang-row .btn-lang-choice').length,
        hasToggleModoBtn: !!toggleBtn && !!toggleForm && toggleForm.dataset.activar === '1',
      };
    });
    check(`${nombre} /panel arquitectura sidebar/topbar presente`,
      dashStructure.hasSidebar && dashStructure.hasTopbar && dashStructure.hasTitle && dashStructure.dashActive,
      JSON.stringify(dashStructure));
    check(`${nombre} /panel #dash-range-pills visible en dashboard`, dashStructure.rangePillsVisible);
    check(`${nombre} /panel [donjusto] #btn-toggle-modo-operativo presente en estado inicial (OFF)`, dashStructure.hasToggleModoBtn);
    check(`${nombre} /panel sin selector .dash-topbar-lang duplicado en topbar`,
      dashStructure.topbarLangCount === 0 && dashStructure.anyTopbarLangCount === 0,
      JSON.stringify(dashStructure));
    check(`${nombre} /panel conserva #dash-sidebar .dash-lang-row con 3 idiomas`,
      dashStructure.sidebarLangRowExists && dashStructure.sidebarLangBtnCount === 3,
      JSON.stringify(dashStructure));

    // Verificar que #tray-notificaciones al abrirse no quede oculta por #dash-sidebar ni recortada fuera del viewport
    await page.click('#btn-notificaciones');
    const trayRect = await page.evaluate(() => {
      const tray = document.getElementById('tray-notificaciones');
      const sidebar = document.getElementById('dash-sidebar');
      if (!tray || tray.hidden) return { open: false };
      const tr = tray.getBoundingClientRect();
      const sr = sidebar ? sidebar.getBoundingClientRect() : null;
      const sidebarVisible = sr && sr.width > 0 && sr.right > 0 && window.innerWidth > 992;
      return {
        open: true,
        left: Math.round(tr.left),
        right: Math.round(tr.right),
        top: Math.round(tr.top),
        vw: window.innerWidth,
        sidebarRight: sidebarVisible ? Math.round(sr.right) : 0,
        noOverlapSidebar: !sidebarVisible || tr.left >= sr.right - 1,
        inViewport: tr.left >= 0 && tr.right <= window.innerWidth + 1,
      };
    });
    check(`${nombre} /panel #tray-notificaciones visible sin quedar oculto por barra lateral`,
      trayRect.open && trayRect.noOverlapSidebar && trayRect.inViewport,
      JSON.stringify(trayRect));
    await page.keyboard.press('Escape');

    // Alineación DESIGN.md: borderRadius <= 2px, boxShadow === 'none', topbar backdropFilter === 'none'
    const designMetrics = await page.evaluate(() => {
      const checkGroup = (sel) => [...document.querySelectorAll(sel)].map(el => {
        const cs = window.getComputedStyle(el);
        const radii = [cs.borderTopLeftRadius, cs.borderTopRightRadius, cs.borderBottomLeftRadius, cs.borderBottomRightRadius]
          .map(v => parseFloat(v) || 0);
        return {
          maxRadius: Math.max(...radii),
          boxShadow: cs.boxShadow,
        };
      });
      const cards = checkGroup('.dash-card');
      const kpis = checkGroup('.dash-kpi-card');
      const topBtns = checkGroup('.dash-topbar .btn');
      const topbar = document.querySelector('.dash-topbar');
      const topbarCs = topbar ? window.getComputedStyle(topbar) : null;
      return {
        cardsCount: cards.length,
        kpisCount: kpis.length,
        topBtnsCount: topBtns.length,
        allRadiiOk: [...cards, ...kpis, ...topBtns].every(x => x.maxRadius <= 2),
        allShadowNone: [...cards, ...kpis, ...topBtns].every(x => x.boxShadow === 'none'),
        backdropFilter: topbarCs ? (topbarCs.backdropFilter || 'none') : 'missing',
      };
    });
    check(`${nombre} /panel DESIGN.md geometría recta (<=2px) y sin sombras`,
      designMetrics.cardsCount > 0 && designMetrics.kpisCount > 0 && designMetrics.allRadiiOk && designMetrics.allShadowNone,
      JSON.stringify(designMetrics));
    check(`${nombre} /panel .dash-topbar backdropFilter === 'none'`,
      designMetrics.backdropFilter === 'none',
      designMetrics.backdropFilter);

    const sinLabelDash = await checkVisibleFormLabels(page);
    check(`${nombre} /panel (dashboard) campos visibles con label`, sinLabelDash.length === 0, sinLabelDash.join(', '));

    for (const lang of ['qu', 'en', 'es']) {
      await switchPanelLang(lang);
      const langState = await page.evaluate(() => {
        const actSub = document.querySelector('[data-i18n="dash.activity_sub"]')?.textContent?.trim() || '';
        const c1Sub = document.querySelector('[data-i18n="dash.c1_sub"]')?.textContent?.trim() || '';
        return {
          htmlLang: document.documentElement.lang,
          actSub,
          c1Sub,
        };
      });
      check(`${nombre} /panel idioma -> ${lang} actualiza html[lang]`, langState.htmlLang === lang, langState.htmlLang);
      check(`${nombre} /panel [${lang}] subtítulo Actividad reciente != dash.c1_sub`,
        langState.actSub.length > 0 && langState.actSub !== langState.c1Sub,
        `actSub="${langState.actSub}" vs c1Sub="${langState.c1Sub}"`);
    }

    await page.screenshot({ path: path.join(OUT, `${nombre}_panel_dashboard.png`), fullPage: false });

    // 4b. Bandeja de notificaciones #btn-notificaciones y #tray-notificaciones sin desborde horizontal
    const btnNotif = await page.$('#btn-notificaciones');
    check(`${nombre} /panel botón #btn-notificaciones presente`, !!btnNotif);
    if (btnNotif) {
      await btnNotif.click();
      await page.waitForTimeout(150);
      const trayOpenState = await page.evaluate(() => {
        const tray = document.getElementById('tray-notificaciones');
        const btn = document.getElementById('btn-notificaciones');
        return {
          open: !!tray && !tray.hidden && window.getComputedStyle(tray).display !== 'none',
          expanded: btn?.getAttribute('aria-expanded') === 'true',
          overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
        };
      });
      check(`${nombre} /panel #tray-notificaciones abre sin desborde horizontal`,
        trayOpenState.open && trayOpenState.expanded && !trayOpenState.overflow,
        JSON.stringify(trayOpenState));
      await btnNotif.click();
      await page.waitForTimeout(100);
      const trayClosed = await page.$eval('#tray-notificaciones', el => el.hidden);
      check(`${nombre} /panel #tray-notificaciones cierra al volver a pulsar`, trayClosed);
    }

    // 5. Verificar restricciones por defecto de donjusto (solo supervisión M1/M2/M3) + nuevas secciones Finanzas/Informes/Admin
    if (nombre !== 'desktop') {
      const burgerVisible = await page.$eval('#btn-sidebar-open', el => window.getComputedStyle(el).display !== 'none');
      check(`${nombre} /panel #btn-sidebar-open visible`, burgerVisible);
    }

    await navToPanelSection('sec-acopio');
    const acopioDefaultState = await page.evaluate(() => {
      const secAcopio = document.querySelector('#sec-acopio');
      const secDash = document.querySelector('#sec-dashboard');
      const rangePills = document.querySelector('#dash-range-pills');
      return {
        acopioVisible: !!secAcopio && !secAcopio.hidden,
        dashHidden: !!secDash && secDash.hidden,
        rangePillsHidden: !!rangePills && rangePills.hidden && window.getComputedStyle(rangePills).display === 'none',
        hasBtnVozAdmin: !!document.querySelector('#btn-voz'),
        editAcopioCount: document.querySelectorAll('#sec-acopio .js-edit-acopio').length,
        overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
      };
    });
    check(`${nombre} /panel sec-acopio activa y dashboard oculto`, acopioDefaultState.acopioVisible && acopioDefaultState.dashHidden);
    check(`${nombre} /panel #dash-range-pills oculto fuera del dashboard`, acopioDefaultState.rangePillsHidden);
    check(`${nombre} /panel [donjusto][default] sec-acopio sin #btn-voz ni .js-edit-acopio`,
      !acopioDefaultState.hasBtnVozAdmin && acopioDefaultState.editAcopioCount === 0,
      JSON.stringify(acopioDefaultState));
    check(`${nombre} /panel sec-acopio sin desborde horizontal`, !acopioDefaultState.overflow);
    const sinLabelAcopioDef = await checkVisibleFormLabels(page);
    check(`${nombre} /panel sec-acopio campos visibles con label`, sinLabelAcopioDef.length === 0, sinLabelAcopioDef.join(', '));

    await navToPanelSection('sec-calidad');
    const calidadDefaultCount = await page.$$eval('#sec-calidad .js-confirm-form[data-confirm-kind="calidad"]', els => els.length);
    const calidadDefaultOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    check(`${nombre} /panel [donjusto][default] sec-calidad sin botones de evaluación`, calidadDefaultCount === 0, `count=${calidadDefaultCount}`);
    check(`${nombre} /panel sec-calidad sin desborde horizontal`, !calidadDefaultOverflow);

    await navToPanelSection('sec-tina');
    const tinaDefaultState = await page.evaluate(() => ({
      hasNuevaTina: !!document.querySelector('#sec-tina [data-open-modal="modal-nueva-tina"]'),
      hasRecetasBase: !!document.querySelector('#sec-tina #btn-recetas-base, #sec-tina [data-open-modal="modal-recetas-base"]'),
      overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
    }));
    check(`${nombre} /panel [donjusto][default] sec-tina sin botones operativos ni crear tina`,
      !tinaDefaultState.hasNuevaTina && !tinaDefaultState.hasRecetasBase,
      JSON.stringify(tinaDefaultState));
    check(`${nombre} /panel sec-tina sin desborde horizontal`, !tinaDefaultState.overflow);

    // Verificar #sec-quincena: liquidación estilo Excel, tablas separadas ventas/sueldos, envío Excel con modal negrita, notificación contable
    await navToPanelSection('sec-quincena');
    const finanzasState = await page.evaluate(() => ({
      hasLiqTable: !!document.getElementById('tabla-excel-liquidacion'),
      hasEditLiqBtn: !!document.getElementById('btn-toggle-edit-liq'),
      hasVentasTable: !!document.getElementById('tabla-excel-ventas'),
      hasSueldosTable: !!document.getElementById('tabla-excel-sueldos'),
      hasSendExcelForm: !!document.getElementById('form-enviar-excel-contador'),
      hasNotifContableForm: !!document.getElementById('form-notif-contable'),
      overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
    }));
    check(`${nombre} /panel sec-quincena tiene tablas separadas, botón editar liquidación y formularios contables`,
      finanzasState.hasLiqTable
        && finanzasState.hasEditLiqBtn
        && finanzasState.hasVentasTable
        && finanzasState.hasSueldosTable
        && finanzasState.hasSendExcelForm
        && finanzasState.hasNotifContableForm,
      JSON.stringify(finanzasState));
    check(`${nombre} /panel sec-quincena sin desborde horizontal`, !finanzasState.overflow);
    const sinLabelFin = await checkVisibleFormLabels(page);
    check(`${nombre} /panel sec-quincena campos visibles con label`, sinLabelFin.length === 0, sinLabelFin.join(', '));

    await page.click('#btn-toggle-edit-liq');
    await page.waitForTimeout(100);
    const sinLabelFinEdit = await checkVisibleFormLabels(page);
    check(`${nombre} /panel sec-quincena modo edición liquidación mantiene labels accesibles`,
      sinLabelFinEdit.length === 0,
      sinLabelFinEdit.join(', '));
    await page.click('#btn-cancel-edit-liq');
    await page.waitForTimeout(100);

    // Probar confirmación con aviso en negrita al enviar Excel al contador y cancelar con Escape
    await page.click('#form-enviar-excel-contador button[type="submit"]');
    await page.waitForTimeout(150);
    const sendExcelModalState = await page.evaluate(() => {
      const modal = document.getElementById('modal-confirmacion-lat');
      const badge = document.getElementById('confirm-modal-badge');
      const strong = document.querySelector('#confirm-modal-message strong');
      return {
        open: !!modal && !modal.hidden,
        badgeVerde: !!badge && !badge.hidden && badge.classList.contains('is-verde'),
        strongLen: strong?.textContent?.trim()?.length || 0,
      };
    });
    check(`${nombre} /panel #form-enviar-excel-contador abre #modal-confirmacion-lat con aviso verde y negrita`,
      sendExcelModalState.open && sendExcelModalState.badgeVerde && sendExcelModalState.strongLen > 0,
      JSON.stringify(sendExcelModalState));
    await page.keyboard.press('Escape');
    await page.waitForTimeout(120);

    // Verificar #sec-estadisticas: #card-informes-planta visible para donjusto con 0 desborde
    await navToPanelSection('sec-estadisticas');
    const statsState = await page.evaluate(() => ({
      hasInformesCard: !!document.getElementById('card-informes-planta'),
      overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
    }));
    const sinLabelStats = await checkVisibleFormLabels(page);
    check(`${nombre} /panel [donjusto] sec-estadisticas tiene #card-informes-planta sin desborde y con labels`,
      statsState.hasInformesCard && !statsState.overflow && sinLabelStats.length === 0,
      JSON.stringify({ ...statsState, sinLabelStats }));

    // Verificar #sec-admin: NO tiene #qr-svg-preview, sí #modal-nueva-planta con PLT-
    await navToPanelSection('sec-admin');
    const adminCheck = await page.evaluate(() => ({
      hasQrPreview: !!document.getElementById('qr-svg-preview'),
      overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
    }));
    check(`${nombre} /panel sec-admin sin #qr-svg-preview y sin desborde horizontal`,
      !adminCheck.hasQrPreview && !adminCheck.overflow,
      JSON.stringify(adminCheck));

    const btnNuevaPlanta = await page.$('#sec-admin [data-open-modal="modal-nueva-planta"]');
    check(`${nombre} /panel sec-admin botón abrir #modal-nueva-planta presente`, !!btnNuevaPlanta);
    if (btnNuevaPlanta) {
      await btnNuevaPlanta.click();
      await page.waitForTimeout(150);
      const plantaModalCheck = await page.evaluate(() => {
        const modal = document.getElementById('modal-nueva-planta');
        const addon = modal?.querySelector('.input-prefix-addon');
        const sufijo = document.getElementById('np-cod-sufijo');
        const lbl = document.querySelector('label[for="np-cod-sufijo"]');
        return {
          open: !!modal && !modal.hidden,
          addonText: addon?.textContent?.trim() || '',
          sufijoVisible: !!sufijo && sufijo.getBoundingClientRect().width > 0,
          hasLabel: !!lbl && lbl.textContent.trim().length > 0,
        };
      });
      const sinLabelModalPlanta = await checkVisibleFormLabels(page);
      check(`${nombre} /panel #modal-nueva-planta prefijo PLT- y #np-cod-sufijo con label`,
        plantaModalCheck.open && plantaModalCheck.addonText === 'PLT-' && plantaModalCheck.sufijoVisible && plantaModalCheck.hasLabel && sinLabelModalPlanta.length === 0,
        JSON.stringify({ ...plantaModalCheck, sinLabelModalPlanta }));
      await page.click('#modal-nueva-planta [data-close-modal="modal-nueva-planta"]');
      await page.waitForTimeout(100);
    }

    // 5b. Activar Modo Operativo con #btn-toggle-modo-operativo -> modal con negrita -> confirmar
    await page.click('#btn-toggle-modo-operativo');
    await page.waitForTimeout(150);
    const toggleOnModal = await page.evaluate(() => {
      const modal = document.getElementById('modal-confirmacion-lat');
      const badge = document.getElementById('confirm-modal-badge');
      const strong = document.querySelector('#confirm-modal-message strong');
      return {
        open: !!modal && !modal.hidden,
        badgeVisible: !!badge && !badge.hidden,
        strongLen: strong?.textContent?.trim()?.length || 0,
      };
    });
    check(`${nombre} /panel #btn-toggle-modo-operativo abre #modal-confirmacion-lat con aviso en negrita`,
      toggleOnModal.open && toggleOnModal.badgeVisible && toggleOnModal.strongLen > 0,
      JSON.stringify(toggleOnModal));

    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click('#btn-confirmar-accion-js'),
    ]);

    const opsModeActive = await page.evaluate(() => {
      const form = document.getElementById('form-toggle-modo-operativo');
      const btn = document.getElementById('btn-toggle-modo-operativo');
      return !!form && form.dataset.activar === '0' && !!btn && btn.classList.contains('btn-verde');
    });
    check(`${nombre} /panel [donjusto] modo operativo activado tras confirmar`, opsModeActive);

    // Flujo de modales apilados en #sec-acopio con modo operativo activo
    await navToPanelSection('sec-acopio');
    const editAcopioBtn = await page.$('#sec-acopio .js-edit-acopio');
    check(`${nombre} /panel [modo-operativo] sec-acopio tiene botón .js-edit-acopio`, !!editAcopioBtn);
    if (editAcopioBtn) {
      await editAcopioBtn.click();
      await page.waitForTimeout(150);
      const editOpen = await page.$eval('#modal-edit-acopio', el => !el.hidden);
      check(`${nombre} /panel click .js-edit-acopio abre #modal-edit-acopio`, editOpen);

      const sinLabelEditAcopio = await checkVisibleFormLabels(page);
      check(`${nombre} /panel #modal-edit-acopio campos visibles con label`, sinLabelEditAcopio.length === 0, sinLabelEditAcopio.join(', '));

      await page.click('#form-edit-acopio button[type="submit"]');
      await page.waitForTimeout(150);
      const stackedState = await page.evaluate(() => {
        const editModal = document.getElementById('modal-edit-acopio');
        const confirmModal = document.getElementById('modal-confirmacion-lat');
        const zEdit = editModal ? parseInt(window.getComputedStyle(editModal).zIndex, 10) || 0 : 0;
        const zConfirm = confirmModal ? parseInt(window.getComputedStyle(confirmModal).zIndex, 10) || 0 : 0;
        const confirmMsg = document.getElementById('confirm-modal-message')?.textContent?.trim() || '';
        return {
          editOpen: !!editModal && !editModal.hidden,
          confirmOpen: !!confirmModal && !confirmModal.hidden,
          zConfirmGreater: zConfirm > zEdit,
          confirmMsgLen: confirmMsg.length,
        };
      });
      check(`${nombre} /panel enviar #form-edit-acopio apila #modal-confirmacion-lat encima`,
        stackedState.editOpen && stackedState.confirmOpen && stackedState.zConfirmGreater && stackedState.confirmMsgLen > 0,
        JSON.stringify(stackedState));

      await page.keyboard.press('Escape');
      await page.waitForTimeout(150);
      const afterFirstEsc = await page.evaluate(() => ({
        editStillOpen: !document.getElementById('modal-edit-acopio')?.hidden,
        confirmClosed: !!document.getElementById('modal-confirmacion-lat')?.hidden,
      }));
      check(`${nombre} /panel Escape cierra solo #modal-confirmacion-lat y mantiene #modal-edit-acopio abierto`,
        afterFirstEsc.confirmClosed && afterFirstEsc.editStillOpen,
        JSON.stringify(afterFirstEsc));

      await page.keyboard.press('Escape');
      await page.waitForTimeout(150);
      const editClosedAfterSecondEsc = await page.$eval('#modal-edit-acopio', el => el.hidden);
      check(`${nombre} /panel segundo Escape cierra #modal-edit-acopio`, editClosedAfterSecondEsc);
    }

    await page.screenshot({ path: path.join(OUT, `${nombre}_panel_acopio.png`), fullPage: false });

    // 6. Verificaciones completas en Quechua Cusqueño (qu) con modo operativo activo: persistencia, título, 7 secciones, calidad, tinas y recetas base
    await page.goto(BASE + '/', { waitUntil: 'networkidle' });
    await Promise.all([
      page.waitForResponse(r => r.url().includes('/idioma/qu') && r.ok()),
      switchHomeLang('qu'),
    ]);
    await page.reload({ waitUntil: 'networkidle' });
    const homeQuReload = await page.evaluate(() => ({
      lang: document.documentElement.lang,
      title: document.title,
    }));
    check(`${nombre} / persistencia qu tras reload (html[lang] === 'qu')`, homeQuReload.lang === 'qu', homeQuReload.lang);
    check(`${nombre} / title en qu actualizado`, homeQuReload.title.includes('Chakra Lichi'), homeQuReload.title);

    await page.goto(BASE + '/panel', { waitUntil: 'networkidle' });
    await Promise.all([
      page.waitForResponse(r => r.url().includes('/idioma/es') && r.ok()),
      switchPanelLang('es'),
    ]);
    const panelEsTitle = await page.title();
    await Promise.all([
      page.waitForResponse(r => r.url().includes('/idioma/qu') && r.ok()),
      switchPanelLang('qu'),
    ]);
    const panelQuImmediateTitle = await page.title();
    check(`${nombre} /panel document.title cambia de es a qu`,
      panelEsTitle.includes('Mi Panel') && !panelQuImmediateTitle.includes('Mi Panel') && panelQuImmediateTitle.includes("Llank'ana Panelniy"),
      `es="${panelEsTitle}" qu="${panelQuImmediateTitle}"`);
    await page.reload({ waitUntil: 'networkidle' });
    const panelQuReload = await page.evaluate(() => ({
      lang: document.documentElement.lang,
      title: document.title,
    }));
    check(`${nombre} /panel persistencia qu tras reload (html[lang] === 'qu')`, panelQuReload.lang === 'qu', panelQuReload.lang);
    check(`${nombre} /panel document.title en qu tras reload`,
      !panelQuReload.title.includes('Mi Panel') && panelQuReload.title.includes("Llank'ana Panelniy"),
      panelQuReload.title);

    const allSections = [
      'sec-dashboard',
      'sec-acopio',
      'sec-calidad',
      'sec-tina',
      'sec-quincena',
      'sec-estadisticas',
      'sec-admin',
    ];
    for (const sec of allSections) {
      await navToPanelSection(sec);
      const secOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
      check(`${nombre} /panel [qu] ${sec} sin desborde horizontal`, !secOverflow);
    }

    // 6d. Verificar modal de confirmación de Calidad (3 acciones: buena, acida, descartada) en sec-calidad con badge de color y sin español
    await navToPanelSection('sec-calidad');
    const calidadForms = await page.$$('#sec-calidad .js-confirm-form[data-confirm-kind="calidad"]');
    check(`${nombre} /panel [qu] sec-calidad tiene 3 botones de evaluación`, calidadForms.length >= 3, `count=${calidadForms.length}`);

    const calidadExpects = [
      { key: 'buena', cls: 'is-verde', needle: 'Allin chaskisqa (Hatun tina)', forbid: 'Aprobada (Tina principal)' },
      { key: 'acida', cls: 'is-amarillo', needle: "P'usqu (4 p'unchaw kisillu)", forbid: 'Ácida (Desviar' },
      { key: 'descartada', cls: 'is-rojo', needle: "wikch'uy", forbid: '¿Está seguro' },
    ];
    for (const exp of calidadExpects) {
      const btn = await page.$(`#sec-calidad .js-confirm-form[data-confirm-kind="calidad"][data-estado-key="${exp.key}"] button[type="submit"]`);
      check(`${nombre} /panel [qu] sec-calidad tiene botón ${exp.key}`, !!btn);
      if (btn) {
        await btn.click();
        await page.waitForTimeout(120);
        const modalCal = await page.evaluate(() => {
          const modal = document.getElementById('modal-confirmacion-lat');
          const badge = document.getElementById('confirm-modal-badge');
          const msg = document.getElementById('confirm-modal-message')?.textContent?.trim() || '';
          const br = badge ? badge.getBoundingClientRect() : null;
          return {
            open: !!modal && !modal.hidden,
            badgeVisible: !!badge && !badge.hidden && !!br && br.width > 0 && br.height > 0,
            badgeClasses: badge ? Array.from(badge.classList) : [],
            badgeText: badge?.textContent?.trim() || '',
            msg,
          };
        });
        check(`${nombre} /panel [qu] modal calidad (${exp.key}) muestra #confirm-modal-badge.${exp.cls} y sin español`,
          modalCal.open
            && modalCal.badgeVisible
            && modalCal.badgeClasses.includes(exp.cls)
            && modalCal.badgeText.length > 0
            && modalCal.msg.includes(exp.needle)
            && !modalCal.msg.includes(exp.forbid)
            && !modalCal.msg.includes('¿Está seguro'),
          JSON.stringify(modalCal));
        await page.click('#modal-confirmacion-lat [data-close-modal="modal-confirmacion-lat"]');
        await page.waitForTimeout(100);
      }
    }

    // 6f. Verificar #sec-tina en modo operativo: #aviso-calidad-tinas, #modal-nueva-tina (TINA-/VAT-) y #modal-recetas-base
    await navToPanelSection('sec-tina');
    const avisoTinas = await page.evaluate(() => {
      const el = document.getElementById('aviso-calidad-tinas');
      if (!el || el.hidden) return { visible: false, textLen: 0 };
      const cs = window.getComputedStyle(el);
      const r = el.getBoundingClientRect();
      return {
        visible: cs.display !== 'none' && cs.visibility !== 'hidden' && r.width > 0 && r.height > 0,
        textLen: el.textContent.trim().length,
      };
    });
    check(`${nombre} /panel sec-tina #aviso-calidad-tinas visible`,
      avisoTinas.visible && avisoTinas.textLen > 0,
      JSON.stringify(avisoTinas));

    const btnRecetasBase = await page.$('#sec-tina #btn-recetas-base, #sec-tina [data-open-modal="modal-recetas-base"]');
    check(`${nombre} /panel [modo-operativo] sec-tina tiene botón #modal-recetas-base`, !!btnRecetasBase);
    if (btnRecetasBase) {
      await btnRecetasBase.click();
      await page.waitForTimeout(150);
      const rbState = await page.evaluate(() => {
        const modal = document.getElementById('modal-recetas-base');
        return {
          open: !!modal && !modal.hidden,
          overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
        };
      });
      const sinLabelRb = await checkVisibleFormLabels(page);
      check(`${nombre} /panel #modal-recetas-base abre con labels accesibles y 0 desborde`,
        rbState.open && !rbState.overflow && sinLabelRb.length === 0,
        JSON.stringify({ ...rbState, sinLabelRb }));
      await page.click('#modal-recetas-base [data-close-modal="modal-recetas-base"]');
      await page.waitForTimeout(100);
    }

    const btnOpenTinaModal = await page.$('[data-open-modal="modal-nueva-tina"]');
    check(`${nombre} /panel [qu] botón abrir #modal-nueva-tina presente`, !!btnOpenTinaModal);
    if (btnOpenTinaModal) {
      await btnOpenTinaModal.click();
      await page.waitForTimeout(150);
      const modalQuState = await page.evaluate(() => {
        const modal = document.getElementById('modal-nueva-tina');
        const title = document.getElementById('title-nueva-tina')?.textContent?.trim() || '';
        const btn = modal?.querySelector('button[type="submit"]')?.textContent?.trim() || '';
        const addon = modal?.querySelector('.js-tina-prefix-addon')?.textContent?.trim() || '';
        const sufijo = document.getElementById('nt-codigo-sufijo');
        const lbl = document.querySelector('label[for="nt-codigo-sufijo"]');
        const tipoEl = document.getElementById('nt-tipo');
        return {
          open: !!modal && !modal.hidden,
          title,
          btn,
          addon,
          sufijoVisible: !!sufijo && sufijo.getBoundingClientRect().width > 0,
          hasLabel: !!lbl && lbl.textContent.trim().length > 0,
          tipoIsInputWithDatalist: !!tipoEl && tipoEl.tagName === 'INPUT' && tipoEl.getAttribute('list') === 'lista-tipos-tina',
          overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
        };
      });
      const sinLabelModalTina = await checkVisibleFormLabels(page);
      check(`${nombre} /panel [qu] modal #modal-nueva-tina traducido con prefijo TINA-, labels y sin desborde`,
        modalQuState.open
          && modalQuState.title === 'Musuq tinata yapay'
          && modalQuState.btn === 'Tinata kamariy'
          && modalQuState.addon === 'TINA-'
          && modalQuState.sufijoVisible
          && modalQuState.hasLabel
          && modalQuState.tipoIsInputWithDatalist
          && !modalQuState.overflow
          && sinLabelModalTina.length === 0,
        JSON.stringify({ ...modalQuState, sinLabelModalTina }));
      await page.click('#modal-nueva-tina [data-close-modal="modal-nueva-tina"]');
      await page.waitForTimeout(100);

      await Promise.all([
        page.waitForResponse(r => r.url().includes('/idioma/en') && r.ok()),
        switchPanelLang('en'),
      ]);
      await btnOpenTinaModal.click();
      await page.waitForTimeout(150);
      const modalEnState = await page.evaluate(() => {
        const modal = document.getElementById('modal-nueva-tina');
        const addon = modal?.querySelector('.js-tina-prefix-addon')?.textContent?.trim() || '';
        const tipoEl = document.getElementById('nt-tipo');
        return {
          open: !!modal && !modal.hidden,
          addon,
          tipoIsInputWithDatalist: !!tipoEl && tipoEl.tagName === 'INPUT' && tipoEl.getAttribute('list') === 'lista-tipos-tina',
        };
      });
      check(`${nombre} /panel [en] #modal-nueva-tina usa prefijo VAT- y #nt-tipo con datalist`,
        modalEnState.open && modalEnState.addon === 'VAT-' && modalEnState.tipoIsInputWithDatalist,
        JSON.stringify(modalEnState));
      await page.click('#modal-nueva-tina [data-close-modal="modal-nueva-tina"]');
      await page.waitForTimeout(100);
    }

    // Desactivar modo operativo para dejar el perfil en estado inicial limpio
    await page.click('#btn-toggle-modo-operativo');
    await page.waitForTimeout(150);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click('#btn-confirmar-accion-js'),
    ]);
    const opsModeOff = await page.evaluate(() => {
      const form = document.getElementById('form-toggle-modo-operativo');
      return !!form && form.dataset.activar === '1';
    });
    check(`${nombre} /panel [donjusto] modo operativo desactivado limpiamente`, opsModeOff);

    check(`${nombre} sin errores JS ni de consola`, errores.length === 0, errores.join(' | '));
    await ctx.close();

    // 7. Verificar controles operativos de acopio.crear (#btn-voz, .botonera, dictado qu) y start_batch con andres.anta
    const ctxOp = await browser.newContext({ viewport: vp });
    const pageOp = await ctxOp.newPage();
    const erroresOp = [];
    pageOp.on('pageerror', e => erroresOp.push('pageerror: ' + e.message));
    pageOp.on('console', m => {
      if (m.type() === 'error' && !m.text().includes('unsplash') && !m.text().includes('ERR_')) {
        erroresOp.push('console: ' + m.text());
      }
    });
    await pageOp.goto(BASE + '/login', { waitUntil: 'networkidle' });
    await pageOp.click('.perfil-btn[data-usuario="andres.anta"]');
    await Promise.all([
      pageOp.waitForURL('**/panel**'),
      pageOp.click('button[type=submit].btn-ember'),
    ]);
    await pageOp.waitForLoadState('networkidle');

    const navToOpSection = async (sec) => {
      if (nombre !== 'desktop') {
        await pageOp.click('#btn-sidebar-open');
        await pageOp.waitForTimeout(260);
      }
      await pageOp.click(`#dash-nav-list .dash-nav-btn[data-section="${sec}"]`);
      await pageOp.waitForTimeout(150);
    };

    await navToOpSection('sec-acopio');
    const opAcopioHeights = await pageOp.evaluate(() => {
      const btnVoz = document.querySelector('#btn-voz');
      const teclas = [...document.querySelectorAll('.botonera button')];
      return {
        vozHeight: btnVoz ? btnVoz.getBoundingClientRect().height : 0,
        teclaMinHeight: teclas.length ? Math.min(...teclas.map(b => b.getBoundingClientRect().height)) : 0,
        teclaCount: teclas.length,
      };
    });
    check(`${nombre} /panel [andres.anta] #btn-voz altura >= 48px`, opAcopioHeights.vozHeight >= 48, `${opAcopioHeights.vozHeight}px`);
    check(`${nombre} /panel [andres.anta] .botonera button altura >= 48px`,
      opAcopioHeights.teclaCount > 0 && opAcopioHeights.teclaMinHeight >= 48,
      `${opAcopioHeights.teclaMinHeight}px`);

    await switchPanelLangOn(pageOp, 'qu');
    const vozQuResult = await pageOp.evaluate(() => {
      class MockSpeechRecognition {
        start() {
          if (typeof this.onresult === 'function') {
            this.onresult({ results: [[{ transcript: 'kimsa chunka pichqayuq litru' }]] });
          }
        }
      }
      window.SpeechRecognition = MockSpeechRecognition;
      window.webkitSpeechRecognition = MockSpeechRecognition;
      const btn = document.getElementById('btn-voz');
      if (btn) btn.click();
      return {
        litros: document.getElementById('input-litros')?.value || '',
        metodo: document.getElementById('input-metodo')?.value || '',
        transcripcion: document.getElementById('transcripcion-voz')?.textContent?.trim() || '',
      };
    });
    check(`${nombre} /panel [andres.anta][qu] dictado "kimsa chunka pichqayuq litru" -> 35 L`,
      vozQuResult.litros === '35' && vozQuResult.metodo === 'voz' && vozQuResult.transcripcion.includes('Uyarisqa'),
      JSON.stringify(vozQuResult));

    await navToOpSection('sec-tina');

    const openIniciarBtn = await pageOp.$('#sec-tina .js-open-iniciar-tina');
    check(`${nombre} /panel [andres.anta] tina libre tiene botón .js-open-iniciar-tina`, !!openIniciarBtn);
    if (openIniciarBtn) {
      await openIniciarBtn.click();
      await pageOp.waitForTimeout(150);

      const iniciarOpen = await pageOp.$eval('#modal-iniciar-tina', el => !el.hidden);
      check(`${nombre} /panel [andres.anta] click .js-open-iniciar-tina abre #modal-iniciar-tina`, iniciarOpen);

      const sinLabelIniciar = await checkVisibleFormLabels(pageOp);
      check(`${nombre} /panel [andres.anta] #modal-iniciar-tina campos visibles con label accesible`,
        sinLabelIniciar.length === 0,
        sinLabelIniciar.join(', '));

      const seleccion = await pageOp.evaluate(() => {
        const cap = parseFloat(document.getElementById('it-tina-capacidad')?.value || '150') || 150;
        const items = Array.from(document.querySelectorAll('#modal-iniciar-tina .it-acopio-item:not([hidden])'));
        const elegidos = [];
        let suma = 0;
        for (const item of items) {
          const cb = item.querySelector('.js-it-acopio-cb');
          const lit = parseFloat(item.dataset.litros || '0') || 0;
          if (cb && lit > 0 && (suma + lit) <= cap) {
            cb.click();
            suma += lit;
            elegidos.push(lit);
            if (elegidos.length >= 2) break;
          }
        }
        return {
          count: elegidos.length,
          expectedSum: Number(suma.toFixed(1)),
          litrosTotalVal: parseFloat(document.getElementById('it-litros-total')?.value || '0') || 0,
          resumenTxt: document.getElementById('it-litros-resumen')?.textContent?.trim() || '',
        };
      });
      check(`${nombre} /panel [andres.anta] seleccionar múltiples .js-it-acopio-cb actualiza #it-litros-total en vivo`,
        seleccion.count >= 2
          && Math.abs(seleccion.litrosTotalVal - seleccion.expectedSum) < 0.05
          && seleccion.resumenTxt.includes(seleccion.expectedSum.toFixed(1)),
        JSON.stringify(seleccion));

      await pageOp.fill('#it-duracion', '60');
      await pageOp.fill('#it-cuajo', '4.25');
      await pageOp.click('#form-iniciar-tina button[type="submit"]');
      await pageOp.waitForTimeout(150);

      const startBatchModal = await pageOp.evaluate(() => ({
        iniciarStillOpen: !document.getElementById('modal-iniciar-tina')?.hidden,
        confirmOpen: !document.getElementById('modal-confirmacion-lat')?.hidden,
        msg: document.getElementById('confirm-modal-message')?.textContent?.trim() || '',
      }));
      check(`${nombre} /panel [andres.anta] enviar #form-iniciar-tina abre #modal-confirmacion-lat con código de tina`,
        startBatchModal.iniciarStillOpen && startBatchModal.confirmOpen && startBatchModal.msg.includes('TINA-') && startBatchModal.msg.includes('60 min'),
        JSON.stringify(startBatchModal));

      await pageOp.keyboard.press('Escape');
      await pageOp.waitForTimeout(150);
      const afterFirstEsc = await pageOp.evaluate(() => ({
        confirmClosed: !!document.getElementById('modal-confirmacion-lat')?.hidden,
        iniciarStillOpen: !document.getElementById('modal-iniciar-tina')?.hidden,
      }));
      check(`${nombre} /panel [andres.anta] primer Escape cierra solo #modal-confirmacion-lat`,
        afterFirstEsc.confirmClosed && afterFirstEsc.iniciarStillOpen,
        JSON.stringify(afterFirstEsc));

      await pageOp.keyboard.press('Escape');
      await pageOp.waitForTimeout(150);
      const iniciarClosedAfterSecondEsc = await pageOp.$eval('#modal-iniciar-tina', el => el.hidden);
      check(`${nombre} /panel [andres.anta] segundo Escape cierra #modal-iniciar-tina`, iniciarClosedAfterSecondEsc);
    }
    check(`${nombre} [andres.anta] sin errores JS ni de consola`, erroresOp.length === 0, erroresOp.join(' | '));
    await ctxOp.close();
  }

  await browser.close();
  console.log(problemas.length ? 'FALLOS: ' + problemas.length : 'TODO OK');
  process.exit(problemas.length ? 1 : 0);
})();
