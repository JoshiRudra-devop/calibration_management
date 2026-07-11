<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle  = 'Settings – Calibration Management System';
$activePage = 'settings';
include __DIR__ . '/includes/header.php';

$db = getDB();
$pwError = '';
$pwSuccess = '';

// Handle Password Change POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (!$current || !$new || !$confirm) {
        $pwError = "All password fields are required.";
    } elseif (strlen($new) < 8) {
        $pwError = "New password must be at least 8 characters.";
    } elseif (!preg_match('/[0-9]/', $new)) {
        $pwError = "New password must contain at least one number.";
    } elseif ($new !== $confirm) {
        $pwError = "New password and confirmation do not match.";
    } else {
        $userId = $_SESSION['user_id'];
        // Fetch current hash
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();
        
        if (!$hash || !password_verify($current, $hash)) {
            $pwError = "Current password is incorrect.";
        } else {
            // Update password
            $newHash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
            $update = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update->execute([$newHash, $userId]);
            $pwSuccess = "Password changed successfully! ✨";
        }
    }
}

// Handle Image Uploads POST
$imgError = '';
$imgSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_images') {
    $uploadDir = __DIR__ . '/assets/images/';
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $fields = ['header', 'footer', 'stamp', 'sign'];
    
    $uploadedCount = 0;
    foreach ($fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES[$field]['tmp_name'];
            $type = mime_content_type($tmpPath);
            
            if (in_array($type, $allowedTypes)) {
                // We enforce saving as .jpeg because the PDF generator looks for .jpeg
                $destPath = $uploadDir . $field . '.jpeg';
                
                // If it's a PNG, we could convert it to JPEG, or simply move it.
                // jspdf's addImage handles PNGs and JPEGs. The JS code uses:
                // getImg(BASE + '/header.jpeg')
                // so the file MUST be named .jpeg, even if the internal encoding is PNG.
                if (move_uploaded_file($tmpPath, $destPath)) {
                    $uploadedCount++;
                }
            } else {
                $imgError = "Only JPG and PNG images are allowed.";
            }
        }
    }
    
    if ($imgError === '') {
        if ($uploadedCount > 0) {
            $imgSuccess = "$uploadedCount template image(s) updated successfully! ✨";
        } else {
            $imgError = "Please select at least one image to upload.";
        }
    }
}
// Fetch stats
$totalCerts = (int) $db->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
$totalParties = (int) $db->query("SELECT COUNT(*) FROM parties")->fetchColumn();
$totalTypes = (int) $db->query("SELECT COUNT(*) FROM instrument_types")->fetchColumn();
?>

