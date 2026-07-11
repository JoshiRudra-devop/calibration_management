<?php
// ============================================================
//  API: Contact  POST /api/contact.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . APP_URL);
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Vary: Origin');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

$action = $_GET['action'] ?? 'save';

if ($action === 'stats') {
    requireLogin();
    $db = getDB();
    $stats = [];
    $stats['total_certs']   = (int) $db->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
    $stats['total_parties'] = (int) $db->query("SELECT COUNT(*) FROM parties")->fetchColumn();
    $stats['due_soon']      = (int) $db->query("SELECT COUNT(*) FROM certificates WHERE next_due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY)")->fetchColumn();
    $stats['this_month']    = (int) $db->query("SELECT COUNT(*) FROM certificates WHERE MONTH(calibration_date)=MONTH(CURDATE()) AND YEAR(calibration_date)=YEAR(CURDATE())")->fetchColumn();

    // Last 6 months chart data
    $chartStmt = $db->query("
        SELECT DATE_FORMAT(calibration_date,'%b %Y') AS month_label,
               COUNT(*) AS count
        FROM   certificates
        WHERE  calibration_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP  BY YEAR(calibration_date), MONTH(calibration_date)
        ORDER  BY calibration_date ASC
        LIMIT  6
    ");
    $stats['chart'] = $chartStmt->fetchAll();

    // Top instruments
    $topStmt = $db->query("
        SELECT it.label, COUNT(*) AS cnt
        FROM   certificates c
        JOIN   instrument_types it ON it.id = c.instrument_type_id
        GROUP  BY c.instrument_type_id
        ORDER  BY cnt DESC
        LIMIT  5
    ");
    $stats['top_instruments'] = $topStmt->fetchAll();

    jsonResponse(true, 'ok', ['stats' => $stats]);
}

// Save contact message
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'POST required');
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?? $_POST;

// CSRF check — prevents cross-site form hijacking
verifyCsrf($body['csrf_token'] ?? null);

// Rate limit — 5 messages per IP per hour (persistent, survives cookie clears)
rateLimitCheck('contact_' . ($_SERVER['REMOTE_ADDR'] ?? '0'), 5, 3600);

$name    = clean($body['name']    ?? '');
$email   = trim($body['email']   ?? '');
$subject = clean($body['subject'] ?? '');
$message = clean($body['message'] ?? '');

if (!$name || !$message) {
    jsonResponse(false, 'Name and message are required', [], 422);
}

// Length limits
if (mb_strlen($name) > 100) {
    jsonResponse(false, 'Name must be 100 characters or fewer.', [], 422);
}
if (mb_strlen($subject) > 200) {
    jsonResponse(false, 'Subject must be 200 characters or fewer.', [], 422);
}
if (mb_strlen($message) > 2000) {
    jsonResponse(false, 'Message must be 2000 characters or fewer.', [], 422);
}

// Email validation
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Invalid email address.', [], 422);
}
$email = clean($email);

$db = getDB();
$db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)")
   ->execute([$name, $email, $subject, $message]);

jsonResponse(true, 'Message sent successfully');