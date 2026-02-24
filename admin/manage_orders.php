<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Access Control
if (!is_logged_in() || (!has_role('admin') && !has_role('staff'))) {
    redirect('/TuneTrove/auth/login.php', 'Restricted access.', 'error');
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitize($_POST['status']);
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    redirect('manage_orders.php', "Order status updated to $new_status.");
}

// Fetch all orders with user details
$stmt = $pdo->query("SELECT o.*, u.username, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();

$status_options = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];
?>

<div class="container" style="padding-top: 2rem;">
    <div style="margin-bottom: 2rem;">
        <a href="dashboard.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">← Back to Dashboard</a>
    </div>

    <div style="background: var(--surface); padding: 3rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
        <h1 style="font-family: var(--font-heading); margin-bottom: 3rem;">Manage Customer Orders</h1>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                        <th style="padding: 1.5rem 1rem;">Order ID</th>
                        <th style="padding: 1.5rem 1rem;">Customer</th>
                        <th style="padding: 1.5rem 1rem;">Total Amount</th>
                        <th style="padding: 1.5rem 1rem;">Date</th>
                        <th style="padding: 1.5rem 1rem;">Status</th>
                        <th style="padding: 1.5rem 1rem; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" style="padding: 4rem; text-align: center; color: var(--text-muted);">No orders found in the system.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 1.5rem 1rem; font-weight: 700;">MM-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td style="padding: 1.5rem 1rem;">
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">@<?php echo htmlspecialchars($order['username']); ?></div>
                                </td>
                                <td style="padding: 1.5rem 1rem; font-weight: 600;"><?php echo format_price($order['total_amount']); ?></td>
                                <td style="padding: 1.5rem 1rem; font-size: 0.875rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td style="padding: 1.5rem 1rem;">
                                    <?php 
                                        $status_color = '#64748b';
                                        if ($order['status'] === 'paid') $status_color = '#3b82f6';
                                        if ($order['status'] === 'shipped') $status_color = '#8b5cf6';
                                        if ($order['status'] === 'completed') $status_color = '#10b981';
                                        if ($order['status'] === 'cancelled') $status_color = '#ef4444';
                                    ?>
                                    <span style="padding: 0.35rem 0.85rem; border-radius: 999px; font-size: 0.75rem; font-weight: 800; background: <?php echo $status_color; ?>15; color: <?php echo $status_color; ?>; border: 1px solid <?php echo $status_color; ?>30;">
                                        <?php echo strtoupper($order['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1.5rem 1rem; text-align: right;">
                                    <form action="manage_orders.php" method="POST" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" style="padding: 0.5rem; border-radius: 0.4rem; border: 1px solid var(--border); font-size: 0.875rem; background: #f8fafc;">
                                            <?php foreach ($status_options as $opt): ?>
                                                <option value="<?php echo $opt; ?>" <?php echo $order['status'] === $opt ? 'selected' : ''; ?>><?php echo ucfirst($opt); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
