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
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
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
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/general.css?v=<?= filemtime(__DIR__ . '/../assets/css/general.css') ?>">
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
    // ── Global Custom Autocomplete ──
    document.addEventListener('DOMContentLoaded', async () => {
      const nameInput = document.getElementById('partyName') || document.getElementById('parentCompanyName');
      const locInput = document.getElementById('siteLocation') || document.getElementById('parentSiteLocation');
      
      if (!nameInput) return;

      // Turn off native autocomplete
      nameInput.setAttribute('autocomplete', 'off');

      // Wrap the input if it's not already wrapped
      if (!nameInput.parentElement.classList.contains('autocomplete-wrapper')) {
        const wrapper = document.createElement('div');
        wrapper.className = 'autocomplete-wrapper';
        nameInput.parentNode.insertBefore(wrapper, nameInput);
        wrapper.appendChild(nameInput);
      }
      
      const wrapper = nameInput.parentElement;
      let dropdown = wrapper.querySelector('.autocomplete-dropdown');
      if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.className = 'autocomplete-dropdown';
        wrapper.appendChild(dropdown);
      }

      let companiesList = [];
      try {
        const res = await fetch(SHREEJI_CONFIG.apiBase + '/get_parties_try.php?t=' + Date.now());
        const data = await res.json();
        if (data.success && data.parties) {
          companiesList = data.parties;
        }
      } catch (err) {
        if (window.SHREEJI_DEBUG) console.error('Failed to load companies autocomplete:', err);
      }

      let _ignoreNextInput = false;

      const renderDropdown = function() {
        if (_ignoreNextInput) return;
        const query = nameInput.value.toLowerCase().trim();
        dropdown.innerHTML = '';
        
        let matches = [];
        if (query.length === 0) {
          matches = companiesList;
        } else {
          const startsWithMatches = [];
          const containsMatches = [];
          companiesList.forEach(item => {
            const nameLower = item.name.toLowerCase();
            if (nameLower.startsWith(query)) {
              startsWithMatches.push(item);
            } else if (nameLower.includes(query)) {
              containsMatches.push(item);
            }
          });
          matches = startsWithMatches.concat(containsMatches);
        }
        
        if (matches.length === 0) {
          dropdown.classList.remove('active');
          return;
        }
        
        matches.forEach(item => {
          const div = document.createElement('div');
          div.className = 'autocomplete-item';
          
          const nameSpan = document.createElement('span');
          nameSpan.textContent = item.name;
          div.appendChild(nameSpan);
          
          if (item.site_location) {
            const locTag = document.createElement('span');
            locTag.className = 'loc-tag';
            locTag.textContent = item.site_location;
            div.appendChild(locTag);
          }
          
          // Handle both mousedown and touchstart for instant mobile response
          const selectItem = function(e) {
            e.preventDefault(); // Prevent input from losing focus immediately
            nameInput.value = item.name;
            if (locInput) locInput.value = item.site_location || '';
            dropdown.classList.remove('active');
            nameInput.blur(); // Force keyboard to hide on mobile
            
            _ignoreNextInput = true;
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            if (locInput) locInput.dispatchEvent(new Event('input', { bubbles: true }));
          };
          div.addEventListener('mousedown', selectItem);
          div.addEventListener('touchstart', selectItem, {passive: false});
          
          dropdown.appendChild(div);
        });
        
        dropdown.classList.add('active');
      };

      let _acDebounce;
      nameInput.addEventListener('input', () => {
        if (_ignoreNextInput) {
          _ignoreNextInput = false;
          return;
        }
        clearTimeout(_acDebounce);
        _acDebounce = setTimeout(renderDropdown, 280);
      });
      
      nameInput.addEventListener('focus', renderDropdown);
      nameInput.addEventListener('click', renderDropdown);
      
      // Auto hide when clicking outside or losing focus
      nameInput.addEventListener('blur', () => {
        setTimeout(() => dropdown.classList.remove('active'), 200);
      });


      let _acIndex = -1;
      nameInput.addEventListener('keydown', function(e) {
        const items = dropdown.querySelectorAll('.autocomplete-item');
        if (!dropdown.classList.contains('active') || items.length === 0) return;

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          _acIndex = Math.min(_acIndex + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          _acIndex = Math.max(_acIndex - 1, -1);
        } else if (e.key === 'Enter' && _acIndex >= 0) {
          e.preventDefault();
          items[_acIndex]?.dispatchEvent(new MouseEvent('mousedown'));
          _acIndex = -1;
          return;
        } else if (e.key === 'Escape') {
          dropdown.classList.remove('active');
          _acIndex = -1;
          return;
        } else {
          return;
        }
        items.forEach((el, i) => el.classList.toggle('autocomplete-item--active', i === _acIndex));
        if (_acIndex >= 0) items[_acIndex].scrollIntoView({ block: 'nearest' });
      });

      document.addEventListener('click', function(e) {
        if (!nameInput.contains(e.target) && !dropdown.contains(e.target)) {
          dropdown.classList.remove('active');
          _acIndex = -1;
        }
      });
    });
  </script>
