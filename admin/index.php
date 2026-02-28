<?php
require_once 'includes/admin-header.php';

// Fetch Statistics
try {
    // Total Revenue
    $revStmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status IN ('paid', 'completed')");
    $totalRevenue = $revStmt->fetch()['total'] ?? 0;

    // Total Orders
    $orderStmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $orderStmt->fetch()['total'] ?? 0;

    // Total Products
    $prodStmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $prodStmt->fetch()['total'] ?? 0;

    // Total Users
    $userStmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $userStmt->fetch()['total'] ?? 0;

    // Recent Orders
    $recentOrdersStmt = $pdo->query("
        SELECT o.*, u.full_name as customer_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recentOrders = $recentOrdersStmt->fetchAll();

} catch (PDOException $e) {
    // Fallback if DB issues
    $totalRevenue = 0; $totalOrders = 0; $totalProducts = 0; $totalUsers = 0;
    $recentOrders = [];
}
?>

        <!-- Widgets -->
        <div class="widgets-grid">
            <div class="widget-card">
                <div class="widget-label">Total Revenue</div>
                <div class="widget-value">$<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="widget-trend trend-up">Summary of paid orders</div>
            </div>
            <div class="widget-card">
                <div class="widget-label">Orders</div>
                <div class="widget-value"><?php echo $totalOrders; ?></div>
                <div class="widget-trend">Total transactions</div>
            </div>
            <div class="widget-card">
                <div class="widget-label">Products</div>
                <div class="widget-value"><?php echo $totalProducts; ?></div>
                <div class="widget-trend">In inventory</div>
            </div>
            <div class="widget-card">
                <div class="widget-label">Registered Users</div>
                <div class="widget-value"><?php echo $totalUsers; ?></div>
                <div class="widget-trend">Community size</div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Recent Orders</h3>
                <a href="orders.php" style="color: var(--admin-primary); font-size: 0.875rem; font-weight: 600; text-decoration: none;">View All</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem;">No orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <?php 
                                        $display_status = $order['status'] ?: 'pending';
                                        $icon = ['pending'=>'⏳','pending_payment'=>'💳','paid'=>'✅','processing'=>'⚙️','shipped'=>'🚚','completed'=>'🎉','cancelled'=>'❌'][$display_status] ?? '❓';
                                    ?>
                                    <span class="status-badge status-<?php echo $display_status; ?>" style="display: inline-flex; align-items: center; gap: 0.3rem;">
                                        <span><?php echo $icon; ?></span>
                                        <?php echo $display_status; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
