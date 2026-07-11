<?php
require_once __DIR__ . '/includes/config.php';
$pdo = getDB();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPhone = clean($_POST['phone']);
    $newPass = $_POST['password'];

    if (!empty($newPhone) && !empty($newPass)) {
        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        
        try {
            // Update the admin user (user ID 1)
            $stmt = $pdo->prepare("UPDATE users SET phone = ?, password_hash = ? WHERE id = 1");
            $stmt->execute([$newPhone, $hash]);
            $message = "<div style='color:green;'>Success! Your login credentials have been updated. <b>Delete this file immediately.</b></div>";
        } catch (Exception $e) {
            $message = "<div style='color:red;'>Error updating database: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div style='color:red;'>Please fill in both fields.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Admin Credentials</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        input { display: block; width: 100%; margin-bottom: 15px; padding: 10px; box-sizing: border-box; }
        button { padding: 10px 20px; background: #004d40; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Change Admin Login</h2>
    <?= $message ?>
    <form method="POST">
        <label>New Login ID (Phone):</label>
        <input type="text" name="phone" required placeholder="Enter new ID">
        
        <label>New Password:</label>
        <input type="password" name="password" required placeholder="Enter new password">
        
        <button type="submit">Update Credentials</button>
    </form>
</body>
</html>
