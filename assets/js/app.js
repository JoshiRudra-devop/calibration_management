/* ============================================================
   SHREEJI INSTRUMENTS — Global JS Utilities
   ============================================================ */

// ── Loader ────────────────────────────────────────────────
const Loader = {
  overlay: null,
  spinner: null,
  tick: null,
  text: null,
  okBtn: null,

  init() {
    this.overlay = document.getElementById('loaderOverlay');
    this.spinner = document.getElementById('loaderSpinner');
    this.tick    = document.getElementById('loaderTick');
    this.text    = document.getElementById('loaderText');
    this.okBtn   = document.getElementById('loaderOkBtn');
    if (this.okBtn) this.okBtn.addEventListener('click', () => this.hide());
  },

  show(msg = 'Processing…') {
    if (!this.overlay) this.init();
    this.overlay?.classList.add('active');
    if (this.spinner) this.spinner.style.display = 'block';
    if (this.tick)    this.tick.style.display    = 'none';
    if (this.text)    this.text.textContent      = msg;
  },

  success(msg = 'Done! ✨') {
    if (this.spinner) this.spinner.style.display = 'none';
    if (this.tick)  {
      this.tick.style.display = 'flex';
      // replay animation
      const svg = this.tick.querySelector('svg');
      if (svg) {
        ['circle','polyline'].forEach(sel => {
          const el = svg.querySelector(sel);
          if (el) { el.style.animation = 'none'; void svg.offsetWidth; el.style.animation = ''; }
        });
      }
    }
    if (this.text)  this.text.textContent = msg;
  },

  error(msg = 'Something went wrong 😞') {
    if (this.spinner) this.spinner.style.display = 'none';
    if (this.text)    this.text.textContent = msg;
    setTimeout(() => this.hide(), 2500);
  },

  hide() { this.overlay?.classList.remove('active'); }
};

// ── Toast notifications ───────────────────────────────────
const Toast = {
  _container: null,

  _getContainer() {
    if (!this._container) {
      this._container = document.getElementById('toast-container');
      if (!this._container) {
        this._container = document.createElement('div');
        this._container.id = 'toast-container';
        document.body.appendChild(this._container);
      }
    }
    return this._container;
  },

  show(message, type = 'info', duration = 3500) {
    const icons = { success: '✓', error: '✕', warn: '⚠', info: 'ℹ' };
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.innerHTML = `<span class="toast-icon">${icons[type] ?? icons.info}</span><span>${message}</span>`;
    this._getContainer().appendChild(el);
    requestAnimationFrame(() => { requestAnimationFrame(() => el.classList.add('show')); });
    setTimeout(() => {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 350);
    }, duration);
  },

  success(msg, dur) { this.show(msg, 'success', dur); },
  error(msg, dur)   { this.show(msg, 'error',   dur ?? 5000); },
  warn(msg, dur)    { this.show(msg, 'warn',     dur); },
  info(msg, dur)    { this.show(msg, 'info',     dur); },
};

// ── QR Code for PDF certificate verification stamps ───────
function generateQRDataURLSync(text, pixelSize = 128) {
  if (typeof QRCode === 'undefined') return null;
  const container = document.createElement('div');
  container.style.cssText = 'position:absolute;left:-9999px;visibility:hidden;';
  document.body.appendChild(container);
  try {
    new QRCode(container, {
      text,
      width: pixelSize,
      height: pixelSize,
      colorDark: '#000000',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M,
    });
    const canvas = container.querySelector('canvas');
    return canvas ? canvas.toDataURL('image/png') : null;
  } finally {
    document.body.removeChild(container);
  }
}

