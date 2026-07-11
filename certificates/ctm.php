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

// Load proving ring standards from DB
$ringRows = $db->query(
    "SELECT ring_key, ring_label, ring_no, load_steps, deflection_steps
     FROM ctm_proving_rings WHERE active = 1 ORDER BY sort_order"
)->fetchAll();
$ringData = [];
foreach ($ringRows as $r) {
    $ringData[$r['ring_key']] = [
        'label'       => $r['ring_label'],
        'ring_no'     => $r['ring_no'],
        'loads'       => json_decode($r['load_steps'],       true),
        'deflections' => json_decode($r['deflection_steps'], true),
    ];
}
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">CUBE TESTING MACHINE CALIBRATION CERTIFICATE</h2>
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
        <label for="operated">Type of Machine:</label>
        <select  id="operated">
          <option value="ELECTRICAL OPERATED">Electrical Operated</option>
          <option value="HAND OPERATED">HAND Operated</option>
        </select>
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
        <select id="capacity">
          <option value="1000 KN">1000 KN</option>
          <option value="1200 KN">1200 KN</option>
          <option value="1500 KN">1500 KN</option>
          <option value="2000 KN">2000 KN</option>
        </select> 
      </div>
      <div class="title_input_pair">
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" required>
      </div>
      <div class="title_input_pair">
        <label for="ring">PROVIRING WANT TO SELECT:</label>
        <select id="ring" onchange="showInputBoxes()">
          <option value="">SELECT PROVIRING</option>
          <?php foreach ($ringData as $key => $ring): ?>
          <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($ring['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="READING-container">
            <div id="reading1000" class="reading1000" style="display: none;">
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
            <div id="reading2000" class="reading2000" style="display: none;">
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
    const CTM_RING_DATA = <?= json_encode($ringData, JSON_UNESCAPED_UNICODE) ?>;
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

    function addImg(doc, details) {
      if (!doc) return;
      const img = new Image();
      img.src = '../assets/images/footer.jpeg';
      doc.addImage(img, 'PNG', 0, 255, 210, 27);

      const img3 = new Image();
      img3.src = '../assets/images/sign.jpeg';
      doc.addImage(img3, 'PNG', 160, 232, 40, 10);

      const img1 = new Image();
      img1.src = '../assets/images/stamp.jpeg';
      doc.addImage(img1, 'PNG', 100, 217, 35, 35);

      const img2 = new Image();
      img2.src = '../assets/images/header.jpeg';
      doc.addImage(img2, 'PNG', 3, 3, 210, 30);
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
      const RING = String(details.ring);
      const ringMeta = CTM_RING_DATA[RING];

      if (ringMeta) {
        const capacityLabel = RING.startsWith('1000') ? '1000KN' : '2000KN';
        const userInputs = RING.startsWith('1000') ? details.inputs1000 : details.inputs2000;

        doc.text(`CALIBRATION INSTRUMENT ${capacityLabel}`, 14, 120);
        doc.text(`PROVING RING NO:${ringMeta.ring_no}`, 14, 125);
        doc.text(`CALIBRATED BY : NATIONAL COUNCIL FOR CEMENT AND BUILDING MATERIALS`, 14, 130);

        doc.setFont("helvetica", "bold");
        doc.setFontSize(11);
        const startX  = 13;
        const startY  = 142;
        const cellWidth  = 30;
        const cellHeight = 7;

        doc.text(`IN DIVISIONS`, 16, 141);
        doc.text(`KN`, 174, 141);

        const fixedTexts = ["DEFLECTION", "LOAD IN KN", "1st SET IN KN", "2nd SET IN KN", "3rd SET IN KN", "AVERAGE IN"];
        for (let k = 0; k < 6; k++) {
          const x = startX + k * cellWidth;
          doc.rect(x, 132, cellWidth, 10);
          doc.text(fixedTexts[k], x + 2, 137);
        }

        for (let i = 0; i < ringMeta.deflections.length; i++) {
          for (let j = 0; j < 6; j++) {
            const x = startX + j * cellWidth;
            const y = startY + i * cellHeight;
            doc.rect(x, y, cellWidth, cellHeight);

            let textValue = "";
            if (j === 0) textValue = ringMeta.deflections[i];
            else if (j === 1) textValue = ringMeta.loads[i];
            else textValue = userInputs[i] || '';

            const textWidth  = doc.getTextWidth(textValue);
            const centeredX  = x + (cellWidth - textWidth) / 2;
            doc.text(textValue, centeredX, y + 6);
          }
        }
      }
      doc.text(`CALIBRATION BY      :-   YOGESH BHAI`, 14, 225); 
      doc.setFontSize(12);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
      doc.text("PROPRIETOR", 170, 245);
    }
  </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
