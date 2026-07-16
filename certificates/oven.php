<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Oven Calibration';
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
        <label for="size">SIZE:</label>
        <input type="text" id="size" required>
      </div>
      <div class="title_input_pair">
        <label for="capacity">Capacity:</label>
        <input type="text" id="capacity" required>
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
    <script src="<?= APP_URL ?>/assets/js/general.js?v=<?= filemtime(__DIR__ . '/../assets/js/general.js') ?>"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    const INSTRUMENT_SLUG = 'oven';
  </script>
      <script>
    
    // Function to fetch form details
    window.getFormDetails = function() {
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        partyName: document.getElementById("partyName").value,
        // equipmentType: document.getElementById("equipmentType").value,
        make: document.getElementById("make").value,
        size: document.getElementById("size").value,
        capacity: document.getElementById("capacity").value,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
      };
    }
    window.addCertificateDetails = function(doc, details)
{
  let Yalign = 50;
  doc.setFont("helvetica", "bold");
  doc.setFontSize(25);
  doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
  
  // Spacing
  doc.setFontSize(12);
  doc.text(`DATE:-${details.calibrationDate}`, 155, Yalign += 10);
  doc.text(`REF NO                        :-     ${details.certificateNumber}`, 14, Yalign);
  
  // --- Party Name with wrapping ---
  const partyNamePrefix = "NAME OF PARTY        :-     ";
  const prefixWidth = doc.getTextWidth(partyNamePrefix);
  const maxWidth = 180 - prefixWidth;
  const partyNameLines = doc.splitTextToSize(details.partyName, maxWidth);
  
  doc.text(partyNamePrefix + (partyNameLines[0] || ""), 14, Yalign += 10);
  for (let i = 1; i < partyNameLines.length; i++) {
    doc.text(partyNameLines[i], 14 + prefixWidth, Yalign += 5);
  }
 
  
  doc.text(`EQUIPMENT NAME     :-     ELECTRICAL HOT AIR OVEN(${details.size})`, 14, Yalign += 10);
  doc.text(`CAPACITY & MAKE    :-     ${details.capacity} & ${details.make}`, 14, Yalign += 10);
  doc.text(`SR NO                          :-     ${details.certificateNumber}`, 14, Yalign += 10);
  doc.text(`NEXT DUE DATE        :-     ${details.nextCalibrationDate}`, 14, Yalign += 10);
  
  // --- Site Location with wrapping ---
  const siteLocPrefix = "SITE LOCATION          :-     ";
  const siteLocPrefixWidth = doc.getTextWidth(siteLocPrefix);
  const siteLocMaxWidth = 180 - siteLocPrefixWidth;
  const siteLocLines = doc.splitTextToSize(details.siteLocation, siteLocMaxWidth);
  
  doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign += 10);
  for (let i = 1; i < siteLocLines.length; i++) {
    doc.text(siteLocLines[i], 14 + siteLocPrefixWidth, Yalign += 5);
  }

  
  const tableStartY = Yalign += 10;
  const data = [
    ["1", " 50", "50"],
    ["2", " 100", "100"],
    ["3", " 150", "150"],
    ["4", " 200", "200"],
    ["5", " 250", "250"],
    ["6", " 300", "300"]
  ];
  
  doc.autoTable({
    head: [['SR.NO', 'STANDARD TEMPERATURE', 'STANDARD TEMPERATURE BY 1 St BUCKET "A"']],
    body: data,
    startY: tableStartY + 10,
    styles: {
      fontSize: 12,
      lineColor: [0, 0, 0],
      textColor: [0, 0, 0],
      lineWidth: 0.2,
      halign: 'center',
      valign: 'middle',
    },
    headStyles: {
      fontSize: 15,
      fillColor: [255, 255, 255],
      textColor: [0, 0, 0],
      lineColor: [0, 0, 0],
      lineWidth: 0.2,
      halign: 'center',
      valign: 'middle',
    },
    alternateRowStyles: {
      fillColor: [255, 255, 255]
    }
  });
  
  let tableStartY2 = doc.autoTable.previous.finalY;
  // Add calibrated by
  doc.setFontSize(12);
  doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableStartY2 += 10);
  // Add footer
  doc.setFont("helvetica", "bold");
  doc.setFontSize(12);
  doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
  doc.text("PROPRIETOR", 170, 245);
}

     </script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
