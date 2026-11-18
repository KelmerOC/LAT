const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const OUT = path.resolve('C:/Users/kelme/.gemini/antigravity/brain/f120c463-6b44-45b5-afab-c5c013980892/scratch');
fs.mkdirSync(OUT, { recursive: true });
const BASE = 'http://127.0.0.1:8000';

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const problemas = [];
  const check = (n, ok, d = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + n + (ok ? '' : ' -> ' + d)); if (!ok) problemas.push(n); };

  for (const [nombre, vp] of [['desktop', { width: 1280, height: 800 }], ['movil', { width: 390, height: 844 }]]) {
    const ctx = await browser.newContext({ viewport: vp });
    const page = await ctx.newPage();
    const errores = [];
    page.on('pageerror', e => errores.push(e.message));
    page.on('console', m => { if (m.type() === 'error' && !m.text().includes('unsplash') && !m.text().includes('ERR_')) errores.push(m.text()); });

    const r = await page.goto(BASE + '/', { waitUntil: 'networkidle' });
    check(nombre + ' inicio 200', r.status() === 200, r.status());
    const desborde = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    check(nombre + ' inicio sin desborde horizontal', !desborde);

    // Verificación de barra superior azul eliminada / oculta
    const topBarNoVisible = await page.evaluate(() => {
      const tb = document.querySelector('.top-bar');
      return !tb || window.getComputedStyle(tb).display === 'none';
    });
    check(nombre + ' barra superior azul (.top-bar) eliminada/oculta', topBarNoVisible);

    // Verificaciones Swiss Grid en Desktop
    if (nombre === 'desktop') {
      // 1. Barra de navegación plana: altura entre 64px y 72px
      const navAlto = await page.$eval('.nav-principal-inner', el => el.getBoundingClientRect().height);
      check('desktop altura de nav bar entre 64px y 72px', navAlto >= 64 && navAlto <= 72, navAlto);

      // 2. Centrado matemático absoluto de los enlaces de navegación
      const centeringDiff = await page.evaluate(() => {
        const inner = document.querySelector('.nav-principal-inner').getBoundingClientRect();
        const menu = document.querySelector('#menu-links').getBoundingClientRect();
        const innerMid = inner.left + inner.width / 2;
        const menuMid = menu.left + menu.width / 2;
        return Math.abs(innerMid - menuMid);
      });
      check('desktop opciones de navegación centradas simétricamente (desvío <= 2px)', centeringDiff <= 2, `${centeringDiff}px`);

      // 3. Dropdown de selección de idiomas en escritorio
      const btnLang = await page.$('#btn-lang-trigger');
      check('desktop botón dropdown de idioma presente', !!btnLang);

      await page.click('#btn-lang-trigger');
      await page.waitForTimeout(150);
      const dropdownAbierto = await page.$eval('#lang-dropdown', el => el.classList.contains('abierto'));
      const ariaExpanded = await page.$eval('#btn-lang-trigger', el => el.getAttribute('aria-expanded'));
      check('desktop dropdown de idioma se abre al hacer clic', dropdownAbierto && ariaExpanded === 'true');

      // Seleccionar Quechua
      await page.click('#lang-dropdown-menu [data-lang="qu"]');
      await page.waitForTimeout(150);
      const currentLangLabel = await page.$eval('#lang-current-label', el => el.textContent.trim());
      const htmlLang = await page.evaluate(() => document.documentElement.lang);
      check('desktop cambio de idioma a QU actualiza etiqueta y html lang', currentLangLabel === 'QU' && htmlLang === 'qu', `${currentLangLabel} / ${htmlLang}`);

      // Volver a Español
      await page.click('#btn-lang-trigger');
      await page.click('#lang-dropdown-menu [data-lang="es"]');
      await page.waitForTimeout(100);

      // 4. Botón primario y secundario con geometría recta (radius <= 2px)
      const btnEmberRadius = await page.$eval('.hero-acciones .btn-ember', el => parseFloat(window.getComputedStyle(el).borderRadius));
      const btnOutlineRadius = await page.$eval('.hero-acciones .btn-outline', el => parseFloat(window.getComputedStyle(el).borderRadius));
      check('desktop botones primarios y secundarios rectos (<= 2px)', btnEmberRadius <= 2 && btnOutlineRadius <= 2, `${btnEmberRadius}px / ${btnOutlineRadius}px`);

      // 5. Tarjeta de medios con radius suavizado (6px - 8px) y botón (+) en esquina
      const bannerRadius = await page.$eval('#hero-carousel', el => parseFloat(window.getComputedStyle(el).borderRadius));
      const btnPlusVisible = await page.$eval('.btn-plus-corner', el => window.getComputedStyle(el).display !== 'none');
      check('desktop banner multimedia con radio 8px y botón (+)', bannerRadius >= 6 && bannerRadius <= 9 && btnPlusVisible, `${bannerRadius}px`);

      // 6. Eje central del proceso (spine timeline) presente
      const spinePresente = await page.$eval('.arbol-lista', el => {
        const ps = window.getComputedStyle(el, '::before');
        return ps.content !== 'none' && ps.position === 'absolute';
      });
      check('desktop eje central (spine line) presente en proceso', spinePresente);
    }

    await page.screenshot({ path: path.join(OUT, `${nombre}_inicio.png`), fullPage: true });

    // Prueba del Menú Desplegable y selector de idioma en Móvil
    if (nombre === 'movil') {
      const btnMenuVisible = await page.$eval('#btn-menu', el => window.getComputedStyle(el).display !== 'none');
      check('movil botón hamburguesa visible', btnMenuVisible);

      const btnLangMobileVisible = await page.$eval('#btn-lang-trigger-mobile', el => window.getComputedStyle(el).display !== 'none');
      check('movil selector de idioma compacto en barra de navegación visible', btnLangMobileVisible);

      await page.click('#btn-menu');
      await page.waitForTimeout(250);
      const menuAbierto = await page.$eval('#menu-links', el => el.classList.contains('abierto'));
      const ariaExpanded = await page.$eval('#btn-menu', el => el.getAttribute('aria-expanded'));
      check('movil menú se despliega al tocar botón', menuAbierto && ariaExpanded === 'true');

      // Selector de idioma dentro del drawer móvil
      const mobileLangChoiceCount = await page.$$eval('.btn-lang-choice', els => els.length);
      check('movil selector de idioma con 3 opciones en drawer', mobileLangChoiceCount === 3);

      await page.screenshot({ path: path.join(OUT, 'movil_menu_desplegado.png'), fullPage: false });

      // Cerrar menú con escape
      await page.keyboard.press('Escape');
      const menuCerrado = await page.$eval('#menu-links', el => !el.classList.contains('abierto'));
      check('movil menú se cierra con Escape', menuCerrado);
    }

    await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(OUT, `${nombre}_login.png`), fullPage: false });
    await page.click('button[type=submit].btn-ember');
    await page.waitForURL('**/panel**');
    check(nombre + ' login lleva al panel', page.url().includes('/panel'));
    const desbordeP = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    check(nombre + ' panel sin desborde horizontal', !desbordeP);

    // Verificación de líneas divisorias "- - - - -" (dashed) con transparencia en el panel
    const dashedStyles = await page.evaluate(() => {
      const cabecera = window.getComputedStyle(document.querySelector('.panel-cabecera')).borderStyle;
      const card = window.getComputedStyle(document.querySelector('.panel-card')).borderStyle;
      const tablaWrap = window.getComputedStyle(document.querySelector('.panel-tabla-wrap')).borderStyle;
      const tdBorder = window.getComputedStyle(document.querySelector('.tabla-plana td')).borderBottomStyle;
      return { cabecera, card, tablaWrap, tdBorder };
    });
    check(nombre + ' panel cabecera con borde dashed', dashedStyles.cabecera.includes('dashed'), dashedStyles.cabecera);
    check(nombre + ' panel tarjeta con borde dashed', dashedStyles.card.includes('dashed'), dashedStyles.card);
    check(nombre + ' panel tabla wrap con borde dashed', dashedStyles.tablaWrap.includes('dashed'), dashedStyles.tablaWrap);
    check(nombre + ' panel filas de tabla con separador dashed', dashedStyles.tdBorder.includes('dashed'), dashedStyles.tdBorder);

    // Tamaños táctiles
    const vozAlto = await page.$eval('#btn-voz', el => el.getBoundingClientRect().height);
    check(nombre + ' botón de voz >= 64px', vozAlto >= 64, vozAlto);
    const teclaAlto = await page.$eval('.botonera button', el => el.getBoundingClientRect().height);
    check(nombre + ' botonera >= 54px', teclaAlto >= 54, teclaAlto);

    // Pruebas específicas del panel móvil vs desktop
    if (nombre === 'movil') {
      const dropdownVisible = await page.$eval('#panel-modulo-dropdown', el => window.getComputedStyle(el).display !== 'none');
      const tabsDesktopOcultas = await page.$eval('.tabs-bar-desktop', el => window.getComputedStyle(el).display === 'none');
      check('movil selector desplegable de módulos visible', dropdownVisible);
      check('movil pestañas desktop ocultas', tabsDesktopOcultas);

      // Desplegar menú de módulos
      await page.click('#btn-dropdown-modulos');
      await page.waitForTimeout(250);
      const dropdownAbierto = await page.$eval('#dropdown-lista-modulos', el => el.classList.contains('abierto'));
      check('movil dropdown de módulos se abre al tocar', dropdownAbierto);
      await page.screenshot({ path: path.join(OUT, 'movil_panel_dropdown_abierto.png'), fullPage: false });

      // Seleccionar Módulo 2 (Calidad Sanitaria)
      await page.click('#dropdown-lista-modulos [data-tab="tab-calidad"]');
      const calidadVisible = await page.$eval('#tab-calidad', el => !el.hidden);
      const acopioOculto = await page.$eval('#tab-acopio', el => el.hidden);
      const titActivo = await page.$eval('#dropdown-titulo-activo', el => el.innerText);
      check('movil selección de módulo calidad funciona', calidadVisible && acopioOculto);
      check('movil título de dropdown activo actualizado', titActivo.includes('Calidad sanitaria'));
      await page.screenshot({ path: path.join(OUT, 'movil_panel_modulo_calidad.png'), fullPage: true });

      // Volver a módulo 1
      await page.click('#btn-dropdown-modulos');
      await page.click('#dropdown-lista-modulos [data-tab="tab-acopio"]');
    } else {
      const tabAlto = await page.$eval('.tab', el => el.getBoundingClientRect().height);
      check('desktop pestañas >= 48px', tabAlto >= 48, tabAlto);

      // Teclado en pestañas desktop
      await page.focus('#tab-btn-acopio');
      await page.keyboard.press('ArrowRight');
      const sel = await page.$eval('#tab-btn-calidad', el => el.getAttribute('aria-selected'));
      check('desktop flecha derecha activa pestaña 2', sel === 'true', sel);
      await page.screenshot({ path: path.join(OUT, 'desktop_panel_calidad.png'), fullPage: true });
      await page.keyboard.press('End');
      await page.screenshot({ path: path.join(OUT, 'desktop_panel_quincena.png'), fullPage: true });
      await page.click('#tab-btn-acopio');
      await page.screenshot({ path: path.join(OUT, 'desktop_panel_acopio.png'), fullPage: true });
      await page.click('#tab-btn-tina');
      await page.screenshot({ path: path.join(OUT, 'desktop_panel_tina.png'), fullPage: true });
    }

    // Etiquetas de formularios
    const sinEtiqueta = await page.evaluate(() => [...document.querySelectorAll('input:not([type=hidden]), select')].filter(i => !i.id || !document.querySelector(`label[for="${i.id}"]`)).map(i => i.name));
    check(nombre + ' todos los campos tienen label', sinEtiqueta.length === 0, sinEtiqueta.join(','));

    check(nombre + ' sin errores JS', errores.length === 0, errores.join(' | '));
    await ctx.close();
  }
  await browser.close();
  console.log(problemas.length ? 'FALLOS: ' + problemas.length : 'TODO OK');
  process.exit(problemas.length ? 1 : 0);
})();
