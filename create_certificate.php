<?php
// ============================================================
//  TRIAL: Unified Dashboard (index_try.php)
//  Allows filling company details once and dynamically loading instrument forms.
// ============================================================
require_once __DIR__ . '/includes/config.php';
$pageTitle  = 'New Report â€“ Calibration Management System';
$activePage = 'create_certificate';
include __DIR__ . '/includes/header.php';

// Fetch instrument types from DB for the modal selection
$db = getDB();
$types = $db->query("SELECT slug, label FROM instrument_types ORDER BY sort_order")->fetchAll();
?>

<!-- Custom CSS for the Trial page to keep main CSS untouched -->
<style>
  .trial-hero {
    background: #e0f2f1 !important;
    color: #004d40 !important;
    padding: 1.5rem 2rem 1.25rem !important;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 121, 107, 0.03) !important;
    border: 1.5px solid #b2dfdb !important;
    border-radius: var(--radius-lg);
    margin-bottom: 2rem;
  }
  .trial-hero h2 {
    font-size: 1.3rem !important;
    font-weight: 700 !important;
    margin-bottom: 0.3rem !important;
    color: #00796b !important;
  }
  .trial-hero p {
    color: #00695c !important;
    font-size: 0.85rem !important;
  }
  
  .company-card {
    background: #fff;
    border-radius: var(--radius-lg);
    padding: 2rem;
    box-shadow: 0 4px 20px -2px rgba(0, 121, 107, 0.03);
    border: 1.5px solid var(--border);
    margin-bottom: 2.5rem;
  }
  .company-card h3 {
    color: var(--primary);
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: 1.25rem;
    border-bottom: 1.5px solid var(--border);
    padding-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  /* Autocomplete input styling */
  .autocomplete-wrapper {
    position: relative;
    width: 100%;
  }
  .autocomplete-dropdown {
    position: absolute;
    top: 105%;
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    max-height: 280px;
    overflow-y: auto;
    z-index: 2000;
    display: none;
  }
  .autocomplete-dropdown.active {
    display: block;
  }
  .autocomplete-item {
    padding: 0.8rem 1.2rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.95rem;
    transition: background 0.15s, color 0.15s;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .autocomplete-item:last-child {
    border-bottom: none;
  }
  .autocomplete-item:hover,
  .autocomplete-item--active {
    background: var(--accent-lt);
    color: var(--primary-dk);
  }
  .autocomplete-item .loc-tag {
    font-size: 0.8rem;
    background: #e2e8f0;
    color: var(--text-mid);
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
  }

  /* Instrument Card Wrapper */
  .instrument-wrapper-card {
    background: #fff;
    border-radius: var(--radius-lg);
    border: 1.5px solid var(--border);
    box-shadow: 0 4px 20px -2px rgba(0, 121, 107, 0.03);
    margin-bottom: 2rem;
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
  }
  .instrument-card-header {
    background: #f5fbfb;
    border-bottom: 1.5px solid var(--border);
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .instrument-card-header h3 {
    font-size: 1.05rem;
    color: var(--text);
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
  }
  .instrument-actions-group {
    display: flex;
    gap: 0.75rem;
    align-items: center;
  }
  .instrument-action-btn {
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.15s ease;
  }
  .instrument-action-btn:active {
    transform: scale(0.97);
  }
  .btn-preview {
    background: #e2e8f0;
    color: var(--text);
  }
  .btn-preview:hover {
    background: #cbd5e1;
  }
  .btn-save {
    background: var(--primary-dk) !important;
    color: #fff !important;
  }
  .btn-save:hover {
    background: #00332a !important;
  }
  .btn-print {
    background: #ffffff !important;
    color: #00695c !important;
    border: 1px solid #b2dfdb !important;
  }
  .btn-print:hover {
    background: #f0f7f6 !important;
    border-color: #00796b !important;
    color: #004d40 !important;
  }
  
  /* Dropdown Styles */
  .more-actions-dropdown {
    position: relative;
    display: inline-block;
  }
  .btn-more {
    background: #f1f5f9;
    color: var(--text);
    padding: 0.5rem 0.75rem;
  }
  .btn-more:hover {
    background: #e2e8f0;
  }
  .dropdown-menu {
    position: absolute;
    right: 0;
    top: 110%;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    min-width: 180px;
    z-index: 1000;
    display: none;
    flex-direction: column;
    padding: 0.5rem 0;
    animation: slideIn 0.15s ease-out;
  }
  .dropdown-menu.show {
    display: flex;
  }
  .dropdown-item {
    background: none;
    border: none;
    padding: 0.6rem 1.2rem;
    text-align: left;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    width: 100%;
    transition: background 0.15s, color 0.15s;
  }
  .dropdown-item:hover {
    background: var(--accent-lt);
    color: var(--primary-dk);
  }
  .dropdown-item.text-danger {
    color: var(--danger);
  }
  .dropdown-item.text-danger:hover {
    background: #fee2e2;
    color: var(--danger);
  }
  .dropdown-divider {
    height: 1px;
    background: var(--border);
    margin: 0.4rem 0;
  }
  
  .remove-instrument-btn {
    background: #fee2e2;
    color: var(--danger);
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: background 0.2s, color 0.2s;
  }
  .remove-instrument-btn:hover {
    background: var(--danger);
    color: #fff;
  }
  
  .instrument-iframe {
    width: 100%;
    border: none;
    display: block;
    transition: height 0.2s ease;
  }

  /* Global Actions Bar */
  .global-actions-bar {
    position: sticky;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-top: 1px solid var(--border);
    padding: 1.2rem 2rem;
    box-shadow: 0 -8px 30px rgba(15, 23, 42, 0.08);
    z-index: 5000;
    display: none; /* Show only when activeInstrumentsContainer has cards */
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .global-actions-bar.active {
    display: block;
  }
  .actions-content {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
  }
  .selected-count {
    font-weight: 700;
    color: var(--primary-dk);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .selected-count::before {
    content: '';
    display: inline-block;
    width: 8px;
    height: 8px;
    background: var(--accent);
    border-radius: 50%;
    animation: pulse 1.5s infinite;
  }
  .global-buttons {
    display: flex;
    gap: 1rem;
    align-items: center;
  }
  .global-btn {
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
  }
  .global-btn:active {
    transform: scale(0.97);
  }
  .btn-preview-all {
    background: #e2e8f0;
    color: var(--text);
  }
  .btn-preview-all:hover {
    background: #cbd5e1;
  }
  .btn-print-all {
    background: #f1f5f9;
    color: var(--text);
  }
  .btn-print-all:hover {
    background: #e2e8f0;
  }
  .btn-save-all {
    background: var(--primary-dk) !important;
    color: #fff !important;
    box-shadow: 0 4px 12px rgba(0, 77, 64, 0.25) !important;
  }
  .btn-save-all:hover {
    background: #00332a !important;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(0, 77, 64, 0.35) !important;
  }
  .btn-save-all:active {
    transform: translateY(0);
  }
  
  @keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
  @keyframes pulse {
    0% { transform: scale(0.9); opacity: 0.5; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0.5; }
  }

  /* Add Instrument CTA Button */
  .add-instrument-cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    max-width: 280px;
    margin: 3rem auto;
    padding: 1.1rem;
    background: var(--primary-dk) !important;
    color: #fff !important;
    border: none;
    border-radius: var(--radius);
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(0, 77, 64, 0.3) !important;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .add-instrument-cta:hover {
    background: #00332a !important;
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0, 77, 64, 0.4) !important;
  }
  .add-instrument-cta:active {
    transform: translateY(0);
  }

  /* Selection Modal */
  .instrument-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 10000;
    display: none;
    place-items: center;
    padding: 1.5rem;
  }
  .instrument-modal-overlay.active {
    display: grid;
  }
  .instrument-modal {
    background: #ffffff;
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    max-width: 900px;
    width: 100%;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: var(--shadow-lg);
    animation: modalScaleIn 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }
  .instrument-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid var(--border);
    padding-bottom: 1rem;
    margin-bottom: 2rem;
  }
  .instrument-modal-header h3 {
    font-size: 1.35rem;
    color: var(--primary);
    font-weight: 800;
    margin: 0;
  }
  .close-modal-btn {
    background: none;
    border: none;
    font-size: 2rem;
    color: var(--text-lt);
    cursor: pointer;
    line-height: 1;
  }
  .close-modal-btn:hover {
    color: var(--danger);
  }
  .modal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.2rem;
  }
  .modal-card {
    background: #f8fafc;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    padding: 1.2rem;
    text-align: center;
    cursor: pointer;
    font-weight: 600;
    color: var(--primary);
    transition: all 0.2s ease;
  }
  .modal-card:hover {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 121, 107, 0.2);
  }

  @keyframes slideIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes modalScaleIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }
