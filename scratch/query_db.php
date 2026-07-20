<?php
require_once __DIR__ . '/../includes/config.php';
$db = getDB();

try {
    $stmt = $db->query("SELECT it.id, it.name, it.slug, cc.prefix FROM instrument_types it LEFT JOIN certificate_counter cc ON cc.instrument_type_id = it.id");
    $rows = $stmt->fetchAll();
    
    $out = "=== Database Prefixes ===\n";
    foreach ($rows as $row) {
        $out .= sprintf("ID: %d | Name: %s | Slug: %s | Prefix: %s\n", $row['id'], $row['name'], $row['slug'], $row['prefix'] ?? 'NULL');
    }
    
    file_put_contents(__DIR__ . '/db_result.txt', $out);
    echo "Saved to db_result.txt\n";
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/db_result.txt', "Error: " . $e->getMessage() . "\n");
    echo "Error written to db_result.txt\n";
}
