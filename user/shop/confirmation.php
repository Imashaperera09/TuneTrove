<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    redirect('../index.php', 'Order not found.', 'error');
}

// Fetch order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('../index.php', 'Order not found.', 'error');
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

<div style="background: var(--background); min-height: 100vh; padding-top: 3rem; padding-bottom: 5rem;">
    <div class="container" style="max-width: 800px; text-align: center;">
        <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1.5rem; padding: 3rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
            
            <div style="font-size: 3.5rem; margin-bottom: 1.5rem;">🏆</div>
            <h1 style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; color: #fff; margin-bottom: 1rem; letter-spacing: -0.04em;">Order <span style="color: var(--primary);">Confirmed!</span></h1>
            <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 3rem;">Order <span style="font-weight: 800; color: #fff;">TT-<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></span> has been placed and is being processed.</p>

            <div style="text-align: left; background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.02); border-radius: 1rem; padding: 3.5rem; margin-bottom: 5rem;">
                <h3 style="font-size: 0.8rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 2.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 1.5rem;">Acquisition Summary</h3>
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #94a3b8;">Items Value</span>
                        <span style="font-weight: 800; color: #fff;">$<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #94a3b8;">Logistics & Handling</span>
                        <span style="font-weight: 800; color: #4ade80;"><?php echo $order['shipping_cost'] > 0 ? '$' . number_format($order['shipping_cost'], 2) : 'COMPLIMENTARY'; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px solid rgba(255, 255, 255, 0.03); padding-top: 2rem; margin-top: 1rem;">
                        <span style="font-weight: 800; color: #64748b; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.1em; padding-bottom: 0.5rem;">Final Amount</span>
                        <span style="font-family: var(--font-heading); font-weight: 800; font-size: 2.5rem; color: #fff; letter-spacing: -0.04em;">$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <?php if ($has_digital): ?>
                <div style="background: rgba(14, 165, 233, 0.05); border: 1px solid rgba(14, 165, 233, 0.1); padding: 2rem; border-radius: 1rem; margin-bottom: 2rem; text-align: left;">
                    <h3 style="font-family: var(--font-heading); color: #fff; font-size: 1.75rem; font-weight: 800; margin-bottom: 1rem; letter-spacing: -0.02em;">Digital Masterpiece Access</h3>
                    <p style="color: #94a3b8; font-size: 1.1rem; margin-bottom: 3rem; line-height: 1.6;">Your high-fidelity digital assets are ready for download below.</p>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($items as $item): ?>
                            <?php if ($item['is_digital']): ?>
                                <a href="download.php?order_id=<?php echo $id; ?>&product_id=<?php echo $item['product_id']; ?>" style="background: var(--primary); color: #fff; text-decoration: none; padding: 1rem; border-radius: 4px; font-weight: 800; text-align: center; font-size: 0.9rem; text-transform: uppercase;">
                                    Download <?php echo htmlspecialchars($item['name']); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 1.5rem; justify-content: center;">
                <a href="../account/index.php" class="btn btn-primary" style="padding: 1rem 2.5rem; border-radius: 0.5rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">View Orders</a>
                <a href="/TuneTrove/user/shop/" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); color: #fff; text-decoration: none; padding: 1rem 2.5rem; border-radius: 0.5rem; font-weight: 700; text-transform: uppercase; font-size: 0.95rem; letter-spacing: 0.05em; transition: all 0.2s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.05)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.02)'">Continue Shopping</a>
            </div>

            <p style="margin-top: 2.5rem; color: #475569; font-size: 0.85rem;">A confirmation email has been sent to your registered address.</p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
