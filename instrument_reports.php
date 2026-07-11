<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle  = 'Instrument Reports – Calibration Management System';
$activePage = 'instrument_reports';
include __DIR__ . '/includes/header.php';

$db = getDB();

$instId = isset($_GET['instrument_type_id']) ? (int)$_GET['instrument_type_id'] : 0;
$selectedInst = null;
$certs = [];

if ($instId > 0) {
    // Fetch details of selected instrument type
    $stmt = $db->prepare("SELECT * FROM instrument_types WHERE id = ?");
    $stmt->execute([$instId]);
    $selectedInst = $stmt->fetch();
    
    if ($selectedInst) {
        // Fetch all certificates generated for this instrument type
        $stmtCerts = $db->prepare("
            SELECT c.id, c.cert_number, c.party_name, c.site_location, c.calibration_date, c.next_due_date, c.pdf_url 
            FROM certificates c 
            WHERE c.instrument_type_id = ? 
            ORDER BY c.created_at DESC
        ");
        $stmtCerts->execute([$instId]);
        $certs = $stmtCerts->fetchAll();
    }
}

// Fetch all instrument types with counts for the main cards grid
$instruments = $db->query("
    SELECT it.id, it.slug, it.label, COUNT(c.id) AS cert_count 
    FROM instrument_types it 
    LEFT JOIN certificates c ON it.id = c.instrument_type_id 
    GROUP BY it.id 
    ORDER BY it.sort_order ASC
")->fetchAll();
?>

<div class="page-wrapper">
  
  <?php if ($selectedInst): ?>
    <!-- Secondary Menu Bar -->
    <div class="secondary-menu-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; padding: 0.8rem 1.5rem; background: var(--accent-lt); border: 1.5px solid var(--border); border-radius: var(--radius); max-width: 1200px; margin: 1.5rem auto 1.5rem; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0, 121, 107, 0.02);">
      <!-- Page Title -->
      <h2 style="font-size: 1.25rem; font-weight: 700; color: #00796b; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-file-alt" style="font-size: 1.1rem;"></i> <?= htmlspecialchars($selectedInst['label']) ?> Certificates
      </h2>
      
      <!-- Right side: Back & Search -->
      <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; flex: 1; justify-content: flex-end; max-width: 650px;">
        <!-- Back Button -->
        <a href="instrument_reports.php" class="instrument-action-btn btn-print" style="padding: 0.55rem 1.1rem; border-radius: var(--radius); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 12px; box-shadow: var(--shadow-sm);">
          <i class="fas fa-arrow-left"></i> Back to Instruments
        </a>

        <!-- Search Box -->
        <div class="search-box" style="position: relative; width: 100%; max-width: 300px; margin: 0;">
          <span class="search-icon" style="position: absolute; left: 0.95rem; top: 50%; transform: translateY(-50%); color: var(--text-mid);"><i class="fas fa-search"></i></span>
          <input type="text" id="certSearch" placeholder="Search certificates..." autocomplete="off" style="width: 100%; padding: 0.55rem 1rem 0.55rem 2.4rem; border: 1.5px solid var(--border); border-radius: var(--radius); font-size: 12px; outline: none; background: white;">
        </div>
      </div>
    </div>

    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
      <div style="margin-bottom: 1.5rem;">
        <a href="certificates/<?= htmlspecialchars($selectedInst['slug']) ?>.php" class="btn-save" style="padding: 0.75rem 1.5rem; border-radius: 99px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: var(--shadow-sm);">
          <i class="fas fa-plus-circle"></i> Create New <?= htmlspecialchars($selectedInst['label']) ?>
        </a>
      </div>

      <!-- Log Table Card -->
      <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); border: 1px solid var(--border); overflow: hidden; margin-bottom: 2rem;">
        <div style="overflow-x: auto;">
          <table id="certsTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border); color: var(--text-mid); font-weight: 700;">
                <th style="padding: 1rem 0.75rem;">Certificate No</th>
                <th style="padding: 1rem 0.75rem;">Company Name</th>
                <th style="padding: 1rem 0.75rem;">Site Location</th>
                <th style="padding: 1rem 0.75rem;">Calibration Date</th>
                <th style="padding: 1rem 0.75rem;">Next Due Date</th>
                <th style="padding: 1rem 0.75rem; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($certs)): ?>
                <tr class="no-results-row">
                  <td colspan="6" style="padding: 3rem; text-align: center; color: var(--text-lt);">
                    <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"><i class="fas fa-file-invoice"></i></div>
                    <p style="margin-bottom: 1rem; font-weight: 500;">No certificates created for this instrument yet.</p>
                    <a href="certificates/<?= htmlspecialchars($selectedInst['slug']) ?>.php" class="btn-save" style="padding: 0.6rem 1.2rem; border-radius: 6px; display: inline-block; text-decoration: none;">Create First Certificate</a>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($certs as $cert): 
                  $isOverdue = strtotime($cert['next_due_date']) < time();
                ?>
                  <tr class="cert-row" style="border-bottom: 1px solid var(--border); transition: background 0.15s;" data-search="<?= htmlspecialchars(strtolower($cert['cert_number'] . ' ' . $cert['party_name'] . ' ' . $cert['site_location'])) ?>">
                    <td style="padding: 1rem 0.75rem; font-weight: 700; color: var(--primary-dk);"><?= htmlspecialchars($cert['cert_number']) ?></td>
                    <td style="padding: 1rem 0.75rem; font-weight: 600; color: var(--text);"><?= htmlspecialchars($cert['party_name']) ?></td>
                    <td style="padding: 1rem 0.75rem; color: var(--text-mid); font-size: 0.9rem;"><?= htmlspecialchars($cert['site_location'] ?: '—') ?></td>
                    <td style="padding: 1rem 0.75rem;"><?= date('d/m/Y', strtotime($cert['calibration_date'])) ?></td>
                    <td style="padding: 1rem 0.75rem; font-weight: 600; color: <?= $isOverdue ? 'var(--danger)' : 'var(--text)' ?>;">
                      <?= date('d/m/Y', strtotime($cert['next_due_date'])) ?>
                      <?php if ($isOverdue): ?>
                        <span style="font-size: 0.72rem; background: #fee2e2; color: var(--danger); padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 4px; font-weight: 700;">OVERDUE</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 1rem 0.75rem; text-align: right;">
                      <div style="display: inline-flex; gap: 0.6rem; align-items: center;">
                        <?php if ($cert['pdf_url']): ?>
                          <a href="<?= htmlspecialchars($cert['pdf_url']) ?>" target="_blank" class="instrument-action-btn btn-print" title="View/Print PDF" style="padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.8rem; text-decoration: none;">
                            <i class="fas fa-file-pdf"></i> PDF
                          </a>
                        <?php endif; ?>
                        <a href="certificates/<?= htmlspecialchars($selectedInst['slug']) ?>.php?id=<?= $cert['id'] ?>" class="instrument-action-btn btn-save" title="Edit Certificate details" style="padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.8rem; text-decoration: none;">
                          <i class="fas fa-edit"></i> Edit
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                
                <!-- Client-side No Results row -->
                <tr id="noResultsFoundRow" style="display: none;">
                  <td colspan="6" style="padding: 3rem; text-align: center; color: var(--text-lt);">
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

    <script>
    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('certSearch');
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

  <?php else: ?>
    <!-- Secondary Menu Bar -->
    <div class="secondary-menu-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; padding: 0.8rem 1.5rem; background: var(--accent-lt); border: 1.5px solid var(--border); border-radius: var(--radius); max-width: 1200px; margin: 1.5rem auto 1.5rem; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0, 121, 107, 0.02);">
      <!-- Page Title -->
      <h2 style="font-size: 1.25rem; font-weight: 700; color: #00796b; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-file-alt" style="font-size: 1.1rem;"></i> Instrument Wise Reports
      </h2>
    </div>

    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
      
      <!-- Cards Grid -->
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <?php foreach ($instruments as $inst): ?>
          <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--border); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between; gap: 1rem; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onclick="window.location.href='instrument_reports.php?instrument_type_id=<?= $inst['id'] ?>'">
            
            <div>
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                <h3 style="font-size: 1.15rem; color: var(--primary-dk); font-weight: 700; margin: 0; line-height: 1.3;">
                  <?= htmlspecialchars($inst['label']) ?>
                </h3>
                <span style="font-size: 0.72rem; background: var(--bg); border: 1px solid var(--border); padding: 0.15rem 0.4rem; border-radius: 4px; font-weight: 600; color: var(--text-lt); text-transform: uppercase;">
                  <?= htmlspecialchars($inst['slug']) ?>
                </span>
              </div>
              <p style="color: var(--text-mid); font-size: 0.9rem; margin-top: 0.25rem;">
                Total generated: <strong><?= $inst['cert_count'] ?></strong> certificate(s)
              </p>
            </div>

            <div style="display: flex; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
              <!-- View Certificates (Detailed List) -->
              <a href="instrument_reports.php?instrument_type_id=<?= $inst['id'] ?>" class="instrument-action-btn btn-print" style="flex: 1; padding: 0.5rem; border-radius: 6px; font-size: 0.8rem; text-decoration: none; text-align: center; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 0.3rem;">
                <i class="fas fa-list"></i> View Certificates
              </a>
              
              <!-- Create New Standalone Link -->
              <a href="certificates/<?= htmlspecialchars($inst['slug']) ?>.php" onclick="event.stopPropagation();" class="instrument-action-btn btn-save" style="flex: 1; padding: 0.5rem; border-radius: 6px; font-size: 0.8rem; text-decoration: none; text-align: center; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 0.3rem;">
                <i class="fas fa-plus"></i> Create New
              </a>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
