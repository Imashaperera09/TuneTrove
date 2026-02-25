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

<div class="auth-page-wrapper" style="min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center; padding: 2rem; background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.1), transparent), radial-gradient(circle at bottom left, rgba(168, 85, 247, 0.08), transparent);">
    <div class="auth-glass-card" style="width: 100%; max-width: 440px; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 2.5rem; padding: 3.5rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.7); animation: authReveal 0.8s cubic-bezier(0.16, 1, 0.3, 1);">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 54px; height: 54px; background: linear-gradient(135deg, var(--primary), #1e40af); border-radius: 1.25rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);">🔑</div>
            <h2 style="font-family: var(--font-heading); font-size: 1.85rem; letter-spacing: -0.02em; color: #fff; margin-bottom: 0.25rem;">Welcome <span style="color: var(--primary);">Back</span></h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">Enter credentials to access account</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1rem; border-radius: 0.75rem; color: var(--error); margin-bottom: 1.5rem; font-size: 0.8rem; font-weight: 600;">
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li style="display: flex; align-items: center; gap: 0.5rem;"><span>⚠️</span> <?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div style="margin-bottom: 1.25rem;">
                <label for="username" style="display: block; margin-bottom: 0.5rem; font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.12em;">Username</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 1rem; opacity: 0.4;">👤</span>
                    <input type="text" id="username" name="username" required placeholder="your_username" style="width: 100%; padding: 1rem 1rem 1rem 3rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 0.85rem; color: #fff; font-family: inherit; font-size: 0.95rem; transition: all 0.3s ease; outline: none;" onfocus="this.style.borderColor='var(--primary)'; this.style.background='rgba(15, 23, 42, 0.6)';" onblur="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(15, 23, 42, 0.4)';">
                </div>
            </div>

            <div style="margin-bottom: 1.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <label for="password" style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.12em;">Password</label>
                    <a href="#" style="font-size: 0.65rem; color: var(--primary); text-decoration: none; font-weight: 800; letter-spacing: 0.05em;">FORGOT?</a>
                </div>
                <div style="position: relative;">
                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 1rem; opacity: 0.4;">🔒</span>
                    <input type="password" id="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 1rem 1rem 1rem 3rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 0.85rem; color: #fff; font-family: inherit; font-size: 0.95rem; transition: all 0.3s ease; outline: none;" onfocus="this.style.borderColor='var(--primary)'; this.style.background='rgba(15, 23, 42, 0.6)';" onblur="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.background='rgba(15, 23, 42, 0.4)';">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.15rem; border-radius: 1rem; font-weight: 800; font-size: 0.9rem; letter-spacing: 0.08em; text-transform: uppercase; box-shadow: 0 12px 25px -5px rgba(37, 99, 235, 0.3); margin-bottom: 1.75rem;">Sign In</button>
        </form>

        <div style="text-align: center; font-size: 0.9rem;">
            <span style="color: var(--text-muted);">New to Melody Masters?</span>
            <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 700; margin-left: 0.5rem;">CREATE ACCOUNT</a>
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
