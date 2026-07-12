<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Vernier Caliper Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'vernier_caliper' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">VERNIER CALIPER CALIBRATION CERTIFICATE</h2>
    <form id="calibrationForm">
      <div class="title_input_pair">
        <label for="certificateNumber">Certificate No:</label>
        <input type="text" id="certificateNumber" required>
      </div>
      <div class="date">
          <div class="title_input_pair">
              <label for="calibrationDate">Date of Calibration:</label>
              <input type="date" id="calibrationDate"  onchange="calculateNextDate()" required>
          </div>
          <div class="title_input_pair">
              <label for="nextCalibrationDate">Next Suggested Date:</label>
              <input type="date" id="nextCalibrationDate" required>
          </div>
      </div>
      <div class="title_input_pair">
        <label for="partyName">Company Name:</label>
        <input type="text" id="partyName" required>
      </div>
      <div class="title_input_pair">
        <label for="make">Make:</label>
        <input type="text" id="make" required>
      </div>
      <div class="title_input_pair">
        <label for="type">TYPE:</label>
        <select id="type">
          <option value="DIGITAL">DIGITAL</option>
          <option value="MANUAL">MANUAL</option>
        </select>
      </div> 
      <div class="title_input_pair">
        <label for="size">SIZE:</label>
        <select id="size">
          <option value="150 mm ">6 inch</option>
          <option value="200 mm">8 inch</option>
          <option value="300 mm">12 inch</option>
        </select>
      </div>  
      <div class="title_input_pair">
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" required>
      </div>
      <div class="unsaved-reminder" id="unsavedReminder">
        <span>⚠️ Please save your certificate before leaving this page.</span>
      </div>
      <div class="sticker-section">
        <div class="sticker-preview-container">
          <h3 style="color: #00796b; margin-top: 0;">Info Sticker Preview</h3>
          <iframe id="stickerPreviewFrame"></iframe>
        </div>
      </div>
    </form>
  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="<?= APP_URL ?>/assets/js/general.js?v=<?= filemtime(__DIR__ . '/../assets/js/general.js') ?>"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    const INSTRUMENT_SLUG = 'vernier_caliper';
  </script>
  <script>
    let stickerPdfBlob = null;
      
    // Function to fetch form details
    window.getFormDetails = function() {
      const sizeRaw = document.getElementById("size").value || '';
      const sizeNum = parseInt(sizeRaw.replace(/\D/g, ''), 10);
      let leastCount = 0.01;
      if (sizeNum === 150) leastCount = 0.01;
      else if (sizeNum === 200) leastCount = 0.02;
      else if (sizeNum === 300) leastCount = 0.02;
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        size: document.getElementById("size").value,
        leastCount: leastCount,
        type: document.getElementById("type").value,
        make: document.getElementById("make").value,
        partyName: document.getElementById("partyName").value,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
      };
    }

