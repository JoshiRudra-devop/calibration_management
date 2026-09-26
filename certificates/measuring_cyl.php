<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Measuring Cylinder Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'measuring_cyl' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">MEASURING CYLINDER CALIBRATION CERTIFICATE</h2>
    <form id="calibrationForm">
      <div class="title_input_pair">
        <label for="certificateNumber">Certificate No:</label>
        <input type="text" id="certificateNumber" required>
      </div>
      <div class="title_input_pair">
        <label for="serialNo">Serial No:</label>
        <div style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
          <input type="text" id="serialNo" required>
          <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
            <input type="checkbox" id="useCertNoAsSerial" style="width: auto; margin: 0; cursor: pointer;" onchange="toggleCertNoAsSerial()">
            <label for="useCertNoAsSerial" style="font-weight: normal; font-size: 12px; cursor: pointer; display: inline; margin: 0; user-select: none;">Use Certificate No. as Serial No.</label>
          </div>
        </div>
      </div>
      <div class="date">
          <div class="title_input_pair">
              <label for="calibrationDate">Date of Calibration:</label>
              <input type="date" id="calibrationDate" onchange="calculateNextDate()" required>
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
        <label for="type">TYPE / MATERIAL:</label>
        <select id="type">
          <option value="GLASS">GLASS</option>
          <option value="PP (POLYPROPYLENE)">PP (POLYPROPYLENE)</option>
        </select>
      </div> 
      <div class="title_input_pair">
        <label for="size">CAPACITY / SIZE:</label>
        <select id="size" onchange="updateDefaultReadings()">
          <option value="100 ml">100 ml</option>
          <option value="250 ml">250 ml</option>
          <option value="500 ml">500 ml</option>
          <option value="1000 ml" selected>1000 ml</option>
        </select>
      </div>  
      <div class="title_input_pair">
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" required>
      </div>

      <!-- Volumetric Readings Inputs Section -->
      <div style="margin-top: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
          <div>
            <h4 style="margin:0; color: #1e293b;">Calibration Readings (Observed Volumetric Values):</h4>
            <span style="font-size: 11px; color: #64748b; font-weight: 500;">Accepted Volumetric Error Limit: <strong>±0.5% (Class A) / ±1.0% (Class B)</strong> as per IS 878 / ISO 4788</span>
          </div>
          <div style="display: flex; gap: 6px;">
            <button type="button" onclick="setZeroErrorReadings()" style="background: #00796b; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
              ✨ 0.0% Ideal Error
            </button>
            <button type="button" onclick="generateRandomCylReadings()" style="background: #1e293b; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
              🎲 Random (±0.5% Limit)
            </button>
          </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;" id="readingsContainer">
          <div class="title_input_pair">
            <label id="lbl_obs1" for="obs1">Mark 1 (200 ml):</label>
            <input type="text" id="obs1" placeholder="200.0" value="200.0">
          </div>
          <div class="title_input_pair">
            <label id="lbl_obs2" for="obs2">Mark 2 (400 ml):</label>
            <input type="text" id="obs2" placeholder="400.0" value="400.0">
          </div>
          <div class="title_input_pair">
            <label id="lbl_obs3" for="obs3">Mark 3 (600 ml):</label>
            <input type="text" id="obs3" placeholder="600.0" value="600.0">
          </div>
          <div class="title_input_pair">
            <label id="lbl_obs4" for="obs4">Mark 4 (800 ml):</label>
            <input type="text" id="obs4" placeholder="800.0" value="800.0">
          </div>
          <div class="title_input_pair">
            <label id="lbl_obs5" for="obs5">Mark 5 (1000 ml):</label>
            <input type="text" id="obs5" placeholder="1000.0" value="1000.0">
          </div>
        </div>
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