</style>

<!-- Preload images to browser cache for instant PDF rendering -->
<div style="display: none;">
  <img src="assets/images/header.jpeg" alt="preload">
  <img src="assets/images/footer.jpeg" alt="preload">
  <img src="assets/images/stamp.jpeg" alt="preload">
  <img src="assets/images/sign.jpeg" alt="preload">
  <img src="assets/images/logo.png" alt="preload">
</div>

<div class="page-wrapper">
  <!-- Secondary Menu Bar -->
  <div class="secondary-menu-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; padding: 0.8rem 1.5rem; background: var(--accent-lt); border: 1.5px solid var(--border); border-radius: var(--radius); max-width: 1100px; margin: 1.5rem auto 0; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0, 121, 107, 0.02);">
    <!-- Page Title -->
    <h2 style="font-size: 1.25rem; font-weight: 700; color: #00796b; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fas fa-plus-circle" style="font-size: 1.1rem;"></i> Create New Calibration Report
    </h2>
  </div>

  <div class="container" style="padding: 2rem 1rem; max-width: 1100px; margin: 0 auto;">
    
    <!-- Company Details Card -->
    <div class="company-card">
      <h3><i class="fas fa-building"></i> Company / Party Details</h3>
      
      <div class="form-row" style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <!-- Company Name (With Autocomplete) -->
        <div class="form-group" style="flex: 2; min-width: 250px;">
          <label for="parentCompanyName" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Company Name</label>
          <div class="autocomplete-wrapper">
            <input 
              type="text" 
              id="parentCompanyName" 
              placeholder="Search or enter company name..." 
              autocomplete="off"
              style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 1rem;"
            >
            <!-- Autocomplete list container -->
            <div id="autocompleteDropdown" class="autocomplete-dropdown"></div>
          </div>
        </div>

        <!-- Site Location -->
        <div class="form-group" style="flex: 2; min-width: 200px;">
          <label for="parentSiteLocation" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Site Location</label>
          <input 
            type="text" 
            id="parentSiteLocation" 
            placeholder="e.g., Ahmedabad Plant" 
            style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 1rem;"
          >
        </div>

        <!-- Calibration Date -->
        <div class="form-group" style="flex: 1; min-width: 150px;">
          <label for="parentCalibrationDate" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Calibration Date</label>
          <input 
            type="date" 
            id="parentCalibrationDate" 
            style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 1rem;"
          >
        </div>

        <!-- Next Calibration Date -->
        <div class="form-group" style="flex: 1; min-width: 150px;">
          <label for="parentNextCalibrationDate" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Next Suggested Date</label>
          <input 
            type="date" 
            id="parentNextCalibrationDate" 
            style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 1rem;"
          >
        </div>
      </div>
    </div>

    <!-- Active Instruments List -->
    <div id="activeInstrumentsContainer">
      <!-- Embedded Instrument cards will load dynamically here -->
    </div>

    <!-- Add Instrument Action -->
    <button class="add-instrument-cta" id="btnAddInstrument" type="button">
      <i class="fas fa-plus-circle"></i> Add Instrument
    </button>

  </div>
