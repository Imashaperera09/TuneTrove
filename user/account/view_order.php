<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

if (!is_logged_in()) {
    redirect('/TuneTrove/user/auth/login.php', 'Please login to view your orders.', 'error');
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

<div style="background: var(--background); min-height: 100vh; padding-top: 5rem; padding-bottom: 8rem;">
    <div class="container">
        <div style="margin-bottom: 3rem;">
            <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.2em; display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.25rem;">←</span> Return to Archive
            </a>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 450px; gap: 5rem; align-items: flex-start;">
            <div>
                <div style="background: var(--surface); padding: 4rem; border-radius: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.03); box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5); margin-bottom: 3rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 2.5rem;">
                        <div>
                            <p style="text-transform: uppercase; font-size: 0.75rem; font-weight: 800; color: var(--accent); letter-spacing: 0.2em; margin-bottom: 0.75rem;">Manifest Details</p>
                            <h1 style="font-family: var(--font-heading); font-size: 2.75rem; color: #fff; margin: 0; letter-spacing: -0.04em;">Order TT-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
                            <p style="color: #64748b; margin-top: 0.75rem; font-size: 1.05rem;">Authorized on <?php echo date('F d, Y at H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <span style="padding: 0.6rem 1.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 800; background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.2); text-transform: uppercase; letter-spacing: 0.1em;">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </div>

                    <div style="display: grid; gap: 2.5rem;">
                        <?php foreach ($items as $item): ?>
                            <div style="display: flex; align-items: center; gap: 3rem; padding-bottom: 2.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                                <div style="width: 100px; height: 100px; background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem; filter: drop-shadow(0 0 15px rgba(14, 165, 233, 0.2));">
                                    🎻
                                </div>
                                <div style="flex: 1;">
                                    <h3 style="font-family: var(--font-heading); font-size: 1.35rem; color: #fff; margin-bottom: 0.5rem; letter-spacing: -0.01em;"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p style="font-size: 0.85rem; color: var(--accent); font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;"><?php echo htmlspecialchars($item['brand']); ?></p>
                                </div>
                                <div style="text-align: right;">
                                    <p style="font-weight: 800; color: #fff; font-size: 1.25rem;">$<?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></p>
                                    <p style="font-size: 0.85rem; color: #64748b; font-weight: 600; margin-top: 0.25rem;">Units: <?php echo $item['quantity']; ?></p>
                                    <?php if (in_array($order['status'], ['paid', 'shipped', 'completed'])): ?>
                                        <a href="../shop/product.php?id=<?php echo $item['product_id']; ?>&review=1" style="color: var(--primary); text-decoration: none; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; display: inline-block; margin-top: 0.5rem; border-bottom: 1px solid var(--primary);">Write Review</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php 
                $digital_items = array_filter($items, function($i) { return $i['is_digital']; });
                if (!empty($digital_items)): 
                ?>
                    <div style="background: rgba(14, 165, 233, 0.05); border: 1px solid rgba(14, 165, 233, 0.1); padding: 3.5rem; border-radius: 1.5rem;">
                        <h3 style="font-family: var(--font-heading); color: #fff; font-size: 1.75rem; margin-bottom: 1.5rem; letter-spacing: -0.02em;">Digital Archive Access</h3>
                        
                        <?php if (in_array($order['status'], ['paid', 'completed'])): ?>
                            <p style="color: #94a3b8; font-size: 1rem; margin-bottom: 3rem; line-height: 1.6;">Your high-fidelity assets are available for immediate download.</p>
                            <div style="display: grid; gap: 1.25rem;">
                                <?php foreach ($digital_items as $item): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0, 0, 0, 0.2); padding: 1.5rem 2rem; border-radius: 0.75rem; border: 1px solid rgba(255, 255, 255, 0.03);">
                                        <span style="font-weight: 700; color: #fff; font-size: 1.1rem;"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <a href="download.php?order_id=<?php echo $id; ?>&product_id=<?php echo $item['product_id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 800; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 2px solid var(--primary); padding-bottom: 2px;">Retrieve File</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #ef4444; font-size: 1rem; margin-bottom: 1rem; line-height: 1.6; font-weight: 800;">Downloads Locked</p>
                            <p style="color: #94a3b8; font-size: 0.95rem; line-height: 1.6;">Your digital assets will be available for download once payment has been confirmed for this order.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <aside style="display:grid; gap: 3rem; position: sticky; top: 120px;">
                <div style="background: var(--surface); padding: 3.5rem; border-radius: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.03); box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                    <h3 style="font-family: var(--font-heading); font-size: 1.5rem; color: #fff; margin-bottom: 2rem; letter-spacing: -0.02em;">Delivery Archive</h3>
                    <p style="font-size: 1.05rem; line-height: 1.7; color: #94a3b8;"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                </div>

                <div style="background: var(--surface); padding: 3.5rem; border-radius: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.03); box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                    <h3 style="font-family: var(--font-heading); font-size: 1.5rem; color: #fff; margin-bottom: 2.5rem; letter-spacing: -0.02em;">Valuation Summary</h3>
                    <div style="display: grid; gap: 1.25rem; padding-bottom: 2.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                        <div style="display: flex; justify-content: space-between; color: #94a3b8; font-size: 1.05rem;">
                            <span>Subtotal</span>
                            <span style="font-weight: 700; color: #fff;">$<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; color: #94a3b8; font-size: 1.05rem;">
                            <span>Logistics</span>
                            <span style="font-weight: 700; color: #4ade80;"><?php echo $order['shipping_cost'] > 0 ? '$' . number_format($order['shipping_cost'], 2) : 'COMPLIMENTARY'; ?></span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-top: 2.5rem;">
                        <span style="font-weight: 800; color: #64748b; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; padding-bottom: 0.5rem;">Total Value</span>
                        <span style="font-size: 3rem; font-weight: 800; color: #fff; line-height: 1; letter-spacing: -0.04em;">$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
