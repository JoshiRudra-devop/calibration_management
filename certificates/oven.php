<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Hot Air Oven Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'oven' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">HOT AIR OVEN CALIBRATION CERTIFICATE</h2>
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
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" required>
      </div>
      <div class="title_input_pair">
        <label for="size">INNER CHAMBER SIZE (DIMENSIONS):</label>
        <input type="text" id="size" placeholder="e.g. 14 X 14 X 14 INCH" required>
      </div>
      <div class="title_input_pair">
        <label for="capacity">CAPACITY / TEMP RANGE:</label>
        <input type="text" id="capacity" placeholder="e.g. 50°C to 300°C / 45 LTRS" required>
      </div>
      <div class="title_input_pair">
        <label for="make">MAKE:</label>
        <input type="text" id="make" placeholder="e.g. SHREEJI / ACCURATE" required>
      </div>

      <!-- Temperature Readings Section -->
      <div style="margin-top: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="margin-top:0; color: #1e293b; margin-bottom: 12px;">Temperature Calibration Readings (°C):</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
          <div class="title_input_pair">
            <label for="ind1">50 °C - Indicated / Master (°C):</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="ind1" placeholder="Ind: 50.2" value="50.2">
              <input type="text" id="mst1" placeholder="Mst: 50.0" value="50.0">
            </div>
          </div>
          <div class="title_input_pair">
            <label for="ind2">100 °C - Indicated / Master (°C):</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="ind2" placeholder="Ind: 100.5" value="100.5">
              <input type="text" id="mst2" placeholder="Mst: 100.0" value="100.0">
            </div>
          </div>
          <div class="title_input_pair">
            <label for="ind3">150 °C - Indicated / Master (°C):</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="ind3" placeholder="Ind: 150.3" value="150.3">
              <input type="text" id="mst3" placeholder="Mst: 150.0" value="150.0">
            </div>
          </div>
          <div class="title_input_pair">
            <label for="ind4">200 °C - Indicated / Master (°C):</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="ind4" placeholder="Ind: 200.4" value="200.4">
              <input type="text" id="mst4" placeholder="Mst: 200.0" value="200.0">
            </div>
          </div>
          <div class="title_input_pair">
            <label for="ind5">250 °C - Indicated / Master (°C):</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="ind5" placeholder="Ind: 250.6" value="250.6">
              <input type="text" id="mst5" placeholder="Mst: 250.0" value="250.0">
            </div>
          </div>
          <div class="title_input_pair">
            <label for="ind6">300 °C - Indicated / Master (°C):</label>
            <div style="display:flex; gap:6px;">
              <input type="text" id="ind6" placeholder="Ind: 300.5" value="300.5">
              <input type="text" id="mst6" placeholder="Mst: 300.0" value="300.0">
            </div>
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

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="<?= APP_URL ?>/assets/js/general-v3.js?v=<?= filemtime(__DIR__ . '/../assets/js/general-v3.js') ?>"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    window.INSTRUMENT_SLUG = 'oven';

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
        partyName: document.getElementById("partyName") ? document.getElementById("partyName").value : "",
        make: document.getElementById("make") ? document.getElementById("make").value : "",
        size: document.getElementById("size") ? document.getElementById("size").value : "",
        capacity: document.getElementById("capacity") ? document.getElementById("capacity").value : "",
        ind1: document.getElementById("ind1") ? document.getElementById("ind1").value : "50.2",
        mst1: document.getElementById("mst1") ? document.getElementById("mst1").value : "50.0",
        ind2: document.getElementById("ind2") ? document.getElementById("ind2").value : "100.5",
        mst2: document.getElementById("mst2") ? document.getElementById("mst2").value : "100.0",
        ind3: document.getElementById("ind3") ? document.getElementById("ind3").value : "150.3",
        mst3: document.getElementById("mst3") ? document.getElementById("mst3").value : "150.0",
        ind4: document.getElementById("ind4") ? document.getElementById("ind4").value : "200.4",
        mst4: document.getElementById("mst4") ? document.getElementById("mst4").value : "200.0",
        ind5: document.getElementById("ind5") ? document.getElementById("ind5").value : "250.6",
        mst5: document.getElementById("mst5") ? document.getElementById("mst5").value : "250.0",
        ind6: document.getElementById("ind6") ? document.getElementById("ind6").value : "300.5",
        mst6: document.getElementById("mst6") ? document.getElementById("mst6").value : "300.0",
        saveentry: `Oven_${document.getElementById("partyName") ? document.getElementById("partyName").value : ""}_${certNo}`
      };
    }

    window.addCertificateDetails = function(doc, details) {
      let Yalign = 50;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(22);
      doc.text("TEST REPORT FOR HOT AIR OVEN", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
      
      if (details.size) {
        Yalign += 7;
        doc.setFontSize(14);
        doc.text(`CHAMBER SIZE: ${details.size}`, doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
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
      doc.text(`EQUIPMENT NAME     :-     ELECTRICAL HOT AIR OVEN (${details.size || ''})`, 14, Yalign);
      Yalign += 8;
      doc.text(`CAPACITY & MAKE    :-     ${details.capacity || ''} / ${details.make || ''}`, 14, Yalign);
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

      // Calibration Table: Option A (Standard Thermal Table)
      // Headers: SR. NO. | SET TEMP. (°C) | INDICATED TEMP. (°C) | MASTER TEMP. (°C) | ERROR (°C)
      Yalign += 12;
      const startX = 14;
      const colWidths = [20, 40, 42, 42, 38]; // Total = 182mm
      const rowHeight = 7.5;

      const headers = ["SR. NO.", "SET TEMP. (°C)", "INDICATED TEMP. (°C)", "MASTER TEMP. (°C)", "ERROR (°C)"];

      // Draw Header Row
      doc.setFont("helvetica", "bold");
      doc.setFontSize(9.5);
      let curX = startX;
      for (let k = 0; k < headers.length; k++) {
        doc.rect(curX, Yalign, colWidths[k], 9);
        doc.text(headers[k], curX + (colWidths[k] / 2), Yalign + 6, { align: 'center' });
        curX += colWidths[k];
      }

      // Helper to compute thermal error
      function getThermalRow(sr, setTemp, indVal, mstVal) {
        const indNum = parseFloat(indVal);
        const mstNum = parseFloat(mstVal);
        let errStr = "0.0 °C";
        if (!isNaN(indNum) && !isNaN(mstNum)) {
          const diff = (indNum - mstNum).toFixed(1);
          errStr = diff > 0 ? `+${diff} °C` : `${diff} °C`;
        }
        const indStr = indVal.toString().includes("°C") ? indVal : `${indVal} °C`;
        const mstStr = mstVal.toString().includes("°C") ? mstVal : `${mstVal} °C`;
        return [sr.toString(), `${setTemp} °C`, indStr, mstStr, errStr];
      }

      const rowsData = [
        getThermalRow(1, 50, details.ind1 || "50.2", details.mst1 || "50.0"),
        getThermalRow(2, 100, details.ind2 || "100.5", details.mst2 || "100.0"),
        getThermalRow(3, 150, details.ind3 || "150.3", details.mst3 || "150.0"),
        getThermalRow(4, 200, details.ind4 || "200.4", details.mst4 || "200.0"),
        getThermalRow(5, 250, details.ind5 || "250.6", details.mst5 || "250.0"),
        getThermalRow(6, 300, details.ind6 || "300.5", details.mst6 || "300.0")
      ];

      // Draw Data Rows
      let curY = Yalign + 9;
      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);

      for (let r = 0; r < rowsData.length; r++) {
        curX = startX;
        for (let c = 0; c < rowsData[r].length; c++) {
          doc.rect(curX, curY, colWidths[c], rowHeight);
          doc.text(rowsData[r][c], curX + (colWidths[c] / 2), curY + 5.2, { align: 'center' });
          curX += colWidths[c];
        }
        curY += rowHeight;
      }

      // Footer & Remarks at standard grid positions
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, 206);
      doc.setFontSize(9);
      const rem1 = doc.splitTextToSize("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 85);
      let rY = 212;
      for (let line of rem1) { doc.text(line, 14, rY); rY += 4.5; }
      const rem2 = doc.splitTextToSize("• Temperature calibration carried out using standard master digital temperature indicator with RTD/Thermocouple sensors.", 85);
      for (let line of rem2) { doc.text(line, 14, rY); rY += 4.5; }

      doc.setFont("helvetica", "bold");
      doc.setFontSize(10.5);
      doc.text("FOR, " + (window.PDF_COMPANY_NAME || "SHREEJI INSTRUMENTS"), 150, 228);
      doc.text("PROPRIETOR", 170, 248);
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
        { label: "MODEL", value: details.make || "HOT AIR OVEN" },
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