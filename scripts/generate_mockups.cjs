const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://127.0.0.1:8000';
const OUT_DIRS = [
  path.resolve(__dirname, '../mockups'),
  path.resolve('d:/Programación WEB/LAT/mockups')
];

OUT_DIRS.forEach(dir => {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});

const TEMP_DIR = path.resolve(__dirname, '../scratch/mockup_screens');
if (!fs.existsSync(TEMP_DIR)) fs.mkdirSync(TEMP_DIR, { recursive: true });

(async () => {
  console.log('Iniciando generador perfeccionado de 5 mockups en blanco y negro...');
  const browser = await chromium.launch({ channel: 'chrome', headless: true });

  // 1. Contexto Desktop para Pantallas 1 a 4
  const ctxDesktop = await browser.newContext({
    viewport: { width: 1440, height: 960 },
    deviceScaleFactor: 2
  });
  const page = await ctxDesktop.newPage();

  // Screen 1: Portada Inicio
  console.log('Capturando Pantalla 1: Portada Inicio...');
  await page.goto(BASE_URL + '/', { waitUntil: 'networkidle' });
  await page.evaluate(() => {
    document.querySelectorAll('.alerta').forEach(a => a.remove());
    document.documentElement.style.filter = 'grayscale(100%) contrast(110%)';
  });
  const screen1Path = path.join(TEMP_DIR, 'screen_1.png');
  await page.screenshot({ path: screen1Path, fullPage: false });

  // Login para entrar al panel
  console.log('Iniciando sesión en panel...');
  await page.goto(BASE_URL + '/login', { waitUntil: 'networkidle' });
  await page.click('button[type=submit].btn-ember');
  await page.waitForURL('**/panel**');

  // Registrar un acopio de demostración si hoy está en 0
  const entregasHoy = await page.$$eval('.tabla-plana tbody tr', trs => trs.length);
  const sinEntregas = await page.evaluate(() => document.body.innerText.includes('No hay entregas registradas hoy'));
  if (sinEntregas || entregasHoy === 0) {
    console.log('Registrando entrega de prueba para enriquecer datos del panel...');
    try {
      await page.selectOption('#select-proveedor', { index: 1 });
      await page.fill('#input-litros', '35.0');
      await page.click('#form-acopio button[type=submit]');
      await page.waitForURL('**/panel**');
    } catch (e) {
      console.log('Nota: registro de entrega ya existente o no requerido:', e.message);
    }
  }

  // Quitar alertas flash de sesión para que el encabezado del panel quede limpio
  await page.evaluate(() => {
    document.querySelectorAll('.alerta').forEach(a => a.remove());
    document.documentElement.style.filter = 'grayscale(100%) contrast(110%)';
  });

  // Screen 2: Panel Acopio
  console.log('Capturando Pantalla 2: Panel Acopio...');
  await page.click('#tab-btn-acopio');
  await page.waitForTimeout(150);
  const screen2Path = path.join(TEMP_DIR, 'screen_2.png');
  await page.screenshot({ path: screen2Path, fullPage: false });

  // Screen 3: Panel Calidad
  console.log('Capturando Pantalla 3: Panel Calidad...');
  await page.click('#tab-btn-calidad');
  await page.waitForTimeout(200);
  const screen3Path = path.join(TEMP_DIR, 'screen_3.png');
  await page.screenshot({ path: screen3Path, fullPage: false });

  // Screen 4: Panel Tina y Receta (con scroll para encuadrar la tarjeta térmica y dosificación)
  console.log('Capturando Pantalla 4: Panel Tina y Receta...');
  await page.click('#tab-btn-tina');
  await page.waitForTimeout(200);
  await page.evaluate(() => {
    const el = document.getElementById('tab-tina');
    if (el) el.scrollIntoView({ behavior: 'instant', block: 'center' });
  });
  await page.waitForTimeout(150);
  const screen4Path = path.join(TEMP_DIR, 'screen_4.png');
  await page.screenshot({ path: screen4Path, fullPage: false });

  await ctxDesktop.close();

  // Screen 5: Celular Smartphone con viewport real
  console.log('Capturando Pantalla 5: Celular Smartphone...');
  const ctxMobile = await browser.newContext({
    viewport: { width: 390, height: 844 },
    deviceScaleFactor: 2
  });
  const pageMobile = await ctxMobile.newPage();
  await pageMobile.goto(BASE_URL + '/', { waitUntil: 'networkidle' });
  await pageMobile.evaluate(() => {
    document.querySelectorAll('.alerta').forEach(a => a.remove());
    document.documentElement.style.filter = 'grayscale(100%) contrast(112%)';
  });
  // Desplegar menú de navegación
  await pageMobile.click('#btn-menu');
  await pageMobile.waitForTimeout(250);
  const screen5Path = path.join(TEMP_DIR, 'screen_5.png');
  await pageMobile.screenshot({ path: screen5Path, fullPage: false });
  await ctxMobile.close();

  // 2. Renderizado de Mockups con marcos de presentación técnica
  const ctxRender = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    deviceScaleFactor: 2
  });
  const renderPage = await ctxRender.newPage();

  const mockupsConfig = [
    {
      tipo: 'desktop',
      archivoSalida: 'mockup_1_portada_desktop_bw.png',
      pantalla: screen1Path,
      tag: 'FIG. 01 · PORTADA PRINCIPAL SWISS-GRID',
      titulo: 'SISTEMA EDITORIAL CON NAVEGACIÓN UNIFICADA Y CENTRADA',
      url: 'lat.sanborreal.pe',
      caption: 'LAT · Arquitectura de software rural sin intermediarios. Tipografía con métricas honestas y hairlines expuestas.'
    },
    {
      tipo: 'desktop',
      archivoSalida: 'mockup_2_panel_acopio_desktop_bw.png',
      pantalla: screen2Path,
      tag: 'FIG. 02 · PANEL OPERATIVO · ACOPIO DIARIO',
      titulo: 'RECEPCIÓN DE LECHE CON DICTADO POR VOZ Y BOTONERA TÁCTIL',
      url: 'lat.sanborreal.pe/panel#acopio',
      caption: 'LAT · División de cuadrícula discontinua "- - - - -" con 16% de opacidad y botones de pesaje rápido de 54px.'
    },
    {
      tipo: 'desktop',
      archivoSalida: 'mockup_3_control_sanitario_desktop_bw.png',
      pantalla: screen3Path,
      tag: 'FIG. 03 · PANEL OPERATIVO · CONTROL SANITARIO 74°',
      titulo: 'TRIAGE SANITARIO INMEDIATO Y PRINCIPIO DE CERO DESPERDICIO',
      url: 'lat.sanborreal.pe/panel#calidad',
      caption: 'LAT · Clasificación inmediata en porongo. La leche con acidez se deriva a quesillos frescos de 4 días.'
    },
    {
      tipo: 'desktop',
      archivoSalida: 'mockup_4_tina_receta_desktop_bw.png',
      pantalla: screen4Path,
      tag: 'FIG. 04 · PANEL OPERATIVO · MONITOREO TÉRMICO Y RECETA',
      titulo: 'CURVA TÉRMICA DE TINA Y DOSIFICACIÓN ESTANDARIZADA',
      url: 'lat.sanborreal.pe/panel#tina',
      caption: 'LAT · Control térmico digital de pasteurización a cuajado y cálculo automático de cuajo, sal y cultivo.'
    },
    {
      tipo: 'smartphone',
      archivoSalida: 'mockup_5_movil_smartphone_bw.png',
      pantalla: screen5Path,
      tag: 'FIG. 05 · APLICACIÓN MÓVIL EN CELULAR / TERRENO',
      titulo: 'INTERFAZ MÓVIL CON SELECTOR DE IDIOMA Y MENÚ DESPLEGABLE',
      url: 'lat.sanborreal.pe (Móvil)',
      caption: 'LAT · Experiencia optimizada para uso con una sola mano en centros de acopio rural de Cusco Provincia.'
    }
  ];

  for (let i = 0; i < mockupsConfig.length; i++) {
    const m = mockupsConfig[i];
    console.log(`Renderizando Mockup ${i + 1} de 5: ${m.archivoSalida}...`);

    let html = '';
    const imgBase64 = fs.readFileSync(m.pantalla).toString('base64');
    const dataUri = `data:image/png;base64,${imgBase64}`;

    if (m.tipo === 'desktop') {
      html = `
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    width: 1920px;
    height: 1080px;
    background: #f1f3f6;
    background-image: 
      linear-gradient(rgba(0,0,0,0.035) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,0,0,0.035) 1px, transparent 1px);
    background-size: 32px 32px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    color: #111111;
    overflow: hidden;
    position: relative;
  }
  .tag-plate {
    position: absolute;
    top: 36px;
    left: 80px;
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .badge {
    background: #111111;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    padding: 6px 14px;
    border-radius: 2px;
  }
  .title-text {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.6px;
    color: #444444;
  }
  .meta-plate {
    position: absolute;
    top: 38px;
    right: 80px;
    font-size: 12px;
    font-weight: 600;
    color: #777777;
    letter-spacing: 1px;
  }
  .browser-mockup {
    width: 1540px;
    height: 890px;
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #c8ccd4;
    box-shadow: 
      0 30px 70px -15px rgba(0, 0, 0, 0.28),
      0 0 0 1px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
  .browser-chrome {
    height: 46px;
    background: #e9ebf0;
    border-bottom: 1px solid #d0d4dc;
    display: flex;
    align-items: center;
    padding: 0 18px;
    gap: 18px;
    flex-shrink: 0;
  }
  .traffic-dots {
    display: flex;
    gap: 7px;
  }
  .dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #bcc1cb;
    border: 1px solid rgba(0, 0, 0, 0.12);
  }
  .url-bar {
    flex: 1;
    max-width: 620px;
    margin: 0 auto;
    height: 30px;
    background: #ffffff;
    border: 1px solid #d4d8e0;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 500;
    color: #444444;
    letter-spacing: 0.3px;
  }
  .url-icon {
    width: 11px;
    height: 11px;
    border-radius: 50%;
    border: 1.5px solid #888888;
  }
  .screen-viewport {
    flex: 1;
    overflow: hidden;
    background: #ffffff;
    position: relative;
  }
  .screen-viewport img {
    width: 100%;
    height: auto;
    display: block;
  }
  .caption-plate {
    position: absolute;
    bottom: 24px;
    left: 80px;
    font-size: 12px;
    color: #666666;
    letter-spacing: 0.4px;
  }
  .watermark {
    position: absolute;
    bottom: 24px;
    right: 80px;
    font-size: 12px;
    font-weight: 700;
    color: #888888;
    letter-spacing: 1.5px;
  }
</style>
</head>
<body>
  <div class="tag-plate">
    <span class="badge">${m.tag}</span>
    <span class="title-text">${m.titulo}</span>
  </div>
  <div class="meta-plate">LAT · PROYECTO UNIVERSITARIO 2026</div>

  <div class="browser-mockup">
    <div class="browser-chrome">
      <div class="traffic-dots">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
      </div>
      <div class="url-bar">
        <div class="url-icon"></div>
        <span>https://${m.url}</span>
      </div>
    </div>
    <div class="screen-viewport">
      <img src="${dataUri}" alt="${m.tag}">
    </div>
  </div>

  <div class="caption-plate">${m.caption}</div>
  <div class="watermark">MONOCHROME EDITION · 100% B&amp;W</div>
</body>
</html>
      `;
    } else {
      // Smartphone frame mockup con barra de estado dedicada
      html = `
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    width: 1920px;
    height: 1080px;
    background: #f1f3f6;
    background-image: 
      linear-gradient(rgba(0,0,0,0.035) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,0,0,0.035) 1px, transparent 1px);
    background-size: 32px 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    color: #111111;
    overflow: hidden;
    position: relative;
  }
  .tag-plate {
    position: absolute;
    top: 40px;
    left: 80px;
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .badge {
    background: #111111;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    padding: 6px 14px;
    border-radius: 2px;
  }
  .title-text {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.6px;
    color: #444444;
  }
  .meta-plate {
    position: absolute;
    top: 42px;
    right: 80px;
    font-size: 12px;
    font-weight: 600;
    color: #777777;
    letter-spacing: 1px;
  }

  /* Chasis del Smartphone */
  .phone-wrapper {
    display: flex;
    align-items: center;
    gap: 80px;
  }
  .phone-frame {
    width: 440px;
    height: 900px;
    background: #151618;
    border-radius: 54px;
    padding: 12px;
    box-shadow: 
      0 35px 80px -15px rgba(0, 0, 0, 0.35),
      0 0 0 1px rgba(255, 255, 255, 0.15) inset,
      0 0 0 3px #242528;
    position: relative;
    display: flex;
    flex-direction: column;
  }
  .phone-frame::before {
    content: '';
    position: absolute;
    left: -5px;
    top: 140px;
    width: 3px;
    height: 50px;
    background: #2b2c30;
    border-radius: 3px 0 0 3px;
  }
  .phone-frame::after {
    content: '';
    position: absolute;
    right: -5px;
    top: 180px;
    width: 3px;
    height: 75px;
    background: #2b2c30;
    border-radius: 0 3px 3px 0;
  }

  .phone-screen {
    flex: 1;
    width: 100%;
    background: #ffffff;
    border-radius: 44px;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
  }

  /* Barra de Estado Realista de Celular (44px) */
  .phone-status-bar {
    height: 46px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    position: relative;
    z-index: 60;
    flex-shrink: 0;
  }
  .status-time {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: -0.2px;
    color: #111111;
  }
  .dynamic-island {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    width: 106px;
    height: 26px;
    background: #000000;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 10px;
  }
  .camera-lens {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #18191d;
    border: 1px solid #282828;
  }
  .status-icons {
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .signal-bars {
    display: flex;
    align-items: flex-end;
    gap: 1.5px;
    height: 10px;
  }
  .signal-bars span {
    width: 2.5px;
    background: #111111;
    border-radius: 0.5px;
  }
  .battery-pill {
    width: 19px;
    height: 10px;
    border: 1.5px solid #111111;
    border-radius: 3px;
    position: relative;
    padding: 1px;
  }
  .battery-pill::after {
    content: '';
    position: absolute;
    right: -3px;
    top: 2px;
    width: 1.5px;
    height: 3px;
    background: #111111;
    border-radius: 0 1px 1px 0;
  }
  .battery-fill {
    width: 100%;
    height: 100%;
    background: #111111;
    border-radius: 1px;
  }

  .screen-scroll-view {
    flex: 1;
    overflow: hidden;
    position: relative;
  }
  .screen-scroll-view img {
    width: 100%;
    height: auto;
    display: block;
  }

  /* Panel lateral de especificaciones */
  .specs-panel {
    max-width: 480px;
    display: flex;
    flex-direction: column;
    gap: 24px;
  }
  .specs-card {
    background: #ffffff;
    border: 1px solid #d2d6de;
    border-radius: 4px;
    padding: 24px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.06);
  }
  .specs-card h3 {
    font-size: 15px;
    font-weight: 700;
    color: #111111;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .specs-card p {
    font-size: 13px;
    line-height: 1.5;
    color: #4b5563;
  }
  .specs-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 14px;
  }
  .specs-list li {
    font-size: 12px;
    color: #222222;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .specs-list li strong {
    color: #000000;
    display: inline-block;
    min-width: 135px;
  }

  .caption-plate {
    position: absolute;
    bottom: 30px;
    left: 80px;
    font-size: 12px;
    color: #666666;
    letter-spacing: 0.4px;
  }
  .watermark {
    position: absolute;
    bottom: 30px;
    right: 80px;
    font-size: 12px;
    font-weight: 700;
    color: #888888;
    letter-spacing: 1.5px;
  }
</style>
</head>
<body>
  <div class="tag-plate">
    <span class="badge">${m.tag}</span>
    <span class="title-text">${m.titulo}</span>
  </div>
  <div class="meta-plate">LAT · PROYECTO UNIVERSITARIO 2026</div>

  <div class="phone-wrapper">
    <div class="phone-frame">
      <div class="phone-screen">
        <div class="phone-status-bar">
          <span class="status-time">9:41</span>
          <div class="dynamic-island">
            <div class="camera-lens"></div>
          </div>
          <div class="status-icons">
            <div class="signal-bars">
              <span style="height: 3px;"></span>
              <span style="height: 5px;"></span>
              <span style="height: 7px;"></span>
              <span style="height: 9px;"></span>
            </div>
            <div class="battery-pill"><div class="battery-fill"></div></div>
          </div>
        </div>
        <div class="screen-scroll-view">
          <img src="${dataUri}" alt="${m.tag}">
        </div>
      </div>
    </div>

    <div class="specs-panel">
      <div class="specs-card">
        <h3>Experiencia Operativa en Terreno</h3>
        <p>Diseñada específicamente para entornos rurales donde las manos pueden estar húmedas o con guantes:</p>
        <ul class="specs-list">
          <li><strong>Barra Superior:</strong> Logo + Selector Compacto [🌐 ES ▾] + Menú</li>
          <li><strong>Idioma Rápido:</strong> Conmutador accesible trilingüe (ES / QU / EN)</li>
          <li><strong>Objetivos Táctiles:</strong> Botonera ≥ 54 px, micrófono ≥ 64 px</li>
          <li><strong>Cero Latencia:</strong> Renderizado ligero sin dependencias pesadas</li>
        </ul>
      </div>

      <div class="specs-card" style="border-style: dashed; border-color: #888;">
        <h3>Especificación de Blueprint</h3>
        <p>Mockup oficial en monocromo de alta fidelidad para manual técnico y documentación de arquitectura.</p>
      </div>
    </div>
  </div>

  <div class="caption-plate">${m.caption}</div>
  <div class="watermark">MONOCHROME EDITION · 100% B&amp;W</div>
</body>
</html>
      `;
    }

    await renderPage.setContent(html);
    await renderPage.waitForTimeout(300);

    for (const outDir of OUT_DIRS) {
      const outPath = path.join(outDir, m.archivoSalida);
      await renderPage.screenshot({ path: outPath, fullPage: false });
      console.log(`  Guardado en: ${outPath}`);
    }
  }

  await ctxRender.close();
  await browser.close();
  console.log('¡Todos los 5 mockups perfeccionados fueron generados con éxito!');
})();
