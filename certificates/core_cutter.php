<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Core Cutter Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'core_cutter' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">CORE CUTTER  CALIBRATION CERTIFICATE</h2>
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
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" value="1" min="1" required>
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
    window.INSTRUMENT_SLUG = 'core_cutter';

    function incrementCertificateNumber(baseCertNo, increment) {
      if (!baseCertNo || increment === 0) return baseCertNo;
      let val = baseCertNo;
      for (let i = 0; i < increment; i++) {
        const match = val.match(/^(.*?)(\d+)$/);
        if (match) {
          const prefix = match[1];
          const numStr = match[2];
          const nextNum = parseInt(numStr, 10) + 1;
          const paddedNum = String(nextNum).padStart(numStr.length, '0');
          val = prefix + paddedNum;
        } else {
          break;
        }
      }
      return val;
    }

    function toggleCertNoAsSerial() {
      const useCertCheck = document.getElementById("useCertNoAsSerial");
      const certNumInput = document.getElementById("certificateNumber");
      const serialNoInput = document.getElementById("serialNo");
      
      if (!useCertCheck || !serialNoInput) return;
      
      if (useCertCheck.checked) {
        if (certNumInput) {
          serialNoInput.value = certNumInput.value;
        }
        serialNoInput.readOnly = true;
        serialNoInput.style.backgroundColor = "#e2e8f0";
      } else {
        serialNoInput.readOnly = false;
        serialNoInput.style.backgroundColor = "";
      }
    }

    document.addEventListener("DOMContentLoaded", function() {
      const certNumInput = document.getElementById("certificateNumber");
      if (certNumInput) {
        certNumInput.addEventListener("input", function() {
          const useCertCheck = document.getElementById("useCertNoAsSerial");
          if (useCertCheck && useCertCheck.checked) toggleCertNoAsSerial();
        });
        certNumInput.addEventListener("change", function() {
          const useCertCheck = document.getElementById("useCertNoAsSerial");
          if (useCertCheck && useCertCheck.checked) toggleCertNoAsSerial();
        });
      }
    });
  </script>
  <script>
    let stickerPdfBlob = null;
      
    // Function to fetch form details
    window.getFormDetails = function() {
      const useCertCheck = document.getElementById("useCertNoAsSerial");
      const certNo = document.getElementById("certificateNumber").value;
      const serialNoVal = document.getElementById("serialNo") ? document.getElementById("serialNo").value : "";

      return {
        certificateNumber: certNo,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        partyName: document.getElementById("partyName").value,
        quantity: document.getElementById("quantity") ? (parseInt(document.getElementById("quantity").value) || 1) : 1,
        serialNo: (useCertCheck && useCertCheck.checked) ? certNo : serialNoVal,
        useCertNoAsSerial: useCertCheck ? useCertCheck.checked : false,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
      };
    }

window.addCertificateDetails = function(doc, details)
{
  details = details || {};
  let qty = parseInt(details.quantity);
  if (isNaN(qty) || qty <= 0) qty = 1;

  for (let page = 0; page < qty; page++) {
    if (page > 0) doc.addPage();

    let pageCertNo = incrementCertificateNumber(details.certificateNumber || "", page);
    let pageSerialNo = details.useCertNoAsSerial ? pageCertNo : incrementCertificateNumber(details.serialNo || "", page);

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
    doc.text(`REF NO                         :     ${pageCertNo}`, 14, Yalign);
    
    // --- Party Name with wrapping (only value, not prefix) ---
    const partyNamePrefix = "NAME OF PARTY         :     ";
    const prefixWidth = doc.getTextWidth(partyNamePrefix);
    const maxWidth = 180 - prefixWidth;
    const partyNameLines = doc.splitTextToSize(details.partyName, maxWidth);
    
    doc.text(partyNamePrefix + (partyNameLines[0] || ""), 14, Yalign += 15);
    for (let i = 1; i < partyNameLines.length; i++) {
      doc.text(partyNameLines[i], 14 + prefixWidth, Yalign += 7);
    }
    doc.text(`EQUIPMENT NAME      :     CORE  CUTTER  AS PAR IS : 2720  `, 14, Yalign+=15);
    doc.text(`SIZE                               :    130MM  X 100MM `, 14, Yalign+=15);
    doc.text(`SERIAL NO                   :     ${pageSerialNo}`, 14, Yalign+=15);
    
    // --- Site Location with wrapping (only value, not prefix) ---
    const siteLocPrefix = "SITE LOCATION           :     ";
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
      doc.setFont("helvetica", "bold");
      doc.setFontSize(8.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, 212);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, 217);

    doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
    doc.text("PROPRIETOR", 170, 245);
  }
}
  </script>


<?php include __DIR__ . '/../includes/footer.php'; ?>
