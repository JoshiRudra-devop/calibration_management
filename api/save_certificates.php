<?php
// ============================================================
//  API: Save Certificate
//  POST /api/save_certificate.php
//  Accepts JSON body or multipart/form-data (with pdf file)
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/cloudinary.php';
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

// Parse body ─────────────────────────────────────────────────
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (str_contains($contentType, 'application/json')) {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];
} else {
    $data = $_POST;
}

// Required fields
$required = ['instrument_type', 'party_name', 'calibration_date', 'next_due_date'];
foreach ($required as $f) {
    if (empty($data[$f])) {
        jsonResponse(false, "Missing required field: {$f}", [], 422);
    }
}

// Date format validation (YYYY-MM-DD)
foreach (['calibration_date', 'next_due_date'] as $dateField) {
    $dateVal = $data[$dateField] ?? '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateVal) || !strtotime($dateVal)) {
        jsonResponse(false, "Invalid date format for {$dateField}. Expected YYYY-MM-DD.", [], 422);
    }
}

$db = getDB();

// ── Resolve instrument_type_id ───────────────────────────────
$stmt = $db->prepare("SELECT id, label FROM instrument_types WHERE slug = ? LIMIT 1");
$stmt->execute([clean($data['instrument_type'])]);
$instrType = $stmt->fetch();
if (!$instrType) {
    jsonResponse(false, 'Unknown instrument type: ' . $data['instrument_type']);
}

// ── Check if this is an Update or Insert ─────────────────────
$certId = !empty($data['cert_id']) ? (int)$data['cert_id'] : null;
$isUpdate = false;
$existingCert = null;

if ($certId) {
    $stmt = $db->prepare("SELECT * FROM certificates WHERE id = ?");
    $stmt->execute([$certId]);
    $existingCert = $stmt->fetch();
    if ($existingCert) {
        $isUpdate = true;
        $certNumber = $existingCert['cert_number'];
    }
}

if (!$isUpdate) {
    // ── Auto-generate cert number per instrument type (Monthly sequence format) ────────────
    try {
        $db->beginTransaction();
        $stmt = $db->prepare("SELECT id, prefix, current_no, current_year, current_month FROM certificate_counter WHERE instrument_type_id = ? FOR UPDATE");
        $stmt->execute([$instrType['id']]);
        $counter = $stmt->fetch();
        
        $currentYear  = (int) date('Y');
        $currentMonth = (int) date('n');
        $yy = date('y');
        $mm = str_pad($currentMonth, 2, '0', STR_PAD_LEFT);
        
        if (!$counter) {
            $prefix = strtoupper(substr($data['instrument_type'], 0, 3));
            $newNo  = 1;
            $db->prepare("INSERT INTO certificate_counter (instrument_type_id, prefix, current_no, current_year, current_month) VALUES (?, ?, ?, ?, ?)")
               ->execute([$instrType['id'], $prefix, $newNo, $currentYear, $currentMonth]);
        } else {
            $prefix = $counter['prefix'];
            if ((int)$counter['current_year'] === $currentYear && (int)$counter['current_month'] === $currentMonth) {
                $newNo = (int)$counter['current_no'] + 1;
            } else {
                $newNo = 1; // reset for new month/year
            }
            $db->prepare("UPDATE certificate_counter SET current_no = ?, current_year = ?, current_month = ? WHERE instrument_type_id = ?")
               ->execute([$newNo, $currentYear, $currentMonth, $instrType['id']]);
        }
        $certNumber = $prefix . '-' . $yy . $mm . str_pad($newNo, 2, '0', STR_PAD_LEFT);
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        error_log('Certificate counter error: ' . $e->getMessage());
        jsonResponse(false, 'Could not generate certificate number. Please try again.', [], 500);
    }
}

// Override if user supplied a custom cert number
if (!empty($data['cert_number'])) {
    $certNumber = clean($data['cert_number']);
}

// ── Resolve party_id ─────────────────────────────────────────
$partyName = clean($data['party_name']);
$partyId   = null;

