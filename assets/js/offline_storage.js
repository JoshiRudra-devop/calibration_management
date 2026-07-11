/* SHREEJI INSTRUMENTS - Offline-First Local Storage & Cloud Sync Utility */
// Debug logging -- only active when window.SHREEJI_DEBUG = true
const _log  = (...a) => { if (window.SHREEJI_DEBUG) console.log(...a); };
const _logE = (...a) => { if (window.SHREEJI_DEBUG) console.error(...a); };
const _logW = (...a) => { if (window.SHREEJI_DEBUG) console.warn(...a); };

const OFFLINE_DB_CONFIG = {
  dbName: 'ShreejiOfflineDB',
  storeName: 'settings',
  keyName: 'directory_handle',
  dbVersion: 2
};

const INSTRUMENT_LABELS = {
  'autolevel': 'Auto Level',
  'aggregate_impact': 'Aggregate Impact',
  'ctm': 'CTM',
  'cone_penetro': 'Cone Penetrometer',
  'core_cutter': 'Core Cutter',
  'cube_mould': 'Cube Mould',
  'digital_thermo': 'Digital Thermometer',
  'elongation': 'Elongation Gauge',
  'oven': 'Electrical Hot Air Oven',
  'flakness': 'Flakness Gauge',
  'general': 'General Format',
  'hydrometer': 'Hydrometer',
  'isi_cube': 'ISI Cube Mould',
  'measuring_cyl': 'Measuring Cylinder',
  'pycnometer': 'Pycnometer Bottle',
  'ph_meter': 'PH Meter',
  'rapid_moisture': 'Rapid Moisture Meter',
  'sieves': 'Test Sieves',
  'sand_pouring': 'Sand Pouring Cylinder',
  'slumcone': 'Slump Cone',
  'total_station': 'Total Station',
  'water_bath': 'Water Bath',
  'vernier_caliper': 'Vernier Caliper',
  'weight_balance': 'Weight Balance',
  'weigh_batcher': 'Weigh Batcher',
  'full_lab': 'Full Lab Report'
};

// â”€â”€ Open IndexedDB â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function openOfflineDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(OFFLINE_DB_CONFIG.dbName, OFFLINE_DB_CONFIG.dbVersion);
    request.onupgradeneeded = (e) => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains(OFFLINE_DB_CONFIG.storeName)) {
        db.createObjectStore(OFFLINE_DB_CONFIG.storeName);
      }
      if (!db.objectStoreNames.contains('sync_queue')) {
        db.createObjectStore('sync_queue');
      }
    };
    request.onsuccess = (e) => resolve(e.target.result);
    request.onerror = (e) => reject(e.target.error);
  });
}

// â”€â”€ PERSISTED HANDLE GET/SET â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function getSavedDirectoryHandle() {
  try {
    const db = await openOfflineDB();
    return new Promise((resolve, reject) => {
      const transaction = db.transaction(OFFLINE_DB_CONFIG.storeName, 'readonly');
      const store = transaction.objectStore(OFFLINE_DB_CONFIG.storeName);
      const request = store.get(OFFLINE_DB_CONFIG.keyName);
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  } catch (e) {
    _logE('Error opening IndexedDB for handle retrieval:', e);
    return null;
  }
}

async function saveDirectoryHandle(handle) {
  try {
    const db = await openOfflineDB();
    return new Promise((resolve, reject) => {
      const transaction = db.transaction(OFFLINE_DB_CONFIG.storeName, 'readwrite');
      const store = transaction.objectStore(OFFLINE_DB_CONFIG.storeName);
      const request = store.put(handle, OFFLINE_DB_CONFIG.keyName);
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    });
  } catch (e) {
    _logE('Error saving handle to IndexedDB:', e);
  }
}

// â”€â”€ PERMISSION CHECKING â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function verifyPermission(fileHandle, readWrite) {
  const options = {};
  if (readWrite) {
    options.mode = 'readwrite';
  }
  if ((await fileHandle.queryPermission(options)) === 'granted') {
    return true;
  }
  if ((await fileHandle.requestPermission(options)) === 'granted') {
    return true;
  }
  return false;
}

