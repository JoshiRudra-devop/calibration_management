<?php
// ============================================================
// Centralized Master Instruments Management Module
// ============================================================

require_once __DIR__ . '/config.php';

function initMasterInstrumentsTable(): void {
    static $initialized = false;
    if ($initialized) return;

    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS `master_instruments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slug` VARCHAR(50) NOT NULL UNIQUE,
        `name` VARCHAR(150) NOT NULL,
        `serial_no` VARCHAR(100) NOT NULL,
        `range_capacity` VARCHAR(100) DEFAULT '',
        `least_count` VARCHAR(100) DEFAULT '',
        `calib_date` VARCHAR(20) NOT NULL,
        `due_date` VARCHAR(20) NOT NULL,
        `cert_no` VARCHAR(100) NOT NULL,
        `calibrated_by` VARCHAR(150) DEFAULT '',
        `traceability` TEXT DEFAULT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Check if table has data, if not seed default master instruments
    $count = (int)$db->query("SELECT COUNT(*) FROM `master_instruments`")->fetchColumn();
    if ($count === 0) {
        $defaults = [
            [
                'slug'           => 'digital_vernier_caliper',
                'name'           => 'DIGITAL CALIPER',
                'serial_no'      => 'Accu Plus / 13-200 (DC-01)',
                'range_capacity' => '0-200 mm',
                'least_count'    => '0.01 mm',
                'calib_date'     => '02/10/2025',
                'due_date'       => '01/10/2026',
                'cert_no'        => '1025/392/001',
                'calibrated_by'  => 'ACLPL, AHMEDABAD',
                'traceability'   => 'TRACEABLE TO NATIONAL STANDARDS THROUGH ARSH CALIBRATION LABORATORY PVT. LTD. (Cert No: 1025/392/001, ULR: CC312625000003463F)'
            ],
            [
                'slug'           => 'standard_weights',
                'name'           => 'STANDARD WEIGHTS',
                'serial_no'      => 'W-01 TO W-05',
                'range_capacity' => '500g - 10kg',
                'least_count'    => '0.001g',
                'calib_date'     => '02/10/2025',
                'due_date'       => '01/10/2026',
                'cert_no'        => '1025/392/003 TO 1025/392/007',
                'calibrated_by'  => 'ACLPL, AHMEDABAD',
                'traceability'   => 'TRACEABLE TO NATIONAL STANDARDS THROUGH ARSH CALIBRATION LABORATORY PVT. LTD. (Cert Nos: 1025/392/003-007)'
            ],
            [
                'slug'           => 'standard_thermometer',
                'name'           => 'DIGITAL THERMOMETER',
                'serial_no'      => 'Multi / DTM-01',
                'range_capacity' => '-50°C TO 300°C',
                'least_count'    => '0.1°C',
                'calib_date'     => '02/10/2025',
                'due_date'       => '01/10/2026',
                'cert_no'        => '1025/392/002',
                'calibrated_by'  => 'ACLPL, AHMEDABAD',
                'traceability'   => 'TRACEABLE TO NATIONAL STANDARDS THROUGH ARSH CALIBRATION LABORATORY PVT. LTD. (Cert No: 1025/392/002, ULR: CC312625000003464F)'
            ],
            [
                'slug'           => 'digital_thermo',
                'name'           => 'DIGITAL THERMOMETER',
                'serial_no'      => 'Multi / DTM-01',
                'range_capacity' => '-50°C TO 300°C',
                'least_count'    => '0.1°C',
                'calib_date'     => '02/10/2025',
                'due_date'       => '01/10/2026',
                'cert_no'        => '1025/392/002',
                'calibrated_by'  => 'ACLPL, AHMEDABAD',
                'traceability'   => 'TRACEABLE TO NATIONAL STANDARDS THROUGH ARSH CALIBRATION LABORATORY PVT. LTD. (Cert No: 1025/392/002, ULR: CC312625000003464F)'
            ],
            [
                'slug'           => 'proving_ring_load_cell',
                'name'           => 'MASTER PROVING RING / LOAD CELL',
                'serial_no'      => 'PR-500KN',
                'range_capacity' => '0-2000 KN',
                'least_count'    => '0.1 KN',
                'calib_date'     => '02/10/2025',
                'due_date'       => '01/10/2026',
                'cert_no'        => 'PR-302',
                'calibrated_by'  => 'ACLPL, AHMEDABAD',
                'traceability'   => 'TRACEABLE TO NATIONAL STANDARDS FOR FORCE CALIBRATION.'
            ],
            [
                'slug'           => 'slip_gauges',
                'name'           => 'STANDARD GAUGE BLOCKS (SLIP GAUGES)',
                'serial_no'      => '170037 SET',
                'range_capacity' => '0.5 - 100MM',
                'least_count'    => '0.001MM',
                'calib_date'     => '24/01/2025',
                'due_date'       => '24/01/2027',
                'cert_no'        => 'QSI/0054/25/01',
                'calibrated_by'  => 'QUALITY SYSTEMS & INSTRUMENTS',
                'traceability'   => 'TRACEABLE TO NATIONAL PHYSICAL LABORATORY (NPL) DIMENSIONAL STANDARDS (CC-2717).'
            ],
            [
                'slug'           => 'buffer_solutions',
                'name'           => 'STANDARD BUFFER SOLUTIONS',
                'serial_no'      => 'NIST-BUF-2026',
                'range_capacity' => 'pH 4.0, 7.0, 9.2',
                'least_count'    => '0.01 pH',
                'calib_date'     => '02/10/2025',
                'due_date'       => '01/10/2026',
                'cert_no'        => 'BUF-55',
                'calibrated_by'  => 'CERTIFIED REFERENCE MATERIAL LAB',
                'traceability'   => 'TRACEABLE TO NIST SECONDARY pH STANDARDS.'
            ],
            [
                'slug'           => 'optical_collimator',
                'name'           => 'OPTICAL COLLIMATOR BENCH',
                'serial_no'      => 'OC-401 System',
                'range_capacity' => '0 - 360 DEGREES',
                'least_count'    => '1 SECOND',
                'calib_date'     => '02/10/2025',
                'due_date'       => '01/10/2026',
                'cert_no'        => 'OC-889',
                'calibrated_by'  => 'ACLPL, AHMEDABAD',
                'traceability'   => 'TRACEABLE TO NATIONAL PHYSICAL LABORATORY (NPL) OPTICAL STANDARDS.'
            ]
        ];

        $stmt = $db->prepare("INSERT INTO `master_instruments` (`slug`, `name`, `serial_no`, `range_capacity`, `least_count`, `calib_date`, `due_date`, `cert_no`, `calibrated_by`, `traceability`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($defaults as $d) {
            $stmt->execute([
                $d['slug'], $d['name'], $d['serial_no'], $d['range_capacity'],
                $d['least_count'], $d['calib_date'], $d['due_date'], $d['cert_no'],
                $d['calibrated_by'], $d['traceability']
            ]);
        }
    }

    $initialized = true;
}