$stmt = $db->prepare("SELECT id FROM parties WHERE LOWER(name) = LOWER(?) LIMIT 1");
$stmt->execute([$partyName]);
$party = $stmt->fetch();
if ($party) {
    $partyId = $party['id'];
} else {
    $db->prepare("INSERT INTO parties (name, address, phone) VALUES (?,?,?)")
       ->execute([$partyName, clean($data['site_location'] ?? ''), '']);
    $partyId = (int) $db->lastInsertId();
}

// ── Upload PDF to Cloudinary ──────────────────────────────────
$pdfUrl      = null;
$pdfPublicId = null;

$instrLabel = $instrType['label'] ?? 'Other Instruments';
$siteLocation = clean($data['site_location'] ?? '');

if (!function_exists('sanitizeCloudinarySegment')) {
    function sanitizeCloudinarySegment(string $name): string {
        $clean = preg_replace('/[\\\\:*?"<>|]/', '_', $name);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean, " \t\n\r\0\x0B.");
        return $clean !== '' ? $clean : 'Unknown';
    }
}

$cloudinaryFolder = 'shreeji_certificates/' . sanitizeCloudinarySegment($instrLabel) . '/' . sanitizeCloudinarySegment($partyName);
if (!empty($siteLocation)) {
    $cloudinaryFolder .= '/' . sanitizeCloudinarySegment($siteLocation);
}

define('PDF_MAX_BYTES', 50 * 1024 * 1024); // 50 MB

if (!empty($_FILES['pdf_file']['tmp_name'])) {
    // Validate MIME type and size for multipart upload
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($_FILES['pdf_file']['tmp_name']);
    if ($mimeType !== 'application/pdf') {
        jsonResponse(false, 'Only PDF files are allowed.', [], 422);
    }
    if ($_FILES['pdf_file']['size'] > PDF_MAX_BYTES) {
        jsonResponse(false, 'PDF file exceeds the 50 MB size limit.', [], 422);
    }
    $tmpPath = $_FILES['pdf_file']['tmp_name'];
    try {
        $result      = cloudinaryUpload($tmpPath, $certNumber, false, $cloudinaryFolder);
        $pdfUrl      = $result['url'];
        $pdfPublicId = $result['public_id'];
    } catch (Exception $e) {
        error_log('Cloudinary upload error: ' . $e->getMessage());
    }
} elseif (!empty($data['pdf_base64'])) {
    // Base64 encoded PDF from JS — validate before decoding
    $b64Stripped = preg_replace('#^data:[^;]+;base64,#', '', $data['pdf_base64']);
    if (!base64_decode($b64Stripped, true)) {
        jsonResponse(false, 'Invalid PDF data.', [], 422);
    }
    $raw = base64_decode($b64Stripped);
    if (strlen($raw) > PDF_MAX_BYTES) {
        jsonResponse(false, 'PDF file exceeds the 50 MB size limit.', [], 422);
    }
    // Quick PDF magic-bytes check (%PDF-)
    if (substr($raw, 0, 5) !== '%PDF-') {
        jsonResponse(false, 'Only PDF files are allowed.', [], 422);
    }
    try {
        $result      = cloudinaryUpload($raw, $certNumber, true, $cloudinaryFolder);
        $pdfUrl      = $result['url'];
        $pdfPublicId = $result['public_id'];
    } catch (Exception $e) {
        error_log('Cloudinary upload error: ' . $e->getMessage());
    }
}

// Serialize form_data to JSON string
$formData = !empty($data['form_data']) ? (is_array($data['form_data']) ? json_encode($data['form_data']) : $data['form_data']) : null;

