<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
include __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch all dashboard statistics in 1 consolidated fast query
$stats = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM certificates) AS totalCerts,
        (SELECT COUNT(*) FROM parties) AS totalParties,
        (SELECT COUNT(*) FROM instrument_types) AS totalTypes,
        (SELECT COUNT(*) FROM certificates WHERE MONTH(calibration_date) = MONTH(CURDATE()) AND YEAR(calibration_date) = YEAR(CURDATE())) AS thisMonth,
        (SELECT COUNT(*) FROM certificates WHERE next_due_date < CURDATE()) AS overdueCount,
        (SELECT COUNT(*) FROM certificates WHERE next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)) AS dueThisWeekCount,
        (SELECT COUNT(*) FROM certificates WHERE next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)) AS dueThisMonthCount
")->fetch(PDO::FETCH_ASSOC);

$totalCerts        = (int)($stats['totalCerts'] ?? 0);
$totalParties      = (int)($stats['totalParties'] ?? 0);
$totalTypes        = (int)($stats['totalTypes'] ?? 0);
$thisMonth         = (int)($stats['thisMonth'] ?? 0);
$overdueCount      = (int)($stats['overdueCount'] ?? 0);
$dueThisWeekCount  = (int)($stats['dueThisWeekCount'] ?? 0);
$dueThisMonthCount = (int)($stats['dueThisMonthCount'] ?? 0);

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

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
      <h1 style="margin: 0; color: var(--primary);">Dashboard</h1>
      <button type="button" onclick="window.history.back()" class="instrument-action-btn btn-print" style="padding: 0.55rem 1.1rem; border-radius: var(--radius); font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 13px; cursor: pointer; box-shadow: var(--shadow-sm);">
        <i class="fas fa-arrow-left"></i> Back
      </button>
    </div>

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
    <style>
      .filtered-btn {
        padding: 0.55rem 1rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.2s ease;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
      }
      .filtered-btn:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        transform: translateY(-1px);
      }
      .filtered-btn.btn-save {
        background: var(--primary, #00796b);
        color: #ffffff;
        border: 1px solid var(--primary, #00796b);
        box-shadow: 0 2px 4px rgba(0,121,107,0.25);
      }
      .filtered-btn.btn-save:hover {
        background: #004d40;
        border-color: #004d40;
      }
      .filtered-btn.btn-print, .filtered-btn.btn-share {
        border-style: dashed;
        background: #f8fafc;
      }
    </style>

    <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-md); margin-bottom: 2rem;">
      <h3 style="margin-bottom: 1.5rem; color: var(--primary); border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <span>Certificate History & Filtering</span>
        <span style="font-size: 0.85rem; font-weight: 700; color: #0369a1; background: #e0f2fe; padding: 0.35rem 0.75rem; border-radius: 6px;">
          <?= number_format($totalRows) ?> Matching Record(s)
        </span>
      </h3>
      
      <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; align-items: end;">
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

      <!-- Actions Toolbar for All Filtered Records -->
      <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem;">
          <span style="font-weight: 700; color: var(--primary); font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
            <i class="fas fa-layer-group"></i> Filtered Data Operations:
          </span>
          <span style="font-size: 0.82rem; color: #475569; background: #e2e8f0; padding: 0.2rem 0.55rem; border-radius: 4px; font-weight: 700;">
            <?= number_format($totalRows) ?> Records
          </span>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
          <button type="button" onclick="previewFilteredPDF()" class="filtered-btn" title="Preview Combined PDF of Filtered Records">
            📋 Preview Certificate
          </button>
          <button type="button" onclick="exportCombinedPDF()" class="filtered-btn btn-save" title="Download Merged PDF of All Filtered Certificates">
            📄 SAVE (Combined PDF)
          </button>
          <button type="button" onclick="printCombinedPDF()" class="filtered-btn btn-print" title="Print All Filtered Certificates Combined">
            🖨️ Print
          </button>
          <button type="button" onclick="generateCombinedStickers()" class="filtered-btn btn-sticker" title="Generate Multi-page Info Stickers for All Filtered Records">
            🏷️ Generate Info Sticker
          </button>
          <button type="button" onclick="shareCombinedPDF()" class="filtered-btn btn-share" title="Share Combined PDF of Filtered Records">
            📤 Share PDF
          </button>
        </div>
      </div>

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
                  <td style="padding: 1rem; display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap;">
                    <?php if ($cert['pdf_url']): ?>
                      <a href="api/proxy_pdf.php?url=<?= urlencode($cert['pdf_url']) ?>" target="_blank" style="color: var(--accent); font-weight: 600; text-decoration: underline; font-size: 0.85rem;">View PDF</a>
                    <?php endif; ?>
                    <a href="certificates/<?= htmlspecialchars($cert['instrument_slug']) ?>.php?id=<?= $cert['id'] ?>" style="color: var(--primary); font-weight: 600; text-decoration: underline; font-size: 0.85rem;">Edit/Prefill</a>
                    <button type="button" onclick="deleteCertificate(<?= $cert['id'] ?>, '<?= htmlspecialchars($cert['cert_number'], ENT_QUOTES) ?>')" class="btn-delete" title="Delete Certificate">
                      <i class="fas fa-trash-alt"></i> Delete
                    </button>
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

<script src="assets/js/pdf-lib.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js" integrity="sha512-CQBWl4fJHWbryGE+Pc7UAxWMUMNMWzWxF4SQo9CgkJIN1kx6djDQZjh3Y8SZ1d+6I+1zze6Z7kHXO7q3UyZAWw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
// ── Bulk Operations on Filtered Data ─────────────────────────────────

function showLoader(msg) {
  if (typeof Loader !== 'undefined' && Loader.show) {
    Loader.show(msg);
  } else {
    console.log('[Loader]', msg);
  }
}

function hideLoader() {
  if (typeof Loader !== 'undefined' && Loader.hide) {
    Loader.hide();
  }
}

function showLoaderSuccess(msg) {
  if (typeof Loader !== 'undefined' && Loader.success) {
    Loader.success(msg);
  } else {
    alert(msg);
  }
}

async function fetchFilteredCertificatesData() {
  const currentParams = new URLSearchParams(window.location.search);
  currentParams.set('_t', Date.now()); // Prevent browser GET caching
  
  const response = await fetch('api/get_filtered_certificates.php?' + currentParams.toString(), {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  });

  if (!response.ok) {
    throw new Error(`Server returned HTTP status ${response.status}`);
  }

  const result = await response.json();
  
  let certs = null;
  if (Array.isArray(result.certificates)) {
    certs = result.certificates;
  } else if (result.data && Array.isArray(result.data.certificates)) {
    certs = result.data.certificates;
  } else if (result.data && Array.isArray(result.data)) {
    certs = result.data;
  }

  if (!certs || !Array.isArray(certs)) {
    throw new Error(`No certificates found in response (${result.message || 'unknown error'})`);
  }

  return certs;
}

async function getCombinedPDFBlob(includeLetterhead = true) {
  const certs = await fetchFilteredCertificatesData();
  if (!certs || certs.length === 0) {
    throw new Error('No matching records found for the applied filter.');
  }

  showLoader(`Fetching records for ${certs.length} certificate(s)...`);

  const pdfLibObj = window.PDFLib || (typeof PDFLib !== 'undefined' ? PDFLib : null);
  if (!pdfLibObj || !pdfLibObj.PDFDocument) {
    throw new Error('PDF merging library (PDFLib) failed to load. Please refresh the page.');
  }

  const mergedPdf = await pdfLibObj.PDFDocument.create();

  // Load letterhead images if includeLetterhead is true (for SAVE or SHARE)
  let headerImg = null, footerImg = null, stampImg = null, signImg = null;
  if (includeLetterhead) {
    try {
      const fetchImg = (url) => fetch(url).then(r => r.ok ? r.arrayBuffer() : null).catch(() => null);
      const [hBytes, fBytes, stBytes, siBytes] = await Promise.all([
        fetchImg('assets/images/header.jpeg'),
        fetchImg('assets/images/footer.jpeg'),
        fetchImg('assets/images/stamp.jpeg'),
        fetchImg('assets/images/sign.jpeg')
      ]);

      if (hBytes)  headerImg  = await mergedPdf.embedJpg(hBytes).catch(() => null);
      if (fBytes)  footerImg  = await mergedPdf.embedJpg(fBytes).catch(() => null);
      if (stBytes) stampImg   = await mergedPdf.embedJpg(stBytes).catch(() => null);
      if (siBytes) signImg    = await mergedPdf.embedJpg(siBytes).catch(() => null);
    } catch (e) {
      console.warn('Letterhead image load warning:', e);
    }
  }

  // Filter certificates that have pdf_url
  const pdfCerts = certs.filter(c => c.pdf_url && c.pdf_url.trim() !== '');

  let mergedPagesCount = 0;

  if (pdfCerts.length > 0) {
    let loadedCount = 0;
    for (const cert of pdfCerts) {
      loadedCount++;
      showLoader(`Merging certificate PDF ${loadedCount} of ${pdfCerts.length} (${cert.cert_number})...`);
      
      try {
        const proxyUrl = 'api/proxy_pdf.php?url=' + encodeURIComponent(cert.pdf_url);
        const pdfBytesRes = await fetch(proxyUrl);
        if (!pdfBytesRes.ok) {
          console.warn(`Failed to fetch PDF for cert ${cert.cert_number}: ${pdfBytesRes.statusText}`);
          continue;
        }
        const pdfBytes = await pdfBytesRes.arrayBuffer();
        
        // Validate magic bytes (%PDF)
        if (pdfBytes.byteLength < 4) continue;
        const headerBytes = new Uint8Array(pdfBytes.slice(0, 4));
        const headerStr = String.fromCharCode(...headerBytes);
        if (headerStr !== '%PDF') {
          console.warn(`Invalid PDF header for cert ${cert.cert_number}`);
          continue;
        }

        const pdfDoc = await pdfLibObj.PDFDocument.load(pdfBytes);
        const copiedPages = await mergedPdf.copyPages(pdfDoc, pdfDoc.getPageIndices());
        copiedPages.forEach((page) => {
          mergedPdf.addPage(page);
          mergedPagesCount++;
        });
      } catch (err) {
        console.error(`Error merging PDF for ${cert.cert_number}:`, err);
      }
    }
  }

  // Apply Letterhead manipulation on every page in mergedPdf
  const pageCount = mergedPdf.getPageCount();
  for (let i = 0; i < pageCount; i++) {
    const page = mergedPdf.getPage(i);
    const { width, height } = page.getSize();

    if (!includeLetterhead) {
      // PREVIEW & PRINT: Remove/white-out images of header, footer, stamp, and sign
      // 1. Top Header image whiteout (top 33mm)
      page.drawRectangle({
        x: 0,
        y: height - (33 * 2.83465),
        width: width,
        height: 33 * 2.83465,
        color: PDFLib.rgb(1, 1, 1)
      });

      // 2. Bottom Footer image whiteout (bottom 27mm)
      page.drawRectangle({
        x: 0,
        y: 0,
        width: width,
        height: 27 * 2.83465,
        color: PDFLib.rgb(1, 1, 1)
      });

      // 3. Stamp image whiteout
      page.drawRectangle({
        x: 98 * 2.83465,
        y: height - (254 * 2.83465),
        width: 39 * 2.83465,
        height: 39 * 2.83465,
        color: PDFLib.rgb(1, 1, 1)
      });

      // 4. Sign image whiteout
      page.drawRectangle({
        x: 158 * 2.83465,
        y: height - (244 * 2.83465),
        width: 44 * 2.83465,
        height: 14 * 2.83465,
        color: PDFLib.rgb(1, 1, 1)
      });
    } else {
      // SAVE & SHARE: Draw letterhead images (header, footer, stamp, sign)
      if (headerImg) {
        page.drawImage(headerImg, {
          x: 3 * 2.83465,
          y: height - (33 * 2.83465),
          width: 204 * 2.83465,
          height: 30 * 2.83465
        });
      }
      if (footerImg) {
        page.drawImage(footerImg, {
          x: 0,
          y: 0,
          width: width,
          height: 27 * 2.83465
        });
      }
      if (stampImg) {
        page.drawImage(stampImg, {
          x: 100 * 2.83465,
          y: height - (252 * 2.83465),
          width: 35 * 2.83465,
          height: 35 * 2.83465
        });
      }
      if (signImg) {
        page.drawImage(signImg, {
          x: 160 * 2.83465,
          y: height - (242 * 2.83465),
          width: 40 * 2.83465,
          height: 10 * 2.83465
        });
      }
    }
  }

  const mergedPdfBytes = await mergedPdf.save();
  return new Blob([mergedPdfBytes], { type: 'application/pdf' });
}

async function previewFilteredPDF() {
  try {
    showLoader('Preparing combined PDF preview (format without letterhead)...');
    const blob = await getCombinedPDFBlob(false);
    const blobUrl = URL.createObjectURL(blob);
    if (typeof window.showGlobalPreviewModal === 'function') {
      window.showGlobalPreviewModal(blobUrl);
    } else {
      window.open(blobUrl, '_blank');
    }
    showLoaderSuccess('Preview ready!');
  } catch (err) {
    hideLoader();
    alert('Preview Error: ' + err.message);
  }
}

async function exportCombinedPDF() {
  try {
    showLoader('Building combined PDF file with full letterhead images...');
    const blob = await getCombinedPDFBlob(true);
    
    const fileName = `Combined_Certificates_${new Date().toISOString().slice(0,10)}.pdf`;

    const blobUrl = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.style.display = 'none';
    link.href = blobUrl;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    
    setTimeout(() => {
      if (link.parentNode) document.body.removeChild(link);
      URL.revokeObjectURL(blobUrl);
    }, 5000);

    showLoaderSuccess('Combined PDF saved directly to device! 📄✨');
  } catch (err) {
    hideLoader();
    alert('Export Error: ' + err.message);
  }
}

async function printCombinedPDF() {
  try {
    showLoader('Preparing combined PDF for printing (without letterhead)...');
    const blob = await getCombinedPDFBlob(false);
    const blobUrl = URL.createObjectURL(blob);
    const printWin = window.open(blobUrl, '_blank');
    if (printWin) {
      setTimeout(() => {
        try { printWin.print(); } catch (e) {}
      }, 1000);
      showLoaderSuccess('Print dialog opened!');
    } else {
      showLoaderSuccess('PDF opened in new tab for printing.');
    }
  } catch (err) {
    hideLoader();
    alert('Print Error: ' + err.message);
  }
}

async function shareCombinedPDF() {
  try {
    showLoader('Generating shareable combined PDF with full letterhead images...');
    const blob = await getCombinedPDFBlob(true);
    const fileName = `Combined_Certificates_${new Date().toISOString().slice(0,10)}.pdf`;
    const pdfFile = new File([blob], fileName, { type: 'application/pdf' });

    if (navigator.share && navigator.canShare && navigator.canShare({ files: [pdfFile] })) {
      await navigator.share({
        files: [pdfFile],
        title: 'Filtered Calibration Certificates',
        text: 'Merged PDF of filtered calibration certificates from Shreeji Instruments'
      });
      showLoaderSuccess('Shared successfully!');
    } else {
      const blobUrl = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = blobUrl;
      link.download = fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      showLoaderSuccess('Combined PDF downloaded to device! 📄');
    }
  } catch (err) {
    hideLoader();
    if (err.name !== 'AbortError') {
      alert('Share Error: ' + err.message);
    }
  }
}

async function generateCombinedStickers() {
  try {
    showLoader('Fetching filtered records for Info Stickers...');
    const certs = await fetchFilteredCertificatesData();
    if (!certs || certs.length === 0) {
      hideLoader();
      alert('No matching records found to generate stickers.');
      return;
    }

    showLoader(`Generating Info Stickers for ${certs.length} record(s)...`);
    const { jsPDF } = window.jspdf;
    
    // Sticker dimensions: 60mm x 30mm landscape
    const width = 60 * 2.83465;
    const height = 30 * 2.83465;
    const doc = new jsPDF({
      orientation: 'landscape',
      unit: 'pt',
      format: [width, height]
    });

    const primaryBlue = [19, 52, 165];

    certs.forEach((cert, index) => {
      if (index > 0) {
        doc.addPage([width, height], 'landscape');
      }

      // Outer border
      doc.setDrawColor(...primaryBlue);
      doc.setLineWidth(2.5);
      doc.rect(4, 4, width - 8, height - 8);

      // Table layout
      const tableLeft = 10;
      const tableTop = 10;
      const tableWidth = width - 20;
      const rowHeight = 12.5;
      const labelWidth = tableWidth * 0.38;

      const formatDateStr = (str) => {
        if (!str) return 'N/A';
        const d = new Date(str);
        if (isNaN(d.getTime())) return str;
        return d.toLocaleDateString('en-GB'); // DD/MM/YYYY
      };

      const tableData = [
        { label: 'CERT NO.', value: cert.cert_number || 'N/A' },
        { label: 'PARTY', value: cert.party_name || 'N/A' },
        { label: 'INSTRUMENT', value: cert.instrument_label || 'N/A' },
        { label: 'CALIB. DATE', value: formatDateStr(cert.calibration_date) },
        { label: 'NEXT DUE', value: formatDateStr(cert.next_due_date) }
      ];

      doc.setDrawColor(200, 200, 200);
      doc.setLineWidth(0.8);
      doc.rect(tableLeft, tableTop, tableWidth, rowHeight * tableData.length);

      tableData.forEach((row, rIdx) => {
        const rowY = tableTop + (rIdx * rowHeight);
        if (rIdx > 0) doc.line(tableLeft, rowY, tableLeft + tableWidth, rowY);
        doc.line(tableLeft + labelWidth, rowY, tableLeft + labelWidth, rowY + rowHeight);

        const midY = rowY + rowHeight / 2 + 1;

        // Label
        doc.setFont('times', 'bold');
        doc.setFontSize(4.2);
        doc.setTextColor(...primaryBlue);
        doc.text(row.label, tableLeft + 3, midY, { baseline: 'middle' });

        // Value
        doc.setFont('times', 'normal');
        doc.setFontSize(4.2);
        doc.setTextColor(0, 0, 0);
        let valText = String(row.value);
        if (valText.length > 28) valText = valText.substring(0, 26) + '..';
        doc.text(valText, tableLeft + labelWidth + 4, midY, { baseline: 'middle' });
      });
    });

    const stickerBlob = doc.output('blob');
    const fileName = `InfoStickers_Filtered_${new Date().toISOString().slice(0,10)}.pdf`;

    // Direct download trigger to device
    const blobUrl = URL.createObjectURL(stickerBlob);
    const link = document.createElement('a');
    link.style.display = 'none';
    link.href = blobUrl;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    setTimeout(() => {
      if (link.parentNode) document.body.removeChild(link);
      URL.revokeObjectURL(blobUrl);
    }, 5000);

    showLoaderSuccess(`Info Stickers Saved to Device (${certs.length} records)! 🏷️`);
  } catch (err) {
    hideLoader();
    alert('Sticker Error: ' + err.message);
  }
}

async function shareCombinedPDF() {
  try {
    showLoader('Generating shareable combined PDF...');
    const blob = await getCombinedPDFBlob();
    const fileName = `Combined_Certificates_${new Date().toISOString().slice(0,10)}.pdf`;
    const pdfFile = new File([blob], fileName, { type: 'application/pdf' });

    if (navigator.share && navigator.canShare && navigator.canShare({ files: [pdfFile] })) {
      await navigator.share({
        files: [pdfFile],
        title: 'Filtered Calibration Certificates',
        text: 'Merged PDF of filtered calibration certificates from Shreeji Instruments'
      });
      showLoaderSuccess('Shared successfully!');
    } else {
      const blobUrl = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = blobUrl;
      link.download = fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      showLoaderSuccess('Combined PDF downloaded to device! 📄');
    }
  } catch (err) {
    hideLoader();
    if (err.name !== 'AbortError') {
      alert('Share Error: ' + err.message);
    }
  }
}

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
