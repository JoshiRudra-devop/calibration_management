<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
include __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch statistics
$totalCerts   = (int) $db->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
$totalParties = (int) $db->query("SELECT COUNT(*) FROM parties")->fetchColumn();
$totalTypes   = (int) $db->query("SELECT COUNT(*) FROM instrument_types")->fetchColumn();
$thisMonth    = (int) $db->query("SELECT COUNT(*) FROM certificates WHERE MONTH(calibration_date)=MONTH(CURDATE()) AND YEAR(calibration_date)=YEAR(CURDATE())")->fetchColumn();

// Due dates tracking
$overdueCount      = (int) $db->query("SELECT COUNT(*) FROM certificates WHERE next_due_date < CURDATE()")->fetchColumn();
$dueThisWeekCount  = (int) $db->query("SELECT COUNT(*) FROM certificates WHERE next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
$dueThisMonthCount = (int) $db->query("SELECT COUNT(*) FROM certificates WHERE next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();

// Chart data - last 6 months
$chartStmt = $db->query("
    SELECT DATE_FORMAT(calibration_date,'%b') AS month_label,
           COUNT(*) AS count
    FROM   certificates
    WHERE  calibration_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP  BY YEAR(calibration_date), MONTH(calibration_date)
    ORDER  BY calibration_date ASC
    LIMIT  6
");
$chartData = $chartStmt->fetchAll();

// Top instruments
$topStmt = $db->query("
    SELECT it.label, COUNT(*) AS cnt
    FROM   certificates c
    JOIN   instrument_types it ON it.id = c.instrument_type_id
    GROUP  BY c.instrument_type_id
    ORDER  BY cnt DESC
    LIMIT  5
");
$topInstruments = $topStmt->fetchAll();

// Filter values for dropdowns
$parties = $db->query("SELECT id, name FROM parties ORDER BY name ASC")->fetchAll();
$instrumentTypes = $db->query("SELECT id, label, slug FROM instrument_types ORDER BY label ASC")->fetchAll();

// Fetch parties with their site locations for dynamic filtering
$partyLocations = [];
$plStmt = $db->query("SELECT DISTINCT party_id, site_location FROM certificates WHERE party_id IS NOT NULL AND site_location != ''");
while ($row = $plStmt->fetch()) {
    $partyLocations[$row['party_id']][] = $row['site_location'];
}

// Build Filter Query for History
$where = [];
$params = [];

// Period filter
$period = $_GET['period'] ?? 'all';
if ($period === 'today') {
    $where[] = "c.calibration_date = CURDATE()";
} elseif ($period === 'week') {
    $where[] = "YEARWEEK(c.calibration_date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($period === 'month') {
    $where[] = "MONTH(c.calibration_date) = MONTH(CURDATE()) AND YEAR(c.calibration_date) = YEAR(CURDATE())";
} elseif ($period === 'year') {
    $where[] = "YEAR(c.calibration_date) = YEAR(CURDATE())";
}

// Specific Date
if (!empty($_GET['date_val'])) {
    $where[] = "c.calibration_date = ?";
    $params[] = $_GET['date_val'];
}

// Instrument Type
if (!empty($_GET['instrument_type_id'])) {
    $where[] = "c.instrument_type_id = ?";
    $params[] = (int) $_GET['instrument_type_id'];
}

// Party
$selectedPartyId = $_GET['party_id'] ?? '';
if (!empty($selectedPartyId)) {
    $where[] = "c.party_id = ?";
    $params[] = (int) $selectedPartyId;
}

// Site Location
if (!empty($_GET['location'])) {
    $where[] = "c.site_location = ?";
    $params[] = $_GET['location'];
}

// Due Status filter (from alert clicks)
$dueStatus = $_GET['due_status'] ?? '';
if ($dueStatus === 'overdue') {
    $where[] = "c.next_due_date < CURDATE()";
} elseif ($dueStatus === 'week') {
    $where[] = "c.next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
} elseif ($dueStatus === 'month') {
    $where[] = "c.next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
}

// ── Pagination ───────────────────────────────────────────────
$perPage     = 50;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$offset      = ($currentPage - 1) * $perPage;

$baseWhere = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

// Count total matching rows
$countStmt = $db->prepare("SELECT COUNT(*) FROM certificates c JOIN instrument_types it ON it.id = c.instrument_type_id" . $baseWhere);
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = (int) ceil($totalRows / $perPage);

$sql = "
    SELECT c.id, c.cert_number, c.party_name, c.site_location, it.label AS instrument_label, it.slug AS instrument_slug, c.calibration_date, c.next_due_date, c.pdf_url
    FROM   certificates c
    JOIN   instrument_types it ON it.id = c.instrument_type_id
" . $baseWhere . " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";

$pageParams   = array_merge($params, [$perPage, $offset]);
$stmt = $db->prepare($sql);
$stmt->execute($pageParams);
$certs = $stmt->fetchAll();
?>

<div class="page-wrapper">
  <div class="container" style="padding: 2rem 1rem; max-width: 1200px; margin: 0 auto;">

    <h1 style="margin-bottom: 2rem; color: var(--primary);">Dashboard</h1>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
      
      <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md); border-left: 4px solid var(--primary);">
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);"><?= $totalCerts ?></div>
        <div style="color: var(--text-mid); margin-top: 0.5rem;">Total Certificates</div>
      </div>

      <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md); border-left: 4px solid var(--accent);">
        <div style="font-size: 2rem; font-weight: 700; color: var(--accent);"><?= $totalParties ?></div>
        <div style="color: var(--text-mid); margin-top: 0.5rem;">Total Saved Parties</div>
      </div>

      <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md); border-left: 4px solid #3b82f6;">
        <div style="font-size: 2rem; font-weight: 700; color: #3b82f6;"><?= $totalTypes ?></div>
        <div style="color: var(--text-mid); margin-top: 0.5rem;">Instrument Types</div>
      </div>

      <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md); border-left: 4px solid #7c3aed;">
        <div style="font-size: 2rem; font-weight: 700; color: #7c3aed;"><?= $thisMonth ?></div>
        <div style="color: var(--text-mid); margin-top: 0.5rem;">This Month's Calibrations</div>
      </div>

    </div>

    <!-- Due Dates Tracking Alerts -->
    <?php if ($overdueCount > 0 || $dueThisWeekCount > 0 || $dueThisMonthCount > 0): ?>
      <div style="margin-bottom: 2.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
        <?php if ($overdueCount > 0): ?>
          <div style="background: #fef2f2; border: 1.5px solid #ef4444; border-radius: 8px; padding: 0.75rem 1rem; color: #991b1b; display: flex; align-items: center; justify-content: space-between; font-size: 0.95rem;">
            <span>⚠️ <strong><?= $overdueCount ?></strong> certificate(s) have <strong>OVERDUE</strong> calibration!</span>
            <a href="?due_status=overdue" style="color: #ef4444; font-weight: 700; text-decoration: underline;">Filter Overdue</a>
          </div>
        <?php endif; ?>
        <?php if ($dueThisWeekCount > 0): ?>
          <div style="background: #fffbeb; border: 1.5px solid #f59e0b; border-radius: 8px; padding: 0.75rem 1rem; color: #92400e; display: flex; align-items: center; justify-content: space-between; font-size: 0.95rem;">
            <span>⏳ <strong><?= $dueThisWeekCount ?></strong> certificate(s) are due for calibration <strong>THIS WEEK</strong>!</span>
            <a href="?due_status=week" style="color: #f59e0b; font-weight: 700; text-decoration: underline;">Filter This Week</a>
          </div>
        <?php endif; ?>
        <?php if ($dueThisMonthCount > 0): ?>
          <div style="background: #eff6ff; border: 1.5px solid #3b82f6; border-radius: 8px; padding: 0.75rem 1rem; color: #1e40af; display: flex; align-items: center; justify-content: space-between; font-size: 0.95rem;">
            <span>🔔 <strong><?= $dueThisMonthCount ?></strong> certificate(s) are due for calibration <strong>THIS MONTH</strong>!</span>
            <a href="?due_status=month" style="color: #3b82f6; font-weight: 700; text-decoration: underline;">Filter This Month</a>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Charts & top instruments -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">

      <!-- Trend Chart (Chart.js) -->
      <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md);">
        <h3 style="margin-bottom: 1rem; color: var(--text);">Last 6 Months Trend</h3>
        <?php if (empty($chartData)): ?>
          <div class="empty-state" style="padding: 2.5rem 1rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.35;">📊</div>
            <div style="color: var(--text-lt); font-size: 0.9rem;">No calibrations in the last 6 months.</div>
            <a href="create_certificate.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-flex;">Create First Certificate</a>
          </div>
        <?php else: ?>
          <div style="position: relative; height: 220px;">
            <canvas id="trendChart"></canvas>
          </div>
        <?php endif; ?>
      </div>

      <!-- Top Instruments Chart (Chart.js) -->
      <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md);">
        <h3 style="margin-bottom: 1rem; color: var(--text);">Top Instruments</h3>
        <?php if (empty($topInstruments)): ?>
          <div class="empty-state" style="padding: 2.5rem 1rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.35;">📋</div>
            <div style="color: var(--text-lt); font-size: 0.9rem;">No instrument data yet.</div>
          </div>
        <?php else: ?>
          <div style="position: relative; height: 220px;">
            <canvas id="instrumentsChart"></canvas>
          </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- Certificate Directory & Filtering (View Option) -->
    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-md); margin-bottom: 2rem;">
      <h3 style="margin-bottom: 1.5rem; color: var(--primary); border-bottom: 2px solid var(--border); padding-bottom: 0.5rem;">Certificate History & Filtering</h3>
      
      <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; align-items: end;">
        <!-- Period Filter -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Period</label>
          <select name="period" style="padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px;">
            <option value="all" <?= $period==='all'?'selected':'' ?>>All Time</option>
            <option value="today" <?= $period==='today'?'selected':'' ?>>Today</option>
            <option value="week" <?= $period==='week'?'selected':'' ?>>This Week</option>
            <option value="month" <?= $period==='month'?'selected':'' ?>>This Month</option>
            <option value="year" <?= $period==='year'?'selected':'' ?>>This Year</option>
          </select>
        </div>

        <!-- Date Filter -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Specific Date</label>
          <input type="date" name="date_val" value="<?= htmlspecialchars($_GET['date_val'] ?? '') ?>" style="padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px;">
        </div>

        <!-- Instrument Filter -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Instrument Type</label>
          <select name="instrument_type_id" style="padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px;">
            <option value="">All Instruments</option>
            <?php foreach ($instrumentTypes as $type): ?>
              <option value="<?= $type['id'] ?>" <?= ($_GET['instrument_type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>><?= htmlspecialchars($type['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Company / Party Filter -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Company (Party)</label>
          <select name="party_id" id="filter_party_id" onchange="onPartyChange()" style="padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px;">
            <option value="">All Companies</option>
            <?php foreach ($parties as $party): ?>
              <option value="<?= $party['id'] ?>" <?= $selectedPartyId == $party['id'] ? 'selected' : '' ?>><?= htmlspecialchars($party['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Location Filter -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
          <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-mid);">Site Location</label>
          <select name="location" id="filter_location" style="padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px;">
            <option value="">All Locations</option>
            <!-- Locations will be dynamically populated/filtered by JS -->
          </select>
        </div>

        <!-- Filter / Reset Buttons -->
        <div style="display: flex; gap: 0.5rem;">
          <button type="submit" style="flex: 1; padding: 0.6rem; background: var(--primary); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Filter</button>
          <a href="dashboard.php" style="padding: 0.6rem; background: #e2e8f0; color: var(--text-mid); border: none; border-radius: 6px; font-weight: 600; text-align: center; text-decoration: none;">Reset</a>
        </div>
      </form>

      <!-- History Table -->
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background: var(--bg); border-bottom: 2px solid var(--border);">
              <th style="padding: 1rem; text-align: left; font-weight: 600;">Certificate #</th>
              <th style="padding: 1rem; text-align: left; font-weight: 600;">Party (Company)</th>
              <th style="padding: 1rem; text-align: left; font-weight: 600;">Site Location</th>
              <th style="padding: 1rem; text-align: left; font-weight: 600;">Instrument</th>
              <th style="padding: 1rem; text-align: left; font-weight: 600;">Calibration Date</th>
              <th style="padding: 1rem; text-align: left; font-weight: 600;">Next Due Date</th>
              <th style="padding: 1rem; text-align: left; font-weight: 600;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($certs)): ?>
              <tr>
                <td colspan="7" style="padding: 2rem; text-align: center; color: var(--text-lt);">No certificates found matching these filters.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($certs as $cert): 
                $isOverdue = strtotime($cert['next_due_date']) < time();
              ?>
                <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;">
                  <td style="padding: 1rem; font-weight: 600;"><?= htmlspecialchars($cert['cert_number']) ?></td>
                  <td style="padding: 1rem;"><?= htmlspecialchars($cert['party_name']) ?></td>
                  <td style="padding: 1rem; font-size: 0.9rem; color: var(--text-mid);"><?= htmlspecialchars($cert['site_location'] ?: 'N/A') ?></td>
                  <td style="padding: 1rem;"><?= htmlspecialchars($cert['instrument_label']) ?></td>
                  <td style="padding: 1rem;"><?= date('M d, Y', strtotime($cert['calibration_date'])) ?></td>
                  <td style="padding: 1rem; color: <?= $isOverdue ? '#dc2626' : 'inherit' ?>; font-weight: <?= $isOverdue ? '600' : 'normal' ?>;">
                    <?= date('M d, Y', strtotime($cert['next_due_date'])) ?>
                    <?= $isOverdue ? ' <span style="font-size:0.75rem; background:#fee2e2; color:#dc2626; padding:0.15rem 0.45rem; border-radius:4px; margin-left:4px;">OVERDUE</span>' : '' ?>
                  </td>
                  <td style="padding: 1rem; display: flex; gap: 0.75rem; align-items: center;">
                    <?php if ($cert['pdf_url']): ?>
                      <a href="<?= htmlspecialchars($cert['pdf_url']) ?>" target="_blank" style="color: var(--accent); font-weight: 600; text-decoration: underline;">View PDF</a>
                    <?php endif; ?>
                    <a href="certificates/<?= htmlspecialchars($cert['instrument_slug']) ?>.php?id=<?= $cert['id'] ?>" style="color: var(--primary); font-weight: 600; text-decoration: underline;">Edit/Prefill</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <?php if ($totalPages > 1): ?>
        <?php
        // Build base query string without page for pagination links
        $qp = $_GET;
        unset($qp['page']);
        $baseQS = http_build_query($qp);
        $baseQS = $baseQS ? '?' . $baseQS . '&' : '?';
        ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
          <span style="font-size: 0.9rem; color: var(--text-mid);">
            Showing <strong><?= number_format($offset + 1) ?>–<?= number_format(min($offset + $perPage, $totalRows)) ?></strong> of <strong><?= number_format($totalRows) ?></strong> certificates
          </span>
          <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
            <?php if ($currentPage > 1): ?>
              <a href="<?= $baseQS ?>page=1" style="padding: 0.4rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: var(--primary); text-decoration: none;">« First</a>
              <a href="<?= $baseQS ?>page=<?= $currentPage - 1 ?>" style="padding: 0.4rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: var(--primary); text-decoration: none;">‹ Prev</a>
            <?php endif; ?>

            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage   = min($totalPages, $currentPage + 2);
            for ($p = $startPage; $p <= $endPage; $p++):
              $isActive = $p === $currentPage;
            ?>
              <a href="<?= $baseQS ?>page=<?= $p ?>" style="padding: 0.4rem 0.75rem; border: 1px solid <?= $isActive ? 'var(--primary)' : 'var(--border)' ?>; border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: <?= $isActive ? 'white' : 'var(--primary)' ?>; background: <?= $isActive ? 'var(--primary)' : 'white' ?>; text-decoration: none;"><?= $p ?></a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
              <a href="<?= $baseQS ?>page=<?= $currentPage + 1 ?>" style="padding: 0.4rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: var(--primary); text-decoration: none;">Next ›</a>
              <a href="<?= $baseQS ?>page=<?= $totalPages ?>" style="padding: 0.4rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: var(--primary); text-decoration: none;">Last »</a>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <div style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-lt); text-align: right;">
          <?= number_format($totalRows) ?> certificate(s) total
        </div>
      <?php endif; ?>

    </div>

  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" integrity="sha512-CQBWl4fJHWbryGE+Pc7UAxWMUMNMWzWxF4SQo9CgkJIN1kx6djDQZjh3Y8SZ1d+6I+1zze6Z7kHXO7q3UyZAWw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
const partyLocations = <?= json_encode($partyLocations) ?>;
const selectedLocation = <?= json_encode($_GET['location'] ?? '') ?>;

function onPartyChange() {
    const partyId = document.getElementById('filter_party_id').value;
    const locSelect = document.getElementById('filter_location');
    locSelect.options.length = 1;
    let locations = [];
    if (partyId && partyLocations[partyId]) {
        locations = partyLocations[partyId];
    } else if (!partyId) {
        const allLocs = [];
        for (const locList of Object.values(partyLocations)) {
            locList.forEach(l => { if (!allLocs.includes(l)) allLocs.push(l); });
        }
        locations = allLocs;
    }
    locations.forEach(loc => {
        const opt = document.createElement('option');
        opt.value = loc;
        opt.textContent = loc;
        if (loc === selectedLocation) opt.selected = true;
        locSelect.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    onPartyChange();

    // --- Trend Bar Chart ---
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        try {
            const trendLabels = <?= json_encode(array_column($chartData, 'month_label')) ?>;
            const trendCounts = <?= json_encode(array_map('intval', array_column($chartData, 'count'))) ?>;
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Calibrations',
                        data: trendCounts,
                        backgroundColor: 'rgba(0, 121, 107, 0.75)',
                        borderColor: 'rgba(0, 77, 64, 1)',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.y} certificate${ctx.parsed.y !== 1 ? 's' : ''}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0 },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        } catch (e) {
            trendCtx.parentNode.innerHTML = '<div style="color:red; padding:1rem;">Chart Error: ' + e.message + '</div>';
        }
    }

    // --- Top Instruments Horizontal Bar Chart ---
    const instrCtx = document.getElementById('instrumentsChart');
    if (instrCtx) {
        try {
            const instrLabels = <?= json_encode(array_column($topInstruments, 'label')) ?>;
            const instrCounts = <?= json_encode(array_map('intval', array_column($topInstruments, 'cnt'))) ?>;
            const palette = ['#00796b','#22b55d','#3b82f6','#7c3aed','#f59e0b'];
            new Chart(instrCtx, {
                type: 'bar',
                data: {
                    labels: instrLabels,
                    datasets: [{
                        label: 'Certificates',
                        data: instrCounts,
                        backgroundColor: instrLabels.map((_, i) => palette[i % palette.length] + 'cc'),
                        borderColor:      instrLabels.map((_, i) => palette[i % palette.length]),
                        borderWidth: 1.5,
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.x} certificate${ctx.parsed.x !== 1 ? 's' : ''}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0 },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        y: { grid: { display: false } }
                    }
                }
            });
        } catch (e) {
            instrCtx.parentNode.innerHTML = '<div style="color:red; padding:1rem;">Chart Error: ' + e.message + '</div>';
        }
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