</div>

<!-- Floating Global Actions Bar -->
<div class="global-actions-bar" id="globalActionsBar">
  <div class="actions-content">
    <span class="selected-count"><span id="selectedCountText">0</span> Instrument(s) Added</span>
    <div class="global-buttons">
      <button class="global-btn btn-preview-all" type="button" onclick="previewAllCertificates()">
        <i class="fas fa-eye"></i> Preview Combined PDF
      </button>
      <button class="global-btn btn-print-all" type="button" onclick="printAllCertificates()">
        <i class="fas fa-print"></i> Print Combined PDF
      </button>
      <button class="global-btn btn-print-all" type="button" onclick="shareUnifiedPDF()" style="background: #e0f2fe; color: #0369a1;">
        <i class="fas fa-share-alt"></i> Share Combined PDF
      </button>
      <button class="global-btn btn-save-all" type="button" onclick="saveAllCertificates()">
        <i class="fas fa-save"></i> Save All to DB & Folder
      </button>
    </div>
  </div>
</div>

<!-- Premium Instrument Selector Modal -->
<div class="instrument-modal-overlay" id="instrumentModal">
  <div class="instrument-modal">
    <div class="instrument-modal-header">
      <h3>Select Instrument to Add</h3>
      <button class="close-modal-btn" id="btnCloseModal" type="button">&times;</button>
    </div>
    
    <div class="modal-grid">
      <?php foreach ($types as $t): ?>
        <div class="modal-card" data-slug="<?= htmlspecialchars($t['slug']) ?>" data-label="<?= htmlspecialchars($t['label']) ?>">
          <?= htmlspecialchars($t['label']) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Autocomplete & Dynamic Iframe Logic -->
<script>
window.getUniqueCertificateNumber = function(slug, baseNumber, currentWindow) {
  let candidate = baseNumber;
  let isUnique = false;
  
  function incrementNo(no) {
    const match = no.match(/^(.*?)(\d+)$/);
    if (match) {
      const prefix = match[1];
      const numStr = match[2];
      const nextNum = parseInt(numStr, 10) + 1;
      const paddedNum = String(nextNum).padStart(numStr.length, '0');
      return prefix + paddedNum;
    }
    return no;
  }
  
  let attempts = 0;
  while (!isUnique && attempts < 100) {
    attempts++;
    let foundCollision = false;
    const iframes = document.querySelectorAll('.instrument-iframe');
    for (let i = 0; i < iframes.length; i++) {
      const iframe = iframes[i];
      try {
        const iframeWindow = iframe.contentWindow;
        if (iframeWindow && iframeWindow !== currentWindow && iframeWindow.INSTRUMENT_SLUG === slug) {
          const iframeDoc = iframe.contentDocument || iframeWindow.document;
          const certNumInput = iframeDoc.getElementById('certificateNumber');
          if (certNumInput && certNumInput.value === candidate) {
            foundCollision = true;
            break;
          }
        }
      } catch (e) {
        // Safe catch for iframe loading states
      }
    }
    if (foundCollision) {
      candidate = incrementNo(candidate);
    } else {
      isUnique = true;
    }
  }
  return candidate;
};

let companiesList = [];
let activeIframeId = 0;

// â”€â”€ 1. Fetch Autocomplete Data on Load â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function fetchCompanies() {
  try {
    const res = await fetch(SHREEJI_CONFIG.apiBase + '/get_parties_try.php');
    const data = await res.json();
    if (data.success && data.parties) {
      companiesList = data.parties;
    }
  } catch (err) {
    if (window.SHREEJI_DEBUG) console.error('Failed to load companies autocomplete:', err);
  }
}

// â”€â”€ 2. Autocomplete Dropdown Handler â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const nameInput = document.getElementById('parentCompanyName');
const locInput = document.getElementById('parentSiteLocation');
const dropdown = document.getElementById('autocompleteDropdown');

function renderDropdown() {
  const query = nameInput.value.toLowerCase().trim();
  dropdown.innerHTML = '';
  
  let matches = [];
  if (query.length === 0) {
    // Show all companies sorted alphabetically (pre-sorted from API)
    matches = companiesList;
  } else {
    // Group 1: Starts with query
    const startsWithMatches = [];
    // Group 2: Contains query but does not start with query
    const containsMatches = [];
    
    companiesList.forEach(item => {
      const nameLower = item.name.toLowerCase();
      if (nameLower.startsWith(query)) {
        startsWithMatches.push(item);
      } else if (nameLower.includes(query)) {
        containsMatches.push(item);
      }
    });
    
    // Combine group 1 and group 2 (preserving alphabetical sorting within groups)
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
    
    div.addEventListener('click', function() {
      nameInput.value = item.name;
      locInput.value = item.site_location || '';
      dropdown.classList.remove('active');
      
      // Dispatch event to push values into iframes
      nameInput.dispatchEvent(new Event('input'));
      locInput.dispatchEvent(new Event('input'));
    });
    
    dropdown.appendChild(div);
  });
  
  dropdown.classList.add('active');
}

