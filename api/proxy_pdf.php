<?php
// ============================================================
//  API: Proxy PDF Fetcher (Bypasses CORS & handles remote errors)
//  GET /api/proxy_pdf.php?url=...
// ============================================================
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$rawUrl = $_GET['url'] ?? '';

if (empty($rawUrl)) {
    http_response_code(400);
    die('Missing URL parameter');
}

// Clean and encode spaces in URL
$url = str_replace(' ', '%20', trim($rawUrl));

// Handle relative local URLs if any
if (str_starts_with($url, 'uploads/')) {
    $filePath = __DIR__ . '/../' . $url;
    if (file_exists($filePath)) {
        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($filePath));
        header('Access-Control-Allow-Origin: *');
        readfile($filePath);
        exit;
    }
}

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    die('Invalid URL parameter');
}

// Allow Cloudinary domain or local APP_URL domain
$parsed = parse_url($url);
$host   = $parsed['host'] ?? '';

$allowedHosts = [
    'res.cloudinary.com',
    'cloudinary.com',
    parse_url(APP_URL, PHP_URL_HOST) ?? 'localhost'
];

$isAllowed = false;
foreach ($allowedHosts as $ah) {
    if ($ah && (strtolower($host) === strtolower($ah) || str_ends_with(strtolower($host), '.' . strtolower($ah)))) {
        $isAllowed = true;
        break;
    }
}

if (!$isAllowed) {
    http_response_code(403);
    die('Access to URL host denied.');
}

$pdfData  = false;
$httpCode = 0;

if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    $pdfData  = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}

if (($pdfData === false || $httpCode !== 200) && function_exists('stream_context_create')) {
    $arrContextOptions = [
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
        "http" => [
            "user_agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)",
            "timeout" => 30
        ]
    ];
    $pdfData = @file_get_contents($url, false, stream_context_create($arrContextOptions));
    if ($pdfData !== false) {
        $httpCode = 200;
    }
}

// Verify that the retrieved binary content is actually a valid PDF (%PDF magic bytes)
if ($pdfData === false || strlen($pdfData) < 4 || substr($pdfData, 0, 4) !== '%PDF') {
    // Fail-safe fallback: Redirect directly to remote PDF URL to avoid Bad Gateway error in browser
    if (filter_var($url, FILTER_VALIDATE_URL) && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
        header("Location: " . $url);
        exit;
    }
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch valid PDF binary stream from target URL.',
        'url' => $url
    ]);
    exit;
}

header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdfData));
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=86400');

echo $pdfData;
exit;