<?php
$isEmbed = isset($_GET['embed']) && $_GET['embed'] === 'true';
?>
<?php if (!$isEmbed): ?>
  <style>
  /* Global Page Transition Skeleton Loader */
  #global-page-skeleton {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: #f0f2f5;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    transition: opacity 0.4s ease, visibility 0.4s ease;
    pointer-events: none;
  }
  #global-page-skeleton.hidden {
    opacity: 0;
    visibility: hidden;
  }
  /* Skeleton Header */
  .skeleton-header {
    height: 60px;
    background-color: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    padding: 0 20px;
    gap: 15px;
  }
  .skeleton-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e0e0e0;
    animation: shimmer 1.5s infinite linear;
  }
  .skeleton-title {
    width: 250px;
    height: 20px;
    border-radius: 4px;
    background: #e0e0e0;
    animation: shimmer 1.5s infinite linear;
  }
  /* Skeleton Body */
  .skeleton-body {
    flex: 1;
    padding: 40px 20px;
    display: flex;
    justify-content: center;
  }
  .skeleton-card {
    width: 100%;
    max-width: 800px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 30px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  .skeleton-line {
    height: 20px;
    border-radius: 4px;
    background: #e0e0e0;
    animation: shimmer 1.5s infinite linear;
  }
  .skeleton-line.short { width: 40%; }
  .skeleton-line.medium { width: 70%; }
  .skeleton-line.long { width: 100%; height: 60px; }
  
  @keyframes shimmer {
    0% { background-color: #e0e0e0; }
    50% { background-color: #f5f5f5; }
    100% { background-color: #e0e0e0; }
  }
  </style>
<?php endif; ?>
</head>
<body class="<?= $isEmbed ? 'embed-mode' : '' ?>">

<?php if (!$isEmbed): ?>
  <!-- Global Skeleton Loader -->
  <div id="global-page-skeleton">
    <div class="skeleton-header">
      <div class="skeleton-logo"></div>
      <div class="skeleton-title"></div>
    </div>
    <div class="skeleton-body">
      <div class="skeleton-card">
        <div class="skeleton-line short"></div>
        <div class="skeleton-line medium"></div>
        <div class="skeleton-line long"></div>
        <div class="skeleton-line medium"></div>
        <div class="skeleton-line long"></div>
      </div>
    </div>
  </div>
  <script>
    // Fade out skeleton once page is fully loaded
    window.addEventListener('load', () => {
      const skeleton = document.getElementById('global-page-skeleton');
      if (skeleton) skeleton.classList.add('hidden');
    });

    // Fade in skeleton when navigating away to simulate SPA transition
    window.addEventListener('beforeunload', () => {
      const skeleton = document.getElementById('global-page-skeleton');
      if (skeleton) skeleton.classList.remove('hidden');
    });
  </script>
<?php endif; ?>

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
        <a href="<?= APP_URL ?>/settings.php" class="top-brand-header__link">
          <i class="fas fa-cog"></i> Settings
        </a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login.php" class="top-brand-header__logout">
          <i class="fas fa-sign-in-alt"></i> Login
        </a>
      <?php endif; ?>
    </div>
  </header>
<?php endif; ?>