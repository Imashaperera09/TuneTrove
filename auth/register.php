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

<div class="auth-page-wrapper" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.1), transparent), radial-gradient(circle at bottom right, rgba(168, 85, 247, 0.15), transparent); margin-top: -5rem;">
    <div class="auth-glass-card" style="width: 100%; max-width: 550px; background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 2.5rem; padding: 4rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5); animation: authReveal 1s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <div style="text-align: center; margin-bottom: 3rem;">
            <div style="width: 64px; height: 64px; background: linear-gradient(135deg, var(--primary), #8b5cf6); border-radius: 1.5rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.5rem; box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);">✨</div>
            <h2 style="font-family: var(--font-heading); font-size: 2.25rem; letter-spacing: -0.02em; color: #fff; margin-bottom: 0.5rem;">Create <span style="color: var(--primary);">Account</span></h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Join the elite community of Melody Masters</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1.25rem; border-radius: 1rem; color: var(--error); margin-bottom: 2rem; font-size: 0.875rem; font-weight: 500;">
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li style="display: flex; align-items: center; gap: 0.5rem;"><span>⚠️</span> <?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div style="margin-bottom: 1.5rem;">
                <label for="full_name" style="display: block; margin-bottom: 0.65rem; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Full Name</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); font-size: 1.1rem; opacity: 0.5;">📛</span>
                    <input type="text" id="full_name" name="full_name" placeholder="John Doe" style="width: 100%; padding: 1.15rem 1.15rem 1.15rem 3.5rem; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.15rem; color: #fff; font-family: inherit; font-size: 0.95rem; transition: all 0.3s ease; outline: none;" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" onfocus="this.style.borderColor='var(--primary)';" onblur="this.style.borderColor='rgba(255,255,255,0.05)';">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="username" style="display: block; margin-bottom: 0.65rem; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Username</label>
                    <input type="text" id="username" name="username" required placeholder="user123" style="width: 100%; padding: 1.15rem; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.15rem; color: #fff; font-family: inherit; font-size: 0.95rem; outline: none;" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                </div>
                <div>
                    <label for="email" style="display: block; margin-bottom: 0.65rem; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Email</label>
                    <input type="email" id="email" name="email" required placeholder="john@example.com" style="width: 100%; padding: 1.15rem; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.15rem; color: #fff; font-family: inherit; font-size: 0.95rem; outline: none;" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2.5rem;">
                <div>
                    <label for="password" style="display: block; margin-bottom: 0.65rem; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Password</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 1.15rem; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.15rem; color: #fff; font-family: inherit; font-size: 0.95rem; outline: none;">
                </div>
                <div>
                    <label for="confirm_password" style="display: block; margin-bottom: 0.65rem; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Confirm</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••" style="width: 100%; padding: 1.15rem; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.15rem; color: #fff; font-family: inherit; font-size: 0.95rem; outline: none;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.4rem; border-radius: 1.25rem; font-weight: 800; font-size: 1rem; letter-spacing: 0.05em; text-transform: uppercase; box-shadow: 0 15px 35px -5px rgba(37, 99, 235, 0.4); margin-bottom: 2rem;">Create My Account</button>
        </form>

        <div style="text-align: center; font-size: 0.9rem;">
            <span style="color: var(--text-muted);">Already a member?</span>
            <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 700; margin-left: 0.5rem;">LOGIN HERE</a>
        </div>
    </div>
</div>

<style>
@keyframes authReveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?php require_once '../includes/footer.php'; ?>
