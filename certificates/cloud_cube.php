<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Cloud Cube Calibration';
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
        <label for="quantity">NO. OF CUBE</label>
        <input type="number" id="quantity" required min="1">
      </div>
      <div class="title_input_pair">
        <label for="siteLocation">Site Location:</label>
        <input type="text" id="siteLocation" required>
      </div>
      <div class="title_input_pair">
        <label for="size">SIZE:</label>
        <select id="size" required>
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
      if (withImages && headerImgB64) {
        try { doc.addImage(headerImgB64, 'PNG', 3, 3, 210, 30, undefined, 'FAST'); } catch (e) {}
      }
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

    function addFooterImages(doc) {
      try {
        if (footerImgB64) doc.addImage(footerImgB64, 'PNG', 0, 255, 210, 27, undefined, 'FAST');
        if (stampImgB64)  doc.addImage(stampImgB64,  'PNG', 100, 217, 35, 35, undefined, 'FAST');
        if (signImgB64)   doc.addImage(signImgB64,   'PNG', 160, 232, 40, 10, undefined, 'FAST');
      } catch (e) {}
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
        let tableY = drawHeader(doc, pageDetails, 50, true);
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
        let tableEndY = (doc.autoTable && doc.autoTable.previous) ? doc.autoTable.previous.finalY : tableY + 40;
        doc.setFontSize(12);
        doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableEndY + 10);
        doc.setFont("helvetica", "bold");
        doc.setFontSize(12);
        doc.text("FOR, " + (window.PDF_COMPANY_NAME || "SHREEJI INSTRUMENTS"), 145, 230);
        doc.text("PROPRIETOR", 170, 245);
        addFooterImages(doc);
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
  </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
