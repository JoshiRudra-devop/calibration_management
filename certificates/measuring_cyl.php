<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Measuring Cylinder Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'measuring_cyl' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">MEASURING CYLINDER CALIBRATION CERTIFICATE</h2>
    <form id="calibrationForm">
      <div class="title_input_pair">
        <label for="certificateNumber">Certificate No:</label>
        <input type="text" id="certificateNumber" required>
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
        <label for="type">TYPE:</label>
        <select id="type">
          <option value="PP">PP</option>
          <option value="Glass">GLASS</option>
        </select>
      </div> 
      <div class="title_input_pair">
        <label for="size">SIZE:</label>
        <select id="size">
          <option value="100 ml">100 ml</option>
          <option value="250 ml">250 ml</option>
          <option value="500 ml">500 ml</option>
          <option value="1000 ml">1000 ml</option>
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

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="<?= APP_URL ?>/assets/js/general-v3.js?v=<?= filemtime(__DIR__ . '/../assets/js/general-v3.js') ?>"></script>
  <script>
    const INSTRUMENT_ID = <?= json_encode($instrumentId) ?>;
    window.INSTRUMENT_SLUG = 'measuring_cyl';
  </script>
  <script>
    let stickerPdfBlob = null;
      
    // Function to fetch form details
    window.getFormDetails = function() {
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        size: document.getElementById("size").value,
        type: document.getElementById("type").value,
        partyName: document.getElementById("partyName").value,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
      };
    }

window.addCertificateDetails = function(doc, details)
{
  let Yalign = 50;
  doc.setFont("helvetica", "bold");
  doc.setFontSize(25);
  doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
  doc.setFontSize(12);
  doc.text(window.PDF_COMPANY_NAME + ": Calibration laboratory certifies that the instrument has been inspected,", 12, Yalign+=10);
  doc.text("tested,and calibrated in accordance with documented procedures using measuring and test", 12, Yalign+=7);
  doc.text("equipment traceable to international standards.", 12, Yalign+=7);
  doc.setFontSize(15);
  
  // Certificate Details
  doc.text(`DATE: ${details.calibrationDate}`, 140, Yalign+=15);
  doc.text(`REF NO                         :     ${details.certificateNumber}`, 14, Yalign);
  
  // --- Party Name with wrapping (only value, not prefix) ---
  const partyNamePrefix = "NAME OF PARTY         :     ";
  const prefixWidth = doc.getTextWidth(partyNamePrefix);
  const maxWidth = 180 - prefixWidth;
  const partyNameLines = doc.splitTextToSize(details.partyName, maxWidth);
  
  doc.text(partyNamePrefix + (partyNameLines[0] || ""), 14, Yalign += 15);
  for (let i = 1; i < partyNameLines.length; i++) {
    doc.text(partyNameLines[i], 14 + prefixWidth, Yalign += 7);
  }
  doc.text(`EQUIPMENT NAME      :     MEASURING CYLINDER`, 14, Yalign+=15);
  doc.text(`SIZE                                 :     ${details.size} /   (${details.type})`, 14, Yalign+=15);
  doc.text(`SERIAL NO                  :     ${details.certificateNumber}`, 14, Yalign+=15);
  
  // --- Site Location with wrapping (only value, not prefix) ---
  const siteLocPrefix = "SITE LOCATION          :     ";
  const siteLocPrefixWidth = doc.getTextWidth(siteLocPrefix);
  const siteLocMaxWidth = 180 - siteLocPrefixWidth;
  const siteLocLines = doc.splitTextToSize(details.siteLocation, siteLocMaxWidth);
  
  doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign += 15);
  for (let i = 1; i < siteLocLines.length; i++) {
    doc.text(siteLocLines[i], 14 + siteLocPrefixWidth, Yalign += 7);
  }

  doc.text(`NEXT DUE DATE          :     ${details.nextCalibrationDate}`, 14, Yalign+=15);
  doc.text(`CALIBRATION BY        :     YOGESH BHAI`, 14, Yalign+=15);
  doc.setFontSize(12);
  doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
  doc.text("PROPRIETOR", 170, 245);
}
  </script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
