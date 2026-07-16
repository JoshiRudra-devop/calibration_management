<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'ISI Cube Calibration';
$activePage = 'certificate';
include __DIR__ . '/../includes/header.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM instrument_types WHERE slug = 'isi_cube' LIMIT 1");
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
      <div class="title_input_pair">
        <label for="quantity">NO. OF CUBE</label>
        <input type="number" id="quantity" name="quantity" required min="1">
      </div>
      <div id="serialInputs"></div>
      <?php include __DIR__ . '/../includes/certificate_loader.php'; ?>
      

  <script src="<?= APP_URL ?>/assets/js/general.js?v=<?= filemtime(__DIR__ . '/../assets/js/general.js') ?>"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
  <script>
    const INSTRUMENT_SLUG = 'isi_cube';
    let pdfSaved = false;



    // IMAGE PRELOADING LOGIC
    let headerImgB64, footerImgB64, stampImgB64, signImgB64;
    window.prepareImages = async function() {
      if (!headerImgB64) headerImgB64 = await loadImageToBase64("../assets/images/header.jpeg");
      if (!footerImgB64) footerImgB64 = await loadImageToBase64("../assets/images/footer.jpeg");
      if (!stampImgB64)  stampImgB64  = await loadImageToBase64("../assets/images/stamp.jpeg");
      if (!signImgB64)   signImgB64   = await loadImageToBase64("../assets/images/sign.jpeg");
    }
    function loadImageToBase64(url) {
      return new Promise((resolve, reject) => {
        var img = new window.Image();
        // // img.crossOrigin = "Anonymous";
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

    window.getFormDetails = function() {
      const qty = parseInt(document.getElementById("quantity").value);
      let serials = [];
      for (let i = 1; i <= qty; i++) {
        const el = document.getElementById(`serial${i}`);
        serials.push(el ? el.value : '');
      }
      return {
        certificateNumber: document.getElementById("certificateNumber").value,
        calibrationDate: document.getElementById("calibrationDate").value.split("-").reverse().join("/"),
        siteLocation: document.getElementById("siteLocation").value,
        partyName: document.getElementById("partyName").value,
        quantity: document.getElementById("quantity").value,
        size: document.getElementById("size").value,
        nextCalibrationDate: document.getElementById("nextCalibrationDate").value.split("-").reverse().join("/"),
        serials: serials,
        saveentry: `ISICube_${document.getElementById("partyName").value}_${document.getElementById("certificateNumber").value}`
      };
    }

    function generateSerialInputs() {
      const qty = parseInt(document.getElementById('quantity').value);
      const container = document.getElementById('serialInputs');
      if (isNaN(qty) || qty <= 0) {
        container.innerHTML = '';
        return;
      }
      const currentInputs = container.querySelectorAll('input');
      const currentCount = currentInputs.length;
      if (qty > currentCount) {
        for (let i = currentCount + 1; i <= qty; i++) {
          const div = document.createElement('div');
          div.className = 'title_input_pair';
          div.innerHTML = `<label for="serial${i}">Serial No ${i}:</label><input type="text" id="serial${i}" name="serial${i}" required>`;
          container.appendChild(div);
        }
      } else if (qty < currentCount) {
        for (let i = currentCount; i > qty; i--) {
          const input = document.getElementById(`serial${i}`);
          if (input) input.parentElement.remove();
        }
      }
    }

    function incrementCertificateNumber(baseCertNo, increment) {
      if (!isNaN(Number(baseCertNo))) {
        return String(Number(baseCertNo) + increment);
      } else {
        const match = baseCertNo.match(/^(\d+)(.*)$/);
        if (match) {
          return (Number(match[1]) + increment) + match[2];
        } else {
          return baseCertNo;
        }
      }
    }

    function drawHeader(doc, details, Yalign, withImages) {
      if (withImages && headerImgB64) doc.addImage(headerImgB64, 'PNG', 3, 3, 210, 30);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(23);
      doc.text("TEST REPORT FOR CUBE MOULD", doc.internal.pageSize.getWidth() / 2, Yalign, { align: 'center' });
      doc.text(`${details.size}`, doc.internal.pageSize.getWidth() / 2, Yalign+=7, { align: 'center' });

      doc.setFontSize(12);
      doc.text(`DATE:-${details.calibrationDate}`, 155, Yalign += 10);
      doc.text(`REF NO                        :-     ${details.certificateNumber}`, 14, Yalign);
      doc.text(`NAME OF PARTY        :-     ${details.partyName}`, 14, Yalign += 10);
      doc.text(`EQUIPMENT NAME     :-    ISI  CUBE MOULD (${details.size})`, 14, Yalign += 10);
      doc.text(`NEXT DUE DATE        :-     ${details.nextCalibrationDate}`, 14, Yalign += 10);

      // --- Site Location with wrapping (only value, not prefix) ---
      const siteLocPrefix = "SITE LOCATION          :-     ";
      const prefixWidth = doc.getTextWidth(siteLocPrefix);
      const maxWidth = 180 - prefixWidth;
      const siteLocLines = doc.splitTextToSize(details.siteLocation, maxWidth);
      doc.text(siteLocPrefix + (siteLocLines[0] || ""), 14, Yalign += 10);
      for (let i = 1; i < siteLocLines.length; i++) {
        doc.text(siteLocLines[i], 14 + prefixWidth , Yalign +=4);
      }
      Yalign += ((siteLocLines.length - 1)+5);
      return Yalign;
    }

    function addFooterImages(doc) {
      if (footerImgB64) doc.addImage(footerImgB64, 'PNG', 0, 255, 210, 27);
      if (stampImgB64)  doc.addImage(stampImgB64,  'PNG', 100, 217, 35, 35);
      if (signImgB64)   doc.addImage(signImgB64,   'PNG', 160, 232, 40, 10);
    }

    window.addCertificateDetails = function(doc, details) {
      let qty = parseInt(details.quantity);
      if (isNaN(qty) || qty <= 0) {
        alert("Please enter a valid number of cubes.");
        return;
      }
      let [length, height, width] = details.size.split("x").map(s => s.trim());
      if (!length) length = "";
      if (!height) height = "";
      if (!width) width = "";
      const headers = [["SR.NO", "SERIAL NO", "LENGTH", "HEIGHT", "WIDTH"]];
      const allRows = [];
      for (let i = 1; i <= qty; i++) {
        allRows.push([i, details.serials[i-1], length, height, width]);
      }
      const pageCount = Math.ceil(allRows.length / 10);
      for (let page = 0; page < pageCount; page++) {
        if (page > 0) doc.addPage();
        let pageRows = allRows.slice(page * 10, page * 10 + 10);
        let refNo = incrementCertificateNumber(details.certificateNumber, page);
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
        let tableEndY = doc.autoTable.previous.finalY;
        doc.setFontSize(12);
        doc.text("CALIBRATED BY: YOGESH B JOSHI", 14, tableEndY + 10);
        doc.setFont("helvetica", "bold");
        doc.setFontSize(12);
        doc.text("FOR, " + window.PDF_COMPANY_NAME, 145, 230);
        doc.text("PROPRIETOR", 170, 245);
        addFooterImages(doc);
      }
    }

    // Preload images on load & listen to quantity changes
    document.addEventListener("DOMContentLoaded", async function() {
      // Add quantity change listener to generate serial inputs
      const qtyInput = document.getElementById('quantity');
      if (qtyInput) {
        qtyInput.addEventListener('input', generateSerialInputs);
      }
      await prepareImages();
    });
  </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

