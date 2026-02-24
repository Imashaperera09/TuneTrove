<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "Please fill in all required fields.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $errors[] = "Username or Email already exists.";
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, full_name, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $full_name, $password_hash]);
            
            $_SESSION['msg'] = "Registration successful! You can now login.";
            $_SESSION['msg_type'] = "success";
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<div class="auth-container" style="max-width: 500px; margin: 2rem auto; background: var(--surface); padding: 3rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
    <h2 style="font-family: var(--font-heading); margin-bottom: 2rem; text-align: center;">Join <span class="logo-accent">Melody Masters</span></h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="list-style: none;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST" class="auth-form">
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="full_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Full Name</label>
            <input type="text" id="full_name" name="full_name" placeholder="John Doe" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit;" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>">
        </div>
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Username *</label>
            <input type="text" id="username" name="username" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit;" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Address *</label>
            <input type="email" id="email" name="email" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit;" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password *</label>
            <input type="password" id="password" name="password" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit;">
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Confirm Password *</label>
            <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit;">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
    </form>

    <p style="margin-top: 2rem; text-align: center; color: var(--text-muted);">
        Already have an account? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Login here</a>
    </p>
</div>

<?php require_once '../includes/footer.php'; ?>
