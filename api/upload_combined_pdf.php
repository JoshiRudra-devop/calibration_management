<?php
// ============================================================
//  API: Upload Combined PDF to Cloudinary
//  POST /api/upload_combined_pdf.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/cloudinary.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

verifyCsrf();

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];

if (empty($data['pdf_base64'])) {
    jsonResponse(false, 'Missing pdf_base64 data');
}

$b64Stripped = preg_replace('#^data:[^;]+;base64,#', '', $data['pdf_base64']);
$pdfBytes    = base64_decode($b64Stripped, true);

if (!$pdfBytes || substr($pdfBytes, 0, 4) !== '%PDF') {
    jsonResponse(false, 'Invalid PDF content');
}

$publicId = 'Combined_Certificates_' . date('Ymd_His');
$folder   = 'shreeji_certificates/combined';

try {
    $result = cloudinaryUpload($pdfBytes, $publicId, true, $folder);
    jsonResponse(true, 'Combined PDF uploaded to Cloudinary successfully', [
        'url'       => $result['url'],
        'public_id' => $result['public_id']
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Cloudinary upload failed: ' . $e->getMessage());
}
