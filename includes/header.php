<?php
// includes/header.php  — include at top of every page
// Usage: include __DIR__ . '/includes/header.php';
//   Set $pageTitle before including.
//   Set $activePage = 'home'|'dashboard'|'contact' etc.

$pageTitle  = $pageTitle  ?? 'Calibration Management System';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Calibration Management System – Calibration Certificate Generator">
  <meta name="theme-color" content="#00796b">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <title><?= htmlspecialchars($pageTitle) ?> | Calibration Management System</title>
  <link rel="icon" href="<?= APP_URL ?>/assets/images/logo.png" type="image/png">
  <link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/images/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.8">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/general.css?v=1.8">
  <!-- jsPDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.14/jspdf.plugin.autotable.min.js"></script>
  <!-- QR Code (certificate verification stamps) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <!-- docx (only loaded where needed) -->
  <!-- Config for JS -->
  <script>
    const SHREEJI_CONFIG = {
      apiBase:          '<?= APP_URL ?>/api',
      cloudName:        '<?= CLOUDINARY_CLOUD_NAME ?>',
      cloudinaryPreset: '<?= CLOUDINARY_UPLOAD_PRESET ?>',
      appUrl:           '<?= APP_URL ?>',
      csrfToken:        '<?= htmlspecialchars(csrfToken()) ?>',
    };
    // Set window.SHREEJI_DEBUG = true in browser console to enable debug logging.
    window.SHREEJI_DEBUG = false;
    
    // Dynamic PDF Company Name
    window.PDF_COMPANY_NAME = localStorage.getItem('pdfCompanyName') || 'SHREEJI INSTRUMENTS';
    
    // Global Document Preview Modal Setup
    window.showGlobalPreviewModal = function(pdfUrl) {
      let modalOverlay = document.getElementById('globalPreviewModal');
      if (!modalOverlay) {
        modalOverlay = document.createElement('div');
        modalOverlay.id = 'globalPreviewModal';
        modalOverlay.className = 'preview-modal-overlay';
        modalOverlay.innerHTML = `
          <div class="preview-modal">
            <div class="preview-modal-header">
              <h3><i class="fas fa-file-pdf"></i> Document Preview</h3>
              <button class="preview-modal-close" onclick="closeGlobalPreviewModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="preview-modal-body">
              <iframe id="globalPreviewIframe" src=""></iframe>
            </div>
          </div>
        `;
        document.body.appendChild(modalOverlay);
        
        // Add close function globally
        window.closeGlobalPreviewModal = function() {
          const overlay = document.getElementById('globalPreviewModal');
          if (overlay) {
            overlay.classList.remove('show');
            setTimeout(() => {
              document.getElementById('globalPreviewIframe').src = '';
            }, 300);
          }
        };
        
        // Close on overlay click
        modalOverlay.addEventListener('click', function(e) {
          if (e.target === modalOverlay) {
            window.closeGlobalPreviewModal();
          }
        });
      }
      
      document.getElementById('globalPreviewIframe').src = pdfUrl;
      
      // Trigger animation
      setTimeout(() => modalOverlay.classList.add('show'), 10);
    };
    
    // Global Company Autocomplete for all instrument forms
    document.addEventListener('DOMContentLoaded', async () => {
      const partyInput = document.getElementById('partyName');
      if (partyInput) {
        try {
          const res = await fetch(SHREEJI_CONFIG.apiBase + '/get_parties_try.php');
          const data = await res.json();
          if (data.success && data.parties) {
            const datalist = document.createElement('datalist');
            datalist.id = 'globalPartyList';
            data.parties.forEach(p => {
              const opt = document.createElement('option');
              opt.value = p.name;
              datalist.appendChild(opt);
            });
            document.body.appendChild(datalist);
            partyInput.setAttribute('list', 'globalPartyList');
            partyInput.setAttribute('autocomplete', 'off'); // Disable browser default autofill
            
            // Auto-fill site location if available
            const siteInput = document.getElementById('siteLocation');
            if (siteInput) {
               partyInput.addEventListener('change', () => {
                 const selected = data.parties.find(x => x.name === partyInput.value);
                 if (selected && selected.site_location && !siteInput.value) {
                   siteInput.value = selected.site_location;
                 }
               });
            }
          }
        } catch (err) {
          if (window.SHREEJI_DEBUG) console.error('Failed to load companies datalist:', err);
        }
      }
    });
  </script>
<?php
$isEmbed = isset($_GET['embed']) && $_GET['embed'] === 'true';
?>
</head>
<body class="<?= $isEmbed ? 'embed-mode' : '' ?>">

<?php if ($isEmbed): ?>
  <!-- Embed Mode specific overrides -->
  <style>
    .nav-sidebar, .nav-sidebar-toggle, footer, .back-button, .side-dock { display: none !important; }
    body { background: #ffffff !important; padding: 1.5rem !important; min-height: auto !important; padding-bottom: 0 !important; }
    .page-wrapper, .container { margin: 0 !important; padding: 0 !important; max-width: 100% !important; box-shadow: none !important; background: transparent !important; }
    .form-card, .card { box-shadow: none !important; border: none !important; padding: 1rem 0 !important; }
    h2.centered { display: none !important; }
  </style>
<?php endif; ?>

<!-- ── Loader Overlay ──────────────────────────────────── -->
<div class="loader-overlay" id="loaderOverlay">
  <div class="loader-card">
    <div class="spinner" id="loaderSpinner"></div>
    <div id="loaderTick" style="display:none; flex-direction:column; align-items:center; gap:.75rem;">
      <svg class="tick-svg" width="64" height="64" viewBox="0 0 70 70">
        <circle cx="35" cy="35" r="32" fill="none" stroke="#22b55d" stroke-width="5"/>
        <polyline points="22,38 32,48 50,28" fill="none" stroke="#22b55d" stroke-width="6"
                  stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <button class="loader-ok-btn" id="loaderOkBtn">OK</button>
    </div>
    <p class="loader-text" id="loaderText">Processing…</p>
  </div>
</div>

<?php if (!$isEmbed): ?>
  <!-- ── Top Brand Header ────────────────────────────────────── -->
  <header class="top-brand-header">
    <a href="<?= APP_URL ?>/index.php" class="top-brand-header__left" style="text-decoration: none; display: flex; align-items: center; gap: 0.8rem;">
      <img src="<?= APP_URL ?>/assets/images/logo.png" alt="Calibration Management System Logo" class="top-brand-header__logo">
      <span class="top-brand-header__title">Calibration Management System</span>
    </a>
    <div class="top-brand-header__right">
      <a href="<?= APP_URL ?>/verify.php" class="top-brand-header__link" style="margin-right: 1.25rem;">
        <i class="fas fa-qrcode"></i> Verify Certificate
      </a>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="<?= APP_URL ?>/dashboard.php" class="top-brand-header__link" style="margin-right: 1.25rem;">
          <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <span class="top-brand-header__profile" style="margin-right: 0.5rem;">
          <i class="fas fa-user-circle"></i> Welcome, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?>
        </span>
        <a href="<?= APP_URL ?>/api/auth.php?action=logout" class="top-brand-header__logout">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login.php" class="top-brand-header__logout">
          <i class="fas fa-sign-in-alt"></i> Login
        </a>
      <?php endif; ?>
    </div>
  </header>
<?php endif; ?>