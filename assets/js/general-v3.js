/* SHREEJI INSTRUMENTS - General Certificate Functions */

// ── Unified letterhead applicator ────────────────────────────
// Uses getImg() from app.js — Promise-based canvas loader, cached after first call.
// Works reliably for all 26 certificates on all browsers.
// Applies header/footer/stamp/sign to every page of the document.
async function applyLetterhead(doc) {
  const BASE = '../assets/images';
  const [header, footer, stamp, sign] = await Promise.all([
    getImg(BASE + '/header.jpeg'),
    getImg(BASE + '/footer.jpeg'),
    getImg(BASE + '/stamp.jpeg'),
    getImg(BASE + '/sign.jpeg'),
  ]);
  const pageCount = doc.internal.getNumberOfPages();
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i);
    if (header) doc.addImage(header, 'JPEG', 3,   3,   210, 30, undefined, 'FAST');
    if (footer) doc.addImage(footer, 'JPEG', 0,   255, 210, 27, undefined, 'FAST');
    if (stamp)  doc.addImage(stamp,  'JPEG', 100, 217, 35,  35, undefined, 'FAST');
    if (sign)   doc.addImage(sign,   'JPEG', 160, 232, 40,  10, undefined, 'FAST');
  }
}

if (typeof window.addQRCodeToPDF !== 'function') {
  window.addQRCodeToPDF = function(doc, certNumber) {
    if (typeof generateQRDataURLSync === 'function' && typeof QRCode !== 'undefined') {
      const baseUrl = (typeof SHREEJI_CONFIG !== 'undefined' && SHREEJI_CONFIG.appUrl) ? SHREEJI_CONFIG.appUrl : window.location.origin;
      const verifyUrl = `${baseUrl}/verify.php?cert=${encodeURIComponent(certNumber)}`;
      const qrDataUrl = generateQRDataURLSync(verifyUrl, 128);
      if (qrDataUrl) {
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
          doc.setPage(i);
          try {
            doc.addImage(qrDataUrl, 'PNG', 8, 222, 25, 25, undefined, 'FAST');
          } catch (e) {}
        }
      }
    }
  };
}

function safeGetFormDetails() {
  if (typeof window.getFormDetails === 'function') {
    try {
      const res = window.getFormDetails();
      if (res && typeof res === 'object') return res;
    } catch (e) {
      if (window.SHREEJI_DEBUG) console.warn('window.getFormDetails error:', e);
    }
  }
  if (typeof getFormDetails === 'function') {
    try {
      const res = getFormDetails();
      if (res && typeof res === 'object') return res;
    } catch (e) {
      if (window.SHREEJI_DEBUG) console.warn('getFormDetails error:', e);
    }
  }
  if (window.parent && typeof window.parent.getFormDetails === 'function') {
    try {
      const res = window.parent.getFormDetails();
      if (res && typeof res === 'object') return res;
    } catch (e) {
      if (window.SHREEJI_DEBUG) console.warn('parent.getFormDetails error:', e);
    }
  }

  // Universal Fallback: Read directly from standard DOM form elements
  const getVal = (id) => {
    const el = document.getElementById(id);
    return el ? el.value : "";
  };
  const formatDate = (val) => {
    if (!val) return "";
    if (val.includes("-")) return val.split("-").reverse().join("/");
    return val;
  };

  const certNo = getVal("certificateNumber") || getVal("cert_number");
  const partyName = getVal("partyName") || getVal("partyname");
  const calDate = formatDate(getVal("calibrationDate"));
  const nextDate = formatDate(getVal("nextCalibrationDate"));
  const siteLoc = getVal("siteLocation");
  const qty = getVal("quantity");
  const size = getVal("size");

  return {
    certificateNumber: certNo,
    calibrationDate: calDate,
    nextCalibrationDate: nextDate,
    partyName: partyName,
    siteLocation: siteLoc,
    quantity: qty,
    size: size,
    make: getVal("make"),
    modelNo: getVal("modelNo") || getVal("model_no"),
    serialNo: getVal("serialNo") || getVal("serial_no"),
    capacity: getVal("capacity"),
    selectedSubSizes: getVal("selectedSubSizes"),
    saveentry: `${window.INSTRUMENT_SLUG || 'Cert'}_${partyName}_${certNo}`
  };
}