// â”€â”€ UTILITIES â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function sanitizeFolderName(name) {
  if (!name) return 'Unknown';
  let cleanName = name.replace(/[\\/:*?"<>|]/g, '_');
  cleanName = cleanName.replace(/\s+/g, ' ');
  cleanName = cleanName.trim().replace(/\.+$/, '').trim();
  if (!cleanName || cleanName === '') {
    return 'Unknown';
  }
  return cleanName;
}

function getInstrumentLabel(slug) {
  if (typeof SHREEJI_CONFIG !== 'undefined' && SHREEJI_CONFIG.instrumentLabel) {
    return SHREEJI_CONFIG.instrumentLabel;
  }
  return INSTRUMENT_LABELS[slug] || slug || 'Other Instruments';
}

// â”€â”€ PROMPT DIRECTORY PICKER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function promptForDirectorySelection() {
  if (!('showDirectoryPicker' in window)) {
    alert('This browser does not support local folder saving. Please use Google Chrome or Microsoft Edge.');
    return null;
  }
  try {
    const handle = await window.showDirectoryPicker({
      mode: 'readwrite'
    });
    if (handle) {
      await saveDirectoryHandle(handle);
      hideSetupBanner();
      updateLocalStorageStatus(true);
      
      // Initialize subfolders instantly on first setup
      try {
        const hasPerm = await verifyPermission(handle, true);
        if (hasPerm) {
          await handle.getDirectoryHandle('Calibration Certificates', { create: true });
        }
      } catch (err) {
        _logE('Failed to create Calibration Certificates main folder:', err);
      }
      
      return handle;
    }
  } catch (err) {
    _logE('Directory selection cancelled or failed:', err);
    if (err.name !== 'AbortError') {
      alert('Failed to access selected directory: ' + err.message);
    }
  }
  return null;
}

// â”€â”€ CHECK AND MANAGE SETUP BANNER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function checkLocalStorageSetup() {
  // Setup banner disabled by user request (online only)
  return;
}

function showSetupBanner() {
  if (document.getElementById('localStorageBanner')) return;
  
  const banner = document.createElement('div');
  banner.id = 'localStorageBanner';
  banner.className = 'local-storage-banner';
  banner.innerHTML = `
    <div class="banner-content">
      <i class="fas fa-folder-open banner-icon"></i>
      <span><strong>Configure Local Storage:</strong> Enable offline-first local saving by selecting a folder.</span>
    </div>
    <button id="btnConfigureLocalStorage" class="banner-btn" type="button">Select Folder</button>
  `;
  document.body.appendChild(banner);
  
  // Trigger transition
  setTimeout(() => banner.classList.add('show'), 100);
  
  document.getElementById('btnConfigureLocalStorage').addEventListener('click', promptForDirectorySelection);
}

function hideSetupBanner() {
  const banner = document.getElementById('localStorageBanner');
  if (banner) {
    banner.classList.remove('show');
    setTimeout(() => banner.remove(), 400);
  }
}

function updateLocalStorageStatus(connected, labelText) {
  const statusDiv = document.getElementById('localStorageStatus');
  if (!statusDiv) return;
  
  if (connected) {
    statusDiv.className = 'local-storage-status connected';
    statusDiv.innerHTML = `
      <i class="fas fa-folder-open"></i>
      <span>Local Storage Active</span>
    `;
    statusDiv.title = 'Local storage directory connected successfully.';
    statusDiv.onclick = null;
  } else {
    statusDiv.className = 'local-storage-status disconnected';
    statusDiv.innerHTML = `
      <i class="fas fa-folder"></i>
      <span>${labelText || 'Local Storage Offline'}</span>
    `;
    statusDiv.title = 'Click to connect or select a local storage folder.';
    statusDiv.onclick = async function() {
      const handle = await getSavedDirectoryHandle();
      if (handle) {
        const hasPerm = await verifyPermission(handle, true);
        if (hasPerm) {
          updateLocalStorageStatus(true);
        }
      } else {
        await promptForDirectorySelection();
      }
    };
  }
}