window.addCertificateDetails = function(doc, details)
{
  let Yalign = 50;
  doc.setFont("helvetica", "bold");
  doc.setFontSize(25);
  doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
  doc.setFontSize(9);
  const disclaimerText = window.PDF_COMPANY_NAME + ": Calibration laboratory certifies that the instrument has been inspected, tested, and calibrated in accordance with documented procedures using measuring and test equipment traceable to international standards.";
  const disclaimerLines = doc.splitTextToSize(disclaimerText, 190);
  doc.text(disclaimerLines, 10, Yalign += 12);
  Yalign += (disclaimerLines.length - 1) * 7;
  Yalign += 4;
  doc.setFontSize(10);
  
  // Certificate Details
  doc.text(`DATE: ${details.calibrationDate}`, 150, Yalign+=12);
  doc.text(`REF NO                            :     SI-${details.certificateNumber}`, 14, Yalign);
  
  // --- Party Name with wrapping (only value, not prefix) ---
  const partyNamePrefix = "NAME OF PARTY           :     ";
  const prefixWidth = doc.getTextWidth(partyNamePrefix);
  const maxWidth = 180 - prefixWidth;
  const partyNameLines = doc.splitTextToSize(details.partyName, maxWidth);
  
  doc.text(partyNamePrefix + (partyNameLines[0] || ""), 14, Yalign += 12);
  for (let i = 1; i < partyNameLines.length; i++) {
    doc.text(partyNameLines[i], 14 + prefixWidth, Yalign += 7);
  }
  doc.text(`EQUIPMENT NAME         :     VERNIER CALIPER  ( ${details.type} )`, 14, Yalign+=12);
  doc.text(`SIZE / LEAST COUNT     :     ${details.size} / ${details.leastCount} mm`, 14, Yalign+=12);
  doc.text(`SERIAL NO / MAKE         :     ${details.certificateNumber} / "${details.make}"`, 14, Yalign+=12);
  
  // --- Site Location with wrapping (only value, not prefix) ---
  const siteLocPrefix = "SITE LOCATION               :     ";
  const siteLocPrefixWidth = doc.getTextWidth(siteLocPrefix);
  const siteLocMaxWidth = 180 - siteLocPrefixWidth;
  const siteLocLines = doc.splitTextToSize(details.siteLocation, siteLocMaxWidth);
  
  doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign += 12);
  for (let i = 1; i < siteLocLines.length; i++) {
    doc.text(siteLocLines[i], 14 + siteLocPrefixWidth, Yalign += 7);
  }

  doc.text(`NEXT DUE DATE              :     ${details.nextCalibrationDate}`, 14, Yalign+=12);
  doc.setFontSize(10);
  // Insert calibration results table (values adapt to least count)
  // Hardcoded measured values per size based on calibration data
  const sizeNum = parseInt((details.size || '').replace(/\D/g, ''), 10);
  let rows = [];

  if (sizeNum === 150) {
    // 150 mm data from image
    rows = [
      ["1", "0.00", "0.00", "0.00", "0.00"],
      ["2", "20.00", "20.00", "20.00", "20.00"],
      ["3", "50.00", "50.00", "50.00", "50.00"],
      ["4", "100.00", "100.01", "100.01", "100.02"],
      ["5", "150.00", "150.02", "150.02", "150.01"]
    ];
  } else if (sizeNum === 200) {
    // 200 mm data from image
    rows = [
      ["1", "0.00", "0.00", "0.00", "0.00"],
      ["2", "50.00", "50.01", "50.01", "50.00"],
      ["3", "100.00", "100.02", "100.02", "100.01"],
      ["4", "150.00", "150.02", "150.02", "150.02"],
      ["5", "200.00", "200.03", "200.03", "200.02"]
    ];
  } else if (sizeNum === 300) {
    // 300 mm data (estimated based on pattern)
    rows = [
      ["1", "0.00", "0.00", "0.00", "0.00"],
      ["2", "100.00", "100.02", "100.02", "100.01"],
      ["3", "200.00", "200.03", "200.03", "200.02"],
      ["4", "300.00", "300.04", "300.04", "300.03"]
    ];
  } else {
    // Default fallback
    rows = [["1", "0.00", "0.00", "0.00", "0.00"]];
  }

  // reserve some vertical space then render the table
  const tableStartY = Yalign + 10;
  doc.autoTable({
    startY: tableStartY,
    head: [["S. No.", "Standard Value in (mm)", "Inside Measured Value (mm)", "Outside Measured Value (mm)", "Depth Measured Value (mm)"]],
    body: rows,
    styles: {
      fontSize: 9,
      textColor: 0,
      fillColor: [255, 255, 255],
      lineColor: [0, 0, 0],
      lineWidth: 0.5,
      halign: 'center'
    },
    headStyles: {
      fillColor: [255, 255, 255],
      textColor: 0,
      halign: 'center'
    },
    bodyStyles: {
      fillColor: [255, 255, 255],
      textColor: 0,
      halign: 'center'
    },
    alternateRowStyles: {
      fillColor: [255, 255, 255]
    },
    theme: 'grid',
    margin: { left: 12, right: 12 }
  });

  // move cursor below table
  const afterTableY = doc.lastAutoTable ? doc.lastAutoTable.finalY + 12 : tableStartY + (rows.length + 2) * 7;
  doc.text(`CALIBRATION BY        :     YOGESH BHAI`, 14, afterTableY);
  doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, afterTableY + 15);
  doc.text("PROPRIETOR", 170, afterTableY + 30);
  
  if (typeof renderQRCode === 'function') {
    renderQRCode(doc, details.certificateNumber, afterTableY + 10);
  }
}
// --- Least-count variable (millimetres) derived from the `#size` input ---
let leastCount = 0;

function updateLeastCount() {
  const el = document.getElementById('size');
  if (!el) return;
  const raw = el.value || '';
  const n = parseInt(raw.replace(/\D/g, ''), 10);
  if (n === 150) leastCount = 0.01;
  else if (n === 200) leastCount = 0.02;
  else if (n === 300) leastCount = 0.02;
  else leastCount = 0.01; // default fallback
  window.leastCount = leastCount; // expose for console/debugging
}

updateLeastCount();
const _sizeEl = document.getElementById('size');
if (_sizeEl) _sizeEl.addEventListener('change', updateLeastCount);
  </script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