function safeAddCertificateDetails(doc, details) {
  let success = false;
  if (typeof window.addCertificateDetails === 'function') {
    try {
      window.addCertificateDetails(doc, details);
      success = true;
    } catch (e) {
      console.error("Error executing window.addCertificateDetails:", e);
    }
  }
  if (!success && typeof addCertificateDetails === 'function') {
    try {
      addCertificateDetails(doc, details);
      success = true;
    } catch (e) {
      console.error("Error executing addCertificateDetails:", e);
    }
  }
  if (!success && window.parent && typeof window.parent.addCertificateDetails === 'function') {
    try {
      window.parent.addCertificateDetails(doc, details);
      success = true;
    } catch (e) {
      console.error("Error executing window.parent.addCertificateDetails:", e);
    }
  }
  if (!success) {
    console.warn("No custom addCertificateDetails executed. Running universal fallback drawer.");
    drawFallbackCertificateContent(doc, details);
  }
}

function drawFallbackCertificateContent(doc, details) {
  details = details || safeGetFormDetails();
  let qty = parseInt(details.quantity);
  if (isNaN(qty) || qty <= 0) qty = 1;

  let sizeStr = details.size || "";
  let [length, height, width] = sizeStr.includes("x") ? sizeStr.split("x").map(s => s.trim()) : [sizeStr, "", ""];
  const headers = [["SR.NO", "LENGTH", "HEIGHT", "WIDTH"]];
  const allRows = [];
  for (let i = 1; i <= qty; i++) {
    allRows.push([i, length || "", height || "", width || ""]);
  }

  const rawSlug = window.INSTRUMENT_SLUG || "CUBE_MOULD";
  const titleText = "TEST REPORT FOR " + rawSlug.replace(/_/g, ' ').toUpperCase();
  const pageCount = Math.ceil(allRows.length / 10);

  function incrementCertNo(baseCertNo, inc) {
    if (inc === 0) return baseCertNo;
    let val = baseCertNo;
    for (let i = 0; i < inc; i++) {
      const match = val.match(/^(.*?)(\d+)$/);
      if (match) {
        const prefix = match[1];
        const numStr = match[2];
        const nextNum = parseInt(numStr, 10) + 1;
        const paddedNum = String(nextNum).padStart(numStr.length, '0');
        val = prefix + paddedNum;
      } else break;
    }
    return val;
  }

  for (let page = 0; page < pageCount; page++) {
    if (page > 0) doc.addPage();
    let Yalign = 50;
    let pageRows = allRows.slice(page * 10, page * 10 + 10);
    let pageRefNo = incrementCertNo(details.certificateNumber || "", page);

    doc.setFont("helvetica", "bold");
    doc.setFontSize(22);
    doc.text(titleText, doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
    
    if (details.size) {
      Yalign += 7;
      doc.text(`${details.size}`, doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
    }

    doc.setFontSize(12);
    Yalign += 10;
    doc.text(`DATE:-${details.calibrationDate || ''}`, 155, Yalign);
    doc.text(`REF NO                        :-     ${pageRefNo}`, 14, Yalign);
    Yalign += 10;
    doc.text(`NAME OF PARTY        :-     ${details.partyName || ''}`, 14, Yalign);
    Yalign += 10;
    doc.text(`EQUIPMENT NAME     :-     ${rawSlug.replace(/_/g, ' ').toUpperCase()} (${details.size || ''})`, 14, Yalign);
    Yalign += 10;
    doc.text(`NEXT DUE DATE        :-     ${details.nextCalibrationDate || ''}`, 14, Yalign);

    const siteLocPrefix = "SITE LOCATION          :-     ";
    const prefixWidth = doc.getTextWidth(siteLocPrefix);
    const maxWidth = 180 - prefixWidth;
    const siteLocStr = details.siteLocation || "";
    const siteLocLines = doc.splitTextToSize(siteLocStr, maxWidth);
    Yalign += 10;
    doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign);
    for (let i = 1; i < siteLocLines.length; i++) {
      Yalign += 4;
      doc.text(siteLocLines[i], 14 + prefixWidth, Yalign);
    }
    Yalign += ((siteLocLines.length - 1) + 5);

    if (typeof doc.autoTable === 'function') {
      try {
        doc.autoTable({
          head: headers,
          body: pageRows,
          startY: Yalign + 2,
          styles: { fontSize: 12, lineColor: [0, 0, 0], textColor: [0, 0, 0], lineWidth: 0.2, halign: 'center' },
          headStyles: { fontSize: 15, fillColor: [255, 255, 255], textColor: [0, 0, 0], lineColor: [0, 0, 0], lineWidth: 0.2 }
        });
        Yalign = (doc.autoTable.previous && typeof doc.autoTable.previous.finalY === 'number') ? doc.autoTable.previous.finalY : Yalign + 40;
      } catch (e) {
        Yalign += 40;
      }
    } else {
      let curY = Yalign + 5;
      doc.setFontSize(14);
      doc.rect(14, curY, 180, 8);
      doc.text("SR.NO", 20, curY + 6);
      doc.text("LENGTH", 60, curY + 6);
      doc.text("HEIGHT", 110, curY + 6);
      doc.text("WIDTH", 160, curY + 6);
      curY += 8;
      doc.setFontSize(12);
      for (let r of pageRows) {
        doc.rect(14, curY, 180, 8);
        doc.text(String(r[0]), 20, curY + 6);
        doc.text(String(r[1]), 60, curY + 6);
        doc.text(String(r[2]), 110, curY + 6);
        doc.text(String(r[3]), 160, curY + 6);
        curY += 8;
      }
      Yalign = curY;
    }

    doc.setFontSize(12);
    doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, Yalign + 10);
    doc.setFont("helvetica", "bold");
    doc.setFontSize(12);
    doc.text("FOR, " + (window.PDF_COMPANY_NAME || "SHREEJI INSTRUMENTS"), 145, 230);
    doc.text("PROPRIETOR", 170, 245);
  }
}

