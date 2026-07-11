<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Rapid Moisture Analyzer Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'rapid_moisture' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">RAPID MOISTURE METER CALIBRATION CERTIFICATE</h2>
    <form id="calibrationForm">
      <div class="title_input_pair">
        <label for="certificateNumber">Certificate No:</label>
        <input type="text" class=".error-input" id="certificateNumber" required>
      </div>
      <div id="certificateNumberError" class="error-message">This certificate number already exists!</div>
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
        <label for="instrumentType">Instrument Name:</label>
        <input type="text" id="instrumentType" value="RAPID MOISTURE" readonly required>
      </div>
      <div class="title_input_pair">
        <label for="make">Make:</label>
        <input type="text" id="make" required>
      </div>  
      <div class="title_input_pair">
        <label for="serialNo">Serial No:</label>
        <input type="text" id="serialNo" required>
      </div>
      <!-- <div class="title_input_pair">
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" required>
      </div> -->
      <?php include __DIR__ . '/../includes/certificate_loader.php'; ?>
      
    </form>
  </div>
  <script src="<?= APP_URL ?>/assets/js/general.js?v=1.8"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    const INSTRUMENT_SLUG = 'rapid_moisture';
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
  <script>
    let stickerPdfBlob = null;

    function getFormDetails() {
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        partyName: document.getElementById("partyName").value,
        make: document.getElementById("make").value,
        serialNo: document.getElementById("serialNo").value,
        instrumentType:document.getElementById("instrumentType").value ,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
      };
    }

    function addCertificateDetails(doc, details) {
      let Yalign = 48;

      doc.setFont("helvetica", "bold");
      doc.setFontSize(25);
      doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
     
      doc.setFontSize(10);
      doc.setFont("helvetica", "bold");   
      doc.text(`DATE:${details.calibrationDate}`, 155, Yalign+=5);
      doc.text(`REF No                          :     SE-25-26 / SI-${details.certificateNumber}`, 14, Yalign)

      doc.setFontSize(10);
      doc.text(`NAME OF PARTY          :     ${details.partyName}`, 14, Yalign+=5);
      doc.text(`EQUIPMENT NAME       :     RAPID MOISTURE METER`, 14, Yalign+=5);
      doc.text(`RANGE                           :     0-50 %`, 14, Yalign+=5);
      doc.text(`SERIAL NO / MAKE       :     ${details.serialNo} / ${details.make}`, 14, Yalign+=5);
      doc.text(`NEXT DUE DATE           :     ${details.nextCalibrationDate}`, 14, Yalign+=5);

      doc.setFontSize(9);
      doc.text("TABLE TO CONVERT RAPID MOISTURE METER READING TO PERCENTAGE MOISTURE CONTENT ON DRY WEIGHT BASIS.", 14, Yalign+=5);

      const head = [
        [
          "Rapid Moisture Meter Reading %",
          "Moisture Content on Dry Weight Basis %",
          "Rapid Moisture Meter Reading %",
          "Moisture Content on Dry Weight Basis %"
        ]
      ];

      const data = [
        [ "01", "1",  "26", "27" ],
        [ "02", "2",  "27", "28" ],
        [ "03", "3",  "28", "29" ],
        [ "04", "4",  "29", "30" ],
        [ "05", "5",  "30", "32" ],
        [ "06", "6",  "31", "33" ],
        [ "07", "7",  "32", "34" ],
        [ "08", "8",  "33", "35" ],
        [ "09", "9",  "34", "36" ],
        [ "10", "10", "35", "37" ],
        [ "11", "12", "36", "38" ],
        [ "12", "13", "37", "39" ],
        [ "13", "14", "38", "40" ],
        [ "14", "15", "39", "41" ],
        [ "15", "16", "40", "42" ],
        [ "16", "17", "41", "43" ],
        [ "17", "18", "42", "44" ],
        [ "18", "19", "43", "45" ],
        [ "19", "20", "44", "46" ],
        [ "20", "21", "45", "47" ],
        [ "21", "22", "46", "48" ],
        [ "22", "23", "47", "49" ],
        [ "23", "24", "48", "50" ],
        [ "24", "25", "49", "51" ],
        [ "25", "26", "50", "52" ],
      ];

      doc.autoTable({
        head: head,
        body: data,
        startY: Yalign+=3,
        theme: 'grid',
        styles: {
            lineColor: [0, 0, 0],
            textColor: [0, 0, 0],
            lineWidth: 0.2,
            halign: 'center',
            valign: 'middle',
            fontSize: 9,
            fontStyle: 'bold',
            cellPadding: 0.5,
            lineHeight: 0.8
        },
        headStyles: {
            fontSize: 9,
            fillColor: [255, 255, 255],
            textColor: [0, 0, 0],
            lineColor: [0, 0, 0],
            lineWidth: 0.2,
            halign: 'center',
            valign: 'middle'
        }
      });

      let finalY = doc.lastAutoTable.finalY+5;
      doc.setFontSize(10);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, finalY);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, finalY+=5);
      doc.text("• NOTE: This calibration report refers to", 14, finalY+=5);
      doc.text("  'Oven Drying Method'.", 14, finalY+=5);

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
        { label: "INST. ID NO.", value: details.serialNo || "N/A" },
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
      const fileName = `InfoSticker_${details.serialNo || 'Unknown'}_${details.make || 'Unknown'}.pdf`;
      await savePDFWithLocation(stickerPdfBlob, fileName);
    }
  </script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
