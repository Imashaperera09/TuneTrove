<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

if (!is_logged_in()) {
    redirect('/TuneTrove/auth/login.php', 'Please login to access your account.', 'error');
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get order history
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<div class="container" style="padding-top: 2rem;">
    <div style="display: flex; gap: 3rem; align-items: flex-start;">
        <!-- Sidebar -->
        <aside style="width: 280px; background: var(--surface); padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 80px; height: 80px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 1rem;">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <h3 style="font-family: var(--font-heading);"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p style="color: var(--text-muted); font-size: 0.875rem;">@<?php echo htmlspecialchars($user['username']); ?></p>
            </div>
            <nav>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;"><a href="#orders" style="display: block; padding: 0.75rem; background: #f1f5f9; border-radius: 0.5rem; text-decoration: none; color: var(--text); font-weight: 500;">Order History</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="#profile" style="display: block; padding: 0.75rem; border-radius: 0.5rem; text-decoration: none; color: var(--text-muted);">Edit Profile</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="/TuneTrove/auth/logout.php" style="display: block; padding: 0.75rem; border-radius: 0.5rem; text-decoration: none; color: var(--error);">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div style="flex: 1;">
            <div id="orders" style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); margin-bottom: 2rem;">
                <h2 style="font-family: var(--font-heading); margin-bottom: 2rem;">My Orders</h2>
                
                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 3rem; background: var(--background); border-radius: var(--radius);">
                        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">You haven't placed any orders yet.</p>
                        <a href="/TuneTrove/shop/" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                                    <th style="padding: 1rem;">Order #</th>
                                    <th style="padding: 1rem;">Date</th>
                                    <th style="padding: 1rem;">Total</th>
                                    <th style="padding: 1rem;">Status</th>
                                    <th style="padding: 1rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr style="border-bottom: 1px solid var(--border);">
                                        <td style="padding: 1rem;">MM-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td style="padding: 1rem;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td style="padding: 1rem; font-weight: 600;"><?php echo format_price($order['total_amount']); ?></td>
                                        <td style="padding: 1rem;">
                                            <span style="padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: bold; background: #e0f2fe; color: #0369a1;">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <a href="view_order.php?id=<?php echo $order['id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 500;">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div id="profile" style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
                <h2 style="font-family: var(--font-heading); margin-bottom: 2rem;">Account Settings</h2>
                <form>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Full Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); background: #f8fafc;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); background: #f8fafc;">
                        </div>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); background: #f8fafc;">
                    </div>
                    <p style="margin-top: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">* Profile editing is coming soon.</p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
