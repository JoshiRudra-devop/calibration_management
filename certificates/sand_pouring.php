<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Sand Pouring Density Apparatus Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'sand_pouring' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">SAND POURING CYLINDER CALIBRATION CERTIFICATE</h2>
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
        <label for="size">SIZE:</label>
        <select id="size">
          <option value="100 MM">100 </option>
          <option value="150 MM">150 </option>
          <option value="200 MM">200 </option>
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

<script src="<?= APP_URL ?>/assets/js/general-v3.js?v=<?= filemtime(__DIR__ . '/../assets/js/general-v3.js') ?>"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    window.INSTRUMENT_SLUG = 'sand_pouring';
  </script>
  <script>
    window.stickerPdfBlob = null;
      
    // Function to fetch form details
    window.getFormDetails = function() {
      const useCertCheck = document.getElementById("useCertNoAsSerial");
      const certNo = document.getElementById("certificateNumber") ? document.getElementById("certificateNumber").value : "";
      const serialNoVal = document.getElementById("serialNo") ? document.getElementById("serialNo").value : "";
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        serialNo: (useCertCheck && useCertCheck.checked) ? certNo : serialNoVal,

        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        size: document.getElementById("size").value,
        partyName: document.getElementById("partyName").value,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
      };
    }

window.addCertificateDetails = function(doc, details)
{
  details = details || {};
  let Yalign = 46;
  doc.setFont("helvetica", "bold");
  doc.setFontSize(24);
  doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
  
  doc.setFontSize(10.5);
  doc.setFont("helvetica", "normal");
  const headerPara = (window.PDF_COMPANY_NAME || "SHREEJI INSTRUMENTS") + ": Calibration laboratory certifies that the instrument has been inspected, tested, and calibrated in accordance with documented procedures using measuring and test equipment traceable to international standards.";
  const headerLines = doc.splitTextToSize(headerPara, 182);
  doc.text(headerLines, 14, Yalign += 9);
  Yalign += (headerLines.length * 4.5);

  doc.setFont("helvetica", "bold");
  doc.setFontSize(11);
  
  // Certificate Details
  Yalign += 8;
  doc.text(`DATE: ${details.calibrationDate || ''}`, 155, Yalign);
  doc.text(`REF NO                         :     ${details.certificateNumber || ''}`, 14, Yalign);
  
  // --- Party Name with wrapping ---
  const partyNamePrefix = "NAME OF PARTY         :     ";
  const prefixWidth = doc.getTextWidth(partyNamePrefix);
  const maxWidth = 180 - prefixWidth;
  const partyNameLines = doc.splitTextToSize(details.partyName || "", maxWidth);
  
  Yalign += 8.5;
  doc.text(partyNamePrefix + (partyNameLines[0] || ""), 14, Yalign);
  for (let i = 1; i < partyNameLines.length; i++) {
    Yalign += 5;
    doc.text(partyNameLines[i], 14 + prefixWidth, Yalign);
  }
  
  Yalign += 8.5;
  doc.text(`EQUIPMENT NAME      :     SAND POURING CYLINDER`, 14, Yalign);
  Yalign += 8.5;
  doc.text(`SIZE                               :     ${details.size || '150 MM'}`, 14, Yalign);
  Yalign += 8.5;
  doc.text(`SERIAL NO                    :     ${details.serialNo || ''}`, 14, Yalign);
  
  // --- Site Location with wrapping ---
  const siteLocPrefix = "SITE LOCATION            :     ";
  const siteLocPrefixWidth = doc.getTextWidth(siteLocPrefix);
  const siteLocMaxWidth = 180 - siteLocPrefixWidth;
  const siteLocLines = doc.splitTextToSize(details.siteLocation || "", siteLocMaxWidth);
  
  Yalign += 8.5;
  doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign);
  for (let i = 1; i < siteLocLines.length; i++) {
    Yalign += 5;
    doc.text(siteLocLines[i], 14 + siteLocPrefixWidth, Yalign);
  }

  Yalign += 8.5;
  doc.text(`NEXT DUE DATE          :     ${details.nextCalibrationDate || ''}`, 14, Yalign);

  // Calibration Observation Table (IS: 2720 Part 28 Static Readings)
  Yalign += 8.5;
  doc.setFont("helvetica", "bold");
  doc.setFontSize(10);
  doc.text("CALIBRATION OBSERVATION (AS PER IS: 2720 PART-28)", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });

  const sizeStr = (details.size || '150 MM').toUpperCase();
  const actualDia = sizeStr.includes('100') ? '100.10 MM' : (sizeStr.includes('200') ? '200.15 MM' : '150.10 MM');
  
  const calibData = [
    [ '1', 'Internal Diameter of Pouring Cylinder', sizeStr, actualDia ],
    [ '2', 'Height of Pouring Cylinder', '600 ± 2.0 MM', '600.20 MM' ],
    [ '3', 'Internal Diameter of Calibrating Container', sizeStr, actualDia ],
    [ '4', 'Volume of Calibrating Container', sizeStr.includes('100') ? '1000 cm³' : '2250 cm³', sizeStr.includes('100') ? '1000.40 cm³' : '2250.50 cm³' ],
    [ '5', 'Mean Weight of Sand in Cone', 'As per Calibration', '465.00 g' ],
    [ '6', 'Bulk Density of Standard Sand', '1.42 ± 0.05 g/cm³', '1.425 g/cm³' ]
  ];

  doc.autoTable({
    head: [['S. NO.', 'PARAMETER / SPECIFICATION', 'AS PER IS REQUIREMENT', 'ACTUAL MEASURED']],
    body: calibData,
    startY: Yalign + 4,
    styles: {
      fontSize: 8.5,
      cellPadding: 2,
      textColor: [0, 0, 0],
      lineColor: [0, 0, 0],
      lineWidth: 0.2,
      halign: 'center',
      valign: 'middle'
    },
    headStyles: {
      fontSize: 9,
      cellPadding: 2.5,
      fontStyle: 'bold',
      fillColor: [255, 255, 255],
      textColor: [0, 0, 0],
      lineColor: [0, 0, 0],
      lineWidth: 0.2,
      halign: 'center',
      valign: 'middle'
    },
    alternateRowStyles: {
      fillColor: [255, 255, 255]
    },
    theme: 'grid',
    margin: { left: 14, right: 14 }
  });

  doc.setFont("helvetica", "bold");
  doc.setFontSize(10);
  doc.text("CALIBRATED BY          :     YOGESH B JOSHI", 14, 206);
  doc.setFontSize(9);
  doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, 212);
  doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, 217);
  doc.setFont("helvetica", "bold");
  doc.setFontSize(10.5);
  doc.text("FOR, " + (window.PDF_COMPANY_NAME || "SHREEJI INSTRUMENTS"), 150, 236);
  doc.text("PROPRIETOR", 170, 254);
}
  
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
        doc.text(window.PDF_COMPANY_NAME, 30, 13);

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
          { label: "MODEL", value: details.equipmentType || details.modelNo || "N/A" },
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
        const pdfURL = URL.createObjectURL(stickerPdfBlob);
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