<div class="page-wrapper">
  
  <!-- Secondary Menu Bar -->
  <div class="secondary-menu-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; padding: 0.8rem 1.5rem; background: var(--accent-lt); border: 1.5px solid var(--border); border-radius: var(--radius); max-width: 1200px; margin: 1.5rem auto 1.5rem; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0, 121, 107, 0.02);">
    <!-- Page Title -->
    <h2 style="font-size: 1.25rem; font-weight: 700; color: #00796b; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fas fa-cog" style="font-size: 1.1rem;"></i> System Settings
    </h2>
  </div>

  <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
    
    <!-- Left Column: Profile & Storage & Database Stats -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">
      
      <!-- User Profile Card -->
      <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border);">
        <h3 style="color: var(--primary); margin-top: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; font-weight: 700;">
          <i class="fas fa-user-circle"></i> Operator Profile
        </h3>
        
        <div style="display: flex; flex-direction: column; gap: 0.8rem; font-size: 0.95rem;">
          <div>
            <strong style="color: var(--text-mid);">Full Name:</strong> 
            <span style="color: var(--text); font-weight: 600;"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></span>
          </div>
          
          <div>
            <strong style="color: var(--text-mid);">Phone Number:</strong> 
            <span style="color: var(--text); font-weight: 600;"><?= htmlspecialchars($_SESSION['user']['phone'] ?? '9999999999') ?></span>
          </div>

          <div>
            <strong style="color: var(--text-mid);">Access Role:</strong> 
            <span style="background: var(--accent-lt); color: var(--primary-dk); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase;">
              <?= htmlspecialchars($_SESSION['user']['role'] ?? 'Admin') ?>
            </span>
          </div>
        </div>
        
        <div style="margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
          <span style="color: var(--text-lt); font-size: 0.9rem;">Sign out of the current session:</span>
          <a href="<?= APP_URL ?>/api/auth.php?action=logout" class="instrument-action-btn btn-print" style="padding: 0.6rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 700; background: #fee2e2; color: var(--danger) !important; border: 1px solid rgba(229,57,53,0.15);">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>

      <!-- Offline Storage Configuration Card -->
      <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border);">
        <h3 style="color: var(--primary); margin-top: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; font-weight: 700;">
          <i class="fas fa-folder-open"></i> Offline Saving & Sync
        </h3>
        
        <p style="color: var(--text-mid); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1.5rem;">
          Configure a local folder on your computer. When generating certificates, the PDF will be saved directly into a nested directory structure (Category/Company/Site) offline first, then synced to Cloudinary when online.
        </p>

        <!-- Status widget -->
        <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg); padding: 1rem; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 1.5rem;">
          <span style="font-weight: 600; font-size: 0.9rem;">Status:</span>
          <div id="localStorageStatus" class="local-storage-status disconnected" style="margin-top: 0; border: none; padding: 0;">
            <i class="fas fa-folder"></i>
            <span>Local Storage Offline</span>
          </div>
        </div>

        <button id="btnSettingsFolderPick" class="btn-save" style="width: 100%; padding: 0.75rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
          <i class="fas fa-folder-open"></i> Select / Change Folder
        </button>
      </div>

      <!-- Database Statistics -->
      <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border);">
        <h3 style="color: var(--text); margin-top: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; font-weight: 700;">
          <i class="fas fa-database"></i> Database Stats
        </h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; text-align: center;">
          <div style="background: var(--bg); padding: 1rem; border-radius: 8px; border: 1px solid var(--border);">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);"><?= $totalCerts ?></div>
            <div style="font-size: 0.75rem; color: var(--text-lt); margin-top: 0.25rem; font-weight: 600; text-transform: uppercase;">Certificates</div>
          </div>
          
          <div style="background: var(--bg); padding: 1rem; border-radius: 8px; border: 1px solid var(--border);">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent);"><?= $totalParties ?></div>
            <div style="font-size: 0.75rem; color: var(--text-lt); margin-top: 0.25rem; font-weight: 600; text-transform: uppercase;">Parties</div>
          </div>

          <div style="background: var(--bg); padding: 1rem; border-radius: 8px; border: 1px solid var(--border);">
            <div style="font-size: 1.5rem; font-weight: 700; color: #3b82f6;"><?= $totalTypes ?></div>
            <div style="font-size: 0.75rem; color: var(--text-lt); margin-top: 0.25rem; font-weight: 600; text-transform: uppercase;">Instruments</div>
          </div>
        </div>
        <div style="margin-top: 1.5rem; border-top: 1px solid var(--border); padding-top: 1.25rem;">
          <a href="dashboard.php" class="instrument-action-btn btn-print" style="width: 100%; padding: 0.75rem; border-radius: 6px; text-decoration: none; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="fas fa-chart-line"></i> View Detailed Analytics Dashboard
          </a>
        </div>
      </div>

    </div>

    <div style="display: flex; flex-direction: column; gap: 2rem;">
      <!-- Right Column: PDF Generation Settings -->
      <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border);">
        <h3 style="color: var(--primary); margin-top: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; font-weight: 700;">
          <i class="fas fa-file-pdf"></i> PDF Generation Settings
        </h3>
        
        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Company Name on PDF Header</label>
          <select id="pdfCompanySelect" style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem; background: var(--bg); margin-bottom: 1rem;">
            <!-- Options populated by JS -->
          </select>
        </div>

        <div id="newCompanyWrapper" style="display: none; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
          <input type="text" id="newCompanyInput" placeholder="Enter new company name..." style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
          <button id="saveNewCompanyBtn" class="btn-print" style="width: 100%; padding: 0.6rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="fas fa-save"></i> Save New Company
          </button>
        </div>
      </div>

    <!-- Right Column: Change Password -->
    <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border);">
      <h3 style="color: var(--primary); margin-top: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; font-weight: 700;">
        <i class="fas fa-lock"></i> Change Password
      </h3>

      <?php if ($pwError): ?>
        <div style="background: #fef2f2; border: 1.5px solid var(--danger); color: var(--danger); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 600;">
          <?= htmlspecialchars($pwError) ?>
        </div>
      <?php endif; ?>

      <?php if ($pwSuccess): ?>
        <div style="background: #f0fdf4; border: 1.5px solid var(--accent); color: var(--primary-dk); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 600;">
          <?= htmlspecialchars($pwSuccess) ?>
        </div>
      <?php endif; ?>

      <form method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <input type="hidden" name="action" value="change_password">
        
        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Current Password</label>
          <input type="password" name="current_password" required placeholder="Enter current password" style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
        </div>

        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">New Password</label>
          <input type="password" name="new_password" required placeholder="Enter new password" style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
        </div>

        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Confirm New Password</label>
          <input type="password" name="confirm_password" required placeholder="Confirm new password" style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
        </div>

        <button type="submit" class="btn-save" style="width: 100%; padding: 0.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; margin-top: 0.5rem;">
          <i class="fas fa-key"></i> Update Password
        </button>
      </form>
    </div>

    <!-- Right Column: Certificate Template Images -->
    <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border); margin-top: 2rem;">
      <h3 style="color: var(--primary); margin-top: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; font-weight: 700;">
        <i class="fas fa-images"></i> Certificate Template Images
      </h3>

      <?php if ($imgError): ?>
        <div style="background: #fef2f2; color: #991b1b; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #f87171; font-size: 0.9rem;">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($imgError) ?>
        </div>
      <?php endif; ?>
      <?php if ($imgSuccess): ?>
        <div style="background: #ecfdf5; color: #065f46; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #34d399; font-size: 0.9rem;">
          <i class="fas fa-check-circle"></i> <?= htmlspecialchars($imgSuccess) ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <input type="hidden" name="action" value="upload_images">
        
        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Header Image (header.jpeg)</label>
          <input type="file" name="header" accept="image/jpeg, image/png" style="width: 100%; padding: 0.5rem; border: 1.5px dashed var(--border); border-radius: 6px; font-size: 0.9rem; background: var(--bg);">
        </div>

        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Footer Image (footer.jpeg)</label>
          <input type="file" name="footer" accept="image/jpeg, image/png" style="width: 100%; padding: 0.5rem; border: 1.5px dashed var(--border); border-radius: 6px; font-size: 0.9rem; background: var(--bg);">
        </div>

        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Stamp Image (stamp.jpeg)</label>
          <input type="file" name="stamp" accept="image/jpeg, image/png" style="width: 100%; padding: 0.5rem; border: 1.5px dashed var(--border); border-radius: 6px; font-size: 0.9rem; background: var(--bg);">
        </div>

        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Signature Image (sign.jpeg)</label>
          <input type="file" name="sign" accept="image/jpeg, image/png" style="width: 100%; padding: 0.5rem; border: 1.5px dashed var(--border); border-radius: 6px; font-size: 0.9rem; background: var(--bg);">
        </div>

        <button type="submit" class="btn-print" style="width: 100%; padding: 0.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; margin-top: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
          <i class="fas fa-upload"></i> Upload & Replace Images
        </button>
      </form>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const btnPick = document.getElementById('btnSettingsFolderPick');
  if (btnPick) {
    btnPick.addEventListener('click', async () => {
      if (typeof window.promptForDirectorySelection === 'function') {
        const handle = await window.promptForDirectorySelection();
        if (handle) {
          alert('Local storage directory connected successfully! Folder is active.');
          location.reload();
        }
      } else {
        alert('File System Access utilities not loaded. Please use a modern browser.');
      }
    });
  }

  // --- PDF Company Name Logic ---
  const pdfSelect = document.getElementById('pdfCompanySelect');
  const newCompWrapper = document.getElementById('newCompanyWrapper');
  const newCompInput = document.getElementById('newCompanyInput');
  const saveNewCompBtn = document.getElementById('saveNewCompanyBtn');

  // Load custom options from localStorage
  let customOptions = [];
  try {
    const stored = localStorage.getItem('pdfCompanyOptions');
    if (stored) customOptions = JSON.parse(stored);
  } catch(e) {}

  const defaultOptions = ['SHREEJI INSTRUMENTS', 'SHREEJI ENTERPRISE'];
  let allOptions = [...new Set([...defaultOptions, ...customOptions])];
  
  function populateDropdown() {
    pdfSelect.innerHTML = '';
    allOptions.forEach(opt => {
      const el = document.createElement('option');
      el.value = opt;
      el.textContent = opt;
      pdfSelect.appendChild(el);
    });
    const addEl = document.createElement('option');
    addEl.value = 'ADD_NEW';
    addEl.textContent = '+ Add New Company';
    pdfSelect.appendChild(addEl);

    const savedVal = localStorage.getItem('pdfCompanyName') || 'SHREEJI INSTRUMENTS';
    if (allOptions.includes(savedVal)) {
      pdfSelect.value = savedVal;
    } else {
      pdfSelect.value = allOptions[0];
    }
  }

  populateDropdown();

  pdfSelect.addEventListener('change', () => {
    if (pdfSelect.value === 'ADD_NEW') {
      newCompWrapper.style.display = 'flex';
      newCompInput.focus();
    } else {
      newCompWrapper.style.display = 'none';
      localStorage.setItem('pdfCompanyName', pdfSelect.value);
    }
  });

  saveNewCompBtn.addEventListener('click', () => {
    const val = newCompInput.value.trim().toUpperCase();
    if (val) {
      if (!allOptions.includes(val)) {
        customOptions.push(val);
        localStorage.setItem('pdfCompanyOptions', JSON.stringify(customOptions));
        allOptions.push(val);
      }
      localStorage.setItem('pdfCompanyName', val);
      populateDropdown();
      pdfSelect.value = val;
      newCompInput.value = '';
      newCompWrapper.style.display = 'none';
      alert('Company name saved and set as active!');
    }
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
