<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Access Control - Admin Only
if (!is_logged_in() || !has_role('admin')) {
    redirect('/TuneTrove/admin/dashboard.php', 'Restricted to Administrators only.', 'error');
}

// Handle Role Update
if (isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = sanitize($_POST['role']);
    
    // Prevent changing own role
    if ($user_id == $_SESSION['user_id']) {
        redirect('manage_users.php', "You cannot change your own role.", "error");
    }

    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$new_role, $user_id]);
    redirect('manage_users.php', "User role updated successfully.");
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$roles = ['admin', 'staff', 'customer'];
?>

<div class="container" style="padding-top: 2rem;">
    <div style="margin-bottom: 2rem;">
        <a href="dashboard.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">← Back to Dashboard</a>
    </div>

    <div style="background: var(--surface); padding: 3rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h1 style="font-family: var(--font-heading); margin-bottom: 3rem;">User Management</h1>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                        <th style="padding: 1.5rem 1rem;">User</th>
                        <th style="padding: 1.5rem 1rem;">Email</th>
                        <th style="padding: 1.5rem 1rem;">Joined</th>
                        <th style="padding: 1.5rem 1rem;">Role</th>
                        <th style="padding: 1.5rem 1rem; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1.5rem 1rem;">
                                <div style="font-weight: 700;"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                <div style="font-size: 0.875rem; color: var(--text-muted);">@<?php echo htmlspecialchars($u['username']); ?></div>
                            </td>
                            <td style="padding: 1.5rem 1rem; font-size: 0.875rem;"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td style="padding: 1.5rem 1rem; font-size: 0.875rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td style="padding: 1.5rem 1rem;">
                                <?php 
                                    $role_bg = '#f1f5f9';
                                    $role_text = '#64748b';
                                    if ($u['role'] === 'admin') { $role_bg = '#fef2f2'; $role_text = '#ef4444'; }
                                    if ($u['role'] === 'staff') { $role_bg = '#f0f9ff'; $role_text = '#0ea5e9'; }
                                ?>
                                <span style="padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.7rem; font-weight: 800; background: <?php echo $role_bg; ?>; color: <?php echo $role_text; ?>; border: 1px solid <?php echo $role_text; ?>20;">
                                    <?php echo strtoupper($u['role']); ?>
                                </span>
                            </td>
                            <td style="padding: 1.5rem 1rem; text-align: right;">
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <form action="manage_users.php" method="POST" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <select name="role" style="padding: 0.4rem; border-radius: 0.4rem; border: 1px solid var(--border); font-size: 0.8rem; background: #f8fafc;">
                                            <?php foreach ($roles as $r): ?>
                                                <option value="<?php echo $r; ?>" <?php echo $u['role'] === $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_role" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.7rem;">Update</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-italic;">(Logged In)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
