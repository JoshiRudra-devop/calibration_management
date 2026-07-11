<?php
// ============================================================
//  API: Get Certificate Details by ID
//  GET /api/get_certificate.php?id=123
// ============================================================
require_once __DIR__ . '/../includes/config.php';
requireLogin();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . APP_URL);
header('Access-Control-Allow-Methods: GET');
header('Vary: Origin');

$certId = (int) ($_GET['id'] ?? 0);

if (!$certId) {
    jsonResponse(false, 'Certificate ID is required');
}

$db = getDB();

try {
    // Fetch certificate master details
    $stmt = $db->prepare("
        SELECT c.*, it.slug AS instrument_slug, it.label AS instrument_label
        FROM certificates c
        JOIN instrument_types it ON it.id = c.instrument_type_id
        WHERE c.id = ? LIMIT 1
    ");
    $stmt->execute([$certId]);
    $cert = $stmt->fetch();
    
    if (!$cert) {
        jsonResponse(false, 'Certificate not found');
    }
    
    // Fetch CTM readings if instrument is CTM
    $ctmReadings = [];
    if ($cert['instrument_slug'] === 'ctm') {
        $rStmt = $db->prepare("SELECT * FROM ctm_readings WHERE certificate_id = ? ORDER BY id ASC");
        $rStmt->execute([$certId]);
        $ctmReadings = $rStmt->fetchAll();
    }
    
    // Fetch Cube serials if instrument is cube_mould or isi_cube
    $cubeSerials = [];
    if (in_array($cert['instrument_slug'], ['cube_mould', 'isi_cube', 'cloud_cube'])) {
        $sStmt = $db->prepare("SELECT serial_no FROM cube_serials WHERE certificate_id = ? ORDER BY sr_no ASC");
        $sStmt->execute([$certId]);
        $cubeSerials = $sStmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Decode form_data if saved as JSON
    $formData = null;
    if (!empty($cert['form_data'])) {
        $formData = json_decode($cert['form_data'], true);
    }
    
    jsonResponse(true, 'Certificate retrieved successfully', [
        'certificate'  => $cert,
        'form_data'    => $formData,
        'ctm_readings' => $ctmReadings,
        'cube_serials' => $cubeSerials
    ]);

} catch (Exception $e) {
    error_log('get_certificate error: ' . $e->getMessage());
    jsonResponse(false, 'Database error. Please contact support.', [], 500);
}