// â”€â”€ SAVE PDF TO LOCAL FOLDER HIERARCHY â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function savePdfToLocalFolder(pdfBlob, filename, instrumentType, partyName, siteLocation) {
  let dirHandle = await getSavedDirectoryHandle();
  if (!dirHandle) {
    dirHandle = await promptForDirectorySelection();
    if (!dirHandle) {
      throw new Error('Local storage base folder not selected.');
    }
  }

  // Request/verify permissions
  const hasPermission = await verifyPermission(dirHandle, true);
  if (!hasPermission) {
    throw new Error('Write permission to the selected folder was denied.');
  }

  updateLocalStorageStatus(true);

  // 1. Create/Navigate to "Calibration Certificates"
  const mainDir = await dirHandle.getDirectoryHandle('Calibration Certificates', { create: true });

  // 2. Create/Navigate to Category Folder
  const instrLabel = getInstrumentLabel(instrumentType);
  const sanitizedCategory = sanitizeFolderName(instrLabel);
  const categoryDir = await mainDir.getDirectoryHandle(sanitizedCategory, { create: true });

  // 3. Create/Navigate to Company Folder
  const sanitizedCompany = sanitizeFolderName(partyName);
  const companyDir = await categoryDir.getDirectoryHandle(sanitizedCompany, { create: true });

  // 4. Create/Navigate to Site Location Folder if available
  let targetDir = companyDir;
  let finalSiteLocation = '';
  if (siteLocation && siteLocation.trim() !== '') {
    let folderName = siteLocation.trim();
    if (folderName.length > 30) {
      const proposed = folderName.substring(0, 30).trim();
      const userShort = prompt(
        `The site location name is quite long:\n"${folderName}"\n\nPlease enter a shorter name for the local folder:`,
        proposed
      );
      if (userShort !== null && userShort.trim() !== '') {
        folderName = userShort.trim();
      } else {
        folderName = proposed;
      }
    }
    const sanitizedSite = sanitizeFolderName(folderName);
    finalSiteLocation = sanitizedSite;
    targetDir = await companyDir.getDirectoryHandle(sanitizedSite, { create: true });
  }

  // 5. Save the PDF
  const sanitizedFilename = sanitizeFolderName(filename.replace(/\.pdf$/i, '')) + '.pdf';
  const fileHandle = await targetDir.getFileHandle(sanitizedFilename, { create: true });
  const writable = await fileHandle.createWritable();
  await writable.write(pdfBlob);
  await writable.close();

  return finalSiteLocation;
}

// â”€â”€ OFFLINE QUEUE MANAGEMENT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function queueForSync(payload, filename) {
  try {
    const db = await openOfflineDB();
    const item = {
      id: Date.now().toString(),
      payload: payload,
      filename: filename,
      timestamp: new Date().toISOString()
    };
    await new Promise((resolve, reject) => {
      const transaction = db.transaction('sync_queue', 'readwrite');
      const store = transaction.objectStore('sync_queue');
      const request = store.put(item, item.id);
      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    });
    _logE('Queued certificate for sync:', filename);
  } catch (err) {
    _logE('Failed to queue certificate for sync:', err);
  }
}

// â”€â”€ SYNC QUEUE PROCESSING â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function processSyncQueue() {
  if (!navigator.onLine) return;
  try {
    const db = await openOfflineDB();
    const items = await new Promise((resolve, reject) => {
      const transaction = db.transaction('sync_queue', 'readonly');
      const store = transaction.objectStore('sync_queue');
      const request = store.getAll();
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });

    if (items.length === 0) return;

    _logE(`Processing sync queue: ${items.length} item(s) found.`);
    
    for (const item of items) {
      try {
        const csrfToken = (typeof SHREEJI_CONFIG !== 'undefined' ? SHREEJI_CONFIG.csrfToken : '');
        const response = await fetch((typeof SHREEJI_CONFIG !== 'undefined' ? SHREEJI_CONFIG.apiBase : '/api') + '/save_certificates.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
          body: JSON.stringify({ ...item.payload, csrf_token: csrfToken })
        });
        const result = await response.json();
        
        if (result.success) {
          // Remove from IndexedDB queue
          await new Promise((resolve, reject) => {
            const transaction = db.transaction('sync_queue', 'readwrite');
            const store = transaction.objectStore('sync_queue');
            const request = store.delete(item.id);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
          });
          
          showSyncToast(`Synced "${item.filename}" to cloud!`);
        }
      } catch (err) {
        _logE(`Error syncing certificate ${item.filename}:`, err);
        break; // Stop loop and retry later if network fails again
      }
    }
  } catch (err) {
    _logE('Error processing sync queue:', err);
  }
}