const CERTIFICATE_CONFIG = {
  apiBase: typeof SHREEJI_CONFIG !== 'undefined' ? SHREEJI_CONFIG.apiBase : '/api',
  certificatePath: typeof SHREEJI_CONFIG !== 'undefined' ? SHREEJI_CONFIG.appUrl + '/certificates' : '/certificates',
};

let formModified = false;
let pdfSaved = false;
window.isPdfSaved = function() {
  return pdfSaved;
};

// Enable/disable the buttons that require a saved certificate.
// Called on page load, after every form edit, and after every successful save.
function updateDockState() {
  document.querySelectorAll('#sideDock [data-requires-save]').forEach(btn => {
    btn.disabled = !pdfSaved;
    btn.title    = pdfSaved ? '' : 'Save the certificate first';
  });
  if (window.parent && typeof window.parent.notifyIframeChange === 'function') {
    window.parent.notifyIframeChange();
  }
}

// â”€â”€ Dock Toggle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function toggleDock() {
  const dock = document.getElementById('sideDock');
  if (dock) {
    dock.classList.toggle('active');
  }
}

// â”€â”€ Close dock when clicking elsewhere â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.addEventListener('click', function(e) {
  const dock = document.getElementById('sideDock');
  const toggle = document.querySelector('.dock-toggle');
  if (dock && toggle && !dock.contains(e.target) && !toggle.contains(e.target)) {
    dock.classList.remove('active');
  }
});

// â”€â”€ Form modification tracking â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const form = document.getElementById('calibrationForm');
if (form) {
  form.addEventListener('input', function() {
    formModified = true;
    pdfSaved = false;
    updateDockState();
    const reminder = document.getElementById('unsavedReminder');
    if (reminder) reminder.classList.add('show');
  });
}

// â”€â”€ Back button with save prompt â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function goBackOrPromptSave() {
  const goBack = () => {
    if (document.referrer && document.referrer.indexOf(window.location.hostname) !== -1) {
      window.history.back();
    } else {
      window.location.href = '../index.php';
    }
  };

  if (formModified) {
    if (confirm('You have unsaved changes. Are you sure you want to go back?')) {
      goBack();
    }
  } else {
    goBack();
  }
}

