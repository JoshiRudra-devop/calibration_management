<?php
// ============================================================
//  API: Get Next Certificate Number
//  GET /api/get_next_certificate_number.php?instrument_type=autolevel
// ============================================================
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Rate limit — 120 requests/IP/min prevents bulk enumeration
rateLimitCheck('data_' . ($_SERVER['REMOTE_ADDR'] ?? '0'), 120, 60);

header('Content-Type: application/json');

$slug = clean($_GET['instrument_type'] ?? '');

if (!$slug) {
    jsonResponse(false, 'Instrument type is required');
}

$db = getDB();

try {
    // 1. Resolve instrument type id
    $stmt = $db->prepare("SELECT id FROM instrument_types WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $instrType = $stmt->fetch();
    
    if (!$instrType) {
        jsonResponse(false, 'Unknown instrument type: ' . $slug);
    }
    
    // 2. Fetch the counter
    $stmt = $db->prepare("SELECT prefix, current_no, current_year, current_month FROM certificate_counter WHERE instrument_type_id = ?");
    $stmt->execute([$instrType['id']]);
    $counter = $stmt->fetch();
    
    $currentYear  = (int) date('Y');
    $currentMonth = (int) date('n');
    $yy = date('y'); // e.g. 26
    $mm = str_pad($currentMonth, 2, '0', STR_PAD_LEFT); // e.g. 06
    
    if (!$counter) {
        $prefix = strtoupper(substr($slug, 0, 3));
        $nextNo = 1;
    } else {
        $prefix = $counter['prefix'];
        if ((int)$counter['current_year'] === $currentYear && (int)$counter['current_month'] === $currentMonth) {
            $nextNo = (int)$counter['current_no'] + 1;
        } else {
            $nextNo = 1;
        }
    }
    
    $nextCertNumber = $prefix . '-' . $yy . $mm . str_pad($nextNo, 2, '0', STR_PAD_LEFT);
    
    jsonResponse(true, 'Next certificate number retrieved', [
        'next_certificate_number' => $nextCertNumber
    ]);

} catch (Exception $e) {
    error_log('get_next_certificate_number error: ' . $e->getMessage());
    jsonResponse(false, 'Database error. Please contact support.', [], 500);
}
