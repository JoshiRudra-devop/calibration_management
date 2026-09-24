<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Cube Mould Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'cube_mould' LIMIT 1");
$stmt->execute();
$instrument = $stmt->fetch();
$instrumentId = $instrument['id'] ?? null;
?>

<?php include __DIR__ . '/../includes/certificate_dock.php'; ?>
<div class="container">
    <h2 class="centered">CUBE MOULD CALIBRATION CERTIFICATE</h2>
    <form id="calibrationForm">
      <div class="title_input_pair">
        <label for="certificateNumber">Certificate No:</label>
        <input type="text" id="certificateNumber" name="certificateNumber" required>
      </div>
      <div class="date">
        <div class="title_input_pair">
          <label for="calibrationDate">Date of Calibration:</label>
          <input type="date" id="calibrationDate" name="calibrationDate" onchange="calculateNextDate()" required>
        </div>
        <div class="title_input_pair">
          <label for="nextCalibrationDate">Next Suggested Date:</label>
          <input type="date" id="nextCalibrationDate" name="nextCalibrationDate" required>
        </div>
      </div>
      <div class="title_input_pair">
        <label for="partyName">Company Name:</label>
        <input type="text" id="partyName" name="partyName" required>
      </div>
      <div class="title_input_pair">
        <label for="quantity">NO. OF CUBE</label>
        <input type="number" id="quantity" name="quantity" required min="1">
      </div>
      <div class="title_input_pair">
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" name="siteLocation" required>
      </div>
      <div class="title_input_pair">
        <label for="size">SIZE:</label>
        <select id="size" name="size" required>
          <option value="">Select Size</option>
          <option value="150MM x 150MM x 150MM">150MM x 150MM x 150MM</option>
          <option value="50MM x 50MM x 50MM">50MM x 50MM x 50MM</option>
          <option value="100MM x 100MM x 100MM">100MM x 100MM x 100MM</option>
          <option value="70.6MM x 70.6MM x 70.6MM">70.6MM x 70.6MM x 70.6MM</option>
          <option value="700MM x 100MM x  100MM ">BIM MOULD</option>
        </select>
      </div>
      <?php include __DIR__ . '/../includes/certificate_loader.php'; ?>
      

  <script src="<?= APP_URL ?>/assets/js/general-v3.js?v=3.6.0&t=<?= time() ?>"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
  <script>
    console.log("Cube Mould script block parsing...");
    window.INSTRUMENT_SLUG = 'cube_mould';
    let pdfSaved = false;

    // IMAGE PRELOADING LOGIC
    let headerImgB64, footerImgB64, stampImgB64, signImgB64;
    window.prepareImages = async function() {
      if (!headerImgB64) headerImgB64 = await loadImageToBase64(SHREEJI_CONFIG.appUrl + "/header.jpeg");
      if (!footerImgB64) footerImgB64 = await loadImageToBase64(SHREEJI_CONFIG.appUrl + "/footer.jpeg");
      if (!stampImgB64)  stampImgB64  = await loadImageToBase64(SHREEJI_CONFIG.appUrl + "/stamp.jpeg");
      if (!signImgB64)   signImgB64   = await loadImageToBase64(SHREEJI_CONFIG.appUrl + "/sign.jpeg");
    }
    function loadImageToBase64(url) {
      return new Promise((resolve, reject) => {
        var img = new window.Image();
        img.crossOrigin = "Anonymous";
        img.onload = function () {
          var canvas = document.createElement("canvas");
          canvas.width = img.width;
          canvas.height = img.height;
          var ctx = canvas.getContext("2d");
          ctx.drawImage(img, 0, 0);
          resolve(canvas.toDataURL("image/png"));
        };
        img.onerror = reject;
        img.src = url;
      });
    }

    function getFormDetails() {
      const getVal = (id) => {
        const el = document.getElementById(id);
        return el ? el.value : "";
      };
      const formatDate = (val) => {
        return val ? val.split("-").reverse().join("/") : "";
      };
      const certNo = getVal("certificateNumber");
      const partyName = getVal("partyName");

      return {
        certificateNumber: certNo,
        calibrationDate: formatDate(getVal("calibrationDate")),
        siteLocation: getVal("siteLocation"),
        partyName: partyName,
        quantity: getVal("quantity"),
        size: getVal("size"),
        nextCalibrationDate: formatDate(getVal("nextCalibrationDate")),
        saveentry: `CubeMould_${partyName}_${certNo}`
      };
    }
    window.getFormDetails = getFormDetails;

    function incrementCertificateNumber(baseCertNo, increment) {
      if (increment === 0) return baseCertNo;
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

    function drawHeader(doc, details, Yalign, withImages) {
      details = details || {};
      
      doc.setFont("helvetica", "bold");
      doc.setFontSize(23);
      doc.text("TEST REPORT FOR CUBE MOULD", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
      Yalign += 7;
      doc.text(`${details.size || ''}`, doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });

      doc.setFontSize(12);
      Yalign += 10;
      doc.text(`DATE:-${details.calibrationDate || ''}`, 155, Yalign);
      doc.text(`REF NO                        :-     ${details.certificateNumber || ''}`, 14, Yalign);
      Yalign += 10;
      doc.text(`NAME OF PARTY        :-     ${details.partyName || ''}`, 14, Yalign);
      Yalign += 10;
      doc.text(`EQUIPMENT NAME     :-     CUBE MOULD (${details.size || ''})`, 14, Yalign);
      Yalign += 10;
      doc.text(`NEXT DUE DATE        :-     ${details.nextCalibrationDate || ''}`, 14, Yalign);

      // --- Site Location with wrapping (only value, not prefix) ---
      const siteLocPrefix = "SITE LOCATION          :-     ";
      const prefixWidth = doc.getTextWidth(siteLocPrefix);
      const maxWidth = 180 - prefixWidth;
      const siteLocStr = details.siteLocation || "";
      const siteLocLines = doc.splitTextToSize(siteLocStr, maxWidth);
      Yalign += 10;
      doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign);
      for (let i = 1; i < siteLocLines.length; i++) {
        Yalign += 4;
        doc.text(siteLocLines[i], 14 + prefixWidth , Yalign);
      }
      Yalign += ((siteLocLines.length - 1) + 5);
      return Yalign;
    }

        }

    window.addCertificateDetails = function(doc, details) {
      details = details || {};
      let qty = parseInt(details.quantity);
      if (isNaN(qty) || qty <= 0) {
        qty = 1;
      }
      let sizeStr = details.size || "";
      let [length, height, width] = sizeStr.includes("x") ? sizeStr.split("x").map(s => s.trim()) : [sizeStr, "", ""];
      if (!length) length = "";
      if (!height) height = "";
      if (!width) width = "";
      const headers = [["SR.NO", "LENGTH", "HEIGHT", "WIDTH"]];
      const allRows = [];
      for (let i = 1; i <= qty; i++) {
        allRows.push([i, length, height, width]);
      }
      const pageCount = Math.ceil(allRows.length / 10);
      for (let page = 0; page < pageCount; page++) {
        if (page > 0) doc.addPage();
        let pageRows = allRows.slice(page * 10, page * 10 + 10);
        let refNo = incrementCertificateNumber(details.certificateNumber || "", page);
        let pageDetails = { ...details, certificateNumber: refNo };
        let tableY = drawHeader(doc, pageDetails, 50, false);
        if (typeof doc.autoTable === 'function') {
          try {
            doc.autoTable({
              head: headers,
              body: pageRows,
              startY: tableY + 1,
              styles: {
                fontSize: 12,
                lineColor: [0, 0, 0],
                textColor: [0, 0, 0],
                lineWidth: 0.2,
                halign: 'center',
                valign: 'middle'
              },
              headStyles: {
                fontSize: 15,
                fillColor: [255, 255, 255],
                textColor: [0, 0, 0],
                lineColor: [0, 0, 0],
                lineWidth: 0.2,
                halign: 'center',
                valign: 'middle'
              },
              alternateRowStyles: {
                fillColor: [255, 255, 255]
              }
            });
          } catch (ae) {
            console.error("autoTable error in cube_mould.php:", ae);
          }
        } else {
          let curY = tableY + 5;
          doc.setFontSize(14);
          doc.rect(14, curY, 180, 8);
          doc.text("SR.NO", 20, curY + 6);
          doc.text("LENGTH", 60, curY + 6);
          doc.text("HEIGHT", 110, curY + 6);
          doc.text("WIDTH", 160, curY + 6);
          curY += 8;
          doc.setFontSize(12);
          for (let r of pageRows) {
            doc.rect(14, curY, 180, 8);
            doc.text(String(r[0]), 20, curY + 6);
            doc.text(String(r[1]), 60, curY + 6);
            doc.text(String(r[2]), 110, curY + 6);
            doc.text(String(r[3]), 160, curY + 6);
            curY += 8;
          }
          if (!doc.autoTable) doc.autoTable = {};
          doc.autoTable.previous = { finalY: curY };
        }
        let tableEndY = (doc.autoTable && doc.autoTable.previous && typeof doc.autoTable.previous.finalY === 'number') ? doc.autoTable.previous.finalY : tableY + 40;
        let endY = (doc.lastAutoTable ? doc.lastAutoTable.finalY : ((doc.autoTable && doc.autoTable.previous) ? doc.autoTable.previous.finalY : tableEndY)) + 4;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, endY += 8);
      doc.setFontSize(10.5);
      doc.text("• REMARKS: This certificate is valid for 12 months from the date of calibration.", 14, endY += 7);
      doc.text("• This certificate refers to the value obtained at the time of calibration.", 14, endY += 6);
      let sigY = Math.max(endY + 18, 225);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, sigY);
      doc.text("PROPRIETOR", 170, sigY + 15);
      }
    };
    function addCertificateDetails(doc, details) {
      return window.addCertificateDetails(doc, details);
    }

    // Preload images on load
    document.addEventListener("DOMContentLoaded", async function() {
      try {
        await prepareImages();
      } catch (e) {
        if (window.SHREEJI_DEBUG) console.error("Error preloading images:", e);
      }
    });
  
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
        const fileName = ;
        await savePDFWithLocation(window.stickerPdfBlob, fileName);
      }

    window.generateInfoSticker = generateInfoSticker;
    window.downloadSticker = downloadSticker;
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