// Build the Cloudinary delivery URL for a certificate PDF.
// Mirrors PHP sanitizeCloudinarySegment() + folder logic in save_certificates.php.
function buildCloudinaryPdfUrl(certNumber, instrSlug, partyName, siteLocation) {
  function _seg(s) {
    return ((s || '')
      .replace(/[\\:*?"<>|]/g, '_')
      .replace(/\s+/g, ' ')
      .replace(/^[\s.]+|[\s.]+$/g, '')
      .trim()) || 'Unknown';
  }
  const cloudName = (typeof SHREEJI_CONFIG !== 'undefined' && SHREEJI_CONFIG.cloudName)
    ? SHREEJI_CONFIG.cloudName
    : 'dqlp56p7n';
  // INSTRUMENT_LABELS is defined in offline_storage.js (loaded on certificate pages)
  const label = (typeof INSTRUMENT_LABELS !== 'undefined' && INSTRUMENT_LABELS[instrSlug])
    ? INSTRUMENT_LABELS[instrSlug]
    : (instrSlug || 'Other');
  const parts = ['shreeji_certificates', _seg(label), _seg(partyName)];
  if (siteLocation && siteLocation.trim()) parts.push(_seg(siteLocation.trim()));
  parts.push(certNumber); // public_id has no .pdf extension
  const path = parts.map(p => encodeURIComponent(p)).join('/');
  return `https://res.cloudinary.com/${cloudName}/image/upload/${path}.pdf`;
}

function addQRCodeToPDF(doc, certNumber) {
  if (!certNumber) return;
  const instrSlug  = (typeof INSTRUMENT_SLUG !== 'undefined') ? INSTRUMENT_SLUG : null;
  const partyName  = (document.getElementById('partyName')  || document.getElementById('partyname'))?.value  || '';
  const siteLoc    = document.getElementById('siteLocation')?.value || '';
  const url        = buildCloudinaryPdfUrl(certNumber, instrSlug, partyName, siteLoc);
  const qrDataUrl  = generateQRDataURLSync(url, 128);
  if (qrDataUrl) {
    // Left of stamp — empty zone x=5–95, y=217–252 (stamp at x=100,y=217,35×35; footer strip at y=255)
    // QR vertically centred with stamp: stamp centre=234.5mm → y=234.5-12.5=222
    doc.addImage(qrDataUrl, 'PNG', 8, 222, 25, 25);
  }
}

// ── Unsaved reminder ──────────────────────────────────────
const UnsavedTracker = {
  saved: false,
  banner: null,

  init(formId, bannerId) {
    this.banner = document.getElementById(bannerId);
    const form  = document.getElementById(formId);
    if (!form) return;

    form.querySelectorAll('input,select,textarea').forEach(el => {
      el.addEventListener('input', () => { this.saved = false; this.update(); });
    });

    window.addEventListener('beforeunload', e => {
      if (!this.saved) {
        e.preventDefault();
        e.returnValue = 'Unsaved changes — save your certificate first!';
      }
    });

    this.update();
  },

  markSaved() { this.saved = true; this.update(); },

  update() {
    if (!this.banner) return;
    this.banner.style.display = this.saved ? 'none' : 'block';
  }
};

// ── Date helpers ──────────────────────────────────────────
function calcNextDate(fromId = 'calibrationDate', toId = 'nextCalibrationDate') {
  const from = document.getElementById(fromId);
  const to   = document.getElementById(toId);
  if (!from?.value) return;
  const d = new Date(from.value);
  d.setFullYear(d.getFullYear() + 1);
  d.setDate(d.getDate() - 1);
  to.value = d.toISOString().split('T')[0];
}

function setTodayDate(id = 'calibrationDate') {
  const el = document.getElementById(id);
  if (el && !el.value) el.value = new Date().toISOString().split('T')[0];
}

// ── PDF save helper ───────────────────────────────────────
async function savePDFBlob(blob, filename) {
  if (window.showSaveFilePicker && !/Mobi|Android/i.test(navigator.userAgent)) {
    try {
      const handle = await window.showSaveFilePicker({
        suggestedName: filename,
        types: [{ description: 'PDF', accept: { 'application/pdf': ['.pdf'] } }],
      });
      const writable = await handle.createWritable();
      await writable.write(blob);
      await writable.close();
      return true;
    } catch (e) {
      if (e.name === 'AbortError') return false;
    }
  }
  // Fallback
  const url = URL.createObjectURL(blob);
  const a   = Object.assign(document.createElement('a'), { href: url, download: filename });
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  setTimeout(() => URL.revokeObjectURL(url), 300);
  return true;
}

// ── Cloudinary upload from JS ─────────────────────────────
async function uploadToCloudinary(pdfBlob, certNumber) {
  const formData = new FormData();
  formData.append('file', pdfBlob, `${certNumber}.pdf`);
  formData.append('upload_preset', SHREEJI_CONFIG?.cloudinaryPreset || 'shreeji_instruments');
  formData.append('folder', 'shreeji_certificates');

  const cloudName = SHREEJI_CONFIG?.cloudName || 'YOUR_CLOUD_NAME';
  const res = await fetch(`https://api.cloudinary.com/v1_1/${cloudName}/auto/upload`, {
    method: 'POST',
    body: formData,
  });

  if (!res.ok) throw new Error('Cloudinary upload failed');
  return await res.json();
}

// ── Save cert to DB via API ───────────────────────────────
async function saveCertificateToDB(payload, pdfBlob, certNumber) {
  Loader.show('Saving certificate…');

  try {
    // 1. Convert PDF to base64
    if (pdfBlob) {
      const reader = new FileReader();
      const b64 = await new Promise(res => {
        reader.onload = () => res(reader.result);
        reader.readAsDataURL(pdfBlob);
      });
      payload.pdf_base64 = b64;
    }

    const res = await fetch(SHREEJI_CONFIG.apiBase + '/save_certificates.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': SHREEJI_CONFIG.csrfToken },
      body: JSON.stringify({ ...payload, csrf_token: SHREEJI_CONFIG.csrfToken }),
    });

    const data = await res.json();

    if (data.success) {
      UnsavedTracker.markSaved();
      Loader.success(`Saved as ${data.cert_number} ✨`);
      if (data.pdf_url) {
        console.log('PDF on Cloudinary:', data.pdf_url);
      }
      return data;
    } else {
      Loader.error(data.message || 'Save failed');
      return null;
    }
  } catch (err) {
    Loader.error('Network error: ' + err.message);
    return null;
  }
}

