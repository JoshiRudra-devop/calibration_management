<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/master_instruments.php';
requireLogin();

$db = getDB();
$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $mastersData = $_POST['masters'] ?? [];
    if (is_array($mastersData) && !empty($mastersData)) {
        $stmt = $db->prepare("UPDATE `master_instruments` SET 
            `name` = ?, 
            `serial_no` = ?, 
            `range_capacity` = ?, 
            `least_count` = ?, 
            `calib_date` = ?, 
            `due_date` = ?, 
            `cert_no` = ?, 
            `calibrated_by` = ?, 
            `traceability` = ? 
            WHERE `slug` = ?");
            
        foreach ($mastersData as $slug => $fields) {
            $stmt->execute([
                clean($fields['name'] ?? ''),
                clean($fields['serial_no'] ?? ''),
                clean($fields['range_capacity'] ?? ''),
                clean($fields['least_count'] ?? ''),
                clean($fields['calib_date'] ?? ''),
                clean($fields['due_date'] ?? ''),
                clean($fields['cert_no'] ?? ''),
                clean($fields['calibrated_by'] ?? ''),
                clean($fields['traceability'] ?? ''),
                $slug
            ]);
        }
        $message = 'Master Instrument Calibration details updated successfully! All certificates will now use these updated details.';
    }
}

$masters = getMasterInstruments();
$pageTitle  = 'Master Instruments Manager - Calibration Management System';
$activePage = 'master_instruments';
include __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 1100px; margin: 30px auto; padding: 0 20px;">
    <div style="background: linear-gradient(135deg, #00796b 0%, #004d40 100%); color: white; padding: 24px 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,121,107,0.2); display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 style="margin: 0 0 6px 0; font-size: 1.6rem; font-weight: 700; color: white;">🛠️ Master Instruments Manager</h1>
            <p style="margin: 0; opacity: 0.9; font-size: 0.92rem;">Update annual calibration dates, certificate numbers, and traceability details once. All certificates will automatically use these updated details.</p>
        </div>
        <a href="<?= APP_URL ?>/dashboard.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); font-weight: 600; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px;">← Back to Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div style="background-color: #e8f5e9; color: #2e7d32; border: 1.5px solid #a5d6a7; padding: 14px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: 600; font-size: 0.95rem;">
            ✓ <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div style="display: flex; flex-direction: column; gap: 25px;">
            <?php foreach ($masters as $slug => $inst): ?>
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; color: #00796b; font-size: 1.15rem; font-weight: 700; text-transform: uppercase;">
                            <?= htmlspecialchars($inst['name']) ?>
                        </h3>
                        <span style="font-size: 11px; background: #e0f2f1; color: #004d40; padding: 4px 10px; border-radius: 12px; font-weight: 700; letter-spacing: 0.5px;">KEY: <?= htmlspecialchars($slug) ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Instrument Name:</label>
                            <input type="text" name="masters[<?= $slug ?>][name]" value="<?= htmlspecialchars($inst['name']) ?>" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div>
                            <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Serial No / Model:</label>
                            <input type="text" name="masters[<?= $slug ?>][serial_no]" value="<?= htmlspecialchars($inst['serial_no']) ?>" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div>
                            <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Range / Capacity:</label>
                            <input type="text" name="masters[<?= $slug ?>][range_capacity]" value="<?= htmlspecialchars($inst['range_capacity']) ?>" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div>
                            <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Least Count / Accuracy:</label>
                            <input type="text" name="masters[<?= $slug ?>][least_count]" value="<?= htmlspecialchars($inst['least_count']) ?>" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div>
                            <label style="font-weight: 600; font-size: 13px; color: #d97706; margin-bottom: 4px; display: block;">📅 Calibration Date (DD/MM/YYYY):</label>
                            <input type="text" name="masters[<?= $slug ?>][calib_date]" value="<?= htmlspecialchars($inst['calib_date']) ?>" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #f59e0b; border-radius: 6px; font-size: 13px; font-weight: 600;">
                        </div>

                        <div>
                            <label style="font-weight: 600; font-size: 13px; color: #dc2626; margin-bottom: 4px; display: block;">📅 Next Due Date (DD/MM/YYYY):</label>
                            <input type="text" name="masters[<?= $slug ?>][due_date]" value="<?= htmlspecialchars($inst['due_date']) ?>" required style="width: 100%; padding: 9px 12px; border: 1.5px solid #ef4444; border-radius: 6px; font-size: 13px; font-weight: 600;">
                        </div>

                        <div>
                            <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Certificate No:</label>
                            <input type="text" name="masters[<?= $slug ?>][cert_no]" value="<?= htmlspecialchars($inst['cert_no']) ?>" required style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>

                        <div>
                            <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Calibrated By / Lab:</label>
                            <input type="text" name="masters[<?= $slug ?>][calibrated_by]" value="<?= htmlspecialchars($inst['calibrated_by']) ?>" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;">
                        </div>
                    </div>

                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 4px; display: block;">Traceability Statement:</label>
                        <textarea name="masters[<?= $slug ?>][traceability]" rows="2" style="width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: inherit; resize: vertical;"><?= htmlspecialchars($inst['traceability']) ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 30px; text-align: center; position: sticky; bottom: 20px; background: rgba(255,255,255,0.9); padding: 15px; border-radius: 12px; box-shadow: 0 -4px 15px rgba(0,0,0,0.08); backdrop-filter: blur(8px); border: 1px solid #cbd5e1;">
            <button type="submit" style="padding: 12px 35px; background: #00796b; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(0,121,107,0.3); transition: all 0.2s;">
                💾 Save All Master Instrument Details
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
