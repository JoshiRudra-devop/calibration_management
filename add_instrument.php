<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle  = 'Add Instrument – Calibration Management System';
$activePage = 'add_instrument';
include __DIR__ . '/includes/header.php';

$db = getDB();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', clean($_POST['slug'] ?? '')));
    $label = clean($_POST['label'] ?? '');
    $has_make = isset($_POST['has_make']) ? 1 : 0;
    $has_serial = isset($_POST['has_serial']) ? 1 : 0;
    $has_model = isset($_POST['has_model']) ? 1 : 0;
    $has_capacity = isset($_POST['has_capacity']) ? 1 : 0;
    $has_size = isset($_POST['has_size']) ? 1 : 0;
    $has_quantity = isset($_POST['has_quantity']) ? 1 : 0;
    $prefix = strtoupper(clean($_POST['prefix'] ?? 'SI'));
    
    if (!$slug || !$label) {
        $error = "Slug and Label are required.";
    } else {
        try {
            // Check if slug already exists
            $check = $db->prepare("SELECT id FROM instrument_types WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetch()) {
                $error = "An instrument type with slug '$slug' already exists.";
            } else {
                // Get max sort_order
                $maxSort = (int) $db->query("SELECT MAX(sort_order) FROM instrument_types")->fetchColumn();
                $sort_order = $maxSort + 1;
                
                $db->beginTransaction();
                
                $stmt = $db->prepare("INSERT INTO instrument_types (slug, label, has_make, has_serial, has_model, has_capacity, has_size, has_quantity, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$slug, $label, $has_make, $has_serial, $has_model, $has_capacity, $has_size, $has_quantity, $sort_order]);
                
                $newId = $db->lastInsertId();
                
                $counterStmt = $db->prepare("INSERT INTO certificate_counter (instrument_type_id, prefix, current_no, current_year, current_month) VALUES (?, ?, 0, ?, ?)");
                $counterStmt->execute([$newId, $prefix, (int)date('Y'), (int)date('m')]);
                
                $db->commit();
                $success = "Instrument '$label' added successfully! Prefix '$prefix' assigned.";
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Fetch current instruments with prefixes
$instruments = $db->query("
    SELECT it.*, cc.prefix 
    FROM instrument_types it 
    LEFT JOIN certificate_counter cc ON it.id = cc.instrument_type_id 
    ORDER BY it.sort_order ASC
")->fetchAll();
?>

<div class="page-wrapper">
  
  <!-- Secondary Menu Bar -->
  <div class="secondary-menu-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; padding: 0.8rem 1.5rem; background: var(--accent-lt); border: 1.5px solid var(--border); border-radius: var(--radius); max-width: 1200px; margin: 1.5rem auto 1.5rem; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0, 121, 107, 0.02);">
    <!-- Page Title -->
    <h2 style="font-size: 1.25rem; font-weight: 700; color: #00796b; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fas fa-tools" style="font-size: 1.1rem;"></i> Add Instrument Type
    </h2>
  </div>

  <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem; display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
    
    <!-- Left Column: Add Form -->
    <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border);">
      <h3 style="color: var(--primary); margin-top: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; font-weight: 700;">
        <i class="fas fa-plus-circle"></i> Add Instrument
      </h3>
      
      <?php if ($error): ?>
        <div style="background: #fef2f2; border: 1.5px solid var(--danger); color: var(--danger); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 600;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div style="background: #f0fdf4; border: 1.5px solid var(--accent); color: var(--primary-dk); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; font-weight: 600;">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
        
        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Slug Name (Lowercase, no spaces)</label>
          <input type="text" name="slug" placeholder="e.g. pressure_gauge" required style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
        </div>

        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Display Label</label>
          <input type="text" name="label" placeholder="e.g. Pressure Gauge" required style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
        </div>

        <div>
          <label style="display: block; margin-bottom: 0.4rem; font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Certificate Prefix</label>
          <input type="text" name="prefix" placeholder="e.g. PG" required style="width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
          <h4 style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: var(--text); font-weight: 700;">Form Attribute Configuration:</h4>
          
          <div style="display: flex; flex-direction: column; gap: 0.6rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
              <input type="checkbox" name="has_make" checked style="width: 16px; height: 16px; accent-color: var(--primary);">
              <span>Has Make field</span>
            </label>

            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
              <input type="checkbox" name="has_serial" checked style="width: 16px; height: 16px; accent-color: var(--primary);">
              <span>Has Serial No field</span>
            </label>

            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
              <input type="checkbox" name="has_model" style="width: 16px; height: 16px; accent-color: var(--primary);">
              <span>Has Model No field</span>
            </label>

            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
              <input type="checkbox" name="has_capacity" style="width: 16px; height: 16px; accent-color: var(--primary);">
              <span>Has Capacity field</span>
            </label>

            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
              <input type="checkbox" name="has_size" style="width: 16px; height: 16px; accent-color: var(--primary);">
              <span>Has Size field</span>
            </label>

            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.9rem;">
              <input type="checkbox" name="has_quantity" style="width: 16px; height: 16px; accent-color: var(--primary);">
              <span>Has Quantity field</span>
            </label>
          </div>
        </div>

        <button type="submit" class="btn-save" style="width: 100%; padding: 0.8rem; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; margin-top: 0.5rem;">
          <i class="fas fa-save"></i> Save Instrument Type
        </button>

      </form>
    </div>

    <!-- Right Column: Current List -->
    <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); border: 1px solid var(--border); overflow: hidden;">
      <h3 style="color: var(--text); margin-top: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; font-weight: 700;">
        <i class="fas fa-list-ul"></i> Existing Instruments
      </h3>
      
      <div style="overflow-y: auto; max-height: 520px;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
          <thead>
            <tr style="border-bottom: 2px solid var(--border); color: var(--text-mid); font-weight: 700;">
              <th style="padding: 0.75rem 0.5rem;">Label</th>
              <th style="padding: 0.75rem 0.5rem;">Slug</th>
              <th style="padding: 0.75rem 0.5rem;">Prefix</th>
              <th style="padding: 0.75rem 0.5rem; text-align: right;">Attributes</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($instruments as $inst): ?>
              <tr style="border-bottom: 1px solid var(--border);">
                <td style="padding: 0.8rem 0.5rem; font-weight: 600; color: var(--primary-dk);"><?= htmlspecialchars($inst['label']) ?></td>
                <td style="padding: 0.8rem 0.5rem; font-family: monospace; font-size: 0.8rem;"><?= htmlspecialchars($inst['slug']) ?></td>
                <td style="padding: 0.8rem 0.5rem; font-weight: 700; color: var(--accent);"><?= htmlspecialchars($inst['prefix'] ?: '—') ?></td>
                <td style="padding: 0.8rem 0.5rem; text-align: right; font-size: 0.75rem; color: var(--text-lt); line-height: 1.3;">
                  <?= $inst['has_make'] ? 'Make ' : '' ?>
                  <?= $inst['has_serial'] ? 'Serial ' : '' ?>
                  <?= $inst['has_model'] ? 'Model ' : '' ?>
                  <?= $inst['has_capacity'] ? 'Capacity ' : '' ?>
                  <?= $inst['has_size'] ? 'Size ' : '' ?>
                  <?= $inst['has_quantity'] ? 'Qty ' : '' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
