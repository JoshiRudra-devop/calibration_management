<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Cone Penetrometer Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'cone_penetro' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
      <h2 class="centered">CONE PENETROMETER CALIBRATION CERTIFICATE</h2>
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
          <label for="make">Make:</label>
          <input type="text" id="make" required>
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
<script src="<?= APP_URL ?>/assets/js/general.js?v=<?= filemtime(__DIR__ . '/../assets/js/general.js') ?>"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    window.INSTRUMENT_SLUG = 'cone_penetro';
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    let stickerPdfBlob = null;
    window.getFormDetails = function() {
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        partyName: document.getElementById("partyName").value,
        make: document.getElementById("make").value,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
      };
    }
    
    window.addCertificateDetails = function(doc, details)
     {
      let Yalign=53;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(25);
      doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });

      doc.setFontSize(12);
      doc.text(window.PDF_COMPANY_NAME + ": Calibration laboratory certifies that the instrument has been inspected,", 12, Yalign+=15);
      doc.text("tested,and calibrated in accordance with documented procedures using measuring and test", 12, Yalign+=7);
      doc.text("equipment traceable to international standards.", 12, Yalign+=7);

      doc.setFontSize(15);
      doc.text(`DATE: ${details.calibrationDate}`, 155, Yalign+=15);
      doc.text(`REF NO                         :     ${details.certificateNumber}`, 14, Yalign);
      doc.text(`NAME OF PARTY         :     ${details.partyName}`, 14, Yalign+=15);
      doc.text(`EQUIPMENT NAME      :     CONE PENETROMETER`, 14, Yalign+=15);
      doc.text(`SERIAL NO / MAKE      :     ${details.certificateNumber} / ${details.make}`, 14, Yalign+=15);
       // --- Site Location with wrapping (only value, not prefix) ---
      const siteLocPrefix = "SITE LOCATION          :-     ";
      const prefixWidth = doc.getTextWidth(siteLocPrefix);
      const maxWidth = 180 - prefixWidth; // adjust as needed according to your layout
      const siteLocLines = doc.splitTextToSize(details.siteLocation, maxWidth);
      doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign += 10);
      for (let i = 1; i < siteLocLines.length; i++) {
        doc.text(siteLocLines[i], 14 + prefixWidth , Yalign +=10);
      }
      Yalign += ((siteLocLines.length - 1)+5) ;
      doc.text(`NEXT DUE DATE         :     ${details.nextCalibrationDate}`, 14, Yalign+=15);
      doc.text(`CALIBRATION BY       :     YOGESH BHAI`, 14, Yalign+=15);

      doc.setFontSize(12);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
      doc.text("PROPRIETOR", 170, 245);
    }
   
    // --- Sticker logic ---
    async function generateInfoSticker() {
      const { jsPDF } = window.jspdf;
      const width = 40 * 2.83465;
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
      logoImg.src = "logo.jpeg";
      await new Promise(resolve => { logoImg.onload = resolve; logoImg.onerror = resolve; });
      if (logoImg.width) {
        doc.addImage(logoImg, "JPEG", 20, 7, 5, 7);
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
        { label: "INST. ID NO.", value: details.certificateNumber || "N/A" },
        { label: "MAKE", value: details.make || "N/A" },
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
      stickerPdfBlob = doc.output('blob');
      const pdfURL = URL.createObjectURL(stickerPdfBlob);
      const frame = document.getElementById("stickerPreviewFrame");
      frame.src = pdfURL;
      frame.style.display = "block";
      const dockDownloadBtn = document.querySelector('.side-dock #downloadStickerBtn');
      dockDownloadBtn.style.display = "block";
      frame.scrollIntoView({ behavior: 'smooth' });
    }
    async function downloadSticker() {
      if (!stickerPdfBlob) {
        alert('Please generate the sticker first!');
        return;
      }
      const details = getFormDetails();
      const fileName = `InfoSticker_${details.certificateNumber || 'Unknown'}_${details.make || 'Unknown'}.pdf`;
      await savePDFWithLocation(stickerPdfBlob, fileName);
    }
   
  </script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
