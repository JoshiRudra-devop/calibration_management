<?php
// ============================================================
//  Cloudinary Helper
// ============================================================
require_once __DIR__ . '/config.php';

/**
 * Upload a PDF file (local path or raw bytes) to Cloudinary.
 *
 * @param  string  $filePathOrData  Path to local file OR raw binary data
 * @param  string  $publicId        e.g.  "certificates/SI-1234"
 * @param  bool    $isRawData       true = $filePathOrData contains raw bytes
 * @return array   ['url'=>string, 'public_id'=>string] on success
 * @throws RuntimeException on failure
 */
function cloudinaryUpload(string $filePathOrData, string $publicId, bool $isRawData = false, string $folderPath = 'shreeji_certificates'): array
{
    $timestamp = time();
    $folder    = $folderPath;

    // Build signature
    $params = [
        'folder'    => $folder,
        'public_id' => $publicId,
        'timestamp' => $timestamp,
    ];
    ksort($params);
    $sigParts = [];
    foreach ($params as $key => $value) {
        $sigParts[] = "$key=$value";
    }
    $sigString = implode('&', $sigParts) . CLOUDINARY_API_SECRET;
    $signature = sha1($sigString);

    // Prepare multipart body
    $boundary = '----CloudinaryBoundary' . uniqid();
    $body     = '';

    $fields = array_merge($params, [
        'api_key'   => CLOUDINARY_API_KEY,
        'signature' => $signature,
    ]);

    foreach ($fields as $key => $value) {
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"{$key}\"\r\n\r\n";
        $body .= "{$value}\r\n";
    }

    // File part
    if ($isRawData) {
        $fileContent = $filePathOrData;
        $filename    = $publicId . '.pdf';
    } else {
        $fileContent = file_get_contents($filePathOrData);
        $filename    = basename($filePathOrData);
    }

    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$filename}\"\r\n";
    $body .= "Content-Type: application/pdf\r\n\r\n";
    $body .= $fileContent . "\r\n";
    $body .= "--{$boundary}--\r\n";

    $url = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD_NAME . '/auto/upload';

    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: multipart/form-data; boundary={$boundary}\r\n" .
                         "Content-Length: " . strlen($body) . "\r\n",
            'content' => $body,
            'timeout' => 60,
        ],
    ];

    $response = file_get_contents($url, false, stream_context_create($opts));

    if ($response === false) {
        // Fallback: try cURL
        $response = cloudinaryCurl($url, $fields, $fileContent, $filename);
    }

    $result = json_decode($response, true);

    if (empty($result['secure_url'])) {
        throw new RuntimeException('Cloudinary upload failed: ' . ($result['error']['message'] ?? $response));
    }

    return [
        'url'       => $result['secure_url'],
        'public_id' => $result['public_id'],
    ];
}

function cloudinaryCurl(string $url, array $fields, string $fileContent, string $filename): string
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('cURL not available');
    }

    $tmpFile = tempnam(sys_get_temp_dir(), 'cld_') . '.pdf';
    file_put_contents($tmpFile, $fileContent);

    $postFields = $fields;
    $postFields['file'] = new CURLFile($tmpFile, 'application/pdf', $filename);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    unlink($tmpFile);

    return $response;
}

/**
 * Delete an asset from Cloudinary by public_id.
 */
function cloudinaryDelete(string $publicId): bool
{
    $timestamp = time();
    $sigString = "public_id={$publicId}&timestamp={$timestamp}" . CLOUDINARY_API_SECRET;
    $signature = sha1($sigString);

    $url  = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD_NAME . '/image/destroy';
    $data = http_build_query([
        'public_id' => $publicId,
        'timestamp' => $timestamp,
        'api_key'   => CLOUDINARY_API_KEY,
        'signature' => $signature,
    ]);

    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $data,
            'timeout' => 30,
        ],
    ];

    $response = @file_get_contents($url, false, stream_context_create($opts));
    $result   = $response ? json_decode($response, true) : [];
    return ($result['result'] ?? '') === 'ok';
}