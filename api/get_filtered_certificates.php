<?php
// ============================================================
//  API: Get Filtered Certificates (for Combined PDF Export)
//  GET /api/get_filtered_certificates.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
requireLogin();

header('Content-Type: application/json');

$db = getDB();

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
if (!empty($_GET['party_id'])) {
    $where[] = "c.party_id = ?";
    $params[] = (int) $_GET['party_id'];
}

// Site Location
if (!empty($_GET['location'])) {
    $where[] = "c.site_location = ?";
    $params[] = $_GET['location'];
}

// Due Status filter
$dueStatus = $_GET['due_status'] ?? '';
if ($dueStatus === 'overdue') {
    $where[] = "c.next_due_date < CURDATE()";
} elseif ($dueStatus === 'week') {
    $where[] = "c.next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
} elseif ($dueStatus === 'month') {
    $where[] = "c.next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
}

$baseWhere = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

$sql = "
    SELECT c.id, c.cert_number, c.party_name, c.site_location, 
           it.label AS instrument_label, it.slug AS instrument_slug, 
           c.calibration_date, c.next_due_date, c.pdf_url
    FROM   certificates c
    JOIN   instrument_types it ON it.id = c.instrument_type_id
" . $baseWhere . " ORDER BY c.created_at DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $certs = $stmt->fetchAll();

    jsonResponse(true, 'Filtered certificates fetched successfully', [
        'total' => count($certs),
        'certificates' => $certs
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to fetch certificates: ' . $e->getMessage(), [], 500);
}
