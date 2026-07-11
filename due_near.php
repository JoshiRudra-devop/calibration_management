<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle  = 'Due Near Certificates – Calibration Management System';
$activePage = 'due_near';
include __DIR__ . '/includes/header.php';

$db = getDB();

// ── Date range / status filter ────────────────────────────
$rangeFilter = $_GET['range'] ?? '30';   // days ahead, or 'overdue', 'all'
$where  = [];
$params = [];

if ($rangeFilter === 'overdue') {
    $where[] = "c.next_due_date < CURDATE()";
} elseif ($rangeFilter === 'all') {
    // no filter — show everything
} else {
    $days = max(1, min(365, (int) $rangeFilter));
    $where[] = "c.next_due_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)";
    $params[] = $days;
}

$baseWhere = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

$sql = "
    SELECT c.id, c.cert_number, c.party_name, c.site_location, it.label AS instrument_label, it.slug AS instrument_slug, c.calibration_date, c.next_due_date, c.pdf_url
    FROM   certificates c
    JOIN   instrument_types it ON it.id = c.instrument_type_id
" . $baseWhere . " ORDER BY c.next_due_date ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$certs = $stmt->fetchAll();
?>

<div class="page-wrapper">
  
  <!-- Secondary Menu Bar -->
  <div class="secondary-menu-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; padding: 0.8rem 1.5rem; background: var(--accent-lt); border: 1.5px solid var(--border); border-radius: var(--radius); max-width: 1200px; margin: 1.5rem auto 1.5rem; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0, 121, 107, 0.02);">
    <!-- Page Title -->
    <h2 style="font-size: 1.25rem; font-weight: 700; color: #00796b; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fas fa-clock" style="font-size: 1.1rem;"></i> Overdue & Due Near
    </h2>
    
    <!-- Filters & Search -->
    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
      <!-- Range filter -->
      <form method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
        <label style="font-size: 12px; font-weight: 600; color: var(--text-mid); white-space: nowrap;">Show:</label>
        <select name="range" onchange="this.form.submit()" style="padding: 0.45rem 0.7rem; border: 1.5px solid var(--border); border-radius: var(--radius); font-size: 12px; background: white;">
          <option value="7"   <?= $rangeFilter === '7'       ? 'selected' : '' ?>>Due in 7 days</option>
          <option value="30"  <?= $rangeFilter === '30'      ? 'selected' : '' ?>>Due in 30 days</option>
          <option value="60"  <?= $rangeFilter === '60'      ? 'selected' : '' ?>>Due in 60 days</option>
          <option value="90"  <?= $rangeFilter === '90'      ? 'selected' : '' ?>>Due in 90 days</option>
          <option value="overdue" <?= $rangeFilter === 'overdue' ? 'selected' : '' ?>>Overdue only</option>
          <option value="all" <?= $rangeFilter === 'all'     ? 'selected' : '' ?>>All</option>
        </select>
      </form>

      <!-- Search -->
      <div class="search-box" style="position: relative; width: 100%; max-width: 300px; margin: 0;">
        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-mid);"><i class="fas fa-search"></i></span>
        <input type="text" id="dueSearch" placeholder="Search company, cert no..." autocomplete="off" style="width: 100%; padding: 0.45rem 0.75rem 0.45rem 2.2rem; border: 1.5px solid var(--border); border-radius: var(--radius); font-size: 12px; outline: none; background: white;">
      </div>
    </div>
  </div>

  <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

    <!-- Due List Card -->
    <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); border: 1px solid var(--border); overflow: hidden; margin-bottom: 2rem;">
      <div style="overflow-x: auto;">
        <table id="dueTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
          <thead>
            <tr style="border-bottom: 2px solid var(--border); color: var(--text-mid); font-weight: 700;">
              <th style="padding: 1rem 0.75rem;">Certificate No</th>
              <th style="padding: 1rem 0.75rem;">Company Name</th>
              <th style="padding: 1rem 0.75rem;">Instrument Type</th>
              <th style="padding: 1rem 0.75rem;">Calibration Date</th>
              <th style="padding: 1rem 0.75rem;">Next Due Date</th>
              <th style="padding: 1rem 0.75rem;">Status</th>
              <th style="padding: 1rem 0.75rem; text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($certs)): ?>
              <tr>
                <td colspan="7" style="padding: 3rem; text-align: center; color: var(--text-lt);">
                  <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"><i class="fas fa-calendar-check"></i></div>
                  <p style="margin-bottom: 0px; font-weight: 500;">All certificates are up to date! No recalibrations due within 30 days.</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($certs as $cert): 
                $diff = strtotime($cert['next_due_date']) - strtotime(date('Y-m-d'));
                $days = round($diff / (60 * 60 * 24));
                
                if ($days < 0) {
                    $badge = '<span style="font-size: 0.75rem; background: #fee2e2; color: var(--danger); padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 700; border: 1.5px solid rgba(229,57,53,0.2);">OVERDUE</span>';
                } elseif ($days == 0) {
                    $badge = '<span style="font-size: 0.75rem; background: #fffbeb; color: var(--warn); padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 700; border: 1.5px solid rgba(245,158,11,0.2);">DUE TODAY</span>';
                } else {
                    $badge = '<span style="font-size: 0.75rem; background: #eff6ff; color: #1d4ed8; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 700; border: 1.5px solid rgba(29,78,216,0.2);">DUE IN ' . $days . ' DAYS</span>';
                }
                $searchString = strtolower($cert['cert_number'] . ' ' . $cert['party_name'] . ' ' . $cert['site_location'] . ' ' . $cert['instrument_label']);
              ?>
                <tr class="due-row" style="border-bottom: 1px solid var(--border); transition: background 0.15s;" data-search="<?= htmlspecialchars($searchString) ?>">
                  <td style="padding: 1rem 0.75rem; font-weight: 700; color: var(--primary-dk);"><?= htmlspecialchars($cert['cert_number']) ?></td>
                  <td style="padding: 1rem 0.75rem; font-weight: 600; color: var(--text);">
                    <?= htmlspecialchars($cert['party_name']) ?>
                    <?php if ($cert['site_location']): ?>
                      <div style="font-size: 0.8rem; color: var(--text-lt); font-weight: 400;"><?= htmlspecialchars($cert['site_location']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 1rem 0.75rem;"><span style="background: var(--bg); border: 1px solid var(--border); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600; color: var(--text-mid);"><?= htmlspecialchars($cert['instrument_label']) ?></span></td>
                  <td style="padding: 1rem 0.75rem;"><?= date('d/m/Y', strtotime($cert['calibration_date'])) ?></td>
                  <td style="padding: 1rem 0.75rem; font-weight: 600; color: <?= $days < 0 ? 'var(--danger)' : 'inherit' ?>;"><?= date('d/m/Y', strtotime($cert['next_due_date'])) ?></td>
                  <td style="padding: 1rem 0.75rem;"><?= $badge ?></td>
                  <td style="padding: 1rem 0.75rem; text-align: right;">
                    <div style="display: inline-flex; gap: 0.6rem;">
                      <?php if ($cert['pdf_url']): ?>
                        <a href="<?= htmlspecialchars($cert['pdf_url']) ?>" target="_blank" class="instrument-action-btn btn-print" title="View PDF" style="padding: 0.45rem 0.8rem; border-radius: 6px; font-size: 0.8rem; text-decoration: none;">
                          <i class="fas fa-file-pdf"></i> PDF
                        </a>
                      <?php endif; ?>
                      <a href="certificates/<?= htmlspecialchars($cert['instrument_slug']) ?>.php?id=<?= $cert['id'] ?>" class="instrument-action-btn btn-save" title="Recalibrate / Prefill Form" style="padding: 0.45rem 0.8rem; border-radius: 6px; font-size: 0.8rem; text-decoration: none;">
                        <i class="fas fa-sync-alt"></i> Recalibrate
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              
              <!-- Client-side No Results row -->
              <tr id="noDueFoundRow" style="display: none;">
                <td colspan="7" style="padding: 3rem; text-align: center; color: var(--text-lt);">
                  <div style="font-size: 2.5rem; margin-bottom: 0.5rem; opacity: 0.3;"><i class="fas fa-search-minus"></i></div>
                  <p>No matching certificates found.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('dueSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.due-row');
      const noResultsRow = document.getElementById('noDueFoundRow');
      let visibleCount = 0;
      
      rows.forEach(row => {
        const searchText = row.getAttribute('data-search');
        if (searchText.includes(query)) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });
      
      if (noResultsRow) {
        noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
      }
    });
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
