<?php
// ============================================================
//  Shared: Certificate Page Dock + Back Button
//  Include AFTER requires/header in every certificates/*.php
//  Depends on: $pageTitle being set before header include
// ============================================================
$loadedCertId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($certId) ? (int)$certId : 0);
?>
<button class="back-button" onclick="goBackOrPromptSave()">← Back</button>
<button class="dock-toggle" onclick="toggleDock()">☰ Actions</button>

<div class="side-dock" id="sideDock">
  <h3>Actions</h3>
  <button class="dock-button" type="button" onclick="preview()">
    📋 Preview Certificate
  </button>
  <button class="dock-button" type="button" id="uploadBtn">
    📄 SAVE
  </button>
  <button class="dock-button" type="button" onclick="printBlankCertificate()" data-requires-save title="Save the certificate first">
    🖨️ Print
  </button>
  <button class="dock-button sticker-btn" type="button" onclick="generateInfoSticker()" data-requires-save title="Save the certificate first">
    🏷️ Generate Info Sticker
  </button>
  <button class="dock-button" id="downloadStickerBtn" type="button" onclick="downloadSticker()" style="display: none;" data-requires-save title="Save the certificate first">
    ⬇️ Download Sticker
  </button>
  <button class="dock-button" type="button" onclick="sharePDF()" data-requires-save title="Save the certificate first">
    📤 Share PDF
  </button>
  <button class="dock-button btn-delete-dock" type="button" id="dockDeleteBtn" onclick="deleteCurrentCertificate()" <?= $loadedCertId > 0 ? '' : 'style="display:none;"' ?>>
    🗑️ Delete Certificate
  </button>
</div>

<script>
function deleteCurrentCertificate() {
  const certId = window.CURRENT_CERT_ID || <?= json_encode($loadedCertId) ?>;
  const certNo = document.getElementById('certificateNumber') ? document.getElementById('certificateNumber').value : '';
  if (certId > 0) {
    deleteCertificate(certId, certNo, function() {
      window.location.href = '../dashboard.php';
    });
  } else {
    alert('No existing saved certificate to delete.');
  }
}
</script>

