<?php
require_once __DIR__ . '/../user/includes/db.php';

try {
    // Recreate admin
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, email, role) VALUES (?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role)");
    $stmt->execute(['admin', 'admin123', 'Administrator', 'admin@tunetrove.com', 'admin']);
    
    // Recreate staff
    $stmt->execute(['staff', 'staff123', 'Staff Member', 'staff@tunetrove.com', 'staff']);
    
    echo "Administrative accounts restored successfully!\n";
    echo "Admin: admin / admin123\n";
    echo "Staff: staff / staff123\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