// â”€â”€ SYNC TOAST NOTIFICATION â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function showSyncToast(msg) {
  let toast = document.getElementById('syncToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'syncToast';
    toast.className = 'sync-toast';
    document.body.appendChild(toast);
  }
  
  toast.innerHTML = `<i class="fas fa-cloud-upload-alt" style="color:var(--accent);"></i> <span>${msg}</span>`;
  setTimeout(() => toast.classList.add('show'), 100);
  
  setTimeout(() => {
    toast.classList.remove('show');
  }, 4000);
}

// â”€â”€ CENTRAL WRAPPER: OFFLINE-FIRST SAVING â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function saveCertificateOfflineFirst(payload, pdfBlob, details) {
  if (typeof Loader !== 'undefined') {
    Loader.show('Saving local certificate...');
  } else {
    _logE('Saving local certificate...');
  }

  const filename = `${payload.cert_number}.pdf`;
  const siteLocation = details.siteLocation || document.getElementById('siteLocation')?.value || '';

  try {
    // Step 1: Save locally using File System Access API
    // Disabled by user request: We only want to save online to Cloudinary/DB
    // const finalSiteLocation = await savePdfToLocalFolder(pdfBlob, filename, payload.instrument_type, payload.party_name, siteLocation);
    
    // Update payload with the actual folder name used
    payload.site_location = siteLocation;
    
    // Check network status
    if (navigator.onLine) {
      if (typeof Loader !== 'undefined') {
        Loader.show('Syncing to cloud...');
      }
      
      const response = await fetch(SHREEJI_CONFIG.apiBase + '/save_certificates.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': SHREEJI_CONFIG.csrfToken },
        body: JSON.stringify({ ...payload, csrf_token: SHREEJI_CONFIG.csrfToken })
      });
      
      const result = await response.json();
      
      if (result.success) {
        if (typeof UnsavedTracker !== 'undefined') {
          UnsavedTracker.markSaved();
        }
        if (typeof Loader !== 'undefined') {
          Loader.success('Saved locally & synced to cloud! âœ¨');
        }
        return result;
      } else {
        throw new Error(result.message || 'Cloud sync rejected by server.');
      }
    } else {
      // Offline: Add to background queue
      await queueForSync(payload, filename);
      if (typeof UnsavedTracker !== 'undefined') {
        UnsavedTracker.markSaved();
      }
      if (typeof Loader !== 'undefined') {
        Loader.success('Saved locally! Queued for sync (offline) â³');
      }
      return { success: true, queued: true, cert_number: payload.cert_number };
    }
  } catch (err) {
    _logE('Offline-first save error:', err);
    if (typeof Loader !== 'undefined') {
      Loader.error('Save failed: ' + err.message);
    } else {
      alert('Save failed: ' + err.message);
    }
    throw err;
  }
}

// â”€â”€ INITIALIZE ON DOM CONTENT LOADED â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.addEventListener('DOMContentLoaded', () => {
  if (typeof SHREEJI_CONFIG !== 'undefined') {
    OFFLINE_DB_CONFIG.apiBase = SHREEJI_CONFIG.apiBase;
  }
  
  checkLocalStorageSetup();
  
  // Periodically process queue and listen to online status
  window.addEventListener('online', processSyncQueue);
  setTimeout(processSyncQueue, 3000);
});

// Export globally
window.savePdfToLocalFolder = savePdfToLocalFolder;
window.saveCertificateOfflineFirst = saveCertificateOfflineFirst;
window.promptForDirectorySelection = promptForDirectorySelection;
window.updateLocalStorageStatus = updateLocalStorageStatus;
window.verifyPermission = verifyPermission;
window.getSavedDirectoryHandle = getSavedDirectoryHandle;
