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

<div style="background: var(--background); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 4rem 2rem;">
    <div style="width: 100%; max-width: 520px; background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1.5rem; padding: 5rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
        
        <div style="text-align: center; margin-bottom: 4rem;">
            <div style="font-size: 4rem; margin-bottom: 1.5rem; filter: drop-shadow(0 0 20px rgba(14, 165, 233, 0.4));">🎻</div>
            <h2 style="font-family: var(--font-heading); font-size: 2.75rem; font-weight: 800; color: #fff; margin-bottom: 0.75rem; letter-spacing: -0.04em;">Welcome <span style="color: var(--primary);">Back</span></h2>
            <p style="color: #64748b; font-size: 1.1rem; letter-spacing: 0.02em;">Access your premium sonic vault.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1.25rem; border-radius: 0.5rem; color: #ef4444; margin-bottom: 2.5rem; font-size: 0.95rem; font-weight: 700;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div style="margin-bottom: 2rem;">
                <label for="username" style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em;">System Identifier</label>
                <input type="text" id="username" name="username" required placeholder="Enter username" style="width: 100%; padding: 1rem 1.25rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-size: 1.1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'">
            </div>

            <div style="margin-bottom: 3rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <label for="password" style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em;">Master Key</label>
                    <a href="#" style="font-size: 0.8rem; color: var(--primary); text-decoration: none; font-weight: 800; letter-spacing: 0.02em;">Recovery options?</a>
                </div>
                <input type="password" id="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 1rem 1.25rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-size: 1.1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.5rem; border-radius: 0.5rem; font-weight: 800; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2.5rem; box-shadow: 0 15px 40px -10px rgba(14, 165, 233, 0.4);">Authorize Session</button>
        </form>

        <div style="text-align: center; font-size: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.03); padding-top: 3rem;">
            <p style="color: #64748b;">New to the Archive? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 800; margin-left: 0.75rem;">Create Identity</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
