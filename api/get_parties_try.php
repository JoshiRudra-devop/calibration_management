<?php
// ============================================================
//  API: Get Parties Autocomplete (Trial)
//  GET /api/get_parties_try.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Rate limit — 120 requests/IP/min prevents bulk enumeration / scraping
rateLimitCheck('data_' . ($_SERVER['REMOTE_ADDR'] ?? '0'), 120, 60);

header('Content-Type: application/json');
header('Cache-Control: private, max-age=60');

$db = getDB();

try {
    $search = trim($_GET['q'] ?? '');
    if (!empty($search)) {
        $searchLike = '%' . $search . '%';
        $sql = "
            SELECT party_name AS name, site_location FROM (
                SELECT DISTINCT party_name, site_location FROM certificates WHERE party_name LIKE ?
                UNION
                SELECT DISTINCT name AS party_name, address AS site_location FROM parties WHERE name LIKE ?
            ) AS combined ORDER BY name ASC LIMIT 50";
        $stmt = $db->prepare($sql);
        $stmt->execute([$searchLike, $searchLike]);
    } else {
        $sql = "
            SELECT party_name AS name, site_location FROM (
                SELECT DISTINCT party_name, site_location FROM certificates WHERE party_name != ''
                UNION
                SELECT DISTINCT name AS party_name, address AS site_location FROM parties WHERE name != ''
            ) AS combined ORDER BY name ASC LIMIT 100";
        $stmt = $db->query($sql);
    }
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonResponse(true, 'Parties retrieved successfully', ['parties' => $result]);
} catch (Exception $e) {
    error_log('get_parties_try error: ' . $e->getMessage());
    jsonResponse(false, 'Database error. Please contact support.', [], 500);
}
