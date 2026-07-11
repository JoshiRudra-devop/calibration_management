<?php
// ============================================================
//  API: Get Parties Autocomplete (Trial)
//  GET /api/get_parties_try.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Rate limit — 120 requests/IP/min prevents bulk enumeration / scraping
rateLimitCheck('data_' . ($_SERVER['REMOTE_ADDR'] ?? '0'), 120, 60);

header('Content-Type: application/json');

$db = getDB();

try {
    // 1. Fetch unique combinations from certificates table
    $stmt = $db->query("
        SELECT DISTINCT party_name, site_location 
        FROM certificates 
        WHERE party_name != '' 
        ORDER BY party_name ASC, site_location ASC
    ");
    $certs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Fetch unique combinations from parties table
    $stmt2 = $db->query("
        SELECT DISTINCT name AS party_name, address AS site_location 
        FROM parties 
        WHERE name != '' 
        ORDER BY name ASC
    ");
    $parties = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Merge and deduplicate
    $result = [];
    $seen = [];
    
    foreach (array_merge($parties, $certs) as $row) {
        $name = trim($row['party_name']);
        $location = trim($row['site_location'] ?? '');
        $key = strtolower($name) . '||' . strtolower($location);
        
        if (!isset($seen[$key]) && !empty($name)) {
            $seen[$key] = true;
            $result[] = [
                'name' => $name,
                'site_location' => $location
            ];
        }
    }
    
    // Sort array by name
    usort($result, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    jsonResponse(true, 'Parties retrieved successfully', ['parties' => $result]);
} catch (Exception $e) {
    error_log('get_parties_try error: ' . $e->getMessage());
    jsonResponse(false, 'Database error. Please contact support.', [], 500);
}
