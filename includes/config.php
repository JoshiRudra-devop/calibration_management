<?php
// ============================================================
// Calibration Management System - Configuration File
// Edit these values to match your XAMPP / hosting environment
// ============================================================

// --- Load .env (secrets override) ---
// Create a .env file in the project root (see .env.example).
// Values in .env take precedence over the defaults below.
$_envFile = __DIR__ . '/../.env';
$_env     = file_exists($_envFile) ? (parse_ini_file($_envFile) ?: []) : [];

// --- MySQL ---
define('DB_HOST',     $_env['DB_HOST']     ?? 'localhost');
define('DB_USER',     $_env['DB_USER']     ?? 'root');
define('DB_PASS',     $_env['DB_PASS']     ?? '');
define('DB_NAME',     $_env['DB_NAME']     ?? 'shreeji_instruments');
define('DB_PORT',     (int)($_env['DB_PORT'] ?? 3306));

// --- Cloudinary ---
define('CLOUDINARY_CLOUD_NAME',    $_env['CLOUDINARY_CLOUD_NAME']    ?? 'dqlp56p7n');
define('CLOUDINARY_API_KEY',       $_env['CLOUDINARY_API_KEY']       ?? '325471695773222');
define('CLOUDINARY_API_SECRET',    $_env['CLOUDINARY_API_SECRET']    ?? 'emjUQeRridUXSpRg2utxRNGDlTA');
define('CLOUDINARY_UPLOAD_PRESET', $_env['CLOUDINARY_UPLOAD_PRESET'] ?? 'shreeji_instruments');

// --- App ---
define('APP_NAME',    $_env['APP_NAME']    ?? 'Calibration Management System');

// Certificate Specific Config
define('CERT_COMPANY_NAME',     $_env['CERT_COMPANY_NAME']     ?? 'CALIBRATION MANAGEMENT SYSTEM');
define('CERT_COMPANY_TAGLINE',  $_env['CERT_COMPANY_TAGLINE']  ?? 'SALES • SERVICE • REPAIRING • CALIBRATIONS');
define('CERT_CALIBRATOR_NAME',  $_env['CERT_CALIBRATOR_NAME']  ?? 'YOGESH BHAI');
define('CERT_CALIBRATOR_TITLE', $_env['CERT_CALIBRATOR_TITLE'] ?? 'PROPRIETOR');
define('CERT_CALIB_ORG',        $_env['CERT_CALIB_ORG']        ?? 'NATIONAL COUNCIL FOR CEMENT AND BUILDING MATERIALS');

// Dynamically detect APP_URL based on the request to support different directories (e.g. directly under htdocs or nested)
$projectDir = str_replace('\\', '/', dirname(__DIR__));
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
if ($docRoot && strpos($projectDir, $docRoot) === 0) {
    $relativePath = substr($projectDir, strlen($docRoot));
} else {
    $relativePath = '/calibration certificate';
}
$relativePath = str_replace(' ', '%20', $relativePath);
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dynamicAppUrl = rtrim($protocol . $host . $relativePath, '/');

define('APP_URL',     $dynamicAppUrl); // Dynamically detected URL without trailing slash
define('UPLOAD_DIR',  dirname(__DIR__) . '/uploads/');
define('TIMEZONE',    'Asia/Kolkata');

date_default_timezone_set(TIMEZONE);

// --- Session hardening ---
session_set_cookie_params([
    'lifetime' => 31536000,                                // Expire in 1 year instead of browser close
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,                                    // Not accessible via JavaScript
    'samesite' => 'Strict',                               // Block cross-site requests
]);
session_start();

// --- Idle session timeout ---
// Removed: The user will now stay logged in until they explicitly click Logout.
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

// --- HTTP Security Headers ---
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header(
        "Content-Security-Policy: " .
        "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; " .
        "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdnjs.cloudflare.com; " .
        "font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com data:; " .
        "img-src 'self' data: res.cloudinary.com *.cloudinary.com; " .
        "connect-src 'self' api.cloudinary.com; " .
        "frame-src 'self'; " .
        "object-src 'none'; " .
        "base-uri 'self'; " .
        "form-action 'self';"
    );
}

// --- PDO Connection ---
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed. Please contact support.']));
        }
    }
    return $pdo;
}

// --- JSON response helper ---
function jsonResponse(bool $success, string $message, array $data = [], int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// --- Sanitize ---
function clean(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)));
}

// --- Auth check ---
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        $isApiCall = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            || isset($_SERVER['HTTP_X_CSRF_TOKEN'])
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
        if ($isApiCall) {
            jsonResponse(false, 'Unauthorised', [], 401);
        }
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

// --- Role check (admin-only pages) ---
function requireRole(string $role): void {
    requireLogin();
    if (($_SESSION['user']['role'] ?? '') !== $role) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            jsonResponse(false, 'Forbidden', [], 403);
        }
        header('Location: ' . APP_URL . '/dashboard.php');
        exit;
    }
}

// --- CSRF helpers ---
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $bodyToken = null): void {
    // Priority: header → pre-decoded body token → re-read body → POST field
    $token = $_SERVER['HTTP_X_CSRF_TOKEN']
           ?? $bodyToken
           ?? (json_decode(file_get_contents('php://input'), true)['csrf_token'] ?? null)
           ?? ($_POST['csrf_token'] ?? '');

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if ($sessionToken === '' || !hash_equals($sessionToken, (string) $token)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
    }
}

// --- Persistent file-based rate limiter ---
// Counters survive page reloads and cookie clears (unlike the session-based login limiter).
// $identifier  — unique key, e.g. 'contact_127.0.0.1'
// $maxHits     — maximum allowed hits within the window
// $windowSec   — rolling window length in seconds
function rateLimitCheck(string $identifier, int $maxHits, int $windowSec): void {
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'shreeji_rl';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    $file = $dir . DIRECTORY_SEPARATOR . md5($identifier) . '.json';
    $fp   = @fopen($file, 'c+');
    if (!$fp) {
        return; // Fail open if filesystem is unavailable
    }

    flock($fp, LOCK_EX);

    $raw  = fread($fp, 512);
    $data = ($raw !== false && $raw !== '') ? (json_decode($raw, true) ?: []) : [];

    $now = time();
    if (empty($data['window_start']) || ($now - (int)$data['window_start']) > $windowSec) {
        $data = ['count' => 0, 'window_start' => $now];
    }

    $data['count']++;

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    flock($fp, LOCK_UN);
    fclose($fp);

    if ($data['count'] > $maxHits) {
        http_response_code(429);
        header('Retry-After: ' . ($windowSec - ($now - (int)$data['window_start'])));
        die(json_encode(['success' => false, 'message' => 'Too many requests. Please slow down and try again later.']));
    }
}