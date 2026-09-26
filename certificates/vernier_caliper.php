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
        <label for="size">SIZE / RANGE:</label>
        <select id="size" onchange="updateReadingsForSize()">
          <option value="150 mm">150 mm (6 inch)</option>
          <option value="200 mm" selected>200 mm (8 inch)</option>
          <option value="300 mm">300 mm (12 inch)</option>
        </select>
      </div>  
      <div class="title_input_pair">
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" required>
      </div>

      <!-- Interactive Calibration Test Data Section -->
      <div style="margin-top: 20px; padding: 15px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
          <h4 style="margin: 0; color: #1e293b; font-size: 14px; text-transform: uppercase;">Calibration Readings (IS 3651 / ISO 13385-1)</h4>
          <div style="display: flex; gap: 8px;">
            <button type="button" onclick="setZeroErrorReadings()" style="background: #0284c7; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: 600;">✨ 0.0% Ideal Error</button>
            <button type="button" onclick="generateRandomReadings()" style="background: #4f46e5; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: 600;">🎲 Random (±0.02 mm Limit)</button>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;" id="readingsGrid">
          <!-- Rows will be injected dynamically -->
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  <script src="<?= APP_URL ?>/assets/js/general-v3.js?v=<?= filemtime(__DIR__ . '/../assets/js/general-v3.js') ?>"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    window.INSTRUMENT_SLUG = 'vernier_caliper';
  </script>
  <script>
    window.stickerPdfBlob = null;

    const nominalConfigs = {
      "150 mm": { steps: [0.00, 20.00, 50.00, 100.00, 150.00], tolerance: "± 0.02" },
      "200 mm": { steps: [0.00, 50.00, 100.00, 150.00, 200.00], tolerance: "± 0.02" },
      "300 mm": { steps: [0.00, 50.00, 100.00, 200.00, 300.00], tolerance: "± 0.03" }
    };

    function updateReadingsForSize() {
      const sizeVal = document.getElementById("size") ? document.getElementById("size").value : "200 mm";
      const config = nominalConfigs[sizeVal] || nominalConfigs["200 mm"];
      const grid = document.getElementById("readingsGrid");
      if (!grid) return;

      let html = "";
      config.steps.forEach((nom, idx) => {
        const i = idx + 1;
        html += `
          <div style="background: white; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px;">
            <div style="font-weight: bold; font-size: 11px; margin-bottom: 4px; color: #334155;">Row ${i} (${nom.toFixed(2)} mm)</div>
            <div style="display: flex; flex-direction: column; gap: 4px;">
              <input type="text" id="obsOut${i}" placeholder="Outside" value="${nom.toFixed(2)}" style="font-size: 11px; padding: 3px 6px;">
              <input type="text" id="obsIn${i}" placeholder="Inside" value="${nom.toFixed(2)}" style="font-size: 11px; padding: 3px 6px;">
              <input type="text" id="obsDep${i}" placeholder="Depth" value="${nom.toFixed(2)}" style="font-size: 11px; padding: 3px 6px;">
            </div>
          </div>
        `;
      });
      grid.innerHTML = html;
      updateLeastCount();
    }

    function setZeroErrorReadings() {
      const sizeVal = document.getElementById("size").value;
      const config = nominalConfigs[sizeVal] || nominalConfigs["200 mm"];
      config.steps.forEach((nom, idx) => {
        const i = idx + 1;
        const valStr = nom.toFixed(2);
        if (document.getElementById(`obsOut${i}`)) document.getElementById(`obsOut${i}`).value = valStr;
        if (document.getElementById(`obsIn${i}`)) document.getElementById(`obsIn${i}`).value = valStr;
        if (document.getElementById(`obsDep${i}`)) document.getElementById(`obsDep${i}`).value = valStr;
      });
    }

    function generateRandomReadings() {
      const sizeVal = document.getElementById("size").value;
      const config = nominalConfigs[sizeVal] || nominalConfigs["200 mm"];
      config.steps.forEach((nom, idx) => {
        const i = idx + 1;
        if (nom === 0) {
          if (document.getElementById(`obsOut${i}`)) document.getElementById(`obsOut${i}`).value = "0.00";
          if (document.getElementById(`obsIn${i}`)) document.getElementById(`obsIn${i}`).value = "0.00";
          if (document.getElementById(`obsDep${i}`)) document.getElementById(`obsDep${i}`).value = "0.00";
        } else {
          // Generate small random error within 0.01 to 0.02 mm
          const randOut = (nom + (Math.random() > 0.5 ? 0.01 : -0.01)).toFixed(2);
          const randIn  = (nom + (Math.random() > 0.5 ? 0.01 : -0.01)).toFixed(2);
          const randDep = (nom + (Math.random() > 0.5 ? 0.01 : 0.00)).toFixed(2);
          if (document.getElementById(`obsOut${i}`)) document.getElementById(`obsOut${i}`).value = randOut;
          if (document.getElementById(`obsIn${i}`)) document.getElementById(`obsIn${i}`).value = randIn;
          if (document.getElementById(`obsDep${i}`)) document.getElementById(`obsDep${i}`).value = randDep;
        }
      });
    }

    // Function to fetch form details
    window.getFormDetails = function() {
      const useCertCheck = document.getElementById("useCertNoAsSerial");
      const certNo = document.getElementById("certificateNumber") ? document.getElementById("certificateNumber").value : "";
      const serialNoVal = document.getElementById("serialNo") ? document.getElementById("serialNo").value : "";
      const sizeVal = document.getElementById("size") ? document.getElementById("size").value : "200 mm";
      const sizeNum = parseInt(sizeVal.replace(/\D/g, ''), 10);
      let leastCount = 0.01;
      if (sizeNum === 150) leastCount = 0.01;
      else if (sizeNum === 200) leastCount = 0.02;
      else if (sizeNum === 300) leastCount = 0.02;

      const details = {
        certificateNumber: certNo,
        serialNo: (useCertCheck && useCertCheck.checked) ? certNo : serialNoVal,
        calibrationDate: document.getElementById("calibrationDate") ? document.getElementById("calibrationDate").value.split("-").reverse().join("/") : "",
        siteLocation: document.getElementById("siteLocation") ? document.getElementById("siteLocation").value : "",
        size: sizeVal,
        leastCount: leastCount,
        type: document.getElementById("type") ? document.getElementById("type").value : "DIGITAL",
        make: document.getElementById("make") ? document.getElementById("make").value : "",
        partyName: document.getElementById("partyName") ? document.getElementById("partyName").value : "",
        nextCalibrationDate: document.getElementById("nextCalibrationDate") ? document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/") : "",
        saveentry: `VernierCaliper_${document.getElementById("partyName") ? document.getElementById("partyName").value : ""}_${certNo}`
      };

      for (let i = 1; i <= 5; i++) {
        details[`obsOut${i}`] = document.getElementById(`obsOut${i}`) ? document.getElementById(`obsOut${i}`).value : "";
        details[`obsIn${i}`]  = document.getElementById(`obsIn${i}`)  ? document.getElementById(`obsIn${i}`).value  : "";
        details[`obsDep${i}`] = document.getElementById(`obsDep${i}`) ? document.getElementById(`obsDep${i}`).value : "";
      }

      return details;
    };

    window.addCertificateDetails = function(doc, details) {
      let Yalign = 50;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(22);
      doc.text("CALIBRATION CERTIFICATE FOR VERNIER CALIPER", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
      
      if (details.size) {
        Yalign += 7;
        doc.setFontSize(13);
        doc.text(`RANGE: ${details.size}  |  LEAST COUNT: ${details.leastCount} mm`, doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
      }

      doc.setFontSize(10);
      Yalign += 12;
      doc.text(`DATE:- ${details.calibrationDate}`, 155, Yalign);
      doc.text(`REF NO                            :-     ${details.certificateNumber}`, 14, Yalign);

      Yalign += 7;
      const partyNamePrefix = "NAME OF PARTY           :-     ";
      const prefixWidth = doc.getTextWidth(partyNamePrefix);
      const maxWidth = 180 - prefixWidth;
      const partyNameLines = doc.splitTextToSize(details.partyName || "", maxWidth);

      doc.text(partyNamePrefix + (partyNameLines[0] || ""), 14, Yalign);
      for (let i = 1; i < partyNameLines.length; i++) {
        Yalign += 4.5;
        doc.text(partyNameLines[i], 14 + prefixWidth, Yalign);
      }

      Yalign += 7;
      doc.text(`EQUIPMENT NAME         :-     VERNIER CALIPER ( ${details.type || 'DIGITAL'} )`, 14, Yalign);
      Yalign += 7;
      doc.text(`MAKE / MODEL                :-     ${details.make || 'STANDARD'}`, 14, Yalign);
      Yalign += 7;
      doc.text(`SERIAL NO                     :-     ${details.serialNo || 'N/A'}`, 14, Yalign);
      doc.text(`NEXT DUE DATE:-     ${details.nextCalibrationDate}`, 135, Yalign);

      Yalign += 7;
      const siteLocPrefix = "SITE LOCATION               :-     ";
      const siteLocPrefixWidth = doc.getTextWidth(siteLocPrefix);
      const siteLocMaxWidth = 180 - siteLocPrefixWidth;
      const siteLocLines = doc.splitTextToSize(details.siteLocation || "", siteLocMaxWidth);

      doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign);
      for (let i = 1; i < siteLocLines.length; i++) {
        Yalign += 4.5;
        doc.text(siteLocLines[i], 14 + siteLocPrefixWidth, Yalign);
      }

      // Construct IS 3651 / ISO 13385-1 Standard Calibration Results Table
      const sizeVal = details.size || "200 mm";
      const config = nominalConfigs[sizeVal] || nominalConfigs["200 mm"];
      const rows = [];

      config.steps.forEach((nom, idx) => {
        const i = idx + 1;
        const outStr = details[`obsOut${i}`] || nom.toFixed(2);
        const inStr  = details[`obsIn${i}`]  || nom.toFixed(2);
        const depStr = details[`obsDep${i}`] || nom.toFixed(2);

        const outNum = parseFloat(outStr);
        const inNum  = parseFloat(inStr);
        const depNum = parseFloat(depStr);

        let maxErr = 0.0;
        if (!isNaN(outNum)) maxErr = Math.max(maxErr, Math.abs(outNum - nom));
        if (!isNaN(inNum))  maxErr = Math.max(maxErr, Math.abs(inNum - nom));
        if (!isNaN(depNum)) maxErr = Math.max(maxErr, Math.abs(depNum - nom));

        const errStr = maxErr > 0 ? `+${maxErr.toFixed(2)} mm` : "0.00 mm";

        rows.push([
          String(i),
          `${nom.toFixed(2)} mm`,
          `${outStr} mm`,
          `${inStr} mm`,
          `${depStr} mm`,
          errStr,
          `${config.tolerance} mm`
        ]);
      });

      const tableStartY = Yalign + 10;
      doc.autoTable({
        startY: tableStartY,
        head: [["S. NO.", "NOMINAL VALUE (mm)", "OUTSIDE MEASURED (mm)", "INSIDE MEASURED (mm)", "DEPTH MEASURED (mm)", "ERROR (mm)", "TOLERANCE (± mm)"]],
        body: rows,
        styles: {
          fontSize: 8.5,
          textColor: [0, 0, 0],
          fillColor: [255, 255, 255],
          lineColor: [0, 0, 0],
          lineWidth: 0.2,
          halign: 'center'
        },
        headStyles: {
          fillColor: [255, 255, 255],
          textColor: [0, 0, 0],
          fontStyle: 'bold',
          halign: 'center',
          lineColor: [0, 0, 0],
          lineWidth: 0.2
        },
        alternateRowStyles: {
          fillColor: [255, 255, 255]
        },
        theme: 'grid',
        margin: { left: 14, right: 14 }
      });

      let endY = (doc.lastAutoTable && doc.lastAutoTable.finalY) ? doc.lastAutoTable.finalY + 8 : Yalign + 15;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY);
      doc.setFontSize(9);
      const rem1 = doc.splitTextToSize("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 85);
      let rY = endY + 6;
      for (let line of rem1) { doc.text(line, 14, rY); rY += 4.5; }
      const rem2 = doc.splitTextToSize("• Calibration carried out using standard gauge blocks traceable to National Standards as per IS 3651 / ISO 13385-1.", 85);
      for (let line of rem2) { doc.text(line, 14, rY); rY += 4.5; }

      let sigY = Math.max(rY + 6, 220);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10.5);
      doc.text("FOR, " + (window.PDF_COMPANY_NAME || "SHREEJI INSTRUMENTS"), 150, sigY);
      doc.text("PROPRIETOR", 170, sigY + 10);
    };

    let leastCount = 0.01;
    function updateLeastCount() {
      const el = document.getElementById('size');
      if (!el) return;
      const raw = el.value || '';
      const n = parseInt(raw.replace(/\D/g, ''), 10);
      if (n === 150) leastCount = 0.01;
      else if (n === 200) leastCount = 0.02;
      else if (n === 300) leastCount = 0.03;
      else leastCount = 0.01;
      window.leastCount = leastCount;
    }

    document.addEventListener("DOMContentLoaded", function() {
      updateReadingsForSize();
    });

    window.stickerPdfBlob = null;
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
        { label: "MODEL", value: details.make || "VERNIER CALIPER" },
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