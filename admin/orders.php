<?php
require_once 'includes/admin-header.php';

// Handle Order Status Update (staff + admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $allowed_statuses = ['pending', 'pending_payment', 'paid', 'processing', 'shipped', 'completed', 'cancelled'];

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
    }
    header("Location: orders.php?msg=Order+status+updated");
    exit();
}

// Fetch Orders
$stmt = $pdo->query("
    SELECT o.*, u.full_name as customer_name, u.email as customer_email
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

$status_colors = [
    'pending'         => '#f59e0b',
    'pending_payment' => '#f97316',
    'paid'            => '#10b981',
    'processing'      => '#3b82f6',
    'shipped'         => '#6366f1',
    'completed'       => '#22c55e',
    'cancelled'       => '#ef4444',
];

$status_icons = [
    'pending'         => '⏳',
    'pending_payment' => '💳',
    'paid'            => '✅',
    'processing'      => '⚙️',
    'shipped'         => '🚚',
    'completed'       => '🎉',
    'cancelled'       => '❌',
];
?>

        <?php if (isset($_GET['msg'])): ?>
            <div style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: #10b981; font-size: 0.875rem;">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">All Orders</h3>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Shipping Address</th>
                        <th>Date</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): 
                        $color = $status_colors[$order['status']] ?? '#94a3b8';
                    ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($order['customer_name'] ?: 'Guest'); ?></div>
                                <?php if ($order['customer_email']): ?>
                                <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            <td>
                                <?php 
                                    $display_status = $order['status'] ?: 'pending';
                                    $icon = $status_icons[$display_status] ?? '❓';
                                ?>
                                <span class="status-badge status-<?php echo $display_status; ?>">
                                    <span style="font-size: 0.85rem;"><?php echo $icon; ?></span>
                                    <?php echo htmlspecialchars($display_status); ?>
                                </span>
                            </td>
                            <td style="max-width: 180px; font-size: 0.875rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($order['shipping_address'] ?: 'N/A'); ?></td>
                            <td style="font-size: 0.875rem; color: var(--admin-text-muted);"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <form method="POST" action="orders.php" style="display: flex; gap: 0.5rem; align-items: center;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" style="padding: 0.4rem 0.5rem; border: 1px solid var(--admin-border); border-radius: 0.375rem; font-size: 0.8rem; background: white; cursor: pointer;">
                                        <?php foreach (array_keys($status_colors) as $s): ?>
                                            <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $s)); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" style="background: var(--admin-primary); color: white; border: none; padding: 0.4rem 0.75rem; border-radius: 0.375rem; font-size: 0.8rem; font-weight: 600; cursor: pointer; white-space: nowrap;">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
