<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'AUTO LEVEL Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

// Get instrument type configuration
$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'autolevel' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();

$instrumentId = $instrument['id'] ?? null;
?>

    <?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
      <h2 class="centered">AUTO LEVEL CALIBRATION CERTIFICATE</h2>
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
        <label for="instrumentType">Instrument Name:</label>
        <input type="text" id="instrumentType" value="AUTOLEVEL" readonly required>
      </div>
        <div class="title_input_pair">
          <label for="make">Make:</label>
          <input type="text" id="make" required>
        </div>  
        <div class="title_input_pair">
          <label for="modelNo">MODEL No:</label>
          <input type="text" id="modelNo" required>
        </div>
        <div class="title_input_pair">
          <label for="serialNo">Serial No:</label>
          <input type="text" id="serialNo" required>
        </div>
      <?php include __DIR__ . '/../includes/certificate_loader.php'; ?>
      
      </form>
    </div>

<script src="<?= APP_URL ?>/assets/js/general.js?v=<?= filemtime(__DIR__ . '/../assets/js/general.js') ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
      let stickerPdfBlob = null;
      const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
      const INSTRUMENT_SLUG = 'autolevel';

      function formatDateToWords(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
      }

      window.getFormDetails = function() {
        return {
          certificateNumber: document.getElementById("certificateNumber").value,
          calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
          partyName: document.getElementById("partyName").value,
          make: document.getElementById("make").value,
          instrumentType: document.getElementById("instrumentType").value,
          serialNo: document.getElementById("serialNo").value,
          modelNo: document.getElementById("modelNo").value,
          nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
          saveentry: `${document.getElementById("make").value} ${document.getElementById("modelNo").value}  ${document.getElementById("serialNo").value || "Unknown"}`
        };
      }

      window.addCertificateDetails = function(doc, details) {     
        let Yalign=48;
        const saveentry = `${details.make} ${details.modelNo || "Unknown"} ${details.serialNo || "Unknown"}`;
        const formattedCalibrationDate = formatDateToWords(document.getElementById("calibrationDate").value);
        const formattedNextDate = formatDateToWords(document.getElementById("nextCalibrationDate").value);

        doc.setFontSize(15);
        doc.setFont("helvetica", "bold");   
        doc.text(`DATE:${details.calibrationDate}`, 155, Yalign);
        doc.text(`REF No:${details.certificateNumber}`, 10, Yalign)
        doc.setFont("helvetica", "bold");
        doc.setFontSize(25);
        doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign+=15, { align: 'center' });
        doc.setFontSize(15);
        doc.text(`FOR , ${details.make}  AUTO LEVEL`, doc.internal.pageSize.getWidth() / 2, Yalign+=7, { align: 'center' });
        doc.text(`MODLE : ${details.modelNo}  ,SR NO:- ${details.serialNo}`, doc.internal.pageSize.getWidth() / 2, Yalign+=7, { align: 'center' });
        doc.text(`NAME OF PARTY         :     ${details.partyName}`, 14, Yalign+=11);
        doc.text(`EQUIPMENT NAME      :     AUTO LEVEL`, 14, Yalign+=9);
        doc.text(`MAKE                            :     ${details.make}`, 14, Yalign+=9);
        doc.text(`MODEL NO                   :     ${details.modelNo}`, 14, Yalign+=9);
        doc.text(`SERIAL NO                   :     ${details.serialNo}`, 14, Yalign+=9);
        doc.text(`DATE                             :     ${details.calibrationDate}`, 14, Yalign+=9);
        doc.text(`NEXT DUE DATE          :     ${details.nextCalibrationDate}`, 14, Yalign+=9);
        doc.setFontSize(12);
        doc.text(`This is to certify that ${details.make} Automatic Level-${details.modelNo} Serial No ${details.serialNo} Been Checked`, 14, Yalign+=10);
        doc.text(` By us as under One year warranty.`, 14, Yalign+=5);
        doc.text(`1. Level was kept At a distance of 20 meter from staff-A and 30 meter from Staff-B, Which`, 14, Yalign+=10);
        doc.text(`were Pre-fixed at same level in our workshop (permanent bench-mark of same elevation).`, 14, Yalign+=5);
        doc.text(`2. Reading of Staff-A was taken as 1,600`, 14, Yalign+=10);
        doc.text(`3. Reading of Staff-B was taken as 1,600.`, 14, Yalign+=10);
        doc.text(`Hence error is NIL (Within the tolerance Level). Therefore, instrument is found free from`, 14, Yalign+=10);
        doc.text(`collimation error as of Date.`, 14, Yalign+=5);
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
          { label: "MODEL", value: details.modelNo || "N/A" },
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
        const fileName = `InfoSticker_${details.serialNo || 'Unknown'}_${details.modelNo || 'Unknown'}.pdf`;
        await savePDFWithLocation(stickerPdfBlob, fileName);
      }
      
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
