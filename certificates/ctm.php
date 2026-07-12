<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'CTM Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'ctm' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<style>
  .READING-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    width: 100%;
    margin-bottom: 20px;
  }
  .READING-container .title_input_pair {
    flex: 1 1 calc(50% - 15px);
    display: flex;
    flex-direction: column;
    margin-bottom: 0;
  }
  .READING-container label {
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-mid);
    font-size: 0.9rem;
  }
  .READING-container input {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 1rem;
  }
</style>
<div class="container">
    <h2 class="centered">CUBE TESTING MACHINE CALIBRATION CERTIFICATE</h2>
    <form id="calibrationForm">
      <div class="form-group">
        <label for="certificateNumber">Certificate No:</label>
        <input type="text" id="certificateNumber" required>
      </div>
      <div class="form-row">
        <div class="form-group">
            <label for="calibrationDate">Date of Calibration:</label>
            <input type="date" id="calibrationDate" onchange="calculateNextDate()" required>
        </div>
        <div class="form-group">
            <label for="nextCalibrationDate">Next Suggested Date:</label>
            <input type="date" id="nextCalibrationDate" required>
        </div>
      </div>
      <div class="form-group">
        <label for="partyName">Company Name:</label>
        <input type="text" id="partyName" required>
      </div>
      <div class="form-group">
        <label for="operated">Type of Machine:</label>
        <select id="operated">
          <option value="ELECTRICAL OPERATED">Electrical Operated</option>
          <option value="HAND OPERATED">HAND Operated</option>
        </select>
      </div>
      <div class="form-group">
        <label for="make">Make:</label>
        <input type="text" id="make" required>
      </div>  
      <div class="form-group">
        <label for="serialNo">Serial No:</label>
        <input type="text" id="serialNo" required>
      </div>
      <div class="form-group">
        <label for="capacity">Capacity:</label>
        <select id="capacity">
          <option value="1000 KN">1000 KN</option>
          <option value="1200 KN">1200 KN</option>
          <option value="1500 KN">1500 KN</option>
          <option value="2000 KN">2000 KN</option>
        </select> 
      </div>
      <div class="form-group">
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" required>
      </div>
      <div class="form-group">
        <label for="ring">PROVIRING WANT TO SELECT:</label>
        <select id="ring" onchange="showInputBoxes()">
          <option value="">SELECT PROVIRING</option>
          <option value='1000KN'>1000 KN</option>
          <option value='2000KN'>2000 KN</option>
          <option value='2000KN new'>2000 KN NEW</option>
        </select>
      </div>
      <div class="READING-container">
            <div id="reading1000" class="READING-container" style="display: none;">
                <div class="title_input_pair">
                  <label for="i1">100</label>
                  <input type="text" id="i1" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i2">200</label>
                  <input type="text" id="i2" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i3">300</label>
                  <input type="text" id="i3" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i4">400</label>
                  <input type="text" id="i4" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i5">500</label>
                  <input type="text" id="i5" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i6">600</label>
                  <input type="text" id="i6" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i7">700</label>
                  <input type="text" id="i7" maxlength="7" >
                </div>  
                <div class="title_input_pair">
                  <label for="i8">800</label>
                  <input type="text" id="i8" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i9">900</label>
                  <input type="text" id="i9" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i10"> 1000</label>
                  <input type="text" id="i10" maxlength="7" >
                </div>
            </div>  
            <div id="reading2000" class="READING-container" style="display: none;">
                <div class="title_input_pair">
                  <label for="i21">200</label>
                  <input type="text" id="i21" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i22">400</label>
                  <input type="text" id="i22" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i23">600</label>
                  <input type="text" id="i23" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i24">800</label>
                  <input type="text" id="i24" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i25">1000</label>
                  <input type="text" id="i25" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i26">1200</label>
                  <input type="text" id="i26" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i27">1400</label>
                  <input type="text" id="i27" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i28">1600</label>
                  <input type="text" id="i28" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i29">1800</label>
                  <input type="text" id="i29" maxlength="7" >
                </div>
                <div class="title_input_pair">
                  <label for="i210"> 2000</label>
                  <input type="text" id="i210" maxlength="7" >
                </div>
            </div>
      </div>
      <?php include __DIR__ . '/../includes/certificate_loader.php'; ?>
    </form>
  </div>

  <script src="<?= APP_URL ?>/assets/js/general.js?v=1.8"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    const INSTRUMENT_SLUG = 'ctm';
    let stickerPdfBlob = null;
    let pdfSaved = false;

    function showInputBoxes() {
      var option = document.getElementById("ring").value;
      var option1Inputs = document.getElementById("reading1000");
      var option2Inputs = document.getElementById("reading2000");
      option1Inputs.style.display = "none";
      option2Inputs.style.display = "none";
      if (option === "1000KN") {
          option1Inputs.style.display = "flex";
      } else if (option === "2000KN" || option === "2000KN new") {
          option2Inputs.style.display = "flex";
      }
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
      const certificateNumber = document.getElementById("certificateNumber").value;
      const capacity = document.getElementById("capacity").value;
      const calibrationDateRaw = document.getElementById("calibrationDate").value;
      const calibrationDate = calibrationDateRaw.split("-").reverse().join("/");
      const nextCalibrationDateRaw = document.getElementById("nextCalibrationDate").value;
      const nextCalibrationDate = nextCalibrationDateRaw.split("-").reverse().join("/");
      const serialNo = document.getElementById("serialNo").value;
      
      const primaryBlue = [19, 52, 165];
      const accentRed = [228, 34, 21];
      doc.setDrawColor(...primaryBlue);
      doc.setLineWidth(3);
      doc.rect(5, 5, width - 10, height - 10);
      
      const logoImg = new Image();
      logoImg.src = "../assets/images/logo.png";
      await new Promise(resolve => { logoImg.onload = resolve; logoImg.onerror = resolve; });
      if (logoImg.width) {
        doc.addImage(logoImg, "PNG", 20, 7, 7, 7);
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
      const rowHeight = 13;
      const labelWidth = tableWidth * 0.4;
      const tableData = [
        { label: "SERIAL NO.", value: serialNo || "N/A" },
        { label: "MODEL", value: capacity || "N/A" },
        { label: "CALIB. DATE", value: calibrationDate || "N/A" },
        { label: "NEXT DATE", value: nextCalibrationDate || "N/A" },
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
      if (dockDownloadBtn) dockDownloadBtn.style.display = "block";
      frame.scrollIntoView({ behavior: 'smooth' });
    }

    async function downloadSticker() {
      if (!stickerPdfBlob) {
        alert('Please generate the sticker first!');
        return;
      }
      const certificateNumber = document.getElementById("certificateNumber").value;
      await savePDFWithLocation(stickerPdfBlob, `CTM_Sticker_${certificateNumber}.pdf`);
    }

    function getFormDetails() {
      const inputs1000 = [];
      for (let i = 1; i <= 10; i++) {
        const el = document.getElementById(`i${i}`);
        inputs1000.push(el ? el.value : '');
      }
      const inputs2000 = [];
      for (let i = 1; i <= 10; i++) {
        const el = document.getElementById(`i2${i}`);
        inputs2000.push(el ? el.value : '');
      }
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        partyName: document.getElementById("partyName").value,
        operated: document.getElementById("operated").value,
        make: document.getElementById("make").value,
        ring: document.getElementById("ring").value,
        serialNo: document.getElementById("serialNo").value,
        capacity: document.getElementById("capacity").value,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
        inputs1000: inputs1000,
        inputs2000: inputs2000,
        saveentry: `CTM_${document.getElementById("make").value}_${document.getElementById("serialNo").value || "Unknown"}`
      };
    }

    function addCertificateDetails(doc, details) {
      doc.setFont("helvetica", "bold");
      doc.setFontSize(25);
      doc.text("CALIBRATION CERTIFICATE", doc.internal.pageSize.getWidth() / 2, 50, { align: "center" });
      doc.setFontSize(10);

      doc.setFont("helvetica", "bold");
      doc.setFontSize(12);
      doc.text(`DATE:-${details.calibrationDate}`, 160, 60);
      doc.text(`REF NO                       :-    ${details.certificateNumber}`, 14, 60);
      doc.text(`NAME OF PARTY       :-    ${details.partyName}`, 14, 70);
      doc.text(`EQUIPMENT NAME    :-    CUBE TESTING MACHINE ( ${details.operated} )`, 14, 80);
      doc.text(`CAPACITY  / MAKE    :-    ${details.capacity}  /  ${details.make}`, 14, 90);
      doc.text(`SERIAL NO                  :-    ${details.serialNo}`, 14, 100);
      doc.text(`NEXT DUE DATE:-${details.nextCalibrationDate}`, 140, 100);
      doc.text(`SITE LOCATION         :-    ${details.siteLocation}`, 14, 110); 

      doc.setFontSize(10);
      doc.setFont("helvetica", "bold");
      let RING = String(details.ring);

      if(RING === "1000KN"){
        doc.text(`CALIBRATION INSTRUMENT 1000KN`, 14, 120);  
        doc.text(`PROVING RING NO:1000KN 065 IS 4169:2014`, 14, 125);  
        doc.text(`CALIBRATED BY : NATIONAL COUNCIL FOR CEMENT AND BUILDING MATERIALS`, 14, 130);  
        doc.setFont("helvetica", "bold");
        doc.setFontSize(11);
        const startX = 13;
        const startY = 142;
        const cellWidth = 30;
        const cellHeight = 7;

        doc.text(`IN DIVISIONS`, 16, 141);
        doc.text(`KN`, 174, 141);

        const fixedTexts = ["DEFLECTION", "LOAD IN KN", "1st SET IN KN", "2nd SET IN KN", "3rd SET IN KN", "AVERAGE IN"];

        for (let k = 0; k < 6; k++) {
          const x = startX + k * cellWidth;
          doc.rect(x, 132, cellWidth, 10);
          doc.text(fixedTexts[k], x + 2, 137);
        }

        const fixedValuesColumn1 = ["79.1", "155.2", "232.3", "308.1", "384.4", "460.1","536.7", "613.4", "689.7", "766.1"];
        const fixedValuesColumn2 = ["100", "200", "300", "400", "500", "600", "700", "800", "900", "1000"];

        for (let i = 0; i < 10; i++) {
          for (let j = 0; j < 6; j++) {
            const x = startX + j * cellWidth;
            const y = startY + i * cellHeight;
            doc.rect(x, y, cellWidth, cellHeight);

            let textValue = "";
            if (j === 0) textValue = fixedValuesColumn1[i];
            else if (j === 1) textValue = fixedValuesColumn2[i];
            else textValue = details.inputs1000[i] || "";

            const textWidth = doc.getTextWidth(textValue);
            const centeredX = x + (cellWidth - textWidth) / 2;
            doc.text(textValue, centeredX, y + 6);
          }
        }
      }
      else if(RING === "2000KN"){
        doc.text(`CALIBRATION INSTRUMENT 2000KN`, 14, 120);  
        doc.text(`PROVING RING NO:2000KN 094 IS 4169:2014`, 14, 125);  
        doc.text(`CALIBRATED BY : NATIONAL COUNCIL FOR CEMENT AND BUILDING MATERIALS`, 14, 130);  
        doc.setFont("helvetica", "bold");
        doc.setFontSize(11);
        const startX = 13;
        const startY = 142;
        const cellWidth = 30;
        const cellHeight = 7;

        doc.text(`IN DIVISIONS`, 16, 141);
        doc.text(`KN`, 174, 141);

        const fixedTexts = ["DEFLECTION", "LOAD IN KN", "1st SET IN KN", "2nd SET IN KN", "3rd SET IN KN", "AVERAGE IN"];

        for (let k = 0; k < 6; k++) {
          const x = startX + k * cellWidth;
          doc.rect(x, 132, cellWidth, 10);
          doc.text(fixedTexts[k], x + 2, 137);
        }

        const fixedValuesColumn1 = ["84.1", "168.6", "254.7", "342.1", "429.1", "513.9", "600.9", "689.2", "776.7", "865.8"];
        const fixedValuesColumn2 = ["200", "400", "600", "800", "1000", "1200", "1400", "1600", "1800", "2000"];

        for (let i = 0; i < 10; i++) {
          for (let j = 0; j < 6; j++) {
            const x = startX + j * cellWidth;
            const y = startY + i * cellHeight;
            doc.rect(x, y, cellWidth, cellHeight);

            let textValue = "";
            if (j === 0) textValue = fixedValuesColumn1[i];
            else if (j === 1) textValue = fixedValuesColumn2[i];
            else textValue = details.inputs2000[i] || "";

            const textWidth = doc.getTextWidth(textValue);
            const centeredX = x + (cellWidth - textWidth) / 2;
            doc.text(textValue, centeredX, y + 6);
          }
        }
      }
      else if(RING === "2000KN new"){
        doc.text(`CALIBRATION INSTRUMENT 2000KN`, 14, 120);  
        doc.text(`PROVING RING NO:2000KN 381 IS 4169:2014`, 14, 125);  
        doc.text(`CALIBRATED BY : NATIONAL COUNCIL FOR CEMENT AND BUILDING MATERIALS`, 14, 130);  
        doc.setFont("helvetica", "bold");
        doc.setFontSize(11);
        const startX = 13;
        const startY = 142;
        const cellWidth = 30;
        const cellHeight = 7;

        doc.text(`IN DIVISIONS`, 16, 141);
        doc.text(`KN`, 174, 141);

        const fixedTexts = ["DEFLECTION", "LOAD IN KN", "1st SET IN KN", "2nd SET IN KN", "3rd SET IN KN", "AVERAGE IN"];

        for (let k = 0; k < 6; k++) {
          const x = startX + k * cellWidth;
          doc.rect(x, 132, cellWidth, 10);
          doc.text(fixedTexts[k], x + 2, 137);
        }

        const fixedValuesColumn1 = ["73.1","145.1","215.2","284.7","356.1","427.4","498.1","569.4","641.3","714.1"];
        const fixedValuesColumn2 = ["200", "400", "600", "800", "1000", "1200", "1400", "1600", "1800", "2000"];

        for (let i = 0; i < 10; i++) {
          for (let j = 0; j < 6; j++) {
            const x = startX + j * cellWidth;
            const y = startY + i * cellHeight;
            doc.rect(x, y, cellWidth, cellHeight);

            let textValue = "";
            if (j === 0) textValue = fixedValuesColumn1[i];
            else if (j === 1) textValue = fixedValuesColumn2[i];
            else textValue = details.inputs2000[i] || "";

            const textWidth = doc.getTextWidth(textValue);
            const centeredX = x + (cellWidth - textWidth) / 2;
            doc.text(textValue, centeredX, y + 6);
          }
        }
      }
      
      doc.text(`CALIBRATION BY      :-   YOGESH BHAI`, 14, 215); 
      doc.setFontSize(12);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
      doc.text("PROPRIETOR", 170, 245);
    }
  </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