function getMasterInstruments(): array {
    initMasterInstrumentsTable();
    $db = getDB();
    $stmt = $db->query("SELECT * FROM `master_instruments` ORDER BY `id` ASC");
    $rows = $stmt->fetchAll();
    $dict = [];
    foreach ($rows as $r) {
        $dict[$r['slug']] = $r;
    }
    return $dict;
}

function getMasterInstrument(string $slug): ?array {
    initMasterInstrumentsTable();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM `master_instruments` WHERE `slug` = ? LIMIT 1");
    $stmt->execute([$slug]);
    $res = $stmt->fetch();
    return $res ?: null;
}

function checkMasterInstrumentsExpiration(int $daysThreshold = 60): array {
    initMasterInstrumentsTable();
    $all = getMasterInstruments();
    $expiring = [];
    $today = new DateTime();

    foreach ($all as $slug => $inst) {
        $dueDateStr = $inst['due_date'] ?? '';
        if (!$dueDateStr) continue;

        $dObj = null;
        if (str_contains($dueDateStr, '/')) {
            $parts = explode('/', $dueDateStr);
            if (count($parts) === 3) {
                $dObj = DateTime::createFromFormat('d/m/Y', $dueDateStr);
            }
        } else if (str_contains($dueDateStr, '-')) {
            $dObj = DateTime::createFromFormat('Y-m-d', $dueDateStr);
        }

        if ($dObj) {
            $dObj->setTime(23, 59, 59);
            $diff = $today->diff($dObj);
            $daysLeft = (int)$diff->format('%r%a');

            if ($daysLeft <= $daysThreshold) {
                $expiring[] = [
                    'slug'      => $slug,
                    'name'      => $inst['name'],
                    'due_date'  => $inst['due_date'],
                    'days_left' => $daysLeft,
                    'is_expired'=> ($daysLeft < 0)
                ];
            }
        }
    }
    return $expiring;
}
