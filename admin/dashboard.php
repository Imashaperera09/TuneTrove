<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Access Control
if (!is_logged_in() || (!has_role('admin') && !has_role('staff'))) {
    redirect('/TuneTrove/auth/login.php', 'Restricted access.', 'error');
}

// Fetch Metrics
$metrics = [];

// Total Revenue
$stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'paid'");
$metrics['revenue'] = $stmt->fetch()['total'] ?? 0;

// Total Orders
$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
$metrics['orders'] = $stmt->fetch()['count'];

// Low Stock Items (under 5)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity < 5 AND is_digital = 0");
$metrics['low_stock'] = $stmt->fetch()['count'];

// Latest Orders
$stmt = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$latest_orders = $stmt->fetchAll();
?>

<div class="container" style="padding-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <h1 style="font-family: var(--font-heading);">Administrative Dashboard</h1>
        <div style="background: var(--surface); padding: 0.5rem 1.5rem; border-radius: 999px; border: 1px solid var(--border); font-size: 0.875rem;">
            Role: <strong style="color: var(--primary);"><?php echo strtoupper($_SESSION['user_role']); ?></strong>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 2rem; margin-bottom: 4rem;">
        <div style="background: var(--surface); padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600; text-transform: uppercase;">Total Revenue</p>
            <h2 style="font-size: 2.5rem; font-family: var(--font-heading); margin-top: 0.5rem;"><?php echo format_price($metrics['revenue']); ?></h2>
        </div>
        <div style="background: var(--surface); padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <p style="color: var(--text-muted); font-size: 0.875rem; font-weight: 600; text-transform: uppercase;">Orders Processed</p>
            <h2 style="font-size: 2.5rem; font-family: var(--font-heading); margin-top: 0.5rem;"><?php echo $metrics['orders']; ?></h2>
        </div>
        <div style="background: var(--surface); padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <p style="color: var(--error); font-size: 0.875rem; font-weight: 600; text-transform: uppercase;">Low Stock Alerts</p>
            <h2 style="font-size: 2.5rem; font-family: var(--font-heading); margin-top: 0.5rem;"><?php echo $metrics['low_stock']; ?></h2>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 3rem;">
        <!-- Latest Orders -->
        <div style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h3 style="font-family: var(--font-heading);">Recent Activity</h3>
                <a href="manage_orders.php" style="font-size: 0.875rem; color: var(--primary); text-decoration: none; font-weight: 600;">View All Orders →</a>
            </div>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                        <th style="padding: 1rem 0;">ID</th>
                        <th style="padding: 1rem 0;">Customer</th>
                        <th style="padding: 1rem 0;">Total</th>
                        <th style="padding: 1rem 0;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latest_orders as $order): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 1rem 0;">MM-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td style="padding: 1rem 0;">@<?php echo htmlspecialchars($order['username']); ?></td>
                            <td style="padding: 1rem 0; font-weight: 600;"><?php echo format_price($order['total_amount']); ?></td>
                            <td style="padding: 1rem 0;"><span style="font-size: 0.75rem; font-weight: 700; color: #166534;"><?php echo strtoupper($order['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Admin Tools Sidebar -->
        <aside>
            <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Quick Management</h3>
            <nav style="display: grid; gap: 1rem;">
                <a href="manage_products.php" style="padding: 1.25rem; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); text-decoration: none; color: inherit; display: flex; align-items: center; gap: 1rem; transition: all 0.2s;">
                    <span style="font-size: 1.5rem;">🎸</span>
                    <div>
                        <p style="font-weight: 700; font-size: 0.875rem;">Manage Products</p>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Add, edit and stock control</p>
                    </div>
                </a>
                <a href="manage_orders.php" style="padding: 1.25rem; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); text-decoration: none; color: inherit; display: flex; align-items: center; gap: 1rem; transition: all 0.2s;">
                    <span style="font-size: 1.5rem;">📦</span>
                    <div>
                        <p style="font-weight: 700; font-size: 0.875rem;">Order Fulfillment</p>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Process and ship orders</p>
                    </div>
                </a>
                <?php if (has_role('admin')): ?>
                    <a href="manage_users.php" style="padding: 1.25rem; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); text-decoration: none; color: inherit; display: flex; align-items: center; gap: 1rem; transition: all 0.2s;">
                        <span style="font-size: 1.5rem;">👥</span>
                        <div>
                            <p style="font-weight: 700; font-size: 0.875rem;">User Management</p>
                            <p style="font-size: 0.75rem; color: var(--text-muted);">Manage staff and customers</p>
                        </div>
                    </a>
                <?php endif; ?>
            </nav>
        </aside>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
