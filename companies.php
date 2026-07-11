<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle  = 'All Companies – Calibration Management System';
$activePage = 'companies';
include __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch parties with their certificate count
$parties = $db->query("
    SELECT p.id, p.name, p.phone, p.email, COUNT(c.id) AS cert_count 
    FROM parties p 
    LEFT JOIN certificates c ON p.id = c.party_id 
    GROUP BY p.id 
    ORDER BY p.name ASC
")->fetchAll();

// Fetch site locations for each party
$locationsStmt = $db->query("
    SELECT DISTINCT party_id, site_location 
    FROM certificates 
    WHERE party_id IS NOT NULL AND site_location != '' AND site_location IS NOT NULL
");
$locations = [];
while ($row = $locationsStmt->fetch()) {
    $locations[$row['party_id']][] = $row['site_location'];
}
?>

<div class="page-wrapper">
  
  <!-- Secondary Menu Bar -->
  <div class="secondary-menu-bar" style="display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; padding: 0.8rem 1.5rem; background: var(--accent-lt); border: 1.5px solid var(--border); border-radius: var(--radius); max-width: 1200px; margin: 1.5rem auto 1.5rem; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0, 121, 107, 0.02);">
    <!-- Page Title -->
    <h2 style="font-size: 1.25rem; font-weight: 700; color: #00796b; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
      <i class="fas fa-building" style="font-size: 1.1rem;"></i> Companies & Parties
    </h2>
    
    <!-- Search Box -->
    <div class="search-box" style="position: relative; width: 100%; max-width: 350px; margin: 0;">
      <span class="search-icon" style="position: absolute; left: 0.95rem; top: 50%; transform: translateY(-50%); color: var(--text-mid);"><i class="fas fa-search"></i></span>
      <input type="text" id="partySearch" placeholder="Search companies, sites, phone..." autocomplete="off" style="width: 100%; padding: 0.55rem 1rem 0.55rem 2.4rem; border: 1.5px solid var(--border); border-radius: var(--radius); font-size: 12px; outline: none; background: white;">
    </div>
  </div>

  <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

    <!-- Parties Card List -->
    <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); border: 1px solid var(--border); overflow: hidden; margin-bottom: 2rem;">
      <div style="overflow-x: auto;">
        <table id="partiesTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
          <thead>
            <tr style="border-bottom: 2px solid var(--border); color: var(--text-mid); font-weight: 700;">
              <th style="padding: 1rem 0.75rem;">Company Name</th>
              <th style="padding: 1rem 0.75rem;">Contact Details</th>
              <th style="padding: 1rem 0.75rem;">Site Locations</th>
              <th style="padding: 1rem 0.75rem; text-align: center;">Total Certificates</th>
              <th style="padding: 1rem 0.75rem; text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($parties)): ?>
              <tr>
                <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-lt);">
                  <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"><i class="fas fa-building"></i></div>
                  <p style="margin-bottom: 0px; font-weight: 500;">No companies saved in the database yet.</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($parties as $party): 
                $partyLocations = $locations[$party['id']] ?? [];
                $locsText = !empty($partyLocations) ? implode(', ', $partyLocations) : '—';
                $searchString = strtolower($party['name'] . ' ' . $party['phone'] . ' ' . $party['email'] . ' ' . $locsText);
              ?>
                <tr class="party-row" style="border-bottom: 1px solid var(--border); transition: background 0.15s;" data-search="<?= htmlspecialchars($searchString) ?>">
                  <td style="padding: 1rem 0.75rem; font-weight: 700; color: var(--primary-dk); font-size: 1.05rem;"><?= htmlspecialchars($party['name']) ?></td>
                  <td style="padding: 1rem 0.75rem; color: var(--text); line-height: 1.4;">
                    <?php if ($party['phone']): ?>
                      <div><i class="fas fa-phone" style="font-size: 0.8rem; color: var(--text-lt); width: 16px;"></i> <?= htmlspecialchars($party['phone']) ?></div>
                    <?php endif; ?>
                    <?php if ($party['email']): ?>
                      <div><i class="fas fa-envelope" style="font-size: 0.8rem; color: var(--text-lt); width: 16px;"></i> <?= htmlspecialchars($party['email']) ?></div>
                    <?php endif; ?>
                    <?php if (!$party['phone'] && !$party['email']): ?>
                      <span style="color: var(--text-lt);">—</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 1rem 0.75rem; color: var(--text-mid); font-size: 0.9rem;">
                    <?php if (empty($partyLocations)): ?>
                      <span style="color: var(--text-lt);">No locations recorded</span>
                    <?php else: ?>
                      <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                        <?php foreach ($partyLocations as $loc): ?>
                          <span style="background: var(--bg); border: 1px solid var(--border); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 500; font-size: 0.8rem;"><?= htmlspecialchars($loc) ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 1rem 0.75rem; text-align: center;">
                    <span style="background: var(--accent-lt); color: var(--primary-dk); padding: 0.3rem 0.75rem; border-radius: 99px; font-size: 0.85rem; font-weight: 700; border: 1px solid rgba(0,121,107,0.15);">
                      <?= $party['cert_count'] ?>
                    </span>
                  </td>
                  <td style="padding: 1rem 0.75rem; text-align: right;">
                    <div style="display: inline-flex; gap: 0.6rem;">
                      <a href="dashboard.php?party_id=<?= $party['id'] ?>" class="instrument-action-btn btn-save" title="View historical certificates list" style="padding: 0.45rem 0.9rem; border-radius: 6px; font-size: 0.8rem; text-decoration: none;">
                        <i class="fas fa-history"></i> View Certificates
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              
              <!-- Client-side No Results row -->
              <tr id="noPartiesFoundRow" style="display: none;">
                <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-lt);">
                  <div style="font-size: 2.5rem; margin-bottom: 0.5rem; opacity: 0.3;"><i class="fas fa-search-minus"></i></div>
                  <p>No matching companies found.</p>
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
  const searchInput = document.getElementById('partySearch');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll('.party-row');
      const noResultsRow = document.getElementById('noPartiesFoundRow');
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
