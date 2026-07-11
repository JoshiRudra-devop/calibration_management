<?php
// ============================================================
//  API: Public Certificate Verification
//  POST /api/verify_certificate.php
//  No authentication required. CAPTCHA + rate-limit protected.
// ============================================================
require_once __DIR__ . '/../includes/config.php';
// No requireLogin() — intentionally public

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . APP_URL);
header('Access-Control-Allow-Methods: POST');
header('Vary: Origin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// ── Rate limiting (15 attempts / 15 min per session) ──────
if (!isset($_SESSION['verify_rate'])) {
    $_SESSION['verify_rate'] = ['count' => 0, 'start' => time()];
}
if (time() - $_SESSION['verify_rate']['start'] > 900) {
    $_SESSION['verify_rate'] = ['count' => 0, 'start' => time()];
}
if ($_SESSION['verify_rate']['count'] >= 15) {
    jsonResponse(false, 'Too many attempts. Please wait 15 minutes.', [], 429);
}
$_SESSION['verify_rate']['count']++;

// ── Parse JSON body ───────────────────────────────────────
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    jsonResponse(false, 'Invalid request body', [], 400);
}

$certNumber    = clean($input['cert_number']    ?? '');
$captchaAnswer = (int) ($input['captcha_answer'] ?? -999);

// ── Validate CAPTCHA ──────────────────────────────────────
if (empty($_SESSION['captcha_q']) || time() > ($_SESSION['captcha_exp'] ?? 0)) {
    jsonResponse(false, 'CAPTCHA expired. Please refresh the page.', [], 400);
}
if ($captchaAnswer !== (int) $_SESSION['captcha_a']) {
    // Regenerate on failure
    $a = rand(2, 19);
    $b = rand(1, 12);
    $_SESSION['captcha_q']   = "$a + $b";
    $_SESSION['captcha_a']   = $a + $b;
    $_SESSION['captcha_exp'] = time() + 600;
    jsonResponse(false, 'Incorrect CAPTCHA answer.', [
        'new_captcha' => $_SESSION['captcha_q']
    ], 400);
}

// Consume CAPTCHA — one verification per CAPTCHA solve
$a = rand(2, 19);
$b = rand(1, 12);
$_SESSION['captcha_q']   = "$a + $b";
$_SESSION['captcha_a']   = $a + $b;
$_SESSION['captcha_exp'] = time() + 600;

if (empty($certNumber)) {
    jsonResponse(false, 'Certificate number is required.', [], 422);
}

// ── DB Lookup ─────────────────────────────────────────────
$db = getDB();

try {
    $stmt = $db->prepare("
        SELECT c.cert_number, c.party_name, c.site_location,
               c.calibration_date, c.next_due_date, c.pdf_url,
               it.label AS instrument_label
        FROM   certificates c
        JOIN   instrument_types it ON it.id = c.instrument_type_id
        WHERE  c.cert_number = ?
        LIMIT  1
    ");
    $stmt->execute([$certNumber]);
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cert) {
        jsonResponse(false, 'Certificate not found. Please check the number and try again.', [], 404);
    }

    $isExpired = !empty($cert['next_due_date']) && $cert['next_due_date'] < date('Y-m-d');

    jsonResponse(true, 'Certificate verified successfully', [
        'cert_number'      => $cert['cert_number'],
        'party_name'       => $cert['party_name'],
        'site_location'    => $cert['site_location'],
        'instrument'       => $cert['instrument_label'],
        'calibration_date' => $cert['calibration_date'],
        'next_due_date'    => $cert['next_due_date'],
        'pdf_url'          => $cert['pdf_url'],
        'is_expired'       => $isExpired,
        'status'           => $isExpired ? 'Expired' : 'Valid',
        'new_captcha'      => $_SESSION['captcha_q'],
    ]);

} catch (Exception $e) {
    error_log('verify_certificate API: ' . $e->getMessage());
    jsonResponse(false, 'Database error. Please contact support.', [], 500);
}
