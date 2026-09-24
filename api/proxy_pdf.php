<?php
// ============================================================
//  API: Proxy PDF Fetcher (Bypasses CORS for PDF merging)
//  GET /api/proxy_pdf.php?url=...
// ============================================================
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$url = $_GET['url'] ?? '';

if (empty($url)) {
    http_response_code(400);
    die('Missing URL parameter');
}

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

$pdfData = false;
if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $pdfData = curl_exec($ch);
    curl_close($ch);
}

if ($pdfData === false || strlen($pdfData) === 0) {
    $arrContextOptions = [
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
    ];
    $pdfData = @file_get_contents($url, false, stream_context_create($arrContextOptions));
}

if ($pdfData === false || strlen($pdfData) === 0) {
    http_response_code(502);
    die('Failed to fetch PDF resource.');
}

header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdfData));
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=86400');

echo $pdfData;
exit;
