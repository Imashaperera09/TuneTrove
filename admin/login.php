<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../user/includes/db.php';
require_once __DIR__ . '/../user/includes/functions.php';

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'superadmin')) {
    header("Location: index.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $errors[] = "Please enter both username and password.";
    }

    if (empty($errors)) {
        // We can allow login by username or email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Check if user is an admin
            if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
                // Success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];

                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Access denied. You do not have administrator privileges.";
            }
        } else {
            $errors[] = "Invalid credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuneTrove | Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-ui.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--admin-bg);">

    <div class="content-card" style="width: 100%; max-width: 400px; padding: 2rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem; filter: drop-shadow(0 0 10px rgba(79, 70, 229, 0.4));">🎵</div>
            <h2 style="font-family: 'Inter', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--admin-text-dark); margin-bottom: 0.5rem;">TuneTrove Admin</h2>
            <p style="color: var(--admin-text-muted); font-size: 0.875rem;">Sign in to access the control panel</p>
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

        <form method="POST" action="login.php">
            <div style="margin-bottom: 1.25rem;">
                <label for="username" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--admin-text-dark); margin-bottom: 0.5rem;">Username or Email</label>
                <input type="text" id="username" name="username" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--admin-border); border-radius: 0.5rem; font-size: 1rem;">
            </div>

            <div style="margin-bottom: 2rem;">
                <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--admin-text-dark); margin-bottom: 0.5rem;">Password</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--admin-border); border-radius: 0.5rem; font-size: 1rem;">
            </div>

            <button type="submit" style="width: 100%; background: var(--admin-primary); color: white; border: none; padding: 0.875rem; border-radius: 0.5rem; font-weight: 700; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);">Sign In to Dashboard</button>
        </form>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="/TuneTrove/user/" style="color: var(--admin-text-muted); text-decoration: none; font-size: 0.875rem; font-weight: 500;">&larr; Return to Storefront</a>
        </div>
    </div>

</body>
</html>