<script src="<?= APP_URL ?>/assets/js/general-v3.js?v=<?= filemtime(__DIR__ . '/../assets/js/general-v3.js') ?>"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    window.INSTRUMENT_SLUG = 'measuring_cyl';

    function getNominalMarks() {
      const sizeVal = document.getElementById("size") ? document.getElementById("size").value : "1000 ml";
      if (sizeVal.includes("100 ml")) return [20, 40, 60, 80, 100];
      if (sizeVal.includes("250 ml")) return [50, 100, 150, 200, 250];
      if (sizeVal.includes("500 ml")) return [100, 200, 300, 400, 500];
      return [200, 400, 600, 800, 1000];
    }

    function setZeroErrorReadings() {
      const marks = getNominalMarks();
      marks.forEach((m, idx) => {
        const el = document.getElementById("obs" + (idx + 1));
        if (el) el.value = m.toFixed(1);
      });
      const form = document.getElementById('calibrationForm');
      if (form) form.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function generateRandomCylReadings() {
      const marks = getNominalMarks();
      const capacity = marks[marks.length - 1];
      // Max offset for <= ±0.5% Class A accuracy limit
      const maxOffset = capacity * 0.004; // 0.4% max offset
      marks.forEach((m, idx) => {
        const el = document.getElementById("obs" + (idx + 1));
        const randOffset = (Math.random() * (2 * maxOffset) - maxOffset);
        const roundedOffset = Math.round(randOffset * 10) / 10;
        const val = (m + roundedOffset).toFixed(1);
        if (el) el.value = val;
      });
      const form = document.getElementById('calibrationForm');
      if (form) form.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function updateDefaultReadings() {
      const marks = getNominalMarks();
      marks.forEach((m, idx) => {
        const l = document.getElementById("lbl_obs" + (idx + 1));
        const el = document.getElementById("obs" + (idx + 1));
        if (l) l.textContent = `Mark ${idx + 1} (${m} ml):`;
        if (el && (!el.value || el.dataset.autoFilled === "true")) {
          el.value = m.toFixed(1);
          el.dataset.autoFilled = "true";
        }
      });
    }

    document.addEventListener("DOMContentLoaded", function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (!urlParams.get('id')) {
        setZeroErrorReadings();
      } else {
        updateDefaultReadings();
      }
    });

    window.stickerPdfBlob = null;
      
    // Function to fetch form details
    window.getFormDetails = function() {
      const useCertCheck = document.getElementById("useCertNoAsSerial");
      const certNo = document.getElementById("certificateNumber") ? document.getElementById("certificateNumber").value : "";
      const serialNoVal = document.getElementById("serialNo") ? document.getElementById("serialNo").value : "";
      return {
        certificateNumber: certNo,
        serialNo: (useCertCheck && useCertCheck.checked) ? certNo : serialNoVal,
        calibrationDate: document.getElementById("calibrationDate") ? document.getElementById("calibrationDate").value.split("-").reverse().join("/") : "",
        nextCalibrationDate: document.getElementById("nextCalibrationDate") ? document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/") : "",
        siteLocation: document.getElementById("siteLocation") ? document.getElementById("siteLocation").value : "",
        size: document.getElementById("size") ? document.getElementById("size").value : "1000 ml",
        type: document.getElementById("type") ? document.getElementById("type").value : "GLASS",
        partyName: document.getElementById("partyName") ? document.getElementById("partyName").value : "",
        obs1: document.getElementById("obs1") ? document.getElementById("obs1").value : "",
        obs2: document.getElementById("obs2") ? document.getElementById("obs2").value : "",
        obs3: document.getElementById("obs3") ? document.getElementById("obs3").value : "",
        obs4: document.getElementById("obs4") ? document.getElementById("obs4").value : "",
        obs5: document.getElementById("obs5") ? document.getElementById("obs5").value : "",
        saveentry: `MeasuringCyl_${document.getElementById("partyName") ? document.getElementById("partyName").value : ""}_${certNo}`
      };
    }

    window.addCertificateDetails = function(doc, details) {
      let Yalign = 50;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(22);
      doc.text("TEST REPORT FOR MEASURING CYLINDER", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
      
      if (details.size) {
        Yalign += 7;
        doc.setFontSize(14);
        doc.text(`CAPACITY: ${details.size} (${details.type || 'GLASS'})`, doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
      }

      doc.setFontSize(10.5);
      Yalign += 12;
      doc.text(`DATE:-${details.calibrationDate}`, 155, Yalign);
      doc.text(`REF NO                        :-     ${details.certificateNumber}`, 14, Yalign);

      Yalign += 8;
      const partyPrefix = "NAME OF PARTY        :-     ";
      const partyPrefixWidth = doc.getTextWidth(partyPrefix);
      const partyLines = doc.splitTextToSize(details.partyName || "", 180 - partyPrefixWidth);
      doc.text(partyPrefix + (partyLines[0] || ""), 14, Yalign);
      for (let i = 1; i < partyLines.length; i++) {
        Yalign += 4.5;
        doc.text(partyLines[i], 14 + partyPrefixWidth, Yalign);
      }

      Yalign += 8;
      doc.text(`EQUIPMENT NAME     :-     MEASURING CYLINDER (${details.size || ''})`, 14, Yalign);
      Yalign += 8;
      doc.text(`MATERIAL TYPE        :-     ${details.type || 'GLASS'}`, 14, Yalign);
      Yalign += 8;
      doc.text(`SERIAL NO                  :-     ${details.serialNo}`, 14, Yalign);
      doc.text(`NEXT DUE DATE:-${details.nextCalibrationDate}`, 135, Yalign);

      Yalign += 8;
      const siteLocPrefix = "SITE LOCATION         :-     ";
      const siteLocPrefixWidth = doc.getTextWidth(siteLocPrefix);
      const siteLocLines = doc.splitTextToSize(details.siteLocation || "", 180 - siteLocPrefixWidth);
      doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign);
      for (let i = 1; i < siteLocLines.length; i++) {
        Yalign += 4.5;
        doc.text(siteLocLines[i], 14 + siteLocPrefixWidth, Yalign);
      }

      // Calibration Table
      Yalign += 10;

      // Table Configuration: Standard Volumetric Table (Option A)
      // Headers: SR. NO. | NOMINAL VOL. (ml) | OBSERVED VOL. (ml) | ERROR (ml) | TOLERANCE (± ml)
      const startX = 14;
      const colWidths = [20, 42, 42, 38, 40]; // Total = 182mm
      const rowHeight = 8;

      const headers = ["SR. NO.", "NOMINAL VOL. (ml)", "OBSERVED VOL. (ml)", "ERROR (ml)", "TOLERANCE (± ml)"];

      // Draw Header Row
      doc.setDrawColor(0, 0, 0);
      doc.setLineWidth(0.35);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(9.5);
      let curX = startX;
      for (let k = 0; k < headers.length; k++) {
        doc.rect(curX, Yalign, colWidths[k], 9);
        doc.text(headers[k], curX + (colWidths[k] / 2), Yalign + 6, { align: 'center' });
        curX += colWidths[k];
      }

      // Helper to calculate error string
      function calcErr(nom, obsStr, defaultObsStr) {
        const valStr = (obsStr && obsStr.trim() !== "") ? obsStr.trim() : defaultObsStr;
        const obsNum = parseFloat(valStr);
        if (isNaN(obsNum)) return { obs: valStr, err: "0.0 ml" };
        const diff = (obsNum - nom).toFixed(1);
        const signStr = diff > 0 ? `+${diff}` : `${diff}`;
        const finalObs = valStr.includes("ml") ? valStr : `${valStr} ml`;
        return { obs: finalObs, err: `${signStr} ml` };
      }

      let rowsData = [];
      const sizeVal = details.size || "1000 ml";

      if (sizeVal.includes("100 ml")) {
        const r1 = calcErr(20, details.obs1, "20.0");
        const r2 = calcErr(40, details.obs2, "40.0");
        const r3 = calcErr(60, details.obs3, "60.0");
        const r4 = calcErr(80, details.obs4, "80.0");
        const r5 = calcErr(100, details.obs5, "100.0");
        rowsData = [
          ["1", "20 ml", r1.obs, r1.err, "± 0.5 ml"],
          ["2", "40 ml", r2.obs, r2.err, "± 0.5 ml"],
          ["3", "60 ml", r3.obs, r3.err, "± 0.5 ml"],
          ["4", "80 ml", r4.obs, r4.err, "± 0.5 ml"],
          ["5", "100 ml", r5.obs, r5.err, "± 0.5 ml"]
        ];
      } else if (sizeVal.includes("250 ml")) {
        const r1 = calcErr(50, details.obs1, "50.0");
        const r2 = calcErr(100, details.obs2, "100.0");
        const r3 = calcErr(150, details.obs3, "150.0");
        const r4 = calcErr(200, details.obs4, "200.0");
        const r5 = calcErr(250, details.obs5, "250.0");
        rowsData = [
          ["1", "50 ml", r1.obs, r1.err, "± 1.0 ml"],
          ["2", "100 ml", r2.obs, r2.err, "± 1.0 ml"],
          ["3", "150 ml", r3.obs, r3.err, "± 1.0 ml"],
          ["4", "200 ml", r4.obs, r4.err, "± 1.0 ml"],
          ["5", "250 ml", r5.obs, r5.err, "± 1.0 ml"]
        ];
      } else if (sizeVal.includes("500 ml")) {
        const r1 = calcErr(100, details.obs1, "100.0");
        const r2 = calcErr(200, details.obs2, "200.0");
        const r3 = calcErr(300, details.obs3, "300.0");
        const r4 = calcErr(400, details.obs4, "400.0");
        const r5 = calcErr(500, details.obs5, "500.0");
        rowsData = [
          ["1", "100 ml", r1.obs, r1.err, "± 2.5 ml"],
          ["2", "200 ml", r2.obs, r2.err, "± 2.5 ml"],
          ["3", "300 ml", r3.obs, r3.err, "± 2.5 ml"],
          ["4", "400 ml", r4.obs, r4.err, "± 2.5 ml"],
          ["5", "500 ml", r5.obs, r5.err, "± 2.5 ml"]
        ];
      } else {
        // 1000 ml default
        const r1 = calcErr(200, details.obs1, "200.0");
        const r2 = calcErr(400, details.obs2, "400.0");
        const r3 = calcErr(600, details.obs3, "600.0");
        const r4 = calcErr(800, details.obs4, "800.0");
        const r5 = calcErr(1000, details.obs5, "1000.0");
        rowsData = [
          ["1", "200 ml", r1.obs, r1.err, "± 5.0 ml"],
          ["2", "400 ml", r2.obs, r2.err, "± 5.0 ml"],
          ["3", "600 ml", r3.obs, r3.err, "± 5.0 ml"],
          ["4", "800 ml", r4.obs, r4.err, "± 5.0 ml"],
          ["5", "1000 ml", r5.obs, r5.err, "± 5.0 ml"]
        ];
      }

      // Draw Data Rows
      let curY = Yalign + 9;
      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);

      for (let r = 0; r < rowsData.length; r++) {
        curX = startX;
        for (let c = 0; c < rowsData[r].length; c++) {
          doc.rect(curX, curY, colWidths[c], rowHeight);
          doc.text(rowsData[r][c], curX + (colWidths[c] / 2), curY + 5.5, { align: 'center' });
          curX += colWidths[c];
        }
        curY += rowHeight;
      }
      doc.setFont("helvetica", "bold");
      doc.text(`CALIBRATED BY          :     YOGESH B JOSHI`, 14, curY += 15);
      doc.setFontSize(9);
      doc.text(`• REMARKS: This certificate is valid for 12 months from the date of calibration.`, 14, curY += 7);
      doc.text(`• This certificate refers to the value obtained at the time of calibration.`, 14, curY += 7);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10.5);
      doc.text("FOR, " + (window.PDF_COMPANY_NAME || "SHREEJI INSTRUMENTS"), 150, 224);
      doc.text("PROPRIETOR", 170, 238);
    };
  
    async function generateInfoSticker() {
      const { jsPDF } = window.jspdf;
      const width = 60 * 2.83465;
      const height = 30 * 2.83465;
      const doc = new jsPDF({
        orientation: "landscape",
        unit: "pt",
        format: [width, height]
      });
      const details = (typeof safeGetFormDetails === 'function') ? safeGetFormDetails() : getFormDetails();
      const primaryBlue = [19, 52, 165];
      const accentRed = [228, 34, 21];
      doc.setDrawColor(...primaryBlue);
      doc.setLineWidth(3);
      doc.rect(0, 0, width, height);

      const logoImg = new Image();
      logoImg.src = "../assets/images/logo.png";
      await new Promise(resolve => { logoImg.onload = resolve; logoImg.onerror = resolve; });
      if (logoImg.width) {
        doc.addImage(logoImg, "PNG", 8, 4, 12, 16);
      }

      doc.setFont("times", "bold");
      doc.setFontSize(13);
      doc.setTextColor(...accentRed);
      doc.text(window.PDF_COMPANY_NAME || "SHREEJI INSTRUMENTS", 30, 13);

      doc.setFont("times", "normal");
      doc.setFontSize(6);
      doc.setTextColor(...primaryBlue);
      doc.text("SALES • SERVICE • REPAIRING • CALIBRATIONS", 30, 20, { align: "left" });

      const tableLeft = 15;
      const tableTop = 24;
      const tableWidth = width - 30;
      const rowHeight = 13;
      const labelWidth = tableWidth * 0.4;
      const tableData = [
        { label: "SERIAL NO.", value: details.serialNo || details.certificateNumber || "N/A" },
        { label: "MODEL", value: details.type || "MEASURING CYL" },
        { label: "CALIB. DATE", value: details.calibrationDate || "N/A" },
        { label: "NEXT DATE", value: details.nextCalibrationDate || "N/A" },
      ];

      doc.setDrawColor(0, 0, 0);
      doc.setLineWidth(1);
      doc.rect(tableLeft, tableTop, tableWidth, rowHeight * tableData.length);
      tableData.forEach((row, index) => {
        const rowY = tableTop + (index * rowHeight);
        if (index > 0) doc.line(tableLeft, rowY, tableLeft + tableWidth, rowY);
        doc.line(tableLeft + labelWidth, rowY, tableLeft + labelWidth, rowY + rowHeight);
        doc.setFillColor(255, 255, 255);
        doc.rect(tableLeft, rowY, labelWidth, rowHeight, 'F');
        const labelY = rowY + rowHeight / 2 + 1;
        const valueY = rowY + rowHeight / 2 + 1;
        doc.setFont("times", "bold");
        doc.setFontSize(4.5);
        doc.setTextColor(...primaryBlue);
        doc.text(row.label, tableLeft + 4, labelY, { baseline: 'middle' });
        doc.setFont("times", "normal");
        doc.setFontSize(4.5);
        doc.setTextColor(0, 0, 0);
        doc.text(String(row.value), tableLeft + labelWidth + 5, valueY, { baseline: 'middle' });
      });

      window.stickerPdfBlob = doc.output('blob');
      const pdfURL = URL.createObjectURL(window.stickerPdfBlob);
      const frame = document.getElementById("stickerPreviewFrame");
      if (frame) {
        frame.src = pdfURL;
        frame.style.display = "block";
        frame.scrollIntoView({ behavior: 'smooth' });
      }
      const dockDownloadBtn = document.querySelector('.side-dock #downloadStickerBtn');
      if (dockDownloadBtn) dockDownloadBtn.style.display = "block";
    }

    async function downloadSticker() {
      if (!window.stickerPdfBlob) {
        alert('Please generate the sticker first!');
        return;
      }
      const details = (typeof safeGetFormDetails === 'function') ? safeGetFormDetails() : getFormDetails();
      const fileName = `${details.saveentry || 'sticker'}_sticker.pdf`;
      await savePDFWithLocation(window.stickerPdfBlob, fileName);
    }

    window.generateInfoSticker = generateInfoSticker;
    window.downloadSticker = downloadSticker;
  </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>