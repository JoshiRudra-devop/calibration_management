<?php
// ============================================================
//  API: Get Cloudinary Upload Signature
//  GET/POST /api/get_cloudinary_signature.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
requireLogin();

header('Content-Type: application/json');

$cloudName = CLOUDINARY_CLOUD_NAME;
$apiKey    = CLOUDINARY_API_KEY;
$apiSecret = CLOUDINARY_API_SECRET;

if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
    jsonResponse(false, 'Cloudinary credentials not fully configured');
}

$timestamp = time();
$folder    = 'shreeji_certificates/combined';

$params = [
    'folder'    => $folder,
    'timestamp' => $timestamp,
];
ksort($params);

$sigParts = [];
foreach ($params as $key => $value) {
    $sigParts[] = "$key=$value";
}
$sigString = implode('&', $sigParts) . $apiSecret;
$signature = sha1($sigString);

jsonResponse(true, 'Signature generated', [
    'cloud_name' => $cloudName,
    'api_key'    => $apiKey,
    'timestamp'  => $timestamp,
    'folder'     => $folder,
    'signature'  => $signature,
]);