// â”€â”€ Calculate next calibration date (1 year from now) â”€â”€â”€â”€
function calculateNextDate() {
  const calibDate = document.getElementById('calibrationDate');
  const nextDate = document.getElementById('nextCalibrationDate');
  if (calibDate && nextDate && calibDate.value) {
    let dateStr = calibDate.value;
    if (dateStr.includes('/')) {
      const parts = dateStr.split('/');
      if (parts.length === 3) {
        dateStr = `${parts[2]}-${parts[1]}-${parts[0]}`;
      }
    }
    const date = new Date(dateStr);
    if (!isNaN(date.getTime())) {
      date.setFullYear(date.getFullYear() + 1);
      // Subtract 1 day for exact 1 year validity
      date.setDate(date.getDate() - 1);
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      nextDate.value = `${year}-${month}-${day}`;
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const calibDateInput = document.getElementById('calibrationDate');
  const urlParams = new URLSearchParams(window.location.search);
  const isNew = !urlParams.get('id');
  
  if (calibDateInput && (!calibDateInput.value || isNew)) {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    calibDateInput.value = `${year}-${month}-${day}`;
    calculateNextDate();
  }
});

// â”€â”€ Show/Hide Loader â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function showLoader(message = 'Processing...') {
  const overlay = document.getElementById('customLoaderOverlay');
  const text = document.getElementById('loaderText');
  const spinner = document.getElementById('loaderSpinner');
  const tick = document.getElementById('loaderTick');
  
  if (overlay) {
    overlay.classList.add('active');
    if (text) text.textContent = message;
    if (spinner) spinner.style.display = 'block';
    if (tick) tick.style.display = 'none';
  }
}

function hideLoader() {
  const overlay = document.getElementById('customLoaderOverlay') || document.getElementById('loaderOverlay');
  if (overlay) overlay.classList.remove('active');
}

function showLoaderSuccess(message = 'Done! âœ¨') {
  const text = document.getElementById('loaderText');
  const spinner = document.getElementById('loaderSpinner');
  const tick = document.getElementById('loaderTick');
  
  if (spinner) spinner.style.display = 'none';
  if (tick) tick.style.display = 'flex';
  if (text) text.textContent = message;
  
  setTimeout(hideLoader, 2000);
}

// â”€â”€ Setup OK button in loader â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.addEventListener('DOMContentLoaded', function() {
  const okBtn = document.getElementById('loaderOkkBtn');
  if (okBtn) {
    okBtn.addEventListener('click', hideLoader);
  }
});

// â”€â”€ Preview PDF â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function preview() {
  if (!form || !form.checkValidity()) {
    alert('Please fill all required fields');
    return;
  }

  showLoader('Generating PDF preview...');
  
  try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
    
    // Add certificate details using the template's addCertificateDetails function
    const details = safeGetFormDetails();
    safeAddCertificateDetails(doc, details);
    if (typeof addQRCodeToPDF === 'function') {
      addQRCodeToPDF(doc, details.certificateNumber);
    }

    // Display in popup modal if available
    const pdfUrl = doc.output('bloburi');
    
    if (window.parent && typeof window.parent.showGlobalPreviewModal === 'function') {
      window.parent.showGlobalPreviewModal(pdfUrl);
    } else if (typeof window.showGlobalPreviewModal === 'function') {
      window.showGlobalPreviewModal(pdfUrl);
    } else {
      window.open(pdfUrl, '_blank');
    }
    
    hideLoader();
  } catch (error) {
    alert('Error generating preview: ' + error.message);
    hideLoader();
  }
}

// â”€â”€ Print Certificate â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function printBlankCertificate() {
  if (!form || !form.checkValidity()) {
    alert('Please fill all required fields');
    return;
  }

  showLoader('Preparing print...');
  
  try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
    
    const details = safeGetFormDetails();
    safeAddCertificateDetails(doc, details);
    if (typeof addQRCodeToPDF === 'function') {
      addQRCodeToPDF(doc, details.certificateNumber);
    }

    const pdfUrl = doc.output('bloburi');
    const printWindow = window.open(pdfUrl);
    
    setTimeout(() => {
      printWindow.print();
    }, 500);
    
    showLoaderSuccess('Print dialog opened!');
  } catch (error) {
    alert('Error printing: ' + error.message);
    hideLoader();
  }
}

// â”€â”€ Share PDF â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function sharePDF() {
  if (!form || !form.checkValidity()) {
    alert('Please fill all required fields');
    return;
  }

  showLoader('Generating shareable PDF...');
  
  try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
    
    const details = safeGetFormDetails();
    safeAddCertificateDetails(doc, details);
    await applyLetterhead(doc);
    if (typeof addQRCodeToPDF === 'function') {
      addQRCodeToPDF(doc, details.certificateNumber);
    }

    const pdfBlob = doc.output('blob');
    const pdfUrl = URL.createObjectURL(pdfBlob);
    const fileName = `${details.saveentry || 'certificate'}.pdf`;
    const pdfFile = new File([pdfBlob], fileName, { type: "application/pdf" });
    
    if (navigator.share && navigator.canShare && navigator.canShare({ files: [pdfFile] })) {
      await navigator.share({
        files: [pdfFile],
        title: 'Calibration Certificate',
        text: 'Calibration Certificate from Shreeji Instruments'
      });
      showLoaderSuccess('Shared successfully!');
    } else {
      window.open(pdfUrl);
      showLoaderSuccess('Download started!');
    }
  } catch (error) {
    if (error.name !== 'AbortError') {
      alert('Error sharing: ' + error.message);
    }
    hideLoader();
  }
}

