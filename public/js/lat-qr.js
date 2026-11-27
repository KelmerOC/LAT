/**
 * Generador nativo de Código QR (ISO/IEC 18004, modo Byte, ECC Level L/M) en SVG puro.
 * Sin dependencias externas ni peticiones a terceros; apto para entorno local u offline.
 */
(function (global) {
  'use strict';

  const GF_EXP = new Uint8Array(512);
  const GF_LOG = new Uint8Array(256);
  (function initGF() {
    let x = 1;
    for (let i = 0; i < 255; i++) {
      GF_EXP[i] = x;
      GF_LOG[x] = i;
      x <<= 1;
      if (x & 0x100) x ^= 0x11d;
    }
    for (let i = 255; i < 512; i++) GF_EXP[i] = GF_EXP[i - 255];
  })();

  function gfMul(a, b) {
    if (a === 0 || b === 0) return 0;
    return GF_EXP[GF_LOG[a] + GF_LOG[b]];
  }

  function rsGeneratorPoly(deg) {
    let poly = [1];
    for (let i = 0; i < deg; i++) {
      const next = new Array(poly.length + 1).fill(0);
      const factor = GF_EXP[i];
      for (let j = 0; j < poly.length; j++) {
        next[j] ^= poly[j];
        next[j + 1] ^= gfMul(poly[j], factor);
      }
      poly = next;
    }
    return poly;
  }

  function rsEncode(data, ecCount) {
    const gen = rsGeneratorPoly(ecCount);
    const res = new Uint8Array(data.length + ecCount);
    res.set(data, 0);
    for (let i = 0; i < data.length; i++) {
      const coef = res[i];
      if (coef !== 0) {
        for (let j = 0; j < gen.length; j++) {
          res[i + j] ^= gfMul(gen[j], coef);
        }
      }
    }
    return Array.from(res.slice(data.length));
  }

  // Versiones 1..10 (Level L: soporta hasta 271 bytes en v10; single-block hasta v2, multi-block v3+)
  // Tabla: [version, totalCodewords, ecPerBlock, numBlocksGroup1, dataPerBlockG1, numBlocksG2, dataPerBlockG2, alignCenters]
  const VERSIONS_L = [
    null,
    [1, 26, 7, 1, 19, 0, 0, []],
    [2, 44, 10, 1, 34, 0, 0, [6, 18]],
    [3, 70, 15, 1, 55, 0, 0, [6, 22]],
    [4, 100, 20, 1, 80, 0, 0, [6, 26]],
    [5, 134, 26, 1, 108, 0, 0, [6, 30]],
    [6, 172, 18, 2, 68, 0, 0, [6, 34]],
    [7, 196, 20, 2, 78, 0, 0, [6, 22, 38]],
    [8, 242, 24, 2, 97, 0, 0, [6, 24, 42]],
    [9, 292, 30, 2, 116, 0, 0, [6, 26, 46]],
    [10, 346, 18, 2, 68, 2, 69, [6, 28, 50]]
  ];

  function chooseVersion(byteLen) {
    for (let v = 1; v <= 10; v++) {
      const spec = VERSIONS_L[v];
      const cap = spec[3] * spec[4] + spec[5] * spec[6];
      const headerBits = 4 + (v <= 9 ? 8 : 16);
      const maxBytes = Math.floor((cap * 8 - headerBits) / 8);
      if (byteLen <= maxBytes) return v;
    }
    return 10;
  }

  function encodeData(text) {
    const bytes = Array.from(new TextEncoder().encode(text));
    const version = chooseVersion(bytes.length);
    const spec = VERSIONS_L[version];
    const dataCap = spec[3] * spec[4] + spec[5] * spec[6];

    const bits = [];
    const pushBits = (val, len) => {
      for (let i = len - 1; i >= 0; i--) bits.push((val >>> i) & 1);
    };

    pushBits(0b0100, 4); // Byte mode
    pushBits(bytes.length, version <= 9 ? 8 : 16);
    bytes.forEach((b) => pushBits(b, 8));

    const totalDataBits = dataCap * 8;
    const termLen = Math.min(4, totalDataBits - bits.length);
    pushBits(0, termLen);
    while (bits.length % 8 !== 0) bits.push(0);

    const padBytes = [0xec, 0x11];
    let padIdx = 0;
    while (bits.length < totalDataBits) {
      pushBits(padBytes[padIdx % 2], 8);
      padIdx++;
    }

    const dataCodewords = [];
    for (let i = 0; i < bits.length; i += 8) {
      let byte = 0;
      for (let b = 0; b < 8; b++) byte = (byte << 1) | bits[i + b];
      dataCodewords.push(byte);
    }

    const ecPerBlock = spec[2];
    const g1Blocks = spec[3];
    const g1Len = spec[4];
    const g2Blocks = spec[5];
    const g2Len = spec[6];

    const dataBlocks = [];
    const ecBlocks = [];
    let offset = 0;
    for (let i = 0; i < g1Blocks; i++) {
      const blk = dataCodewords.slice(offset, offset + g1Len);
      offset += g1Len;
      dataBlocks.push(blk);
      ecBlocks.push(rsEncode(blk, ecPerBlock));
    }
    for (let i = 0; i < g2Blocks; i++) {
      const blk = dataCodewords.slice(offset, offset + g2Len);
      offset += g2Len;
      dataBlocks.push(blk);
      ecBlocks.push(rsEncode(blk, ecPerBlock));
    }

    const interleaved = [];
    const maxDataLen = Math.max(g1Len, g2Len);
    for (let i = 0; i < maxDataLen; i++) {
      for (let b = 0; b < dataBlocks.length; b++) {
        if (i < dataBlocks[b].length) interleaved.push(dataBlocks[b][i]);
      }
    }
    for (let i = 0; i < ecPerBlock; i++) {
      for (let b = 0; b < ecBlocks.length; b++) {
        interleaved.push(ecBlocks[b][i]);
      }
    }

    return { version, spec, codewords: interleaved };
  }

  function createMatrix(version, alignCenters) {
    const size = version * 4 + 17;
    const mat = Array.from({ length: size }, () => new Int8Array(size));
    const isFunc = Array.from({ length: size }, () => new Uint8Array(size));

    function setMod(r, c, dark) {
      if (r >= 0 && r < size && c >= 0 && c < size) {
        mat[r][c] = dark ? 1 : 0;
        isFunc[r][c] = 1;
      }
    }

    function drawFinder(r0, c0) {
      for (let dr = -1; dr <= 7; dr++) {
        for (let dc = -1; dc <= 7; dc++) {
          const r = r0 + dr;
          const c = c0 + dc;
          if (r < 0 || r >= size || c < 0 || c >= size) continue;
          const on = (dr >= 0 && dr <= 6 && dc >= 0 && dc <= 6) &&
            (dr === 0 || dr === 6 || dc === 0 || dc === 6 || (dr >= 2 && dr <= 4 && dc >= 2 && dc <= 4));
          setMod(r, c, on);
        }
      }
    }

    function drawAlignment(r0, c0) {
      for (let dr = -2; dr <= 2; dr++) {
        for (let dc = -2; dc <= 2; dc++) {
          const on = Math.abs(dr) === 2 || Math.abs(dc) === 2 || (dr === 0 && dc === 0);
          setMod(r0 + dr, c0 + dc, on);
        }
      }
    }

    drawFinder(0, 0);
    drawFinder(0, size - 7);
    drawFinder(size - 7, 0);

    for (let i = 8; i < size - 8; i++) {
      setMod(6, i, i % 2 === 0);
      setMod(i, 6, i % 2 === 0);
    }

    if (alignCenters.length > 0) {
      for (let i = 0; i < alignCenters.length; i++) {
        for (let j = 0; j < alignCenters.length; j++) {
          const r = alignCenters[i];
          const c = alignCenters[j];
          if (isFunc[r][c]) continue;
          drawAlignment(r, c);
        }
      }
    }

    setMod(size - 8, 8, true); // Dark module

    // Reservar formato y versión (v>=7)
    for (let i = 0; i < 9; i++) {
      if (i !== 6) {
        isFunc[8][i] = 1;
        isFunc[i][8] = 1;
      }
    }
    for (let i = 0; i < 8; i++) {
      isFunc[8][size - 1 - i] = 1;
      if (i < 7) isFunc[size - 1 - i][8] = 1;
    }
    if (version >= 7) {
      const verBits = calcVersionInfo(version);
      for (let i = 0; i < 18; i++) {
        const bit = (verBits >>> i) & 1;
        const a = Math.floor(i / 3);
        const b = size - 11 + (i % 3);
        setMod(a, b, bit === 1);
        setMod(b, a, bit === 1);
      }
    }

    return { size, mat, isFunc };
  }

  function calcVersionInfo(version) {
    let rem = version;
    for (let i = 0; i < 12; i++) rem = (rem << 1) ^ ((rem >>> 11) * 0x1f25);
    return (version << 12) | rem;
  }

  function calcFormatBits(mask) {
    // Error correction L = 01
    const data = (1 << 3) | mask;
    let rem = data;
    for (let i = 0; i < 10; i++) rem = (rem << 1) ^ ((rem >>> 9) * 0x537);
    return ((data << 10) | rem) ^ 0x5412;
  }

  function applyFormat(mat, size, mask) {
    const bits = calcFormatBits(mask);
    for (let i = 0; i < 15; i++) {
      const dark = ((bits >>> i) & 1) === 1 ? 1 : 0;
      if (i < 6) mat[i][8] = dark;
      else if (i < 8) mat[i + 1][8] = dark;
      else mat[size - 15 + i][8] = dark;

      if (i < 8) mat[8][size - 1 - i] = dark;
      else if (i < 9) mat[8][7] = dark;
      else mat[8][14 - i] = dark;
    }
  }

  function placeData(mat, isFunc, size, codewords) {
    const bitStream = [];
    codewords.forEach((cw) => {
      for (let b = 7; b >= 0; b--) bitStream.push((cw >>> b) & 1);
    });

    let bitIdx = 0;
    let up = true;
    for (let right = size - 1; right >= 1; right -= 2) {
      if (right === 6) right = 5;
      for (let step = 0; step < size; step++) {
        const r = up ? size - 1 - step : step;
        for (let k = 0; k < 2; k++) {
          const c = right - k;
          if (!isFunc[r][c]) {
            mat[r][c] = bitIdx < bitStream.length ? bitStream[bitIdx++] : 0;
          }
        }
      }
      up = !up;
    }
  }

  function maskFn(mask, r, c) {
    switch (mask) {
      case 0: return (r + c) % 2 === 0;
      case 1: return r % 2 === 0;
      case 2: return c % 3 === 0;
      case 3: return (r + c) % 3 === 0;
      case 4: return (Math.floor(r / 2) + Math.floor(c / 3)) % 2 === 0;
      case 5: return ((r * c) % 2 + (r * c) % 3) === 0;
      case 6: return (((r * c) % 2) + ((r * c) % 3)) % 2 === 0;
      case 7: return (((r + c) % 2) + ((r * c) % 3)) % 2 === 0;
      default: return false;
    }
  }

  function buildQrMatrix(text) {
    const { version, spec, codewords } = encodeData(text);
    const { size, mat, isFunc } = createMatrix(version, spec[7]);
    placeData(mat, isFunc, size, codewords);

    let bestMask = 0;
    let bestScore = Infinity;
    let bestMat = null;

    for (let m = 0; m < 8; m++) {
      const candidate = mat.map((row) => Int8Array.from(row));
      for (let r = 0; r < size; r++) {
        for (let c = 0; c < size; c++) {
          if (!isFunc[r][c] && maskFn(m, r, c)) {
            candidate[r][c] ^= 1;
          }
        }
      }
      applyFormat(candidate, size, m);

      // Penalización rápida por corridas y balance 50/50
      let darkCount = 0;
      let penalty = 0;
      for (let r = 0; r < size; r++) {
        let run = 1;
        for (let c = 0; c < size; c++) {
          darkCount += candidate[r][c];
          if (c > 0 && candidate[r][c] === candidate[r][c - 1]) {
            run++;
            if (run === 5) penalty += 3;
            else if (run > 5) penalty += 1;
          } else {
            run = 1;
          }
        }
      }
      const pct = Math.abs((darkCount * 100) / (size * size) - 50);
      penalty += Math.floor(pct / 5) * 10;

      if (penalty < bestScore) {
        bestScore = penalty;
        bestMask = m;
        bestMat = candidate;
      }
    }

    return { size, matrix: bestMat, mask: bestMask };
  }

  function renderSvgString(text, pxSize) {
    const { size, matrix } = buildQrMatrix(text);
    const quiet = 4;
    const total = size + quiet * 2;
    let d = '';
    for (let r = 0; r < size; r++) {
      for (let c = 0; c < size; c++) {
        if (matrix[r][c]) {
          d += `M${c + quiet},${r + quiet}h1v1h-1z`;
        }
      }
    }
    const dim = pxSize || 220;
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${total} ${total}" width="${dim}" height="${dim}" shape-rendering="crispEdges" role="img" aria-label="Código QR de acceso móvil"><rect width="${total}" height="${total}" fill="#ffffff"/><path d="${d}" fill="#0a2f4d"/></svg>`;
  }

  global.LAT_QR = {
    renderTo(container, text, pxSize) {
      if (!container) return;
      container.innerHTML = renderSvgString(text, pxSize || 220);
    },
    toSvgString: renderSvgString
  };
})(window);
