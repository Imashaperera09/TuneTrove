<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    redirect('index.php', 'Order not found.', 'error');
}

// Fetch order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('index.php', 'Order not found.', 'error');
}

// Fetch order items and check for digital products
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.is_digital, dp.file_path 
                      FROM order_items oi 
                      JOIN products p ON oi.product_id = p.id 
                      LEFT JOIN digital_products dp ON p.id = dp.product_id
                      WHERE oi.order_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$has_digital = false;
foreach ($items as $item) {
    if ($item['is_digital']) $has_digital = true;
}
?>

<div class="container" style="padding-top: 4rem; text-align: center;">
    <div style="max-width: 600px; margin: 0 auto; background: var(--surface); padding: 4rem; border-radius: calc(var(--radius) * 2); border: 1px solid var(--border); box-shadow: var(--shadow-lg);">
        <span style="font-size: 5rem; display: block; margin-bottom: 2rem;">🎉</span>
        <h1 style="font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 1rem;">Thank You!</h1>
        <p style="color: var(--text-muted); font-size: 1.125rem; margin-bottom: 3rem;">Your order <strong style="color: var(--text);">MM-<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></strong> has been placed successfully.</p>

        <div style="text-align: left; padding: 2rem; background: #f8fafc; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 3rem;">
            <h3 style="font-size: 1rem; margin-bottom: 1.5rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em;">Order Summary</h3>
            <div style="display: grid; gap: 1rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Items Total</span>
                    <span><?php echo format_price($order['total_amount'] - $order['shipping_cost']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Shipping</span>
                    <span><?php echo format_price($order['shipping_cost']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 800; border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
                    <span>Total Paid</span>
                    <span><?php echo format_price($order['total_amount']); ?></span>
                </div>
            </div>
        </div>

        <?php if ($has_digital): ?>
            <div style="background: #fffbeb; border: 1px solid #fef3c7; padding: 2.5rem; border-radius: var(--radius); margin-bottom: 3rem;">
                <h3 style="color: #92400e; margin-bottom: 1rem;">Digital Downloads Ready!</h3>
                <p style="color: #b45309; font-size: 0.875rem; margin-bottom: 2rem;">You can download your sheet music directly from your account or using the links below:</p>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($items as $item): ?>
                        <?php if ($item['is_digital']): ?>
                            <a href="download.php?order_id=<?php echo $id; ?>&product_id=<?php echo $item['product_id']; ?>" class="btn" style="background: #92400e; color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                📥 Download <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a href="account.php" class="btn btn-primary">Go to My Account</a>
            <a href="shop/index.php" class="btn" style="border: 1px solid var(--border);">Continue Shopping</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
