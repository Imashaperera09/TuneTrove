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

<div class="confirmation-stage" style="min-height: 100vh; background: radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.08), transparent); padding-top: 10rem; padding-bottom: 10rem; text-align: center;">
    <div class="container" style="max-width: 800px;">
        <div class="glass-panel" style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 4rem; padding: 5rem; box-shadow: 0 50px 120px -30px rgba(0,0,0,0.6); animation: authReveal 1s cubic-bezier(0.16, 1, 0.3, 1);">
            
            <div style="font-size: 5rem; margin-bottom: 3rem; animation: trophyBounce 2s infinite alternate ease-in-out;">🏆</div>
            
            <header style="margin-bottom: 4rem;">
                <h1 style="font-family: var(--font-heading); font-size: 3.5rem; letter-spacing: -0.05em; color: #fff; margin-bottom: 0.5rem;">Acquisition <span style="color: var(--primary);">Verified</span></h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Order Reference: <span style="color: #fff; font-weight: 800;">MM-<?php echo str_pad($id, 6, '0', STR_PAD_LEFT); ?></span></p>
            </header>

            <div style="text-align: left; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 2.5rem; padding: 3rem; margin-bottom: 4rem;">
                <h3 style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 1.5rem;">Allocation Summary</h3>
                <div style="display: grid; gap: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.9rem; color: rgba(255,255,255,0.7);">Masterpiece Allocation</span>
                        <span style="font-weight: 800; color: #fff;"><?php echo format_price($order['total_amount'] - $order['shipping_cost']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.9rem; color: rgba(255,255,255,0.7);">Fulfillment Logic</span>
                        <span style="font-weight: 800; color: #fff;"><?php echo format_price($order['shipping_cost']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 1.5rem; margin-top: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 800; color: var(--primary); text-transform: uppercase;">Total Investment Authorized</span>
                        <span style="font-family: var(--font-heading); font-weight: 800; font-size: 1.75rem; color: #fff;"><?php echo format_price($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>

            <?php if ($has_digital): ?>
                <div style="background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.2); padding: 3rem; border-radius: 2.5rem; margin-bottom: 4rem; text-align: left;">
                    <h3 style="font-family: var(--font-heading); color: #fff; font-size: 1.5rem; margin-bottom: 1rem;">Digital Assets Ready</h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 2.5rem;">Your high-fidelity digital performance assets have been provisioned in your secure vault.</p>
                    <div style="display: grid; gap: 1rem;">
                        <?php foreach ($items as $item): ?>
                            <?php if ($item['is_digital']): ?>
                                <a href="download.php?order_id=<?php echo $id; ?>&product_id=<?php echo $item['product_id']; ?>" class="btn" style="background: var(--primary); color: #fff; text-decoration: none; padding: 1.25rem; border-radius: 1.25rem; font-weight: 800; font-size: 0.85rem; letter-spacing: 0.05em; display: flex; align-items: center; justify-content: center; gap: 0.75rem; transition: all 0.3s;" onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 10px 20px rgba(37, 99, 235, 0.3)'" onmouseout="this.style.transform='none'; this.style.boxShadow='none'">
                                    📥 DOWNLOAD <?php echo strtoupper(htmlspecialchars($item['name'])); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 1.5rem; justify-content: center;">
                <a href="../account/index.php" class="btn btn-primary" style="padding: 1.25rem 2.5rem; border-radius: 1.25rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">ENTER PATRON PORTAL</a>
                <a href="index.php" class="btn" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; text-decoration: none; padding: 1.25rem 2.5rem; border-radius: 1.25rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">CONTINUE BROWSING</a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes authReveal {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes trophyBounce {
    from { transform: translateY(0) scale(1); }
    to { transform: translateY(-15px) scale(1.1); }
}
</style>

<?php require_once '../includes/footer.php'; ?>
