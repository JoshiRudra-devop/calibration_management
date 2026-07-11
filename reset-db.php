<?php
/**
 * QUICK RESET - Reinitialize database tables and admin user
 * This file will reset the database schema and create a fresh admin user
 */

require_once __DIR__ . '/includes/config.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['confirm'] ?? '') !== 'yes') {
    echo "<!DOCTYPE html>
<html><head><title>Database Reset</title>
<style>body{font-family:Arial;max-width:600px;margin:2rem auto;padding:1rem}
.warning{background:#fef2f2;border:2px solid #dc2626;padding:1rem;border-radius:8px;color:#dc2626;margin:1rem 0}
.button{padding:0.75rem 1.5rem;background:#dc2626;color:white;border:none;border-radius:6px;cursor:pointer;font-size:1rem}
</style></head><body>
<h1>⚠️ Database Reset</h1>
<div class='warning'>
  <p><strong>This will:</strong></p>
  <ul>
    <li>Drop all existing tables</li>
    <li>Recreate all tables fresh</li>
    <li>Create admin user: 9999999999 / admin123</li>
  </ul>
</div>
<form method='POST'>
  <label><input type='checkbox' name='confirm' value='yes' required> I understand and want to reset</label><br><br>
  <button class='button' type='submit'>Reset Database Now</button>
</form>
</body></html>";
    exit;
}

$db = getDB();
$success = true;
$messages = [];

try {
    // Drop existing tables
    $tables = ['certificates', 'parties', 'certificate_counter', 'instrument_types', 'users'];
    foreach ($tables as $table) {
        try {
            $db->exec("DROP TABLE IF EXISTS $table");
            $messages[] = "✅ Dropped table: $table";
        } catch (Exception $e) {
            $messages[] = "⚠️  Table $table: " . $e->getMessage();
        }
    }
    
    // Read and execute database.sql
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $db->exec($sql);
    $messages[] = "✅ Database schema recreated";
    
    // Verify admin user
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE phone = '9999999999'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $messages[] = "✅ Admin user exists";
    } else {
        throw new Exception("Admin user not created");
    }
    
    // Test password
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE phone = '9999999999'");
    $stmt->execute();
    $hash = $stmt->fetchColumn();
    
    if (password_verify('admin123', $hash)) {
        $messages[] = "✅ Admin password verified (admin123)";
    } else {
        $messages[] = "⚠️  Password verification failed - regenerating...";
        $newHash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE phone = '9999999999'");
        $stmt->execute([$newHash]);
        $messages[] = "✅ Password regenerated";
    }
    
} catch (Exception $e) {
    $success = false;
    $messages[] = "❌ Error: " . $e->getMessage();
}

echo "<!DOCTYPE html>
<html><head><title>Reset Complete</title>
<style>body{font-family:Arial;max-width:600px;margin:2rem auto;padding:1rem}
.result{padding:1rem;border-radius:8px;margin:1rem 0}
.success{background:#f0fdf4;border:2px solid #22b55d;color:#22b55d}
.error{background:#fef2f2;border:2px solid #dc2626;color:#dc2626}
a{color:#0369a1;text-decoration:underline;margin-top:1rem;display:inline-block}
</style></head><body>
<h1>" . ($success ? "✅ Success!" : "❌ Failed") . "</h1>
<div class='result " . ($success ? "success" : "error") . "'>";

foreach ($messages as $msg) {
    echo "<p>$msg</p>";
}

echo "</div>
<p><strong>Next Steps:</strong></p>
<a href='" . APP_URL . "/login.php'>Go to Login</a><br>
<p><strong>Credentials:</strong><br>Phone: 9999999999<br>Password: admin123</p>
</body></html>";
