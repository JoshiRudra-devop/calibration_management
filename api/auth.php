<?php
// Must be before require_once so session_start() warnings can't corrupt JSON output
ini_set('display_errors', '0');

// ============================================================
//  API: Auth  POST /api/auth.php?action=login|logout
// ============================================================
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');
$allowedOrigin = APP_URL;
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

$action = $_GET['action'] ?? 'login';

if ($action === 'logout') {
    // Clear session data, destroy session, and expire the cookie
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();

    // Redirect to login if normal browser request, otherwise return JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        jsonResponse(true, 'Logged out');
    } else {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

if ($action === 'check') {
    jsonResponse(true, 'ok', [
        'logged_in' => !empty($_SESSION['user_id']),
        'user'      => $_SESSION['user'] ?? null,
    ]);
}

// LOGIN
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'POST required');
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true) ?? $_POST;

// Pass the already-decoded body token so verifyCsrf doesn't need to re-read php://input
verifyCsrf($body['csrf_token'] ?? null);

$phone    = clean($body['phone']    ?? '');
$password = $body['password'] ?? '';

if (!$phone || !$password) {
    jsonResponse(false, 'Phone and password required');
}

// ── Rate limiting: max 5 failed attempts per IP per 15 minutes ──
$ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rlKey    = 'rl_' . md5($ip);
$maxTries = 5;
$windowSec = 900; // 15 minutes

if (empty($_SESSION[$rlKey])) {
    $_SESSION[$rlKey] = ['count' => 0, 'first_at' => time()];
}

$rl = &$_SESSION[$rlKey];

// Reset window if expired
if (time() - $rl['first_at'] > $windowSec) {
    $rl = ['count' => 0, 'first_at' => time()];
}

if ($rl['count'] >= $maxTries) {
    $waitSec = $windowSec - (time() - $rl['first_at']);
    jsonResponse(false, "Too many failed attempts. Please wait " . ceil($waitSec / 60) . " minute(s) and try again.");
}

$db   = getDB();

try {
    $stmt = $db->prepare("SELECT * FROM users WHERE phone = ? AND active = 1 LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    jsonResponse(false, 'Database error. Please contact support.');
}

if (!$user || !password_verify($password, $user['password_hash'])) {
    $rl['count']++;
    $remaining = $maxTries - $rl['count'];
    $msg = $remaining > 0
        ? "Invalid credentials. {$remaining} attempt(s) remaining."
        : "Invalid credentials. Account temporarily locked for 15 minutes.";
    jsonResponse(false, $msg);
}

// Successful login — reset rate limit, regenerate session for security
unset($_SESSION[$rlKey]);

// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['user']    = [
    'id'    => $user['id'],
    'name'  => $user['name'],
    'phone' => $user['phone'],
    'role'  => $user['role'],
];

jsonResponse(true, 'Login successful', ['user' => $_SESSION['user']]);