// Debounced input — avoids re-rendering on every keystroke
let _acDebounce;
nameInput.addEventListener('input', () => {
  clearTimeout(_acDebounce);
  _acDebounce = setTimeout(renderDropdown, 280);
});
nameInput.addEventListener('focus', renderDropdown);
nameInput.addEventListener('click', renderDropdown);

// Keyboard navigation: ArrowDown / ArrowUp / Enter / Escape
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
    items[_acIndex]?.click();
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

// Reset index on re-render
const _origRenderDropdown = renderDropdown;

// Close autocomplete when clicking elsewhere
document.addEventListener('click', function(e) {
  if (!nameInput.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.classList.remove('active');
    _acIndex = -1;
  }
});

// â”€â”€ 3. Modal Actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const modal = document.getElementById('instrumentModal');
const btnAdd = document.getElementById('btnAddInstrument');
const btnClose = document.getElementById('btnCloseModal');

btnAdd.addEventListener('click', () => modal.classList.add('active'));
btnClose.addEventListener('click', () => modal.classList.remove('active'));

modal.addEventListener('click', (e) => {
  if (e.target === modal) modal.classList.remove('active');
});

// â”€â”€ 4. Dynamically Add Instrument Iframes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const container = document.getElementById('activeInstrumentsContainer');

document.querySelectorAll('.modal-card').forEach(card => {
  card.addEventListener('click', function() {
    const slug = this.getAttribute('data-slug');
    const label = this.getAttribute('data-label');
    
    activeIframeId++;
    const uniqueId = `iframe_inst_${activeIframeId}`;
    
    // Create Wrapper Card
    const wrapper = document.createElement('div');
    wrapper.className = 'instrument-wrapper-card';
    wrapper.id = `card_${uniqueId}`;
    
    wrapper.innerHTML = `
      <div class="instrument-card-header">
        <h3><i class="fas fa-microscope" style="color:var(--primary);"></i> ${label} Certificate</h3>
        <div class="instrument-actions-group">
          <div class="more-actions-dropdown">
            <button class="instrument-action-btn btn-more" type="button" onclick="toggleMoreActionsDropdown('${uniqueId}')">
              <i class="fas fa-cog"></i> Actions
            </button>
            <div class="dropdown-menu" id="dropdown_${uniqueId}">
              <button class="dropdown-item" type="button" onclick="triggerIframeAction('${uniqueId}', 'share')">
                <i class="fas fa-paper-plane"></i> Share PDF
              </button>
              <button class="dropdown-item" type="button" onclick="triggerIframeAction('${uniqueId}', 'sticker')">
                <i class="fas fa-tag"></i> Generate Sticker
              </button>
              <button class="dropdown-item btn-dl-sticker" type="button" id="dl_sticker_btn_${uniqueId}" onclick="triggerIframeAction('${uniqueId}', 'downloadSticker')" style="display: none;">
                <i class="fas fa-download"></i> Download Sticker
              </button>
            </div>
          </div>
          <button class="remove-instrument-btn" type="button" onclick="removeInstrument('${uniqueId}')">
            <i class="fas fa-trash"></i> Remove
          </button>
        </div>
      </div>
      <iframe 
        src="certificates/${slug}.php?embed=true" 
        id="${uniqueId}" 
        class="instrument-iframe" 
        onload="initEmbeddedIframe('${uniqueId}')"
      ></iframe>
    `;
    
    container.appendChild(wrapper);
    updateGlobalActionsState();
    modal.classList.remove('active');
  });
});

function removeInstrument(uniqueId) {
  const card = document.getElementById(`card_${uniqueId}`);
  if (card && confirm('Are you sure you want to remove this instrument sub-form? Any unsaved inputs inside it will be lost.')) {
    card.remove();
    updateGlobalActionsState();
  }
}

// â”€â”€ 5. Global Actions State & Bulk Handlers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function updateGlobalActionsState() {
  const container = document.getElementById('activeInstrumentsContainer');
  const bar = document.getElementById('globalActionsBar');
  const countText = document.getElementById('selectedCountText');
  
  const count = container.querySelectorAll('.instrument-wrapper-card').length;
  if (countText) countText.textContent = count;
  
  if (count > 0) {
    bar.classList.add('active');
  } else {
    bar.classList.remove('active');
  }
}

function validateAllForms() {
  const iframes = document.querySelectorAll('.instrument-iframe');
  for (let i = 0; i < iframes.length; i++) {
    const iframe = iframes[i];
    const iframeWindow = iframe.contentWindow;
    const iframeDoc = iframe.contentDocument || iframeWindow.document;
    const form = iframeDoc.getElementById('calibrationForm');
    const cardHeader = iframe.closest('.instrument-wrapper-card').querySelector('h3').textContent.trim();
    
    if (form && !form.checkValidity()) {
      alert(`Please fill all required fields in the "${cardHeader}" form.`);
      // Focus on the first invalid field inside the iframe
      const invalidField = form.querySelector(':invalid');
      if (invalidField) {
        invalidField.focus();
        iframe.scrollIntoView({ behavior: 'smooth' });
      }
      return false;
    }
  }
  return true;
}

