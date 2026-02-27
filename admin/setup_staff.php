<?php
require_once '../user/includes/db.php';

// Check existing admin/staff users
$stmt = $pdo->query("SELECT id, username, full_name, role FROM users WHERE role IN ('admin', 'superadmin', 'staff') ORDER BY role");
$admins = $stmt->fetchAll();

echo "<h2>Current Admin/Staff Users:</h2><ul>";
foreach ($admins as $u) {
    echo "<li><strong>{$u['username']}</strong> ({$u['role']}) — {$u['full_name']}</li>";
}
echo "</ul>";

// Create staff user if missing
$check = $pdo->prepare("SELECT id FROM users WHERE username = 'staff'");
$check->execute();
if (!$check->fetch()) {
    $hash = password_hash('staff123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, email, full_name, password_hash, role) VALUES (?, ?, ?, ?, ?)")
        ->execute(['staff', 'staff@tunetrove.com', 'Store Staff', $hash, 'staff']);
    echo "<p style='color:green'><strong>✅ Staff user created!</strong> Username: <code>staff</code></p>";
} else {
    echo "<p style='color:blue'><strong>ℹ️ Staff user already exists.</strong></p>";
}

echo "<p><a href='/TuneTrove/admin/login.php'>→ Go to Admin Login</a></p>";
?>
