<?php
require_once 'includes/db.php';

// New users to create
$new_users = [
    ['username' => 'sarah_j', 'full_name' => 'Sarah J.', 'email' => 'sarah.jenkins@gmail.com', 'password' => password_hash('customer123', PASSWORD_DEFAULT)],
    ['username' => 'm_chen', 'full_name' => 'Michael C.', 'email' => 'm.chen7@gmail.com', 'password' => password_hash('customer456', PASSWORD_DEFAULT)]
];

$user_ids = [];
foreach ($new_users as $u) {
    // Check if user already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$u['email']]);
    $existing = $check->fetch();
    
    if (!$existing) {
        $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$u['username'], $u['full_name'], $u['email'], $u['password'], 'user']);
        $user_ids[] = $pdo->lastInsertId();
        echo "Created user {$u['full_name']}\n";
    } else {
        $user_ids[] = $existing['id'];
        echo "User {$u['full_name']} already exists.\n";
    }
}

// Reassign reviews
if (count($user_ids) >= 2) {
    // Find reviews submitted today by 'admin' (user_id 1)
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = 1 ORDER BY created_at DESC LIMIT 2");
    $stmt->execute();
    $revs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($revs) >= 2) {
        $stmt1 = $pdo->prepare("UPDATE reviews SET user_id = ? WHERE id = ?");
        $stmt1->execute([$user_ids[0], $revs[0]]);
        echo "Reassigned review {$revs[0]} to {$user_ids[0]}\n";

        $stmt2 = $pdo->prepare("UPDATE reviews SET user_id = ? WHERE id = ?");
        $stmt2->execute([$user_ids[1], $revs[1]]);
        echo "Reassigned review {$revs[1]} to {$user_ids[1]}\n";
    }
}

echo "Done!";
?>
