<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Weight Balance Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'weight_balance' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">WEIGHT BALANCE CALIBRATION CERTIFICATE</h2>
    <form id="calibrationForm">
      <div class="title_input_pair">
        <label for="certificateNumber">Certificate No:</label>
        <input type="text" id="certificateNumber" required>
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
        <label for="serialNo">Serial No:</label>
        <input type="text" id="serialNo" required>
      </div>
      <div class="title_input_pair">
        <label for="capacity">Capacity:</label>
        <select id="capacity" required>
           <option value="">Select Size</option>
           <option value="600 g">600 g</option>
           <option value="6 KG">6 KG</option>
           <option value="5 KG">5 KG</option>
           <option value="10 KG">10 KG</option>
           <option value="20 KG">20 KG</option>
           <option value="30 KG">30 KG</option>
           <option value="50 KG">50 KG</option>
           <option value="100 KG">100 KG</option>
           <option value="200 KG">200 KG</option>
        </select>
      </div>
      <div class="title_input_pair">
        <label for="make">Make:</label>
        <input type="text" id="make" required>
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
    window.INSTRUMENT_SLUG = 'weight_balance';
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
  <script>
    window.stickerPdfBlob = null;
    
    // Table data for each capacity
    const capacityTables = {
      "600 g": [
        ["01", "50", "50.0 ", "±0.1 g"],
        ["02", "100", "100.0", ' " '],
        ["03", "200", "200.0", ' " '],
        ["04", "400", "399.9", ' " '],
        ["05", "600", "599.9", ' " ']
      ],
      "5 KG": [
        ["01", "0.01", "10.0", "±0.12 g"],
        ["02", "0.05", "50.0", '"'],
        ["03", "0.1", "100.0", "±6 g"],
        ["04", "0.5", "500.0", '"'],
        ["05", "1.0", "1000.0", '"'],
        ["06", "5.0", "5000.0", '"']
      ],
      "30 KG": [
        ["01", "0.05", "0.050", "±0.6 g"],
        ["02", "0.1", "0.100", '"'],
        ["03", "0.5", "0.501", "±6 g"],
        ["04", "1.0", "0.999", '"'],
        ["05", "2.0", "1.998", '"'],
        ["06", "5.0", "4.998", '"'],
        ["07", "10.0", "9.996", '"'],
        ["08", "20.0", "19.995", '"'],
        ["09", "30.0", "29.995", '"']
      ],
      "100 KG":[
        ["01", "0.5", "0.500", "±6 g"],
        ["02", "1.0", "1.000", '"'],
        ["03", "2.0", "2.000", '"'],
        ["04", "5.0", "5.000", '"'],
        ["05", "10.0", "10.000", '"'],
        ["06", "20.0", "19.998", '"'],
        ["07", "40.0", "39.998", '"'],
        ["08", "50.0", "49.996", '"'],
        ["09", "100.0", "99.994", '"']
      ],
      "200 KG":[
        ["01", "0.5", "0.500", "±6 g"],
        ["02", "1.0", "1.000", '"'],
        ["03", "2.0", "2.000", '"'],
        ["04", "5.0", "5.000", '"'],
        ["05", "10.0", "10.000", '"'],
        ["06", "20.0", "19.998", '"'],
        ["07", "40.0", "39.998", '"'],
        ["08", "50.0", "49.996", '"'],
         ["09", "100.0", "99.994", '"'],
         ["10", "200.0", "199.994", '"']
      ] ,
      "6 KG": [
        ["01", "0.01", "10.0", "±0.12 g"],
        ["02", "0.05", "50.0", '"'],
        ["03", "0.1", "100.0", "±6 g"],
        ["04", "0.5", "500.0", '"'],
        ["05", "1.0", "1000.0", '"'],
        ["06", "5.0", "5000.0", '"'],
        ["07", "6.0", "5999.9", '"']
      ],
      "10 KG": [
        ["01", "0.05", "10.0", "0.12 g"],
        ["02", "0.01", "50.0", '"'],
        ["03", "0.1", "100.0", '"'],
        ["04", "0.5", "500.0", "6 g"],
        ["05", "1.0", "1000.0", '"'],
        ["06", "5.0", "4999.9", '"'],
        ["07", "10.0", "9999.9", '"'],
      ],
      "20 KG": [
        ["01", "0.05", "0.050", "±0.6 g"],
        ["02", "0.1", "0.100", '"'],
        ["03", "0.5", "0.501", "±6 g"],
        ["04", "1.0", "0.999", '"'],
        ["05", "2.0", "1.998", '"'],
        ["06", "5.0", "4.998", '"'],
        ["07", "10.0", "9.996", '"'],
        ["08", "20.0", "19.995", '"']
      ],
      "50 KG": [
        ["01", "0.5", "0.500", "±6 g"],
        ["02", "1.0", "1.000", '"'],
        ["03", "2.0", "2.000", '"'],
        ["04", "5.0", "5.000", '"'],
        ["05", "10.0", "10.000", '"'],
        ["06", "20.0", "19.998", '"'],
        ["07", "40.0", "39.998", '"'],
        ["08", "50.0", "49.996", '"']
      ],
    };

    // Get form details
    window.getFormDetails = function() {
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        partyName: document.getElementById("partyName").value,
        serialNo: document.getElementById("serialNo").value,
        make: document.getElementById("make").value,
        capacity: document.getElementById("capacity").value,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
      };
    }
  window.addCertificateDetails = function(doc, details) {
      let Yalign = 46;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(25);
      doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });

      doc.setFontSize(10);
      doc.text(`DATE:-${details.calibrationDate}`, 160, 55);
      doc.text(`REF NO                        :-     ${details.certificateNumber}`, 14, 55);

      let y = 64;
      const partyPrefix = "NAME OF PARTY        :-     ";
      const partyPrefixWidth = doc.getTextWidth(partyPrefix);
      const partyLines = doc.splitTextToSize(details.partyName || "", 180 - partyPrefixWidth);
      doc.text(partyPrefix + (partyLines[0] || ""), 14, y);
      for (let i = 1; i < partyLines.length; i++) {
        y += 4.5;
        doc.text(partyLines[i], 14 + partyPrefixWidth, y);
      }

      y += 9;
      doc.text(`EQUIPMENT NAME     :-     WEIGHT BALANCE`, 14, y);
      y += 9;
      doc.text(`CAPACITY & MAKE    :-     ${details.capacity} & ${details.make}`, 14, y);
      y += 9;
      doc.text(`SR NO                          :-     ${details.serialNo}`, 14, y);
      doc.text(`NEXT DUE DATE        :-     ${details.nextCalibrationDate}`, 140, y);

      y += 9;
      const siteLocPrefix = "SITE LOCATION          :-     ";
      const siteLocPrefixWidth = doc.getTextWidth(siteLocPrefix);
      const siteLocLines = doc.splitTextToSize(details.siteLocation || "", 180 - siteLocPrefixWidth);
      doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, y);
      for (let i = 1; i < siteLocLines.length; i++) {
        y += 4.5;
        doc.text(siteLocLines[i], 14 + siteLocPrefixWidth, y);
      }

      // Get table data for selected capacity
      const data = capacityTables[details.capacity] || [];
      let tableStartY = y + 6;

      if (data.length > 0) {
        let tableHead;
        if (details.capacity === "600 g") {
          tableHead = [['SR.NO', 'APPLIED WEIGHT IN (g)', 'ACTUAL VALUE IN (g)', 'UNCERTANITY AT 95% C.L. (COVARAGE FACTOR k=2)']];
        } else {
          tableHead = [['SR.NO', 'APPLIED WEIGHT IN (KG)', 'ACTUAL VALUE IN (g/kg)', 'UNCERTANITY AT 95% C.L. (COVARAGE FACTOR k=2)']];
        }

        // Dynamically adjust table sizes and padding based on available space / row count
        const numRows = data.length;
        let cellPadding, minCellHeight, bodyFontSize, headFontSize, headMinHeight;
        if (numRows <= 5) {
          bodyFontSize = 10.5;
          headFontSize = 11;
          cellPadding = 3.0;
          minCellHeight = 8.5;
          headMinHeight = 11.0;
        } else if (numRows <= 7) {
          bodyFontSize = 10;
          headFontSize = 10.5;
          cellPadding = 2.2;
          minCellHeight = 6.5;
          headMinHeight = 9.5;
        } else if (numRows <= 9) {
          bodyFontSize = 9.5;
          headFontSize = 10;
          cellPadding = 1.5;
          minCellHeight = 5.2;
          headMinHeight = 8.0;
        } else {
          bodyFontSize = 9;
          headFontSize = 9.5;
          cellPadding = 1.2;
          minCellHeight = 4.5;
          headMinHeight = 7.0;
        }

        doc.autoTable({
          head: tableHead,
          body: data,
          startY: tableStartY + 2,
          margin: { left: 14, right: 14 },
          styles: { 
            fontSize: bodyFontSize,
            lineColor: [0, 0, 0],
            textColor: [0, 0, 0],
            lineWidth: 0.3,
            halign: 'center',
            valign: 'middle',
            cellPadding: cellPadding,
            minCellHeight: minCellHeight,
            fontStyle: 'bold'
          },
          headStyles: {
            fontSize: headFontSize,
            fillColor: [255, 255, 255],
            textColor: [0, 0, 0],
            lineColor: [0, 0, 0],
            lineWidth: 0.3,
            halign: 'center',
            valign: 'middle',
            cellPadding: cellPadding + 0.5,
            minCellHeight: headMinHeight
          },
          columnStyles: {
            0: { cellWidth: 20 },
            1: { cellWidth: 45 },
            2: { cellWidth: 45 },
            3: { cellWidth: 'auto' }
          },
          alternateRowStyles: {
            fillColor: [255, 255, 255]
          }
        });
      }

      let endY = doc.lastAutoTable ? doc.lastAutoTable.finalY + 3 : 180;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, 206);
      doc.setFontSize(9);
      const rem1 = doc.splitTextToSize("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 85);
      let rY = 212;
      for (let line of rem1) { doc.text(line, 14, rY); rY += 4.5; }
      const rem2 = doc.splitTextToSize("• This certificate refers to the value obtained at the time of calibration.", 85);
      for (let line of rem2) { doc.text(line, 14, rY); rY += 4.5; }
      doc.setFont("helvetica", "bold");
      doc.setFontSize(10.5);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 150, 228);
      doc.text("PROPRIETOR", 170, 248);
    }
    // --- Sticker logic ---
    async function generateInfoSticker() {
      const { jsPDF } = window.jspdf;
      const width = 60 * 2.83465;
      const height = 30 * 2.83465;
      const doc = new jsPDF({
        orientation: "landscape",
        unit: "pt",
        format: [width, height]
      });
      const details = getFormDetails();
      const primaryBlue = [19, 52, 165];
      const accentRed = [228, 34, 21];
      doc.setDrawColor(...primaryBlue);
      doc.setLineWidth(3);
      doc.rect(5, 5, width - 10, height - 10);
      const logoImg = new Image();
      logoImg.src = "../assets/images/logo.png";
      await new Promise(resolve => { logoImg.onload = resolve; logoImg.onerror = resolve; });
      if (logoImg.width) {
        doc.addImage(logoImg, "PNG", 8, 4, 12, 16);
      }
      doc.setFont("times", "bold");
      doc.setFontSize(8);
      doc.setTextColor(...accentRed);
      doc.text(window.PDF_COMPANY_NAME, 32, 13);
      doc.setFont("times", "normal");
      doc.setFontSize(4);
      doc.setTextColor(...primaryBlue);
      doc.text("SALES • SERVICE • REPAIRING • CALIBRATIONS", width / 2, 18, { align: "center" });
      const tableLeft = 15;
      const tableTop = 20;
      const tableWidth = width - 30;
      const rowHeight = 14;
      const labelWidth = tableWidth * 0.4;
      const tableData = [
        { label: "INST. ID NO.", value: details.serialNo || "N/A" },
        { label: "CAPACITY", value: details.capacity || "N/A" },
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
        doc.text(row.value, tableLeft + labelWidth + 5, valueY, { baseline: 'middle' });
      });
      window.stickerPdfBlob = doc.output('blob');
      const pdfURL = URL.createObjectURL(stickerPdfBlob);
      const frame = document.getElementById("stickerPreviewFrame");
      frame.src = pdfURL;
      frame.style.display = "block";
      const dockDownloadBtn = document.querySelector('.side-dock #downloadStickerBtn');
      dockDownloadBtn.style.display = "block";
      frame.scrollIntoView({ behavior: 'smooth' });
    }
    
    async function downloadSticker() {
      if (!window.stickerPdfBlob) {
        alert('Please generate the sticker first!');
        return;
      }
      const details = getFormDetails();
      const fileName = `InfoSticker_${details.serialNo || 'Unknown'}_${details.capacity || 'Unknown'}.pdf`;
      await savePDFWithLocation(window.stickerPdfBlob, fileName);
    }
 
    window.generateInfoSticker = generateInfoSticker;
    window.downloadSticker = downloadSticker;
</script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
