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
        // WARNING: Storing passwords in plain text as requested by user. This is insecure.
        $password_hash = $password;
        
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

<div style="background: var(--background); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 4rem 2rem;">
    <div style="width: 100%; max-width: 600px; background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1.5rem; padding: 5rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
        
        <div style="text-align: center; margin-bottom: 4rem;">
            <div style="font-size: 4rem; margin-bottom: 1.5rem; filter: drop-shadow(0 0 20px rgba(14, 165, 233, 0.4));">✨</div>
            <h2 style="font-family: var(--font-heading); font-size: 2.75rem; font-weight: 800; color: #fff; margin-bottom: 0.75rem; letter-spacing: -0.04em;">Create <span style="color: var(--primary);">Identity</span></h2>
            <p style="color: #64748b; font-size: 1.1rem; letter-spacing: 0.02em;">Join the elite circles of the TuneTrove archive.</p>
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

        <form action="register.php" method="POST">
            <div style="margin-bottom: 2rem;">
                <label for="full_name" style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em;">Legal Descriptor</label>
                <input type="text" id="full_name" name="full_name" placeholder="John Doe" style="width: 100%; padding: 1rem 1.25rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-size: 1.1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'" value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <label for="username" style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em;">System ID</label>
                    <input type="text" id="username" name="username" required placeholder="user123" style="width: 100%; padding: 1rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-size: 1.1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                </div>
                <div>
                    <label for="email" style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em;">Communique Channel</label>
                    <input type="email" id="email" name="email" required placeholder="john@example.com" style="width: 100%; padding: 1rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-size: 1.1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 4rem;">
                <div>
                    <label for="password" style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em;">Security Key</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 1rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-size: 1.1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'">
                </div>
                <div>
                    <label for="confirm_password" style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em;">Verification</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••" style="width: 100%; padding: 1rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-size: 1.1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.5rem; border-radius: 0.5rem; font-weight: 800; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 3rem; box-shadow: 0 15px 40px -10px rgba(14, 165, 233, 0.4);">Initialize Identity</button>
        </form>

        <div style="text-align: center; font-size: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.03); padding-top: 3rem;">
            <p style="color: #64748b;">Member of the Archive? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 800; margin-left: 0.75rem;">Authorize Session</a></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
