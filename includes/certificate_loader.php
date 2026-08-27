<?php
// ============================================================
//  Shared: Certificate Page Loader Overlay + Unsaved Reminder
//  Include just before closing </form> in every certificate .php
// ============================================================
$isEmbedded = isset($_GET['embed']) && $_GET['embed'] === 'true';
?>
<div class="unsaved-reminder" id="unsavedReminder" <?= $isEmbedded ? 'style="display: none !important;"' : '' ?>>
  <span>⚠️ Please save your certificate before leaving this page.</span>
</div>

<div class="sticker-section">
  <div class="sticker-preview-container">
    <h3 style="color: #00796b; margin-top: 0;">Info Sticker Preview</h3>
    <iframe id="stickerPreviewFrame"></iframe>
  </div>
</div>

<!-- Loader Overlay (used inside embed iframes and standalone pages) -->
<div id="customLoaderOverlay" class="custom-loader-overlay">
  <div class="custom-loader-content" id="customLoaderContent">
    <div id="loaderSpinner" class="loader-progress-box">
      <div class="loader-progress-track">
        <div class="loader-progress-bar"></div>
      </div>
    </div>
    <div id="loaderTick" class="custom-tick-container" style="display:none;">
      <svg width="70" height="70" viewBox="0 0 70 70">
        <circle class="tick-circle" cx="35" cy="35" r="32" fill="none" stroke="#4caf50" stroke-width="5" />
        <polyline class="tick-check" points="22,38 32,48 50,28" fill="none" stroke="#4caf50" stroke-width="6"
          stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <button id="loaderOkkBtn" class="custom-okk-btn">OK</button>
    </div>
    <div id="loaderText" class="custom-loader-text">Saving certificate...</div>
  </div>
</div>