// â”€â”€ Generate PDF with Letterhead â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function generatePDF() {
  if (!form || !form.checkValidity()) {
    alert('Please fill all required fields');
    return;
  }

  showLoader('Generating PDF...');

  try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });

    const details = safeGetFormDetails();
    safeAddCertificateDetails(doc, details);
    await applyLetterhead(doc);
    if (typeof addQRCodeToPDF === 'function') {
      addQRCodeToPDF(doc, details.certificateNumber);
    }

    const pdfBlob = doc.output('blob');
    const fileName = `${details.saveentry || 'certificate'}.pdf`;
    await savePDFWithLocation(pdfBlob, fileName);
    
    showLoaderSuccess('PDF Downloaded!');
  } catch (error) {
    alert('Error generating PDF: ' + error.message);
    hideLoader();
  }
}

// â”€â”€ Generate PDF without Letterhead â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function generatePDFblankpg() {
  if (!form || !form.checkValidity()) {
    alert('Please fill all required fields');
    return;
  }

  showLoader('Generating PDF (Blank Page)...');

  try {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });

    const details = safeGetFormDetails();
    safeAddCertificateDetails(doc, details);

    const pdfBlob = doc.output('blob');
    const fileName = `${details.saveentry || 'certificate'}.pdf`;
    await savePDFWithLocation(pdfBlob, fileName);
    
    showLoaderSuccess('PDF Downloaded!');
  } catch (error) {
    alert('Error generating PDF: ' + error.message);
    hideLoader();
  }
}


// â”€â”€ Save PDF with Location â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function savePDFWithLocation(pdfBlob, fileName = 'certificate.pdf') {
  const url = URL.createObjectURL(pdfBlob);
  const link = document.createElement('a');
  link.href = url;
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

