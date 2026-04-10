<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('TUNETROVE_ADMIN_SESSION');
    session_start();
}
require_once __DIR__ . '/../user/includes/db.php';
require_once __DIR__ . '/../user/includes/functions.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'staff';

    if (empty($username) || empty($password) || empty($full_name)) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password, $full_name, $email, $role]);
            $success = "Account created successfully! You can now login.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Username or Email already exists.";
            } else {
                $errors[] = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuneTrove | Admin Signup</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-ui.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--admin-bg);">

    <div class="content-card" style="width: 100%; max-width: 450px; padding: 2rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🛡️</div>
            <h2 style="font-family: 'Inter', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--admin-text-dark); margin-bottom: 0.5rem;">Create Admin/Staff Account</h2>
            <p style="color: var(--admin-text-muted); font-size: 0.875rem;">Set up administrative privileges</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: #ef4444; font-size: 0.875rem;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: #10b981; font-size: 0.875rem;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--admin-text-dark); margin-bottom: 0.5rem;">Role</label>
                <div style="display: flex; gap: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="role" value="admin" checked> Admin
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="radio" name="role" value="staff"> Staff
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="username" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--admin-text-dark); margin-bottom: 0.5rem;">Username</label>
                <input type="text" id="username" name="username" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--admin-border); border-radius: 0.5rem; font-size: 1rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="full_name" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--admin-text-dark); margin-bottom: 0.5rem;">Full Name</label>
                <input type="text" id="full_name" name="full_name" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--admin-border); border-radius: 0.5rem; font-size: 1rem;">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--admin-text-dark); margin-bottom: 0.5rem;">Email Address</label>
                <input type="email" id="email" name="email" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--admin-border); border-radius: 0.5rem; font-size: 1rem;">
            </div>

            <div style="margin-bottom: 2rem;">
                <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--admin-text-dark); margin-bottom: 0.5rem;">Password</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--admin-border); border-radius: 0.5rem; font-size: 1rem;">
            </div>

            <button type="submit" style="width: 100%; background: var(--admin-primary); color: white; border: none; padding: 0.875rem; border-radius: 0.5rem; font-weight: 700; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);">Create Account</button>
        </form>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="login.php" style="color: var(--admin-text-muted); text-decoration: none; font-size: 0.875rem; font-weight: 500;">&larr; Already have an account? Sign In</a>
        </div>
    </div>
</body>
</html>