async function generateUnifiedPDF() {
  const iframes = document.querySelectorAll('.instrument-iframe');
  if (iframes.length === 0) {
    alert('No instrument certificates have been added yet.');
    return null;
  }
  
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
  
  // Store original jsPDF functions to restore them at the end of each iframe's drawing pass
  const orgAddPage = doc.addPage;
  const orgSetPage = doc.setPage;
  const orgGetPages = doc.internal.getNumberOfPages;
  
  for (let i = 0; i < iframes.length; i++) {
    const iframe = iframes[i];
    const iframeWindow = iframe.contentWindow;
    
    // Pre-load images inside the iframe asynchronously if prepareImages is defined
    if (typeof iframeWindow.prepareImages === 'function') {
      try {
        await iframeWindow.prepareImages();
      } catch (e) {
        if (window.SHREEJI_DEBUG) console.warn('Failed to pre-run prepareImages inside iframe:', e);
      }
    }
    
    if (i > 0) {
      doc.addPage();
    }
    
    const currentStartPage = doc.internal.getNumberOfPages();
    doc.setPage(currentStartPage);
    
    // Set direct overrides (monkey-patching) on this jsPDF instance for this iframe
    let pageCount = 1;
    doc.addPage = function(...args) {
      const res = orgAddPage.apply(doc, args);
      pageCount++;
      return res;
    };
    doc.setPage = function(pageNum) {
      const physicalPage = currentStartPage + pageNum - 1;
      return orgSetPage.call(doc, physicalPage);
    };
    doc.internal.getNumberOfPages = function() {
      return pageCount;
    };
    
    try {
      if (typeof iframeWindow.getFormDetails === 'function') {
        const details = iframeWindow.getFormDetails();
        
        // Draw certificate details on the overridden document
        if (typeof iframeWindow.addCertificateDetails === 'function') {
          iframeWindow.addCertificateDetails(doc, details);
        }
        
        // Draw images/letterhead on the overridden document
        if (typeof iframeWindow.addImg === 'function') {
          iframeWindow.addImg(doc, details);
        }
      }
    } catch (err) {
      if (window.SHREEJI_DEBUG) console.error(`Failed to draw PDF page for iframe ${i}:`, err);
    } finally {
      // Restore original functions
      doc.addPage = orgAddPage;
      doc.setPage = orgSetPage;
      doc.internal.getNumberOfPages = orgGetPages;
    }
  }
  
  return doc;
}

async function previewAllCertificates() {
  if (!validateAllForms()) return;
  
  Loader.show('Generating unified preview...');
  try {
    const doc = await generateUnifiedPDF();
    if (!doc) {
      Loader.hide();
      return;
    }
    const pdfBlob = doc.output('blob');
    const pdfUrl  = URL.createObjectURL(pdfBlob);
    
    window.showGlobalPreviewModal(pdfUrl);
    
    setTimeout(() => URL.revokeObjectURL(pdfUrl), 60000);
    Loader.hide();
  } catch (err) {
    if (window.SHREEJI_DEBUG) console.error(err);
    Loader.error('Failed to generate preview: ' + err.message);
  }
}

async function printAllCertificates() {
  if (!validateAllForms()) return;
  
  Loader.show('Preparing unified print...');
  try {
    const doc = await generateUnifiedPDF();
    if (!doc) {
      Loader.hide();
      return;
    }
    const pdfBlob  = doc.output('blob');
    const pdfUrl   = URL.createObjectURL(pdfBlob);
    const printWindow = window.open(pdfUrl);
    setTimeout(() => {
      if (printWindow) {
        printWindow.print();
      } else {
        alert('Popup blocker prevented opening the print window. Please allow popups.');
      }
      setTimeout(() => URL.revokeObjectURL(pdfUrl), 5000);
    }, 500);
    Loader.success('Print dialog opened! ðŸ–¨ï¸');
  } catch (err) {
    if (window.SHREEJI_DEBUG) console.error(err);
    Loader.error('Failed to print: ' + err.message);
  }
}

async function shareUnifiedPDF() {
  if (!validateAllForms()) return;
  
  Loader.show('Generating combined PDF to share...');
  try {
    const doc = await generateUnifiedPDF();
    if (!doc) {
      Loader.hide();
      return;
    }
    const pdfBlob = doc.output('blob');
    const pdfUrl = URL.createObjectURL(pdfBlob);
    
    const companyName = nameInput.value.replace(/[^a-zA-Z0-9]/g, '_') || 'company';
    const fileName = `Combined_Certificates_${companyName}.pdf`;
    const pdfFile = new File([pdfBlob], fileName, { type: "application/pdf" });
    
    if (navigator.share && navigator.canShare && navigator.canShare({ files: [pdfFile] })) {
      await navigator.share({
        files: [pdfFile],
        title: 'Calibration Certificates',
        text: `Calibration Certificates for ${nameInput.value}`
      });
      Loader.success('Shared successfully! ðŸ“¤');
    } else {
      window.open(pdfUrl);
      Loader.success('Opened in browser! ðŸ“¥');
    }
  } catch (err) {
    if (err.name !== 'AbortError') {
      Loader.error('Share failed: ' + err.message);
    } else {
      Loader.hide();
    }
  }
}

function saveIframePromise(iframe) {
  return new Promise((resolve, reject) => {
    let onSuccess, onError;
    
    onSuccess = (e) => {
      iframe.removeEventListener('iframeSaveSuccess', onSuccess);
      iframe.removeEventListener('iframeSaveError', onError);
      resolve(e.detail);
    };
    
    onError = (e) => {
      iframe.removeEventListener('iframeSaveSuccess', onSuccess);
      iframe.removeEventListener('iframeSaveError', onError);
      reject(e.detail);
    };
    
    iframe.addEventListener('iframeSaveSuccess', onSuccess);
    iframe.addEventListener('iframeSaveError', onError);
    
    // Trigger the save action click inside the iframe
    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    const btn = iframeDoc.getElementById('uploadBtn');
    if (btn) {
      btn.click();
    } else {
      reject(new Error('Save button not found inside iframe.'));
    }
  });
}