// ── Insert or Update certificate ──────────────────────────────
if ($isUpdate) {
    try {
        $sql = "UPDATE certificates SET
                  party_id = ?, party_name = ?, site_location = ?,
                  calibration_date = ?, next_due_date = ?, make = ?, model_no = ?, serial_no = ?,
                  capacity = ?, size_val = ?, quantity = ?, operated_type = ?, ring_type = ?,
                  form_data = ?" . 
                  ($pdfUrl ? ", pdf_public_id = ?, pdf_url = ?" : "") . "
                WHERE id = ?";
        
        $params = [
            $partyId,
            $partyName,
            clean($data['site_location']   ?? ''),
            $data['calibration_date'],
            $data['next_due_date'],
            clean($data['make']            ?? ''),
            clean($data['model_no']        ?? ''),
            clean($data['serial_no']       ?? ''),
            clean($data['capacity']        ?? ''),
            clean($data['size_val']        ?? ''),
            (int)($data['quantity']        ?? 0),
            clean($data['operated_type']   ?? ''),
            clean($data['ring_type']       ?? ''),
            $formData
        ];
        
        if ($pdfUrl) {
            $params[] = $pdfPublicId;
            $params[] = $pdfUrl;
        }
        $params[] = $certId;
        
        $db->prepare($sql)->execute($params);
        logCertificateAudit($db, $certId, 'update', $_SESSION['user_id'] ?? null, is_array($existingCert) ? $existingCert : null);

        // Recreate CTM / Mould relations
        $db->prepare("DELETE FROM ctm_readings WHERE certificate_id = ?")->execute([$certId]);
        $db->prepare("DELETE FROM cube_serials WHERE certificate_id = ?")->execute([$certId]);
    } catch (PDOException $e) {
        error_log('Certificate update error: ' . $e->getMessage());
        jsonResponse(false, 'Failed to update certificate. Please try again.', [], 500);
    }
} else {
    try {
        $sql = "INSERT INTO certificates
                  (cert_number, instrument_type_id, party_id, party_name, site_location,
                   calibration_date, next_due_date, make, model_no, serial_no,
                   capacity, size_val, quantity, operated_type, ring_type,
                   pdf_public_id, pdf_url, created_by, form_data)
                VALUES (?,?,?,?,?, ?,?,?,?,?, ?,?,?,?,?, ?,?,?,?)";

        $db->prepare($sql)->execute([
            $certNumber,
            $instrType['id'],
            $partyId,
            $partyName,
            clean($data['site_location']   ?? ''),
            $data['calibration_date'],
            $data['next_due_date'],
            clean($data['make']            ?? ''),
            clean($data['model_no']        ?? ''),
            clean($data['serial_no']       ?? ''),
            clean($data['capacity']        ?? ''),
            clean($data['size_val']        ?? ''),
            (int)($data['quantity']        ?? 0),
            clean($data['operated_type']   ?? ''),
            clean($data['ring_type']       ?? ''),
            $pdfPublicId,
            $pdfUrl,
            $_SESSION['user_id']           ?? null,
            $formData
        ]);
        $certId = (int) $db->lastInsertId();
        logCertificateAudit($db, $certId, 'create', $_SESSION['user_id'] ?? null, null);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            jsonResponse(false, 'Certificate number already exists: ' . $certNumber, [], 409);
        }
        error_log('Certificate insert error: ' . $e->getMessage());
        jsonResponse(false, 'Failed to save certificate. Please try again.', [], 500);
    }
}

// ── CTM readings ─────────────────────────────────────────────
if (!empty($data['ctm_readings']) && is_array($data['ctm_readings'])) {
    $ins = $db->prepare("INSERT INTO ctm_readings
        (certificate_id, ring_type, load_kn, deflection,
         reading_set1, reading_set2, reading_set3, average_kn)
        VALUES (?,?,?,?,?,?,?,?)");

    foreach ($data['ctm_readings'] as $r) {
        $ins->execute([
            $certId,
            clean($r['ring_type']     ?? ''),
            (int)($r['load_kn']       ?? 0),
            (float)($r['deflection']  ?? 0),
            (float)($r['set1']        ?? 0),
            (float)($r['set2']        ?? 0),
            (float)($r['set3']        ?? 0),
            (float)($r['average']     ?? 0),
        ]);
    }
}

// ── Cube serials ─────────────────────────────────────────────
if (!empty($data['cube_serials']) && is_array($data['cube_serials'])) {
    $ins = $db->prepare("INSERT INTO cube_serials (certificate_id, sr_no, serial_no) VALUES (?,?,?)");
    foreach ($data['cube_serials'] as $i => $serial) {
        $ins->execute([$certId, $i + 1, clean($serial)]);
    }
}

jsonResponse(true, 'Certificate saved successfully', [
    'cert_id'     => $certId,
    'cert_number' => $certNumber,
    'pdf_url'     => $pdfUrl,
]);