// ── Dock / hamburger toggle ───────────────────────────────
function toggleDock() {
  document.getElementById('sideDock')?.classList.toggle('open');
}

function toggleNav() {
  document.querySelector('.navbar__nav')?.classList.toggle('open');
}

// ── Search filter for cards grid ─────────────────────────
function initCardSearch(inputId = 'cardSearch', gridId = 'cardsGrid') {
  const input = document.getElementById(inputId);
  const grid  = document.getElementById(gridId);
  if (!input || !grid) return;

  input.addEventListener('input', () => {
    const q = input.value.toLowerCase().trim();
    grid.querySelectorAll('.card').forEach(card => {
      card.classList.toggle('hidden', q && !card.textContent.toLowerCase().includes(q));
    });
  });
}

// ── Image → base64 ───────────────────────────────────────
function imgToB64(url) {
  return new Promise(resolve => {
    const img = new Image();
    img.crossOrigin = 'Anonymous';
    img.onload = () => {
      const c = document.createElement('canvas');
      c.width = img.naturalWidth; c.height = img.naturalHeight;
      c.getContext('2d').drawImage(img, 0, 0);
      resolve(c.toDataURL('image/png'));
    };
    img.onerror = () => resolve(null);
    img.src = url;
  });
}

// Preload images once
const ImgCache = {};
async function getImg(url) {
  if (!ImgCache[url]) ImgCache[url] = await imgToB64(url);
  return ImgCache[url];
}

async function addLetterhead(doc) {
  try {
    const header = await getImg('assets/images/header.jpeg');
    const footer = await getImg('assets/images/footer.jpeg');
    const stamp  = await getImg('assets/images/stamp.jpeg');
    const sign   = await getImg('assets/images/sign.jpeg');

    doc.addImage(header, 'JPEG', 3, 3, 210, 30);
    doc.addImage(footer, 'JPEG', 0, 255, 210, 27);
    doc.addImage(stamp,  'JPEG', 100, 217, 35, 35);
    doc.addImage(sign,   'JPEG', 160, 232, 40, 10);
  } catch (e) {
    console.warn('Letterhead images not loaded', e);
  }
}

// ── Init on DOM ready ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  Loader.init();
  setTodayDate('calibrationDate');
  const dateEl = document.getElementById('calibrationDate');
  if (dateEl) {
    calcNextDate();
    dateEl.addEventListener('change', () => calcNextDate());
  }
  initCardSearch();
});