async function saveAllCertificates() {
  if (!validateAllForms()) return;
  
  const iframes = document.querySelectorAll('.instrument-iframe');
  if (iframes.length === 0) {
    alert('No instrument certificates have been added yet.');
    return;
  }
  
  // Verify company details are filled
  if (!nameInput.value.trim()) {
    alert('Please enter a Company Name first.');
    nameInput.focus();
    return;
  }
  
  // Request directory permission on parent level first to satisfy user gesture requirement!
  /* Disabled local folder saving by user request
  if ('showDirectoryPicker' in window) {
    try {
      let dirHandle = await window.getSavedDirectoryHandle();
      if (!dirHandle) {
        dirHandle = await window.promptForDirectorySelection();
      }
      if (dirHandle) {
        const hasPerm = await window.verifyPermission(dirHandle, true);
        if (!hasPerm) {
           alert('Write permission is required to save certificates locally.');
           return;
        }
      } else {
        alert('Local storage base folder is required to save certificates.');
        return;
      }
    } catch (err) {
      if (window.SHREEJI_DEBUG) console.error('Local storage authorization failed on parent:', err);
      alert('Local storage access error: ' + err.message);
      return;
    }
  }
  */
  
  Loader.show('Saving all certificates to database and folders...');
  let successCount = 0;
  let errorCount = 0;
  const errors = [];
  
  // 1. Sequentially save each instrument certificate to local folders & DB
  for (let i = 0; i < iframes.length; i++) {
    const iframe = iframes[i];
    const cardHeader = iframe.closest('.instrument-wrapper-card').querySelector('h3').textContent.trim();
    
    Loader.show(`Saving ${cardHeader} (${i + 1}/${iframes.length})...`);
    
    try {
      const iframeWindow = iframe.contentWindow;
      const iframeDoc = iframe.contentDocument || iframeWindow.document;
      const slug = iframeWindow.INSTRUMENT_SLUG;
      const certNumInput = iframeDoc.getElementById('certificateNumber');
      
      // Query the database counter for a fresh next certificate number to prevent collision
      const urlParams = new URLSearchParams(iframeWindow.location.search);
      const isNew = !urlParams.get('id');
      
      if (isNew && slug && certNumInput) {
        try {
          const response = await fetch(SHREEJI_CONFIG.apiBase + '/get_next_certificate_number.php?instrument_type=' + slug);
          const result = await response.json();
          if (result.success && result.next_certificate_number) {
            let finalCertNo = result.next_certificate_number;
            if (typeof window.getUniqueCertificateNumber === 'function') {
              finalCertNo = window.getUniqueCertificateNumber(slug, finalCertNo, iframeWindow);
            }
            certNumInput.value = finalCertNo;
            certNumInput.dispatchEvent(new Event('input', { bubbles: true }));
            certNumInput.dispatchEvent(new Event('change', { bubbles: true }));
          }
        } catch (err) {
          if (window.SHREEJI_DEBUG) console.error('Failed to update certificate number before saving:', err);
        }
      }
      
      // Temporarily disable window.open inside the iframe to prevent popup blocker errors
      const originalOpen = iframeWindow.open;
      iframeWindow.open = function() { return null; };
      
      const result = await saveIframePromise(iframe);
      
      // Restore window.open
      iframeWindow.open = originalOpen;
      if (result.success) {
        successCount++;
      } else {
        errorCount++;
        errors.push(`${cardHeader}: ${result.message || 'Unknown error'}`);
      }
    } catch (err) {
      errorCount++;
      errors.push(`${cardHeader}: ${err.message || err}`);
    }
  }
  
  // 2. Download the unified, combined multi-page PDF locally for the user
  if (successCount > 0) {
    try {
      Loader.show('Generating combined PDF document...');
      const doc = await generateUnifiedPDF();
      if (doc) {
        const pdfBlob = doc.output('blob');
        const companyName = nameInput.value.replace(/[^a-zA-Z0-9]/g, '_') || 'company';
        const fileName = `Combined_Certificates_${companyName}.pdf`;
        
        // Use browser download
        const url = URL.createObjectURL(pdfBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
      }
    } catch (err) {
      if (window.SHREEJI_DEBUG) console.error('Failed to download combined PDF:', err);
    }
  }
  
  if (errorCount === 0) {
    Loader.success(`Successfully saved and synced all ${successCount} certificate(s)! âœ¨`);
  } else {
    Loader.error(`Saved ${successCount} certificate(s). Failed to save ${errorCount} certificate(s).`);
    alert('Errors:\n' + errors.join('\n'));
  }
}

// â”€â”€ Dropdown Actions & Loader Bridge â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function toggleMoreActionsDropdown(uniqueId) {
  const menu = document.getElementById(`dropdown_${uniqueId}`);
  if (!menu) return;
  
  // Close other dropdowns
  document.querySelectorAll('.dropdown-menu').forEach(m => {
    if (m.id !== `dropdown_${uniqueId}`) {
      m.classList.remove('show');
    }
  });
  
  menu.classList.toggle('show');
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
  if (!e.target.closest('.more-actions-dropdown')) {
    document.querySelectorAll('.dropdown-menu').forEach(m => {
      m.classList.remove('show');
    });
  }
});

