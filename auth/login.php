<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errors[] = "Please enter both username and password.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            $_SESSION['msg'] = "Welcome back, " . htmlspecialchars($user['username']) . "!";
            $_SESSION['msg_type'] = "success";
            
            header("Location: ../index.php");
            exit();
        } else {
            $errors[] = "Invalid username or password.";
        }
    }
}
?>

<div class="auth-container" style="max-width: 450px; margin: 4rem auto; background: var(--surface); padding: 3rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    <h2 style="font-family: var(--font-heading); margin-bottom: 2rem; text-align: center;">Login to <span class="logo-accent">Melody Masters</span></h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="list-style: none;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="auth-form">
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Username</label>
            <input type="text" id="username" name="username" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit;">
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password</label>
            <input type="password" id="password" name="password" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit;">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
    </form>

    <div style="margin-top: 2rem; text-align: center;">
        <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Don't have an account?</p>
        <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Create an Account</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