// â”€â”€ Prefill Flow â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function prefillForm() {
  const urlParams = new URLSearchParams(window.location.search);
  const certId = urlParams.get('id');
  
  // Make the certificate number field read-only
  const certNumInput = document.getElementById('certificateNumber');
  if (certNumInput) {
    certNumInput.readOnly = true;
    certNumInput.style.backgroundColor = '#f1f5f9';
    certNumInput.style.cursor = 'not-allowed';
  }
  
  if (!certId) {
    // New certificate: Fetch next monthly sequence certificate number
    let slug = window.INSTRUMENT_SLUG;
    if (!slug) {
      try {
        const pathParts = window.location.pathname.split('/');
        const filename = pathParts[pathParts.length - 1];
        slug = filename.replace('.php', '');
        if (slug === 'cloud_cube') slug = 'cube_mould';
      } catch (e) {}
    }
    if (!slug) slug = 'autolevel';
    
    try {
      const response = await fetch(CERTIFICATE_CONFIG.apiBase + '/get_next_certificate_number.php?instrument_type=' + slug);
      const result = await response.json();
      if (result.success && result.next_certificate_number && certNumInput) {
        let finalCertNo = result.next_certificate_number;
        if (window.parent && typeof window.parent.getUniqueCertificateNumber === 'function') {
          let parentSlug = window.INSTRUMENT_SLUG;
          if (!parentSlug) {
            try {
              const pathParts = window.location.pathname.split('/');
              const filename = pathParts[pathParts.length - 1];
              parentSlug = filename.replace('.php', '');
              if (parentSlug === 'cloud_cube') parentSlug = 'cube_mould';
            } catch (e) {}
          }
          if (!parentSlug) parentSlug = 'autolevel';
          finalCertNo = window.parent.getUniqueCertificateNumber(parentSlug, finalCertNo, window);
        }
        certNumInput.value = finalCertNo;
        certNumInput.readOnly = true;
        certNumInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
    } catch (error) {
      if (window.SHREEJI_DEBUG) console.error('Error fetching next certificate number:', error);
    }
    return;
  }

  try {
    showLoader('Loading certificate details...');
    const response = await fetch(CERTIFICATE_CONFIG.apiBase + '/get_certificate.php?id=' + certId);
    const result = await response.json();
    hideLoader();

    if (result.success && result.form_data) {
      const formData = result.form_data;
      
      // Loop through all saved keys and set form values
      for (const [key, value] of Object.entries(formData)) {
        let input = document.getElementById(key);
        if (!input && key.toLowerCase() === 'partyname') {
          input = document.getElementById('partyName') || document.getElementById('partyname');
        }
        if (input) {
          input.value = value;
          // Dispatch input/change event so local listeners update
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      }
      
      // Custom triggers for dynamic pages
      // 1. CTM Machine: show reading boxes based on Proving Ring value
      if (typeof showInputBoxes === 'function') {
        showInputBoxes();
      }
      
      // 2. Test Sieves: restore selected sub-sizes checkboxes and rows
      if (typeof restoreSievesState === 'function') {
        setTimeout(restoreSievesState, 300);
      }
      
      // 3. Sticker rendering trigger
      if (typeof generateInfoSticker === 'function') {
        setTimeout(generateInfoSticker, 500);
      }
      
      // Mark as saved so they don't get prompt unless they edit
      pdfSaved = true;
      formModified = false;
      updateDockState();
      const reminder = document.getElementById('unsavedReminder');
      if (reminder) reminder.classList.remove('show');

      // Update title to show editing mode
      const titleEl = document.querySelector('.container h2.centered');
      if (titleEl) {
        titleEl.textContent += ' (Editing)';
      }
    }
  } catch (error) {
    if (window.SHREEJI_DEBUG) console.error('Prefill error:', error);
    hideLoader();
  }
}

// ───────────────────────────────── Upload Certificate to Backend ─────────────
document.addEventListener('DOMContentLoaded', function() {
  const dock = document.getElementById('sideDock');
  if (dock) {
    // Dynamically inject mobile close button if not exists
    if (!document.getElementById('dockCloseBtn')) {
      const closeBtn = document.createElement('button');
      closeBtn.className = 'dock-close-btn';
      closeBtn.type = 'button';
      closeBtn.id = 'dockCloseBtn';
      closeBtn.innerHTML = '&times;';
      closeBtn.addEventListener('click', toggleDock);
      dock.prepend(closeBtn);
    }
    // Check if Save button already exists in side dock
    let uploadBtn = document.getElementById('uploadBtn') || dock.querySelector('button[onclick*="generatePDF"]');
    
    // If no dedicated save/upload button exists, create it dynamically
    if (!document.getElementById('uploadBtn')) {
      const newUploadBtn = document.createElement('button');
      newUploadBtn.className = 'dock-button';
      newUploadBtn.type = 'button';
      newUploadBtn.id = 'uploadBtn';
      newUploadBtn.innerHTML = '📄 SAVE';
      
      // Place it right before the print button or as the second item
      const printBtn = dock.querySelector('button[onclick*="print"]');
      if (printBtn) {
        dock.insertBefore(newUploadBtn, printBtn);
      } else {
        dock.appendChild(newUploadBtn);
      }
    }
  }

  // Initial dock state: locked for new certs; prefillForm unlocks for existing certs
  updateDockState();

  const uploadBtn = document.getElementById('uploadBtn');
  if (uploadBtn) {
    uploadBtn.addEventListener('click', async function() {
      if (!form || !form.checkValidity()) {
        const errorMsg = 'Please fill all required fields in the form.';
        if (window.frameElement) {
          window.frameElement.dispatchEvent(new CustomEvent('iframeSaveError', { detail: { message: errorMsg } }));
        }
        alert(errorMsg);
        return;
      }

      // Check/Request local storage directory permission immediately on click (user gesture!)
      // Attempt local-storage directory setup — NON-BLOCKING.
      // Missing/cancelled directory only skips the local file copy;
      // the DB save always proceeds regardless.
      if ('showDirectoryPicker' in window) {
        try {
          let dirHandle = await window.getSavedDirectoryHandle();
          if (!dirHandle) {
            try { dirHandle = await window.promptForDirectorySelection(); }
            catch (e) { /* user cancelled — skip local save, continue to DB */ }
          }
          if (dirHandle) {
            const hasPerm = await window.verifyPermission(dirHandle, true);
            if (!hasPerm && window.SHREEJI_DEBUG) {
              console.warn('Local storage write permission denied — skipping local save');
            }
          }
        } catch (err) {
          if (window.SHREEJI_DEBUG) console.error('Local storage setup error:', err);
          // Don't return — proceed to DB save
        }
      }

      showLoader('Saving certificate...');

      try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });

        const details = safeGetFormDetails();
        safeAddCertificateDetails(doc, details);
        await applyLetterhead(doc);
        if (typeof addQRCodeToPDF === 'function') {
          addQRCodeToPDF(doc, details.certificateNumber);
        }

        const pdfBlob = doc.output('blob');
        const reader = new FileReader();
        
        reader.onload = async function(e) {
          const base64 = e.target.result;
          
          // Serialize all form inputs
          const formDataObj = {};
          const inputs = form.querySelectorAll('input, select, textarea');
          inputs.forEach(input => {
            if (input.id && input.type !== 'file' && input.type !== 'submit' && input.type !== 'button') {
              formDataObj[input.id] = input.value;
            }
          });

          // Check if editing an existing certificate
          const urlParams = new URLSearchParams(window.location.search);
          const certId = urlParams.get('id');
          
          const payload = {
            cert_id: certId ? parseInt(certId) : null,
            instrument_type: (function() {
              let slug = window.INSTRUMENT_SLUG;
              if (!slug) {
                try {
                  const pathParts = window.location.pathname.split('/');
                  const filename = pathParts[pathParts.length - 1];
                  slug = filename.replace('.php', '');
                  if (slug === 'cloud_cube') slug = 'cube_mould';
                } catch (e) {}
              }
              return slug || 'autolevel';
            })(),
            party_name: details.partyName || details.partyname,
            site_location: document.getElementById('siteLocation')?.value || '',
            calibration_date: document.getElementById('calibrationDate').value,
            next_due_date: document.getElementById('nextCalibrationDate').value,
            make: details.make,
            model_no: details.modelNo,
            serial_no: details.serialNo,
            cert_number: details.certificateNumber,
            pdf_base64: base64,
            form_data: formDataObj,
            csrf_token: (typeof SHREEJI_CONFIG !== 'undefined' ? SHREEJI_CONFIG.csrfToken : '')
          };

          try {
            if (typeof Loader !== 'undefined') Loader.show('Saving certificate...');
            const response = await fetch((typeof SHREEJI_CONFIG !== 'undefined' ? SHREEJI_CONFIG.apiBase : '/api') + '/save_certificates.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': payload.csrf_token },
              body: JSON.stringify(payload)
            });
            const result = await response.json();

            if (result.success) {
              if (window.frameElement) {
                window.frameElement.dispatchEvent(new CustomEvent('iframeSaveSuccess', { detail: result }));
              }
              if (typeof UnsavedTracker !== 'undefined') UnsavedTracker.markSaved();
              if (typeof Loader !== 'undefined') Loader.success('Certificate Saved Successfully! 💾');

              pdfSaved = true;
              formModified = false;
              updateDockState();
              const reminder = document.getElementById('unsavedReminder');
              if (reminder) reminder.classList.remove('show');
              showLoaderSuccess('Certificate Saved Successfully! 💾');
              
              setTimeout(() => {
                if (result.pdf_url) {
                  window.open(result.pdf_url, '_blank');
                }
              }, 1500);
            } else {
              throw new Error(result.message || 'Server rejected saving.');
            }
          } catch (error) {
            if (window.frameElement) {
              window.frameElement.dispatchEvent(new CustomEvent('iframeSaveError', { detail: error }));
            }
            if (typeof Loader !== 'undefined') Loader.error('Save failed: ' + error.message);
            console.error('Saving failed:', error);
          }
        };

        reader.readAsDataURL(pdfBlob);
      } catch (error) {
        alert('Error generating PDF: ' + error.message);
        hideLoader();
      }
    });
  }

  // Run the prefill check on page load
  prefillForm();
});

// â”€â”€ Export functions globally so templates can access them â”€
window.toggleDock = toggleDock;
window.updateDockState = updateDockState;
window.goBackOrPromptSave = goBackOrPromptSave;
window.calculateNextDate = calculateNextDate;
window.showLoader = showLoader;
window.hideLoader = hideLoader;
window.showLoaderSuccess = showLoaderSuccess;
window.preview = preview;
window.generatePDF = generatePDF;
window.generatePDFblankpg = generatePDFblankpg;
window.printBlankCertificate = printBlankCertificate;
window.sharePDF = sharePDF;

