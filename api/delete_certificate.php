<?php
// ============================================================
//  API: Delete Certificate
//  POST /api/delete_certificate.php
//  Accepts JSON body or POST parameters (certificate_id or id)
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/audit.php';
requireLogin();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . APP_URL);
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Vary: Origin');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

verifyCsrf();

// Parse payload
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
} else {
    $data = $_POST;
}

$certId = (int)($data['certificate_id'] ?? $data['id'] ?? 0);

if ($certId <= 0) {
    jsonResponse(false, 'Invalid certificate ID provided');
}

$db = getDB();

try {
    // Fetch cert details for audit / verification
    $stmt = $db->prepare("SELECT id, cert_number, party_name FROM certificates WHERE id = ?");
    $stmt->execute([$certId]);
    $cert = $stmt->fetch();

    if (!$cert) {
        jsonResponse(false, 'Certificate not found or already deleted');
    }

    // Delete certificate from database (cascade deletes ctm_readings, cube_serials)
    $delStmt = $db->prepare("DELETE FROM certificates WHERE id = ?");
    $delStmt->execute([$certId]);

    // Audit log
    if (function_exists('logAudit')) {
        logAudit($_SESSION['user_id'] ?? 0, 'DELETE_CERTIFICATE', 'certificates', $certId, [
            'cert_number' => $cert['cert_number'],
            'party_name'  => $cert['party_name']
        ]);
    }

    jsonResponse(true, 'Certificate deleted successfully', ['deleted_id' => $certId, 'cert_number' => $cert['cert_number']]);

} catch (PDOException $e) {
    if (defined('SHREEJI_DEBUG') && SHREEJI_DEBUG) {
        jsonResponse(false, 'Database error: ' . $e->getMessage());
    }
    jsonResponse(false, 'Failed to delete certificate due to a database error');
}
