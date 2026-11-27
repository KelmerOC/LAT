const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const OUT_DIRS = [
  path.resolve(__dirname, '../docs/mockups')
];

OUT_DIRS.forEach(dir => {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
});

// Helper para generar cajas con "X" cruzada estilo wireframe clásico (según referencia del usuario)
function boxWithX(width, height, label = '') {
  return `
    <div style="position: relative; width: ${width}; height: ${height}; border: 2px solid #000000; background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
      <svg style="position: absolute; inset: 0; width: 100%; height: 100%;" preserveAspectRatio="none" viewBox="0 0 100 100">
        <line x1="0" y1="0" x2="100" y2="100" stroke="#000000" stroke-width="1.5" />
        <line x1="100" y1="0" x2="0" y2="100" stroke="#000000" stroke-width="1.5" />
      </svg>
      ${label ? `<span style="position: relative; z-index: 2; background: #ffffff; padding: 2px 6px; font-size: 11px; font-weight: 700; border: 1px solid #000; text-transform: uppercase;">${label}</span>` : ''}
    </div>
  `;
}

const CSS_BASE = `
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    width: 1440px;
    background: #ffffff;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #000000;
    line-height: 1.4;
    padding: 0;
    margin: 0;
  }
  .wire-canvas {
    width: 1440px;
    padding: 32px 48px 40px;
    background: #ffffff;
    position: relative;
    border: 3px solid #000000;
  }
  .wire-top-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #000000;
    padding-bottom: 12px;
    margin-bottom: 20px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
  }
  .wire-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 2px solid #000000;
    padding: 12px 24px;
    margin-bottom: 24px;
    background: #ffffff;
    height: 68px;
  }
  .wire-nav-links {
    display: flex;
    gap: 24px;
    font-size: 14px;
    font-weight: 700;
  }
  .wire-btn {
    border: 2px solid #000000;
    background: #ffffff;
    color: #000000;
    font-weight: 700;
    font-size: 13px;
    padding: 8px 16px;
    cursor: pointer;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }
  .wire-btn-black {
    background: #000000;
    color: #ffffff;
  }
  .wire-card {
    border: 2px solid #000000;
    padding: 20px;
    background: #ffffff;
  }
  .wire-card-dashed {
    border: 2px dashed #000000;
    padding: 20px;
    background: #ffffff;
  }
  .wire-table {
    width: 100%;
    border-collapse: collapse;
    border: 2px solid #000000;
    font-size: 13px;
  }
  .wire-table th, .wire-table td {
    border: 1px solid #000000;
    padding: 10px 14px;
    text-align: left;
  }
  .wire-table th {
    background: #f0f0f0;
    font-weight: 700;
  }
  .wire-table-dashed td {
    border-bottom: 1.5px dashed #000000;
  }
  .wire-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border: 2px dashed #000000;
    margin-bottom: 20px;
  }
  .wire-kpi-cell {
    padding: 14px 18px;
    border-right: 1.5px dashed #000000;
  }
  .wire-kpi-cell:last-child {
    border-right: none;
  }
  .wire-tabs {
    display: flex;
    border: 2px dashed #000000;
    margin-bottom: 20px;
    background: #ffffff;
  }
  .wire-tab {
    padding: 10px 22px;
    font-weight: 700;
    font-size: 13px;
    border-right: 1.5px dashed #000000;
  }
  .wire-tab:last-child {
    border-right: none;
  }
  .wire-tab.activo {
    background: #000000;
    color: #ffffff;
  }
  .wire-footer-tag {
    margin-top: 28px;
    padding-top: 12px;
    border-top: 2px solid #000000;
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    font-weight: 600;
  }
`;

