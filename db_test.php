<?php
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/plain');

try {
    $db = getDB();
    
    // Query instrument types and their counters
    $query = "
        SELECT 
            it.id, 
            it.slug, 
            it.label, 
            cc.prefix, 
            cc.current_no, 
            cc.current_year, 
            cc.current_month 
        FROM instrument_types it
        LEFT JOIN certificate_counter cc ON it.id = cc.instrument_type_id
        ORDER BY it.id ASC
    ";
    
    $stmt = $db->query($query);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        printf(
            "ID: %2d | Slug: %-20s | Prefix: %-5s | CurrentNo: %3d | Label: %s\n",
            $row['id'],
            $row['slug'],
            $row['prefix'] ?? 'NULL',
            $row['current_no'] ?? 0,
            $row['label']
        );
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
