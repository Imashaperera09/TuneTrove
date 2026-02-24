<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

if (!is_logged_in()) {
    redirect('/TuneTrove/auth/login.php', 'Please login to view your orders.', 'error');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Get order info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('index.php', 'Order not found.', 'error');
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.brand, p.is_digital 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      WHERE oi.order_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
?>

<div class="container" style="padding-top: 2rem;">
    <div style="margin-bottom: 2rem;">
        <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">← Back to My Account</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 4rem; align-items: flex-start;">
        <div>
            <div style="background: var(--surface); padding: 3rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 3rem;">
                    <div>
                        <h1 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 0.5rem;">Order MM-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
                        <p style="color: var(--text-muted);">Placed on <?php echo date('F d, Y at H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                    <span style="padding: 0.5rem 1.5rem; border-radius: 999px; font-size: 0.875rem; font-weight: 700; background: #dcfce7; color: #166534;">
                        <?php echo strtoupper($order['status']); ?>
                    </span>
                </div>

                <div style="display: grid; gap: 2rem;">
                    <?php foreach ($items as $item): ?>
                        <div style="display: flex; align-items: center; gap: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border);">
                            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                🎸
                            </div>
                            <div style="flex: 1;">
                                <h3 style="font-family: var(--font-heading); font-size: 1.125rem; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p style="font-size: 0.875rem; color: var(--text-muted);"><?php echo htmlspecialchars($item['brand']); ?></p>
                            </div>
                            <div style="text-align: right;">
                                <p style="font-weight: 600;"><?php echo format_price($item['price_at_purchase'] * $item['quantity']); ?></p>
                                <p style="font-size: 0.75rem; color: var(--text-muted);">Qty: <?php echo $item['quantity']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php 
            $digital_items = array_filter($items, function($i) { return $i['is_digital']; });
            if (!empty($digital_items)): 
            ?>
                <div style="background: #fff7ed; padding: 2.5rem; border-radius: var(--radius); border: 1px solid #ffedd5;">
                    <h3 style="font-family: var(--font-heading); color: #9a3412; margin-bottom: 1.5rem;">Digital Access</h3>
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($digital_items as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem 1.5rem; border-radius: 0.5rem; border: 1px solid #ffedd5;">
                                <span style="font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></span>
                                <a href="download.php?order_id=<?php echo $id; ?>&product_id=<?php echo $item['product_id']; ?>" style="color: #c2410c; text-decoration: none; font-weight: 700; font-size: 0.875rem;">Download File</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <aside style="display:grid; gap: 2rem;">
            <div style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
                <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Shipping Details</h3>
                <p style="font-size: 0.875rem; line-height: 1.7; color: var(--text-muted);"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>

            <div style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
                <h3 style="font-family: var(--font-heading); margin-bottom: 2rem;">Billing Summary</h3>
                <div style="display: grid; gap: 1rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                        <span>Subtotal</span>
                        <span><?php echo format_price($order['total_amount'] - $order['shipping_cost']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                        <span>Shipping</span>
                        <span><?php echo format_price($order['shipping_cost']); ?></span>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.5rem; padding-top: 1.5rem;">
                    <span>Total</span>
                    <span><?php echo format_price($order['total_amount']); ?></span>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