async function triggerIframeAction(iframeId, action) {
  const iframe = document.getElementById(iframeId);
  if (!iframe) return;
  
  if (action === 'save') {
    // Request directory permission on parent level first to satisfy user gesture requirement!
    /* Disabled local folder saving by user request
    if ('showDirectoryPicker' in window) {
      try {
        let dirHandle = await window.getSavedDirectoryHandle();
        if (!dirHandle) {
          dirHandle = await window.promptForDirectorySelection();
        }
        if (dirHandle) {
          const hasPerm = await window.verifyPermission(dirHandle, true);
          if (!hasPerm) {
             alert('Write permission is required to save certificates locally.');
             return;
          }
        } else {
          alert('Local storage base folder is required to save certificates.');
          return;
        }
      } catch (err) {
        if (window.SHREEJI_DEBUG) console.error('Local storage authorization failed on parent:', err);
        alert('Local storage access error: ' + err.message);
        return;
      }
    }
    */
  }
  
  try {
    const iframeWindow = iframe.contentWindow;
    const iframeDoc = iframe.contentDocument || iframeWindow.document;
    
    if (action === 'preview') {
      if (typeof iframeWindow.preview === 'function') {
        iframeWindow.preview();
      } else {
        const btn = iframeDoc.querySelector('button[onclick*="preview"]');
        if (btn) btn.click();
      }
    } else if (action === 'save') {
      const btn = iframeDoc.getElementById('uploadBtn');
      if (btn) {
        btn.click();
      } else {
        alert('Save button not found in this instrument form.');
      }
    } else if (action === 'print') {
      if (typeof iframeWindow.printBlankCertificate === 'function') {
        iframeWindow.printBlankCertificate();
      } else {
        const btn = iframeDoc.querySelector('button[onclick*="printBlankCertificate"]') || iframeDoc.querySelector('button[onclick*="print"]');
        if (btn) btn.click();
      }
    } else if (action === 'share') {
      if (typeof iframeWindow.sharePDF === 'function') {
        iframeWindow.sharePDF();
      } else {
        const btn = iframeDoc.querySelector('button[onclick*="sharePDF"]');
        if (btn) btn.click();
      }
    } else if (action === 'sticker') {
      if (typeof iframeWindow.generateInfoSticker === 'function') {
        iframeWindow.generateInfoSticker();
      } else {
        const btn = iframeDoc.querySelector('button[onclick*="generateInfoSticker"]');
        if (btn) btn.click();
      }
    } else if (action === 'downloadSticker') {
      if (typeof iframeWindow.downloadSticker === 'function') {
        iframeWindow.downloadSticker();
      } else {
        const btn = iframeDoc.getElementById('downloadStickerBtn');
        if (btn) btn.click();
      }
    }
  } catch (e) {
    if (window.SHREEJI_DEBUG) console.error(`Failed to trigger action "${action}" inside iframe ${iframeId}:`, e);
    alert('An error occurred while executing the action.');
  }
}

// â”€â”€ 6. Style and Sync Iframe DOM â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function initEmbeddedIframe(iframeId) {
  const iframe = document.getElementById(iframeId);
  if (!iframe) return;
  
  // Height observer
  const adjustHeight = () => {
    try {
      const doc = iframe.contentDocument || iframe.contentWindow.document;
      iframe.style.height = (doc.body.scrollHeight + 35) + 'px';
    } catch(e) {
      if (window.SHREEJI_DEBUG) console.warn('Iframe resizing blocked by security policy', e);
    }
  };
  
  try {
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    const iframeWindow = iframe.contentWindow;
    
    // Inject Overriding CSS to hide header/sidebar/footer/h2.centered
    const style = doc.createElement('style');
    style.textContent = `
      .nav-sidebar, .nav-sidebar-toggle, footer, .back-button, .side-dock { display: none !important; }
      body { background: #ffffff !important; padding: 1.5rem !important; min-height: auto !important; }
      .page-wrapper, .container { margin: 0 !important; padding: 0 !important; max-width: 100% !important; box-shadow: none !important; background: transparent !important; }
      .form-card, .card { box-shadow: none !important; border: none !important; padding: 1rem 0 !important; }
      h2.centered { display: none !important; }
    `;
    doc.head.appendChild(style);
    
    // Override local functions inside iframe to use parent Loader
    if (iframeWindow.Loader) {
      iframeWindow.Loader.show = function(msg) { Loader.show(msg); };
      iframeWindow.Loader.success = function(msg) { Loader.success(msg); };
      iframeWindow.Loader.error = function(msg) { Loader.error(msg); };
      iframeWindow.Loader.hide = function() { Loader.hide(); };
    }
    iframeWindow.showLoader = function(msg) { Loader.show(msg); };
    iframeWindow.hideLoader = function() { Loader.hide(); };
    iframeWindow.showLoaderSuccess = function(msg) { Loader.success(msg); };

    // Override local FS functions inside iframe to bypass permission checks (already authorized on parent)
    iframeWindow.verifyPermission = async function() {
      return true;
    };
    iframeWindow.promptForDirectorySelection = async function() {
      return await window.getSavedDirectoryHandle();
    };

    // Intercept saveCertificateOfflineFirst to communicate save success/error back to parent page
    let attempts = 0;
    const hookSave = () => {
      const originalSave = iframeWindow.saveCertificateOfflineFirst;
      if (originalSave) {
        if (iframeWindow.saveCertificateOfflineFirst.__isHooked) return;
        iframeWindow.saveCertificateOfflineFirst = async function(payload, pdfBlob, details) {
          try {
            const result = await originalSave(payload, pdfBlob, details);
            iframe.dispatchEvent(new CustomEvent('iframeSaveSuccess', { detail: result }));
            return result;
          } catch (err) {
            iframe.dispatchEvent(new CustomEvent('iframeSaveError', { detail: err }));
            throw err;
          }
        };
        iframeWindow.saveCertificateOfflineFirst.__isHooked = true;
      } else if (attempts < 20) {
        attempts++;
        setTimeout(hookSave, 100);
      }
    };
    hookSave();

    // Hide repeated fields inside the iframe (Name of Party, Site Location, Calibration Date, Next Suggested Date)
    const fieldsToHide = ['partyName', 'partyname', 'siteLocation', 'calibrationDate', 'nextCalibrationDate'];
    fieldsToHide.forEach(id => {
      const el = doc.getElementById(id);
      if (el) {
        const parent = el.closest('.title_input_pair') || el.closest('.form-group') || el.closest('.form-row') || el.parentElement;
        if (parent) {
          parent.style.setProperty('display', 'none', 'important');
        }
      }
    });

    // Observe changes to the downloadStickerBtn's style attribute to toggle parent's download sticker button
    const dlBtn = doc.getElementById('downloadStickerBtn');
    const parentDlBtn = document.getElementById(`dl_sticker_btn_${iframeId}`);
    if (dlBtn && parentDlBtn) {
      const observer = new MutationObserver(() => {
        if (dlBtn.style.display !== 'none') {
          parentDlBtn.style.display = 'flex';
        } else {
          parentDlBtn.style.display = 'none';
        }
      });
      observer.observe(dlBtn, { attributes: true, attributeFilter: ['style'] });
    }
    
    // Push current parent values down immediately
    syncSingleIframe(iframe);
    
    // Setup height listeners
    adjustHeight();
    setTimeout(adjustHeight, 500);
    
    // Listen to changes in size dynamically
    if (window.ResizeObserver) {
      const observer = new ResizeObserver(() => adjustHeight());
      observer.observe(doc.body);
    } else {
      setInterval(adjustHeight, 2000);
    }
  } catch(e) {
    if (window.SHREEJI_DEBUG) console.error('Cannot style or configure child iframe:', e);
  }
}

