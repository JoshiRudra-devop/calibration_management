<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'General Format Calibration';
$activePage = 'certificate';
$instrumentSlug = 'general';
include __DIR__ . '/../includes/header.php';

// Get instrument type configuration
$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = ? LIMIT 1");
$stmt->execute([$instrumentSlug]);
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

    <?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
      <h2 class="centered">CALIBRATION CERTIFICATE</h2>
      <form id="calibrationForm">
        <div class="title_input_pair">
          <label for="certificateNumber">Certificate No:</label>
          <input type="text" id="certificateNumber" required>
        </div>
        <div class="title_input_pair">
          <label for="calibrationDate">Date of Calibration:</label>
          <input type="date" id="calibrationDate" onchange="calculateNextDate()" required>
        </div>
        <div class="title_input_pair">
          <label for="nextCalibrationDate">Next Suggested Date:</label>
          <input type="date" id="nextCalibrationDate" required>
        </div>
        <div class="title_input_pair">
          <label for="partyName">Company Name:</label>
          <input type="text" id="partyName" required>
        </div>
        <div class="title_input_pair">
          <label for="equipmentType">Instrument Name:</label>
          <input type="text" id="equipmentType" required>
        </div>
        <div class="title_input_pair">
          <label for="make">Make:</label>
          <input type="text" id="make" required>
        </div>  
        <div class="title_input_pair">
          <label for="serialNo">Serial No:</label>
          <input type="text" id="serialNo" required>
        </div>
        <div class="title_input_pair">
          <label for="capacity">Capacity:</label>
          <input type="text" id="capacity" required>
        </div>
        <div class="title_input_pair">
          <label for="siteLocation">Site Location:</label>
          <input type="text" id="siteLocation" required>
        </div>
      <?php include __DIR__ . '/../includes/certificate_loader.php'; ?>
      
      </form>
    </div>

    <script src="<?= APP_URL ?>/assets/js/general-v3.js?v=<?= filemtime(__DIR__ . '/../assets/js/general-v3.js') ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
      let stickerPdfBlob = null;
      const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
      window.INSTRUMENT_SLUG = 'general';

      window.getFormDetails = function() {
        return {
          certificateNumber: document.getElementById("certificateNumber").value,
          calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
          siteLocation: document.getElementById("siteLocation").value,
          partyName: document.getElementById("partyName").value,
          equipmentType: document.getElementById("equipmentType").value,
          make: document.getElementById("make").value,
          serialNo: document.getElementById("serialNo").value,
          capacity: document.getElementById("capacity").value,
          nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
          saveentry: `General_${document.getElementById("equipmentType").value}_${document.getElementById("serialNo").value || "Unknown"}`
        };
      }

      window.addCertificateDetails = function(doc, details) {
        let Yalign = 50;
        doc.setFont("helvetica", "bold");
        doc.setFontSize(25);
        doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });

        doc.setFontSize(12);
        doc.text(window.PDF_COMPANY_NAME + ": Calibration laboratory certifies that the instrument has been inspected,", 12, Yalign += 10);
        doc.text("tested,and calibrated in accordance with documented procedures using measuring and test", 12, Yalign += 7);
        doc.text("equipment traceable to international standards.", 12, Yalign += 7);

        doc.setFontSize(15);
        // Certificate Details
        doc.text(`DATE: ${details.calibrationDate}`, 140, Yalign += 15);
        doc.text(`REF NO                         :     ${details.certificateNumber}`, 14, Yalign);
        doc.text(`NAME OF PARTY         :     ${details.partyName}`, 14, Yalign += 15);
        doc.text(`EQUIPMENT NAME      :     ${details.equipmentType}`, 14, Yalign += 15);
        doc.text(`SERIAL NO / MAKE      :     ${details.serialNo} / ${details.make}`, 14, Yalign += 15);
        doc.text(`SITE LOCATION           :     ${details.siteLocation}`, 14, Yalign += 15);
        doc.text(`CAPACITY                    :     ${details.capacity}`, 14, Yalign += 15);
        doc.text(`NEXT DUE DATE          :     ${details.nextCalibrationDate}`, 14, Yalign += 15);
        doc.text(`CALIBRATION BY        :     YOGESH BHAI`, 14, Yalign += 15);

        doc.setFontSize(12);
        doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
        doc.text("PROPRIETOR", 170, 245);
      }

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
        
        const tableLeft = 15;
        const tableTop = 20;
        const tableWidth = width - 30;
        const rowHeight = 14;
        const labelWidth = tableWidth * 0.4;
        const tableData = [
          { label: "SERIAL NO.", value: details.serialNo || "N/A" },
          { label: "MODEL", value: details.equipmentType || "N/A" },
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
        const fileName = `InfoSticker_${details.serialNo || 'Unknown'}_${details.equipmentType || 'Unknown'}.pdf`;
        await savePDFWithLocation(stickerPdfBlob, fileName);
      }
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