(async () => {
  console.log('Iniciando generador de 5 wireframe mockups según la referencia...');
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 1200 }, deviceScaleFactor: 2 });

  // ---------------------------------------------------------------------------
  // WIREFRAME 1: Portada Principal Desktop
  // ---------------------------------------------------------------------------
  console.log('Generando Wireframe 1: Portada Principal...');
  const html1 = `
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>${CSS_BASE}</style></head>
<body>
  <div class="wire-canvas">
    <div class="wire-top-meta">
      <div>LAT · WIREFRAME ESQUEMÁTICO EN BLANCO Y NEGRO (SWISS-GRID)</div>
      <div>FIG. 01 · PORTADA PRINCIPAL (DESKTOP)</div>
    </div>

    <div class="wire-nav">
      <div style="display: flex; align-items: center; gap: 14px;">
        ${boxWithX('44px', '44px', 'LOGO')}
        <div>
          <strong style="font-size: 18px; letter-spacing: -0.5px;">LAT</strong><br>
          <small style="font-size: 11px; font-weight: 600;">Gestión Láctea Rural</small>
        </div>
      </div>

      <div class="wire-nav-links">
        <span>Inicio</span>
        <span>Herramientas ⌄</span>
        <span>Empresas Familiares ⌄</span>
        <span>Nosotros</span>
      </div>

      <div style="display: flex; align-items: center; gap: 16px;">
        <div class="wire-btn" style="padding: 6px 12px;">🌐 ES ▾</div>
        <span style="font-size: 13px; font-weight: 700; text-decoration: underline;">Iniciar Sesión</span>
        <div class="wire-btn wire-btn-black">Mi Panel</div>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.15fr 1fr; gap: 40px; margin-bottom: 32px; align-items: center;">
      <div>
        <h1 style="font-size: 40px; line-height: 1.15; font-weight: 800; margin-bottom: 16px; letter-spacing: -1px;">
          Donde antes un cuaderno se perdía en el agua, hoy manda la voz.
        </h1>
        <p style="font-size: 15px; line-height: 1.5; margin-bottom: 22px; color: #222;">
          Digitalización accesible para centros de acopio rural, control sanitario de porongo en recepción, monitoreo térmico de tina y liquidaciones transparentes al 0% de comisión.
        </p>
        <div style="display: flex; gap: 14px; margin-bottom: 22px;">
          <div class="wire-btn wire-btn-black" style="padding: 12px 22px; font-size: 14px;">Entrar a Mi Panel Operativo →</div>
          <div class="wire-btn" style="padding: 12px 22px; font-size: 14px;">Ver herramientas de terreno ↗</div>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
          <div class="wire-btn" style="padding: 4px 10px; font-size: 11px;">✓ S/ 0.00 comisión</div>
          <div class="wire-btn" style="padding: 4px 10px; font-size: 11px;">✓ Dictado por voz y modo una mano</div>
          <div class="wire-btn" style="padding: 4px 10px; font-size: 11px;">✓ Triage sanitario 74°</div>
          <div class="wire-btn" style="padding: 4px 10px; font-size: 11px;">✓ Ley N.° 29733 (sin DNI)</div>
        </div>
      </div>

      <div>
        ${boxWithX('100%', '330px', 'HERO CAROUSEL / GANADO LECHERO')}
        <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 11px; font-weight: 600;">
          <span>Fotografías referenciales de centros de acopio</span>
          <span>[ ⏸ Pausa ] [ + ]</span>
        </div>
      </div>
    </div>

    <div style="border: 2px solid #000; padding: 10px 18px; font-weight: 700; font-size: 13px; margin-bottom: 16px; background: #f0f0f0;">
      DIRIGIDA A EMPRESAS FAMILIARES (SIN DATOS SIMULADOS)
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
      <div class="wire-card" style="display: flex; gap: 16px; align-items: center;">
        ${boxWithX('110px', '100px', 'FOTO 1')}
        <div style="flex: 1;">
          <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Queserías familiares</h3>
          <p style="font-size: 12px; margin-bottom: 8px;">Procesamiento de 50 a 1,000 L diarios entre padres e hijos.</p>
          <div class="wire-btn" style="padding: 3px 8px; font-size: 11px;">Ver más »</div>
        </div>
      </div>

      <div class="wire-card" style="display: flex; gap: 16px; align-items: center;">
        ${boxWithX('110px', '100px', 'FOTO 2')}
        <div style="flex: 1;">
          <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Rutas de pequeños ganaderos</h3>
          <p style="font-size: 12px; margin-bottom: 8px;">Recepción por porongos en parcelas rurales por voz o tacto.</p>
          <div class="wire-btn" style="padding: 3px 8px; font-size: 11px;">Ver más »</div>
        </div>
      </div>

      <div class="wire-card" style="display: flex; gap: 16px; align-items: center;">
        ${boxWithX('110px', '100px', 'FOTO 3')}
        <div style="flex: 1;">
          <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Centros de transformación</h3>
          <p style="font-size: 12px; margin-bottom: 8px;">Monitoreo de tina 72° a 38°C y cuentas claras a S/ 0.00.</p>
          <div class="wire-btn" style="padding: 3px 8px; font-size: 11px;">Ver más »</div>
        </div>
      </div>
    </div>

    <div class="wire-footer-tag">
      <span>LAT · PLATAFORMA LÁCTEA RURAL COMUNITARIA</span>
      <span>WIREFRAME 01 / 05 · ESPECIFICACIÓN NORMATIVA</span>
    </div>
  </div>
</body>
</html>
  `;
  await page.setContent(html1);
  const el1 = page.locator('.wire-canvas');
  for (const dir of OUT_DIRS) {
    await el1.screenshot({ path: path.join(dir, 'mockup_1_portada_wireframe.png') });
  }

  // ---------------------------------------------------------------------------
  // WIREFRAME 2: Panel Operativo · Módulo 1: Acopio Diario
  // ---------------------------------------------------------------------------
  console.log('Generando Wireframe 2: Panel Acopio Diario...');
  const html2 = `
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>${CSS_BASE}</style></head>
<body>
  <div class="wire-canvas">
    <div class="wire-top-meta">
      <div>LAT · WIREFRAME ESQUEMÁTICO EN BLANCO Y NEGRO (LÍNEAS DISCONTINUAS "- - - - -")</div>
      <div>FIG. 02 · PANEL OPERATIVO · MÓDULO 1: ACOPIO DIARIO</div>
    </div>

    <div class="wire-nav">
      <div style="display: flex; align-items: center; gap: 14px;">
        ${boxWithX('40px', '40px', 'LAT')}
        <strong>LAT · Gestión Láctea Rural</strong>
      </div>
      <div class="wire-nav-links">
        <span>Inicio</span>
        <span>Herramientas ⌄</span>
        <span>Empresas Familiares ⌄</span>
        <span>Nosotros</span>
      </div>
      <div style="display: flex; align-items: center; gap: 16px;">
        <div class="wire-btn" style="padding: 6px 12px;">🌐 ES ▾</div>
        <span style="font-size: 13px; font-weight: 700;">Cerrar Sesión</span>
        <div class="wire-btn wire-btn-black">Mi Panel</div>
      </div>
    </div>

    <div class="wire-card-dashed" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
      <div>
        <p style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Centro de acopio y transformación</p>
        <h2 style="font-size: 24px; font-weight: 800; margin: 3px 0;">Planta Láctea San Borreal · Cusco Provincia</h2>
        <p style="font-size: 13px;">Operador: <strong>Don Justo Quispe</strong> · Cargo: <strong>ADMINISTRACIÓN</strong></p>
      </div>
      <div class="wire-btn" style="padding: 8px 16px; border-style: dashed;">⬇ Exportar balance CSV</div>
    </div>

    <div class="wire-kpi-grid">
      <div class="wire-kpi-cell">
        <span style="font-size: 12px; font-weight: 700;">Leche recibida hoy</span>
        <div style="font-size: 28px; font-weight: 800; margin: 4px 0;">35.0 <small style="font-size: 15px;">L</small></div>
        <span style="font-size: 11px;">1 entregas registradas</span>
      </div>
      <div class="wire-kpi-cell">
        <span style="font-size: 12px; font-weight: 700;">Precio vigente por litro</span>
        <div style="font-size: 28px; font-weight: 800; margin: 4px 0;">S/ 1.65</div>
        <span style="font-size: 11px;">Comisión LAT: S/ 0.00</span>
      </div>
      <div class="wire-kpi-cell">
        <span style="font-size: 12px; font-weight: 700;">Tina pasteurizadora</span>
        <div style="font-size: 28px; font-weight: 800; margin: 4px 0;">40.0 <small style="font-size: 15px;">°C</small></div>
        <span style="font-size: 11px;">LISTA PARA CUAJO (100%)</span>
      </div>
      <div class="wire-kpi-cell">
        <span style="font-size: 12px; font-weight: 700;">Control sanitario 74°</span>
        <div style="font-size: 28px; font-weight: 800; margin: 4px 0;">100%</div>
        <span style="font-size: 11px;">Todas las entregas verificadas</span>
      </div>
    </div>

    <div class="wire-tabs">
      <div class="wire-tab activo">1. Acopio por voz y tacto</div>
      <div class="wire-tab">2. Calidad sanitaria</div>
      <div class="wire-tab">3. Tina y receta</div>
      <div class="wire-tab">4. Liquidación quincenal</div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.3fr; gap: 24px; align-items: start;">
      <div class="wire-card-dashed">
        <h3 style="font-size: 17px; font-weight: 800; margin-bottom: 4px;">Registro diario de recepción</h3>
        <p style="font-size: 12px; margin-bottom: 12px;">Dicte por voz o ingrese litros de forma manual.</p>

        <div class="wire-btn wire-btn-black" style="width: 100%; min-height: 56px; font-size: 15px; margin-bottom: 8px;">
          🎙 Dictar acopio por voz (64px)
        </div>
        <div style="border: 1px dashed #000; padding: 5px 10px; font-size: 12px; font-style: italic; margin-bottom: 12px;">
          Transcripción activa: "Don Eloy Condori, 35 litros"
        </div>

        <div style="margin-bottom: 10px;">
          <label style="display: block; font-size: 11px; font-weight: 700; margin-bottom: 3px;">Ganadero / productor</label>
          <div style="border: 2px solid #000; padding: 7px 10px; font-size: 13px;">Don Eloy Condori (Markjo) ▾</div>
        </div>

        <div style="margin-bottom: 10px;">
          <label style="display: block; font-size: 11px; font-weight: 700; margin-bottom: 3px;">Volumen en litros (L)</label>
          <div style="border: 2px solid #000; padding: 6px 10px; font-size: 20px; font-weight: 400;">35.0</div>
        </div>

        <label style="display: block; font-size: 11px; font-weight: 700; margin-bottom: 5px;">Botonera táctil rápida (54px)</label>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px; margin-bottom: 14px;">
          <div class="wire-btn" style="min-height: 48px; font-size: 14px; border-style: dashed;">+10</div>
          <div class="wire-btn" style="min-height: 48px; font-size: 14px; border-style: dashed;">+15</div>
          <div class="wire-btn" style="min-height: 48px; font-size: 14px; border-style: dashed;">+20</div>
          <div class="wire-btn" style="min-height: 48px; font-size: 14px; border-style: dashed;">+25</div>
          <div class="wire-btn" style="min-height: 48px; font-size: 14px; border-style: dashed;">+30</div>
          <div class="wire-btn wire-btn-black" style="min-height: 48px; font-size: 14px; border-style: dashed;">+35</div>
          <div class="wire-btn" style="min-height: 48px; font-size: 14px; border-style: dashed;">+40</div>
          <div class="wire-btn" style="min-height: 48px; font-size: 14px; border-style: dashed;">+50</div>
        </div>

        <div class="wire-btn wire-btn-black" style="width: 100%; min-height: 46px; font-size: 14px;">
          Guardar entrega en tina
        </div>
      </div>

      <div class="wire-card-dashed" style="padding: 0; overflow: hidden;">
        <div style="border-bottom: 2px dashed #000; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; background: #f7f7f7;">
          <strong style="font-size: 15px;">Entregas de hoy (04/11/2026)</strong>
          <span style="border: 1px solid #000; padding: 2px 8px; font-size: 12px; font-weight: 700;">Total: 35.0 L</span>
        </div>
        <table class="wire-table wire-table-dashed" style="border: none;">
          <thead>
            <tr>
              <th>Hora</th>
              <th>Productor</th>
              <th style="text-align: right;">Litros</th>
              <th>Método</th>
              <th>Calidad</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>00:28</td>
              <td><strong>Don Eloy Condori</strong><br><small>Markjo · PROV-01</small></td>
              <td style="text-align: right; font-size: 16px; font-weight: 700;">35.0 L</td>
              <td><span style="border: 1px solid #000; padding: 2px 6px; font-size: 11px;">Teclado</span></td>
              <td><span style="border: 1px solid #000; background: #000; color: #fff; padding: 2px 6px; font-size: 11px;">✓ Aprobada</span></td>
            </tr>
            <tr>
              <td>06:45</td>
              <td><strong>Doña María Huamán</strong><br><small>Compone · PROV-02</small></td>
              <td style="text-align: right; font-size: 16px; font-weight: 700;">28.5 L</td>
              <td><span style="border: 1px solid #000; padding: 2px 6px; font-size: 11px;">🎙 Voz</span></td>
              <td><span style="border: 1px solid #000; background: #000; color: #fff; padding: 2px 6px; font-size: 11px;">✓ Aprobada</span></td>
            </tr>
            <tr>
              <td>07:10</td>
              <td><strong>Don Saturnino Choque</strong><br><small>Izcuchaca · PROV-03</small></td>
              <td style="text-align: right; font-size: 16px; font-weight: 700;">22.0 L</td>
              <td><span style="border: 1px solid #000; padding: 2px 6px; font-size: 11px;">Teclado</span></td>
              <td><span style="border: 1px dashed #000; padding: 2px 6px; font-size: 11px;">⚠ Ácida (Quesillo)</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="wire-footer-tag">
      <span>LAT · PANEL OPERATIVO CON LÍNEAS DISCONTINUAS ("- - - - -")</span>
      <span>WIREFRAME 02 / 05 · ACOPIO POR VOZ Y BOTONERA</span>
    </div>
  </div>
</body>
</html>
  `;
  await page.setContent(html2);
  const el2 = page.locator('.wire-canvas');
  for (const dir of OUT_DIRS) {
    await el2.screenshot({ path: path.join(dir, 'mockup_2_panel_acopio_wireframe.png') });
  }

  // ---------------------------------------------------------------------------
  // WIREFRAME 3: Panel Operativo · Módulo 2: Calidad Sanitaria (Triage 74°)
  // ---------------------------------------------------------------------------
  console.log('Generando Wireframe 3: Calidad Sanitaria...');
  const html3 = `
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>${CSS_BASE}</style></head>
<body>
  <div class="wire-canvas">
    <div class="wire-top-meta">
      <div>LAT · WIREFRAME ESQUEMÁTICO EN BLANCO Y NEGRO (LÍNEAS DISCONTINUAS "- - - - -")</div>
      <div>FIG. 03 · PANEL OPERATIVO · MÓDULO 2: CONTROL SANITARIO 74°</div>
    </div>

    <div class="wire-nav">
      <div style="display: flex; align-items: center; gap: 14px;">
        ${boxWithX('40px', '40px', 'LAT')}
        <strong>LAT · Gestión Láctea Rural</strong>
      </div>
      <div class="wire-nav-links">
        <span>Inicio</span>
        <span>Herramientas ⌄</span>
        <span>Empresas Familiares ⌄</span>
        <span>Nosotros</span>
      </div>
      <div style="display: flex; align-items: center; gap: 16px;">
        <div class="wire-btn" style="padding: 6px 12px;">🌐 ES ▾</div>
        <span style="font-size: 13px; font-weight: 700;">Cerrar Sesión</span>
        <div class="wire-btn wire-btn-black">Mi Panel</div>
      </div>
    </div>

    <div class="wire-tabs">
      <div class="wire-tab">1. Acopio por voz y tacto</div>
      <div class="wire-tab activo">2. Calidad sanitaria (Triage 74°)</div>
      <div class="wire-tab">3. Tina y receta</div>
      <div class="wire-tab">4. Liquidación quincenal</div>
    </div>

    <div class="wire-card-dashed">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
        <div>
          <h2 style="font-size: 22px; font-weight: 800; margin-bottom: 6px;">Control sanitario en recepción (prueba de alcohol a 74°)</h2>
          <p style="font-size: 13px; max-width: 900px;">
            <strong>Principio de cero desperdicio:</strong> la leche con acidez no se desecha ni se multa al productor. Se clasifica de inmediato y se deriva a quesillos frescos de 4 días.
          </p>
        </div>
        <div class="wire-btn" style="border-style: dashed; padding: 6px 14px;">Modo Triage Rápido</div>
      </div>

      <table class="wire-table wire-table-dashed">
        <thead>
          <tr>
            <th>Productor</th>
            <th style="text-align: right;">Litros</th>
            <th>Estado prueba</th>
            <th>Destino operativo</th>
            <th>Triage inmediato</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Don Eloy Condori</strong><br><small>Código: PROV-01 · Markjo</small></td>
            <td style="text-align: right; font-size: 15px; font-weight: 700;">35.0 L</td>
            <td><span style="border: 1px solid #000; padding: 3px 8px; font-size: 11px; font-weight: 700;">Óptima</span></td>
            <td>Tina principal (queso maduro / yogur)</td>
            <td>
              <div class="wire-btn wire-btn-black" style="padding: 4px 10px; font-size: 11px;">✓ Aprobada</div>
            </td>
          </tr>
          <tr>
            <td><strong>Doña María Huamán</strong><br><small>Código: PROV-02 · Compone</small></td>
            <td style="text-align: right; font-size: 15px; font-weight: 700;">28.5 L</td>
            <td><span style="border: 1px solid #000; padding: 3px 8px; font-size: 11px; font-weight: 700;">Óptima</span></td>
            <td>Tina principal (queso maduro / yogur)</td>
            <td>
              <div class="wire-btn wire-btn-black" style="padding: 4px 10px; font-size: 11px;">✓ Aprobada</div>
            </td>
          </tr>
          <tr>
            <td><strong>Don Saturnino Choque</strong><br><small>Código: PROV-03 · Izcuchaca</small></td>
            <td style="text-align: right; font-size: 15px; font-weight: 700;">22.0 L</td>
            <td><span style="border: 1.5px dashed #000; padding: 3px 8px; font-size: 11px; font-weight: 700;">⚠ Con acidez</span></td>
            <td><strong>Tina quesillo 4 días</strong></td>
            <td>
              <div class="wire-btn" style="border-style: dashed; padding: 4px 10px; font-size: 11px; font-weight: 700;">Desviada a Quesillo</div>
            </td>
          </tr>
          <tr>
            <td><strong>Doña Gregoria Quispe</strong><br><small>Código: PROV-04 · Huayllacocha</small></td>
            <td style="text-align: right; font-size: 15px; font-weight: 700;">18.0 L</td>
            <td><span style="border: 1px solid #888; padding: 3px 8px; font-size: 11px;">Sin probar</span></td>
            <td>Pendiente de clasificación</td>
            <td>
              <div style="display: flex; gap: 6px;">
                <div class="wire-btn" style="padding: 4px 8px; font-size: 11px;">Aprobar</div>
                <div class="wire-btn" style="padding: 4px 8px; font-size: 11px; border-style: dashed;">Desviar</div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div style="margin-top: 18px; border: 1.5px dashed #000; padding: 14px 20px; display: grid; grid-template-columns: repeat(4, 1fr); text-align: center;">
        <div>
          <span style="font-size: 11px; font-weight: 700;">TOTAL EVALUADO</span>
          <div style="font-size: 20px; font-weight: 800;">103.5 L</div>
        </div>
        <div>
          <span style="font-size: 11px; font-weight: 700;">LECHE ÓPTIMA</span>
          <div style="font-size: 20px; font-weight: 800;">81.5 L</div>
        </div>
        <div>
          <span style="font-size: 11px; font-weight: 700;">DESVIADO A QUESILLO</span>
          <div style="font-size: 20px; font-weight: 800;">22.0 L</div>
        </div>
        <div>
          <span style="font-size: 11px; font-weight: 700;">DESPERDICIO O MERMA</span>
          <div style="font-size: 20px; font-weight: 800;">0.0 L (0%)</div>
        </div>
      </div>
    </div>

    <div class="wire-footer-tag">
      <span>LAT · PANEL OPERATIVO CON LÍNEAS DISCONTINUAS ("- - - - -")</span>
      <span>WIREFRAME 03 / 05 · TRIAGE SANITARIO CON CERO DESPERDICIO</span>
    </div>
  </div>
</body>
</html>
  `;
  await page.setContent(html3);
  const el3 = page.locator('.wire-canvas');
  for (const dir of OUT_DIRS) {
    await el3.screenshot({ path: path.join(dir, 'mockup_3_control_sanitario_wireframe.png') });
  }

  // ---------------------------------------------------------------------------
  // WIREFRAME 4: Panel Operativo · Módulo 3: Tina y Receta
  // ---------------------------------------------------------------------------
  console.log('Generando Wireframe 4: Monitoreo Térmico y Receta...');
  const html4 = `
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>${CSS_BASE}</style></head>
<body>
  <div class="wire-canvas">
    <div class="wire-top-meta">
      <div>LAT · WIREFRAME ESQUEMÁTICO EN BLANCO Y NEGRO (LÍNEAS DISCONTINUAS "- - - - -")</div>
      <div>FIG. 04 · PANEL OPERATIVO · MÓDULO 3: MONITOREO TÉRMICO Y RECETA</div>
    </div>

    <div class="wire-nav">
      <div style="display: flex; align-items: center; gap: 14px;">
        ${boxWithX('40px', '40px', 'LAT')}
        <strong>LAT · Gestión Láctea Rural</strong>
      </div>
      <div class="wire-nav-links">
        <span>Inicio</span>
        <span>Herramientas ⌄</span>
        <span>Empresas Familiares ⌄</span>
        <span>Nosotros</span>
      </div>
      <div style="display: flex; align-items: center; gap: 16px;">
        <div class="wire-btn" style="padding: 6px 12px;">🌐 ES ▾</div>
        <span style="font-size: 13px; font-weight: 700;">Cerrar Sesión</span>
        <div class="wire-btn wire-btn-black">Mi Panel</div>
      </div>
    </div>

    <div class="wire-tabs">
      <div class="wire-tab">1. Acopio por voz y tacto</div>
      <div class="wire-tab">2. Calidad sanitaria</div>
      <div class="wire-tab activo">3. Tina y receta</div>
      <div class="wire-tab">4. Liquidación quincenal</div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
      <div class="wire-card-dashed">
        <span style="font-size: 12px; font-weight: 700; text-transform: uppercase;">Monitoreo térmico</span>
        <h2 style="font-size: 24px; font-weight: 800; margin: 4px 0 8px;">Lote LOTE-20261102-01</h2>
        <p style="font-size: 13px; margin-bottom: 20px;">Curva de enfriamiento: pasteurización a 72 °C hasta el punto de cuajado (36–40 °C).</p>

        <div style="border: 2px solid #000; padding: 24px; text-align: center; margin-bottom: 20px; background: #fafafa;">
          <div style="font-size: 64px; font-weight: 300; line-height: 1;">40.0 <small style="font-size: 24px;">°C</small></div>
          <div style="height: 16px; border: 2px solid #000; margin: 16px 0; background: #fff; position: relative;">
            <div style="width: 100%; height: 100%; background: repeating-linear-gradient(45deg, #000, #000 4px, #fff 4px, #fff 8px);"></div>
          </div>
          <span style="border: 1.5px solid #000; background: #000; color: #fff; padding: 4px 12px; font-size: 11px; font-weight: 700;">
            ESTADO: LISTA PARA CUAJO · AVANCE 100%
          </span>
        </div>

        <div style="border-top: 1.5px dashed #000; padding-top: 16px;">
          <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px;">Simular o ajustar sensor térmico (°C)</label>
          <div style="display: flex; gap: 10px;">
            <div style="border: 2px solid #000; padding: 8px 12px; font-size: 20px; font-weight: 700; flex: 1;">40.0</div>
            <div class="wire-btn wire-btn-black" style="padding: 10px 16px;">Actualizar sensor</div>
          </div>
        </div>
      </div>

      <div class="wire-card-dashed">
        <span style="font-size: 12px; font-weight: 700; text-transform: uppercase;">Estandarización de calidad</span>
        <h2 style="font-size: 24px; font-weight: 800; margin: 4px 0 8px;">Dosificación de tina (105.5 L)</h2>
        <p style="font-size: 13px; margin-bottom: 20px;">Cálculo para mantener uniformidad en acidez, salinidad y firmeza del cuajo.</p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; border: 2px dashed #000; margin-bottom: 20px;">
          <div style="padding: 20px; border-right: 1.5px dashed #000; border-bottom: 1.5px dashed #000;">
            <span style="font-size: 12px; font-weight: 700;">Cuajo líquido</span>
            <div style="font-size: 32px; font-weight: 300; margin: 4px 0;">2.64 <small style="font-size: 16px;">ml</small></div>
            <span style="font-size: 11px; font-style: italic;">Ratio: 2.5 ml / 100 L</span>
          </div>

          <div style="padding: 20px; border-bottom: 1.5px dashed #000;">
            <span style="font-size: 12px; font-weight: 700;">Sal común</span>
            <div style="font-size: 32px; font-weight: 300; margin: 4px 0;">1.90 <small style="font-size: 16px;">kg</small></div>
            <span style="font-size: 11px; font-style: italic;">Ratio: 1.8 kg / 100 L</span>
          </div>

          <div style="padding: 20px; border-right: 1.5px dashed #000;">
            <span style="font-size: 12px; font-weight: 700;">Cultivo iniciador</span>
            <div style="font-size: 32px; font-weight: 300; margin: 4px 0;">1.06 <small style="font-size: 16px;">g</small></div>
            <span style="font-size: 11px; font-style: italic;">Ratio: 1.0 g / 100 L</span>
          </div>

          <div style="padding: 20px;">
            <span style="font-size: 12px; font-weight: 700;">Moldes estimados</span>
            <div style="font-size: 32px; font-weight: 700; margin: 4px 0;">11 <small style="font-size: 16px;">und</small></div>
            <span style="font-size: 11px; font-style: italic;">Rendimiento estimado x volumen</span>
          </div>
        </div>

        <div style="border: 1px solid #000; padding: 12px; font-size: 12px;">
          <strong>Nota de planta:</strong> Los insumos se calculan en base al volumen total en tina (105.5 L) registrado en recepción.
        </div>
      </div>
    </div>

    <div class="wire-footer-tag">
      <span>LAT · PANEL OPERATIVO CON LÍNEAS DISCONTINUAS ("- - - - -")</span>
      <span>WIREFRAME 04 / 05 · MONITOREO TÉRMICO Y DOSIFICACIÓN</span>
    </div>
  </div>
</body>
</html>
  `;
  await page.setContent(html4);
  const el4 = page.locator('.wire-canvas');
  for (const dir of OUT_DIRS) {
    await el4.screenshot({ path: path.join(dir, 'mockup_4_tina_receta_wireframe.png') });
  }

  // ---------------------------------------------------------------------------
  // WIREFRAME 5: Aplicación Móvil en Celular (2 Vistas Wireframe de Smartphone)
  // ---------------------------------------------------------------------------
  console.log('Generando Wireframe 5: Aplicación Móvil Smartphone...');
  const html5 = `
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>${CSS_BASE}</style></head>
<body>
  <div class="wire-canvas">
    <div class="wire-top-meta">
      <div>LAT · WIREFRAME ESQUEMÁTICO EN BLANCO Y NEGRO (SMARTPHONE DE TERRENO)</div>
      <div>FIG. 05 · APLICACIÓN MÓVIL EN CELULAR / TERRENO (2 VISTAS WIREFRAME)</div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; justify-items: center; margin-top: 10px;">
      <!-- Pantalla Móvil A: Portada con Drawer Desplegado -->
      <div style="width: 400px; border: 3px solid #000; border-radius: 28px; padding: 14px; background: #ffffff;">
        <div style="border: 2px solid #000; border-radius: 18px; padding: 16px; background: #ffffff; min-height: 800px; position: relative;">
          <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; margin-bottom: 12px; border-bottom: 1px solid #000; padding-bottom: 4px;">
            <span>09:41</span>
            <span style="border: 1px solid #000; border-radius: 8px; padding: 1px 8px; font-size: 9px;">DYNAMIC ISLAND</span>
            <span>LTE 100%</span>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; border: 2px solid #000; padding: 8px 12px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              ${boxWithX('28px', '28px', 'LAT')}
              <strong style="font-size: 15px;">LAT</strong>
            </div>
            <div style="display: flex; gap: 6px;">
              <div class="wire-btn" style="padding: 4px 8px; font-size: 11px;">🌐 ES ▾</div>
              <div class="wire-btn wire-btn-black" style="padding: 4px 8px; font-size: 11px;">✕ Menú</div>
            </div>
          </div>

          <div style="border: 2px solid #000; padding: 14px; margin-bottom: 16px; background: #fbfbfb;">
            <div style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px;">
              <div style="border: 1.5px solid #000; padding: 8px 12px; font-weight: 700; font-size: 13px; background: #e8e8e8;">Inicio</div>
              <div style="border: 1.5px solid #000; padding: 8px 12px; font-weight: 700; font-size: 13px;">Herramientas ⌄</div>
              <div style="border: 1.5px solid #000; padding: 8px 12px; font-weight: 700; font-size: 13px;">Empresas Familiares ⌄</div>
              <div style="border: 1.5px solid #000; padding: 8px 12px; font-weight: 700; font-size: 13px;">Nosotros</div>
            </div>

            <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">Idioma de la plataforma</label>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: 14px;">
              <div class="wire-btn wire-btn-black" style="padding: 6px; font-size: 11px; flex-direction: column;"><strong>ES</strong><span>Español</span></div>
              <div class="wire-btn" style="padding: 6px; font-size: 11px; flex-direction: column;"><strong>QU</strong><span>Runasimi</span></div>
              <div class="wire-btn" style="padding: 6px; font-size: 11px; flex-direction: column;"><strong>EN</strong><span>English</span></div>
            </div>

            <div class="wire-btn wire-btn-black" style="width: 100%; padding: 10px; font-size: 13px;">→ Iniciar Sesión</div>
          </div>

          <p style="font-size: 11px; text-align: center; font-weight: 700;">VISTA A: NAVEGACIÓN Y SELECTOR TRILINGÜE</p>
        </div>
      </div>

      <!-- Pantalla Móvil B: Panel Operativo de Acopio en Terreno -->
      <div style="width: 400px; border: 3px solid #000; border-radius: 28px; padding: 14px; background: #ffffff;">
        <div style="border: 2px solid #000; border-radius: 18px; padding: 16px; background: #ffffff; min-height: 800px; position: relative;">
          <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; margin-bottom: 12px; border-bottom: 1px solid #000; padding-bottom: 4px;">
            <span>09:41</span>
            <span style="border: 1px solid #000; border-radius: 8px; padding: 1px 8px; font-size: 9px;">DYNAMIC ISLAND</span>
            <span>LTE 100%</span>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; border: 2px solid #000; padding: 8px 12px; margin-bottom: 14px;">
            <strong style="font-size: 14px;">Planta San Borreal</strong>
            <div style="display: flex; gap: 6px;">
              <div class="wire-btn" style="padding: 4px 8px; font-size: 11px;">🌐 ES ▾</div>
              <div class="wire-btn" style="padding: 4px 8px; font-size: 11px;">☰</div>
            </div>
          </div>

          <div style="border: 2px dashed #000; padding: 10px 12px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
            <div>
              <small style="display: block; font-size: 10px; font-weight: 700; text-transform: uppercase;">Módulo Activo</small>
              <strong style="font-size: 13px;">1. Acopio por voz y tacto</strong>
            </div>
            <span>▾</span>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; border: 2px dashed #000; margin-bottom: 14px; text-align: center;">
            <div style="padding: 8px; border-right: 1.5px dashed #000;">
              <small style="font-size: 10px; font-weight: 700;">Leche hoy</small>
              <div style="font-size: 20px; font-weight: 800;">35.0 L</div>
            </div>
            <div style="padding: 8px;">
              <small style="font-size: 10px; font-weight: 700;">Tina</small>
              <div style="font-size: 20px; font-weight: 800;">40.0 °C</div>
            </div>
          </div>

          <div class="wire-btn wire-btn-black" style="width: 100%; min-height: 64px; font-size: 14px; margin-bottom: 12px;">
            🎙 DICTAR ACOPIO POR VOZ (64px)
          </div>

          <div style="border: 2px solid #000; padding: 8px 12px; font-size: 18px; font-weight: 700; text-align: center; margin-bottom: 12px;">
            Volumen: 35.0 L
          </div>

          <label style="display: block; font-size: 11px; font-weight: 700; margin-bottom: 4px;">Botonera rápida (54px)</label>
          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-bottom: 14px;">
            <div class="wire-btn" style="min-height: 52px; font-size: 13px; border-style: dashed;">+10</div>
            <div class="wire-btn" style="min-height: 52px; font-size: 13px; border-style: dashed;">+15</div>
            <div class="wire-btn" style="min-height: 52px; font-size: 13px; border-style: dashed;">+20</div>
            <div class="wire-btn" style="min-height: 52px; font-size: 13px; border-style: dashed;">+25</div>
            <div class="wire-btn" style="min-height: 52px; font-size: 13px; border-style: dashed;">+30</div>
            <div class="wire-btn wire-btn-black" style="min-height: 52px; font-size: 13px; border-style: dashed;">+35</div>
            <div class="wire-btn" style="min-height: 52px; font-size: 13px; border-style: dashed;">+40</div>
            <div class="wire-btn" style="min-height: 52px; font-size: 13px; border-style: dashed;">+50</div>
          </div>

          <div class="wire-btn wire-btn-black" style="width: 100%; min-height: 48px; font-size: 13px;">
            GUARDAR ENTREGA EN TINA
          </div>

          <p style="font-size: 11px; text-align: center; font-weight: 700; margin-top: 12px;">VISTA B: OPERACIÓN TÁCTIL EN RECEPCIÓN</p>
        </div>
      </div>
    </div>

    <div class="wire-footer-tag">
      <span>LAT · INTERFAZ MÓVIL OPTIMIZADA PARA MODO UNA MANO EN TERRENO</span>
      <span>WIREFRAME 05 / 05 · ESPECIFICACIÓN SMARTPHONE</span>
    </div>
  </div>
</body>
</html>
  `;
  await page.setContent(html5);
  const el5 = page.locator('.wire-canvas');
  for (const dir of OUT_DIRS) {
    await el5.screenshot({ path: path.join(dir, 'mockup_5_movil_wireframe.png') });
  }

  await browser.close();
  console.log('¡Los 5 wireframes perfeccionados fueron generados con éxito!');
})();
