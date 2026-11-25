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

    // 1. Página de inicio (/)
    const rInicio = await page.goto(BASE + '/', { waitUntil: 'networkidle' });
    check(`${nombre} / status 200`, rInicio.status() === 200, rInicio.status());
    const desbordeInicio = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    check(`${nombre} / sin desborde horizontal`, !desbordeInicio);

    const sinLabelInicio = await checkVisibleFormLabels(page);
    check(`${nombre} / campos visibles con label accesible`, sinLabelInicio.length === 0, sinLabelInicio.join(', '));

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

    // Ingresar al panel
    await Promise.all([
      page.waitForURL('**/panel**'),
      page.click('button[type=submit].btn-ember'),
    ]);
    await page.waitForLoadState('networkidle');
    check(`${nombre} /panel status y URL`, page.url().includes('/panel'), page.url());

    const desbordePanel = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    check(`${nombre} /panel sin desborde horizontal`, !desbordePanel);

    // 4. Arquitectura sidebar + topbar + DESIGN.md en /panel
    const dashStructure = await page.evaluate(() => {
      const sidebar = document.querySelector('#dash-sidebar');
      const topbar = document.querySelector('.dash-topbar');
      const title = document.querySelector('#topbar-section-title');
      const activeNav = document.querySelector('#dash-nav-list .dash-nav-btn[data-section="sec-dashboard"]');
      const rangePills = document.querySelector('#dash-range-pills');
      return {
        hasSidebar: !!sidebar,
        hasTopbar: !!topbar,
        hasTitle: !!title && title.textContent.trim().length > 0,
        dashActive: !!activeNav && activeNav.classList.contains('activo'),
        rangePillsVisible: !!rangePills && !rangePills.hidden && window.getComputedStyle(rangePills).display !== 'none',
      };
    });
    check(`${nombre} /panel arquitectura sidebar/topbar presente`,
      dashStructure.hasSidebar && dashStructure.hasTopbar && dashStructure.hasTitle && dashStructure.dashActive,
      JSON.stringify(dashStructure));
    check(`${nombre} /panel #dash-range-pills visible en dashboard`, dashStructure.rangePillsVisible);

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

    // Verificar etiquetas en dashboard
    const sinLabelDash = await checkVisibleFormLabels(page);
    check(`${nombre} /panel (dashboard) campos visibles con label`, sinLabelDash.length === 0, sinLabelDash.join(', '));

    // Cambio de idioma es -> qu -> en -> es en /panel y verificación de dash.activity_sub != dash.c1_sub
    const switchPanelLang = async (lang) => {
      const visibleTopbarBtn = await page.$(`.dash-topbar-lang .btn-lang-choice[data-lang="${lang}"]`);
      if (visibleTopbarBtn && await visibleTopbarBtn.isVisible()) {
        await visibleTopbarBtn.click();
      } else {
        await page.click('#btn-sidebar-open');
        await page.waitForTimeout(150);
        await page.click(`.dash-lang-row .btn-lang-choice[data-lang="${lang}"]`);
        await page.keyboard.press('Escape');
      }
      await page.waitForTimeout(150);
    };

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

    // 5. Navegar a sec-acopio (en móvil abriendo primero el drawer con #btn-sidebar-open)
    if (nombre !== 'desktop') {
      const burgerVisible = await page.$eval('#btn-sidebar-open', el => window.getComputedStyle(el).display !== 'none');
      check(`${nombre} /panel #btn-sidebar-open visible`, burgerVisible);
      await page.click('#btn-sidebar-open');
      await page.waitForTimeout(200);
      const sidebarOpen = await page.$eval('#dash-sidebar', el => el.classList.contains('abierto'));
      check(`${nombre} /panel sidebar se abre con #btn-sidebar-open`, sidebarOpen);
    }

    await page.click('#dash-nav-list .dash-nav-btn[data-section="sec-acopio"]');
    await page.waitForTimeout(200);

    const acopioState = await page.evaluate(() => {
      const secAcopio = document.querySelector('#sec-acopio');
      const secDash = document.querySelector('#sec-dashboard');
      const rangePills = document.querySelector('#dash-range-pills');
      const btnVoz = document.querySelector('#btn-voz');
      const teclas = [...document.querySelectorAll('.botonera button')];
      return {
        acopioVisible: !!secAcopio && !secAcopio.hidden,
        dashHidden: !!secDash && secDash.hidden,
        rangePillsHidden: !!rangePills && rangePills.hidden && window.getComputedStyle(rangePills).display === 'none',
        vozHeight: btnVoz ? btnVoz.getBoundingClientRect().height : 0,
        teclaMinHeight: teclas.length ? Math.min(...teclas.map(b => b.getBoundingClientRect().height)) : 0,
        teclaCount: teclas.length,
        overflow: document.documentElement.scrollWidth > window.innerWidth + 1,
      };
    });

    check(`${nombre} /panel sec-acopio activa y dashboard oculto`, acopioState.acopioVisible && acopioState.dashHidden);
    check(`${nombre} /panel #dash-range-pills oculto fuera del dashboard`, acopioState.rangePillsHidden);
    check(`${nombre} /panel #btn-voz altura >= 48px`, acopioState.vozHeight >= 48, `${acopioState.vozHeight}px`);
    check(`${nombre} /panel .botonera button altura >= 48px`,
      acopioState.teclaCount > 0 && acopioState.teclaMinHeight >= 48,
      `${acopioState.teclaMinHeight}px`);
    check(`${nombre} /panel sec-acopio sin desborde horizontal`, !acopioState.overflow);

    const sinLabelAcopio = await checkVisibleFormLabels(page);
    check(`${nombre} /panel sec-acopio campos visibles con label`, sinLabelAcopio.length === 0, sinLabelAcopio.join(', '));

    await page.screenshot({ path: path.join(OUT, `${nombre}_panel_acopio.png`), fullPage: false });

    // Verificar también sec-tina y sec-quincena para etiquetas de formularios y desborde
    for (const sec of ['sec-tina', 'sec-quincena']) {
      if (nombre !== 'desktop') {
        await page.click('#btn-sidebar-open');
        await page.waitForTimeout(150);
      }
      await page.click(`#dash-nav-list .dash-nav-btn[data-section="${sec}"]`);
      await page.waitForTimeout(150);
      const secOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
      check(`${nombre} /panel ${sec} sin desborde horizontal`, !secOverflow);
      const sinLabelSec = await checkVisibleFormLabels(page);
      check(`${nombre} /panel ${sec} campos visibles con label`, sinLabelSec.length === 0, sinLabelSec.join(', '));
    }

    check(`${nombre} sin errores JS ni de consola`, errores.length === 0, errores.join(' | '));
    await ctx.close();
  }

  await browser.close();
  console.log(problemas.length ? 'FALLOS: ' + problemas.length : 'TODO OK');
  process.exit(problemas.length ? 1 : 0);
})();
