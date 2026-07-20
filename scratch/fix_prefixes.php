<?php
// ============================================================
//  Database Fix Utility: Align Instrument Prefixes
//  Run by visiting: http://localhost/calibration(v3)/scratch/fix_prefixes.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
$db = getDB();

$expected = [
  'autolevel'        => 'AL',
  'aggregate_impact' => 'AI',
  'ctm'              => 'CTM',
  'cone_penetro'     => 'CP',
  'core_cutter'      => 'CC',
  'cube_mould'       => 'CM',
  'digital_thermo'   => 'DT',
  'elongation'       => 'EG',
  'oven'             => 'HO',
  'flakness'         => 'FG',
  'general'          => 'GEN',
  'hydrometer'       => 'HY',
  'isi_cube'         => 'ICM',
  'measuring_cyl'    => 'MC',
  'pycnometer'       => 'PC',
  'ph_meter'         => 'PH',
  'rapid_moisture'   => 'RM',
  'sieves'           => 'SA',
  'sand_pouring'     => 'SPC',
  'slumcone'         => 'SC',
  'total_station'    => 'TS',
  'water_bath'       => 'WBT',
  'vernier_caliper'  => 'VC',
  'weight_balance'   => 'WB',
  'weigh_batcher'    => 'VBC',
  'full_lab'         => 'FL'
];

echo "<h2>Aligning Database Certificate Prefixes</h2>";

try {
    foreach ($expected as $slug => $prefix) {
        // 1. Get instrument type ID
        $stmt = $db->prepare("SELECT id FROM instrument_types WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $instr = $stmt->fetch();
        
        if (!$instr) {
            echo "Skipping unknown slug in database: <strong>$slug</strong><br>";
            continue;
        }
        
        $instrId = $instr['id'];
        
        // 2. Fetch current prefix row in certificate_counter
        $stmt = $db->prepare("SELECT prefix FROM certificate_counter WHERE instrument_type_id = ?");
        $stmt->execute([$instrId]);
        $counter = $stmt->fetch();
        
        if ($counter) {
            if ($counter['prefix'] !== $prefix) {
                // Update prefix
                $stmt = $db->prepare("UPDATE certificate_counter SET prefix = ? WHERE instrument_type_id = ?");
                $stmt->execute([$prefix, $instrId]);
                echo "Updated prefix for <strong>$slug</strong>: <code>{$counter['prefix']}</code> &rarr; <code>$prefix</code><br>";
            } else {
                echo "Prefix for <strong>$slug</strong> is already correct: <code>$prefix</code><br>";
            }
        } else {
            // Insert row if missing
            $stmt = $db->prepare("INSERT INTO certificate_counter (instrument_type_id, prefix, current_no, current_year, current_month) VALUES (?, ?, 0, ?, ?)");
            $stmt->execute([$instrId, $prefix, date('Y'), date('n')]);
            echo "Created counter for <strong>$slug</strong> with prefix <code>$prefix</code><br>";
        }
    }
    echo "<h3>All prefixes successfully aligned!</h3>";
} catch (Exception $e) {
    echo "<h3 style='color:red;'>Error running update: " . $e->getMessage() . "</h3>";
}