// â”€â”€ 7. Push Company Details to Child Iframes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function syncAllIframes() {
  const companyName = nameInput.value;
  const siteLocation = locInput.value;
  const calibDate = document.getElementById('parentCalibrationDate').value;
  const nextDate = document.getElementById('parentNextCalibrationDate').value;
  
  document.querySelectorAll('.instrument-iframe').forEach(iframe => {
    syncSingleIframe(iframe, companyName, siteLocation, calibDate, nextDate);
  });
}

function syncSingleIframe(iframe, companyName, siteLocation, calibDate, nextDate) {
  try {
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    const nameVal = companyName !== undefined ? companyName : nameInput.value;
    const locVal = siteLocation !== undefined ? siteLocation : locInput.value;
    const calibVal = calibDate !== undefined ? calibDate : document.getElementById('parentCalibrationDate').value;
    const nextVal = nextDate !== undefined ? nextDate : document.getElementById('parentNextCalibrationDate').value;
    
    const nameField = doc.getElementById('partyName') || doc.getElementById('partyname');
    const locField = doc.getElementById('siteLocation');
    const calibField = doc.getElementById('calibrationDate');
    const nextField = doc.getElementById('nextCalibrationDate');
    
    if (nameField) {
      nameField.value = nameVal;
      nameField.dispatchEvent(new Event('input', { bubbles: true }));
      nameField.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (locField) {
      locField.value = locVal;
      locField.dispatchEvent(new Event('input', { bubbles: true }));
      locField.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (calibField) {
      calibField.value = calibVal;
      calibField.dispatchEvent(new Event('input', { bubbles: true }));
      calibField.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (nextField) {
      nextField.value = nextVal;
      nextField.dispatchEvent(new Event('input', { bubbles: true }));
      nextField.dispatchEvent(new Event('change', { bubbles: true }));
    }
  } catch(e) {
    if (window.SHREEJI_DEBUG) console.warn('Sync failed for iframe:', e);
  }
}

// Parent Dates Auto-calculation
const parentCalibDateInput = document.getElementById('parentCalibrationDate');
const parentNextDateInput = document.getElementById('parentNextCalibrationDate');

function calculateParentNextDate() {
  if (parentCalibDateInput && parentCalibDateInput.value && parentNextDateInput) {
    const date = new Date(parentCalibDateInput.value);
    date.setFullYear(date.getFullYear() + 1);
    date.setDate(date.getDate() - 1);
    parentNextDateInput.value = date.toISOString().split('T')[0];
    syncAllIframes();
  }
}

if (parentCalibDateInput) {
  // Default to today on load
  if (!parentCalibDateInput.value) {
    parentCalibDateInput.value = new Date().toISOString().split('T')[0];
  }
  calculateParentNextDate();
  parentCalibDateInput.addEventListener('change', () => {
    calculateParentNextDate();
    syncAllIframes();
  });
}

if (parentNextDateInput) {
  parentNextDateInput.addEventListener('change', syncAllIframes);
}

// Event listeners to sync details on keypress
nameInput.addEventListener('input', syncAllIframes);
locInput.addEventListener('input', syncAllIframes);

// Initialize
document.addEventListener('DOMContentLoaded', () => {
  fetchCompanies();
  updateGlobalActionsState();
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
