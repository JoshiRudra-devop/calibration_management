<?php
/**
 * SETUP SCRIPT - Initialize database and admin user
 * Visit: http://localhost/shreeji%20instruments/calibration%20certificate/setup.php
 */

require_once __DIR__ . '/includes/config.php';

echo "<!DOCTYPE html>
<html>
<head>
  <title>Setup - Calibration Management System</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 600px; margin: 2rem auto; padding: 1rem; }
    .success { color: #22b55d; padding: 1rem; background: #f0fdf4; border-radius: 8px; margin: 1rem 0; }
    .error { color: #dc2626; padding: 1rem; background: #fef2f2; border-radius: 8px; margin: 1rem 0; }
    .info { color: #0369a1; padding: 1rem; background: #f0f9ff; border-radius: 8px; margin: 1rem 0; }
    table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
    th { background: #f3f4f6; font-weight: 600; }
  </style>
</head>
<body>
  <h1>🔧 Setup & Diagnostics</h1>";

$db = getDB();

// Test database connection
echo "<h2>1️⃣ Database Connection</h2>";
try {
    $result = $db->query("SELECT 1");
    echo "<div class='success'>✅ Database connected</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Database error: " . $e->getMessage() . "</div>";
    exit;
}

// Check if users table exists
echo "<h2>2️⃣ Tables</h2>";
try {
    $tables = ['users', 'certificates', 'parties', 'instrument_types', 'certificate_counter'];
    $result = $db->query("SHOW TABLES");
    $existingTables = $result->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        if (in_array($table, $existingTables)) {
            echo "<div class='success'>✅ Table '$table' exists</div>";
        } else {
            echo "<div class='error'>❌ Table '$table' missing</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>Error checking tables: " . $e->getMessage() . "</div>";
}

// Check and fix certificates.form_data column and prefixes
echo "<h2>2️⃣.5️⃣ Schema Updates (Missing Columns, Prefixes, Slugs)</h2>";
try {
    // 1. form_data column
    $stmt = $db->query("SHOW COLUMNS FROM certificates LIKE 'form_data'");
    $column = $stmt->fetch();
    if ($column) {
        echo "<div class='success'>✅ Column 'form_data' exists in 'certificates' table</div>";
    } else {
        echo "<div class='error'>❌ Column 'form_data' missing in 'certificates' table - adding it...</div>";
        $db->exec("ALTER TABLE certificates ADD COLUMN form_data LONGTEXT NULL AFTER updated_at");
        echo "<div class='success'>✅ Column 'form_data' added successfully</div>";
    }

    // 2. Normalize full-lab slug to full_lab
    $stmt = $db->prepare("SELECT COUNT(*) FROM instrument_types WHERE slug = 'full-lab'");
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        echo "<div class='error'>❌ Found old slug 'full-lab' in 'instrument_types' table - updating to 'full_lab'...</div>";
        $db->exec("UPDATE instrument_types SET slug = 'full_lab' WHERE slug = 'full-lab'");
        echo "<div class='success'>✅ Slug 'full-lab' updated to 'full_lab' successfully</div>";
    } else {
        echo "<div class='success'>✅ Slug 'full_lab' is correct in 'instrument_types'</div>";
    }

    // 3. Update certificate counter prefixes
    $prefixesToUpdate = [
        9  => 'HO',
        13 => 'CM',
        14 => 'MC',
        15 => 'PC',
        18 => 'SA',
        21 => 'TS',
        25 => 'VBC'
    ];
    foreach ($prefixesToUpdate as $instrId => $newPrefix) {
        $stmt = $db->prepare("SELECT prefix FROM certificate_counter WHERE instrument_type_id = ?");
        $stmt->execute([$instrId]);
        $currPrefix = $stmt->fetchColumn();
        if ($currPrefix === false) {
            echo "<div class='error'>❌ Counter missing for instrument ID $instrId - inserting prefix '$newPrefix'...</div>";
            $db->prepare("INSERT INTO certificate_counter (instrument_type_id, prefix, current_no, current_year, current_month) VALUES (?, ?, 0, ?, ?)")
               ->execute([$instrId, $newPrefix, date('Y'), date('n')]);
            echo "<div class='success'>✅ Counter inserted with prefix '$newPrefix'</div>";
        } elseif ($currPrefix !== $newPrefix) {
            echo "<div class='error'>❌ Prefix for instrument ID $instrId is '$currPrefix' - updating to '$newPrefix'...</div>";
            $db->prepare("UPDATE certificate_counter SET prefix = ? WHERE instrument_type_id = ?")->execute([$newPrefix, $instrId]);
            echo "<div class='success'>✅ Prefix updated to '$newPrefix' successfully</div>";
        } else {
            echo "<div class='success'>✅ Prefix for instrument ID $instrId is correct ('$newPrefix')</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>Error performing schema updates: " . $e->getMessage() . "</div>";
}

// Check admin user
echo "<h2>3️⃣ Admin User</h2>";
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE phone = '9999999999' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<div class='success'>✅ Admin user exists</div>";
        echo "<table>
          <tr><th>Field</th><th>Value</th></tr>
          <tr><td>ID</td><td>{$user['id']}</td></tr>
          <tr><td>Name</td><td>{$user['name']}</td></tr>
          <tr><td>Phone</td><td>{$user['phone']}</td></tr>
          <tr><td>Email</td><td>{$user['email']}</td></tr>
          <tr><td>Role</td><td>{$user['role']}</td></tr>
          <tr><td>Active</td><td>" . ($user['active'] ? 'Yes' : 'No') . "</td></tr>
        </table>";
        
        // Test password
        $testPassword = 'admin123';
        $hashMatches = password_verify($testPassword, $user['password_hash']);
        if ($hashMatches) {
            echo "<div class='success'>✅ Password 'admin123' is correct</div>";
        } else {
            echo "<div class='error'>❌ Password hash does not match 'admin123' - updating...</div>";
            $newHash = password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $user['id']]);
            echo "<div class='success'>✅ Password hash updated to match 'admin123'</div>";
        }
    } else {
        echo "<div class='error'>❌ Admin user not found</div>";
        echo "<p><strong>Creating admin user...</strong></p>";
        
        // Create admin user
        $name = 'Admin';
        $phone = '9999999999';
        $email = 'admin@shreejiinstruments.com';
        $password = 'admin123';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $role = 'admin';
        
        $stmt = $db->prepare("
            INSERT INTO users (name, phone, email, password_hash, role, active)
            VALUES (?, ?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE 
                name = VALUES(name),
                password_hash = VALUES(password_hash),
                active = 1
        ");
        
        if ($stmt->execute([$name, $phone, $email, $passwordHash, $role])) {
            echo "<div class='success'>✅ Admin user created/updated successfully</div>";
            echo "<div class='info'><strong>Login Credentials:</strong><br>Phone: 9999999999<br>Password: admin123</div>";
        } else {
            echo "<div class='error'>❌ Failed to create admin user: " . implode(", ", $stmt->errorInfo()) . "</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
}

// Test login flow
echo "<h2>4️⃣ Test Login</h2>";
try {
    $testPhone = '9999999999';
    $testPassword = 'admin123';
    
    $stmt = $db->prepare("SELECT * FROM users WHERE phone = ? AND active = 1 LIMIT 1");
    $stmt->execute([$testPhone]);
    $testUser = $stmt->fetch();
    
    if ($testUser && password_verify($testPassword, $testUser['password_hash'])) {
        echo "<div class='success'>✅ Login simulation successful! You can now login with:<br>
          <strong>Phone:</strong> 9999999999<br>
          <strong>Password:</strong> admin123</div>";
    } else {
        echo "<div class='error'>❌ Login simulation failed</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>Test error: " . $e->getMessage() . "</div>";
}

echo "<hr>
<p><strong>🎯 Next Steps:</strong></p>
<ol>
  <li>If all checks are green ✅, try logging in at: <a href='" . APP_URL . "/login.php'>Login Page</a></li>
  <li>Use credentials: <strong>9999999999 / admin123</strong></li>
  <li>If issues persist, check browser console (F12) for errors</li>
  <li>You can delete this file after setup is complete</li>
</ol>
</body>
</html>";
