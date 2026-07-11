<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();
$pageTitle  = 'Home – Calibration Management System';
$activePage = 'home';
include __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch 100 most recent certificates
$sql = "
    SELECT c.id, c.cert_number, c.party_name, c.site_location, it.label AS instrument_label, it.slug AS instrument_slug, c.calibration_date, c.next_due_date, c.pdf_url
    FROM   certificates c
    JOIN   instrument_types it ON it.id = c.instrument_type_id
    ORDER  BY c.created_at DESC
    LIMIT  100
";
$certs = $db->query($sql)->fetchAll();
?>

<div class="page-wrapper">

  <!-- Secondary Menu Bar -->
  <div class="secondary-menu-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; padding: 0.8rem 1.5rem; background: var(--accent-lt); border: 1.5px solid var(--border); border-radius: var(--radius); max-width: 1200px; margin: 1.5rem auto 1.5rem; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0, 121, 107, 0.02);">
    <!-- Page Title -->
    <h2 style="font-size: 1.25rem; font-weight: 700; color: #00796b; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fas fa-history" style="font-size: 1.1rem;"></i> Recent Certificates
    </h2>
    
    <!-- Right side: Search & CTA -->
    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; flex: 1; justify-content: flex-end; max-width: 650px;">
      <!-- Search Box -->
      <div class="search-box" style="position: relative; width: 100%; max-width: 320px; margin: 0;">
        <span class="search-icon" style="position: absolute; left: 0.95rem; top: 50%; transform: translateY(-50%); color: var(--text-mid);"><i class="fas fa-search"></i></span>
        <input type="text" id="recentCertSearch" placeholder="Search certificates..." autocomplete="off" style="width: 100%; padding: 0.55rem 1rem 0.55rem 2.4rem; border: 1.5px solid var(--border); border-radius: var(--radius); font-size: 12px; outline: none; background: white;">
      </div>
      
      <!-- New CTA (+ New) -->
      <a href="create_certificate.php" class="btn-save" style="padding: 0.55rem 1.1rem; border-radius: var(--radius); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 12px; box-shadow: var(--shadow-sm); transition: transform 0.2s;">
        <i class="fas fa-plus"></i> New
      </a>
    </div>
  </div>

  <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

    <!-- Log Table Card -->
    <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); border: 1px solid var(--border); overflow: hidden; margin-bottom: 2rem;">
      <div style="overflow-x: auto;">
        <table id="certsTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
          <thead>
            <tr style="border-bottom: 2px solid var(--border); color: var(--text-mid); font-weight: 700;">
              <th style="padding: 1rem 0.75rem;">Certificate No</th>
              <th style="padding: 1rem 0.75rem;">Company Name</th>
              <th style="padding: 1rem 0.75rem;">Site Location</th>
              <th style="padding: 1rem 0.75rem;">Instrument Type</th>
              <th style="padding: 1rem 0.75rem;">Calibration Date</th>
              <th style="padding: 1rem 0.75rem;">Next Due Date</th>
              <th style="padding: 1rem 0.75rem; text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($certs)): ?>
              <tr class="no-results-row">
                <td colspan="7" style="padding: 3rem; text-align: center; color: var(--text-lt);">
                  <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"><i class="fas fa-file-invoice"></i></div>
                  <p style="margin-bottom: 1rem; font-weight: 500;">No certificates generated yet.</p>
                  <a href="create_certificate.php" class="btn-save" style="padding: 0.6rem 1.2rem; border-radius: 6px; display: inline-block;">Get Started</a>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($certs as $cert): 
                $isOverdue = strtotime($cert['next_due_date']) < time();
              ?>
                <tr class="cert-row" style="border-bottom: 1px solid var(--border); transition: background 0.15s;" data-search="<?= htmlspecialchars(strtolower($cert['cert_number'] . ' ' . $cert['party_name'] . ' ' . $cert['site_location'] . ' ' . $cert['instrument_label'])) ?>">
                  <td data-label="Certificate No" style="padding: 1rem 0.75rem; font-weight: 700; color: var(--primary-dk);"><?= htmlspecialchars($cert['cert_number']) ?></td>
                  <td data-label="Company Name" style="padding: 1rem 0.75rem; font-weight: 600; color: var(--text);"><?= htmlspecialchars($cert['party_name']) ?></td>
                  <td data-label="Site Location" style="padding: 1rem 0.75rem; color: var(--text-mid); font-size: 0.9rem;"><?= htmlspecialchars($cert['site_location'] ?: '—') ?></td>
                  <td data-label="Instrument Type" style="padding: 1rem 0.75rem;"><span style="background: var(--accent-lt); color: var(--primary-dk); padding: 0.25rem 0.6rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($cert['instrument_label']) ?></span></td>
                  <td data-label="Calibration Date" style="padding: 1rem 0.75rem;"><?= date('d/m/Y', strtotime($cert['calibration_date'])) ?></td>
                  <td data-label="Next Due Date" style="padding: 1rem 0.75rem; font-weight: 600; color: <?= $isOverdue ? 'var(--danger)' : 'var(--text)' ?>;">
                    <?= date('d/m/Y', strtotime($cert['next_due_date'])) ?>
                    <?php if ($isOverdue): ?>
                      <span style="font-size: 0.72rem; background: #fee2e2; color: var(--danger); padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 4px; font-weight: 700;">OVERDUE</span>
                    <?php endif; ?>
                  </td>
                  <td data-label="Actions" style="padding: 1rem 0.75rem; text-align: right;">
                    <div style="display: inline-flex; gap: 0.6rem; align-items: center;">
                      <?php if ($cert['pdf_url']): ?>
                        <a href="<?= htmlspecialchars($cert['pdf_url']) ?>" target="_blank" class="instrument-action-btn btn-print" title="View/Print PDF" style="padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.8rem; text-decoration: none;">
                          <i class="fas fa-file-pdf"></i> PDF
                        </a>
                      <?php endif; ?>
                      <a href="certificates/<?= htmlspecialchars($cert['instrument_slug']) ?>.php?id=<?= $cert['id'] ?>" class="instrument-action-btn btn-save" title="Edit Certificate details" style="padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.8rem; text-decoration: none;">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              
              <!-- Client-side No Results row -->
              <tr id="noResultsFoundRow" style="display: none;">
                <td colspan="7" style="padding: 3rem; text-align: center; color: var(--text-lt);">
                  <div style="font-size: 2.5rem; margin-bottom: 0.5rem; opacity: 0.3;"><i class="fas fa-search-minus"></i></div>
                  <p>No matching certificates found.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if (count($certs) >= 100): ?>
        <div style="padding: 1rem; text-align: center; background: var(--bg); border-top: 1px solid var(--border); border-radius: 0 0 var(--radius-lg) var(--radius-lg); font-size: 0.9rem; color: var(--text-mid);">
          <i class="fas fa-info-circle"></i> Showing the 100 most recent certificates. 
          <a href="instrument_reports.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">View all in Instrument Reports &rarr;</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('recentCertSearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.cert-row');
      const noResultsRow = document.getElementById('noResultsFoundRow');
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
