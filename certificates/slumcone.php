<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Slum Cone Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'slumcone' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">SLUMCONE CALIBRATION CERTIFICATE</h2>
    <form id="calibrationForm">
      <div class="title_input_pair">
        <label for="certificateNumber">Certificate No:</label>
        <input type="text" id="certificateNumber" required>
      </div>
        <div class="certificate-status" id="certificateStatus"></div>
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
        <input type="text" id="instrumentType" value="SLUMCONE"  required>
      </div>
      <div class="title_input_pair">
        <label for="make">Make:</label>
        <input type="text" id="make" required>
      </div>
      <div class="title_input_pair">
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" required>
      </div>
      <?php include __DIR__ . '/../includes/certificate_loader.php'; ?>
      
    </form>
  </div>
  <script src="<?= APP_URL ?>/assets/js/general.js?v=<?= filemtime(__DIR__ . '/../assets/js/general.js') ?>"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    window.INSTRUMENT_SLUG = 'slumcone';
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
  <script>
    let stickerPdfBlob = null;

    // Function to fetch form details
    window.getFormDetails = function() {
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        partyName: document.getElementById("partyName").value,
        instrumentType: document.getElementById("instrumentType").value,
        make: document.getElementById("make").value,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
         saveentry: ` ${document.getElementById("certificateNumber").value || "Unknown"} `
      };
    }

    window.addCertificateDetails = function(doc, details) {
      let Yalign = 50;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(25);
      doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });

      doc.setFontSize(12);
      const maxWidth = doc.internal.pageSize.getWidth() - 24; // 12px margin on both sides
      
      const para1 = window.PDF_COMPANY_NAME + ": Calibration laboratory certifies that the instrument has been inspected, tested, and calibrated in accordance with documented procedures using measuring and test equipment traceable to international standards.";
      const lines1 = doc.splitTextToSize(para1, maxWidth);
      doc.text(lines1, 12, Yalign += 15);
      Yalign += (lines1.length * 6); // Add height for paragraph 1
      
      let hori_axis = Yalign + 5;
      const para2 = "From the below test this is to certify that the Slump test apparatus is meeting necessary requirements as per IS: 7320-1974 within the permissible limit.";
      const lines2 = doc.splitTextToSize(para2, maxWidth);
      doc.text(lines2, 12, hori_axis += 9);
      hori_axis += (lines2.length * 6) - 5; // Add height for paragraph 2

      doc.setFontSize(12);
      // Certificate Details
      doc.text(`REF NO                       :     ${details.certificateNumber}`, 14, hori_axis += 10);
      doc.text(`DATE: ${details.calibrationDate}`, 155, hori_axis);
      doc.text(`NAME OF PARTY       :     ${details.partyName}`, 14, hori_axis += 10);
      doc.text(`EQUIPMENT NAME    :     SLUMCONE `, 14, hori_axis += 10);
      doc.text(`SERIAL NO / MAKE    :     ${details.certificateNumber} / ${details.make}`, 14, hori_axis += 10);
      doc.text(`SITE LOCATION          :     ${details.siteLocation}`, 14, hori_axis += 10);
      doc.text(`NEXT DUE DATE         :     ${details.nextCalibrationDate}`, 14, hori_axis += 10);
      doc.text("SPECIFICATIONS:-", doc.internal.pageSize.getWidth() / 2, hori_axis += 10, { align: 'center' });

      const data = [
        ["TOP DIA", "100 + 3.0 - 1.5", " 100.10"],
        ["BOTTOM DIA", "200 + 3.0 - 1.5", "200.80"],
        ["HEIGHT", "300 + 1.5 - 1.5", "300.40"],
      ];

      doc.autoTable({
        head: [['  ', 'AS PER IS(MM)', 'ACTUAL MEASURED(AVG. OF THREE)']],
        body: data,
        startY: hori_axis + 5,
        styles: {
          fontSize: 12,
          textColor: [0, 0, 0],
          lineColor: [87, 86, 85],
          lineWidth: 0.2,
          halign: 'center',  // horizontal align to center
          valign: 'middle',  // vertical align to middle
        },
        headStyles: {
          fontSize: 15,
          fillColor: [255, 255, 255],
          textColor: [0, 0, 0],
          lineColor: [0, 0, 0],
          lineWidth: 0.2,
          halign: 'center',  // horizontal align to center
          valign: 'middle',  // vertical align to middle
        },
        alternateRowStyles: {
          fillColor: [255, 255, 255]
        }
      });
      let tableStartY2 = doc.autoTable.previous.finalY;
      // Add calibrated by
      doc.setFontSize(12);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableStartY2 += 10);
      doc.setFontSize(12);

      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
      doc.text("PROPRIETOR", 170, 247);
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
        { label: "INST. ID NO.", value: details.certificateNumber || "N/A" },
        { label: "EQUIPMENT", value: "SLUMCONE" },
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
