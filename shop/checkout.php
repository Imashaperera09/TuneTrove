<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

if (!is_logged_in()) {
    redirect('/TuneTrove/auth/login.php', 'Please login to proceed with checkout.', 'error');
}

if (empty($_SESSION['cart'])) {
    redirect('index.php', 'Your cart is empty.', 'error');
}

// Fetch Cart details for summary
$cart_items = [];
$subtotal = 0;
$ids = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($ids) - 1) . '?';
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']];
    $subtotal += $p['price'] * $qty;
    $cart_items[] = array_merge($p, ['qty' => $qty]);
}

$shipping = calculate_shipping($subtotal);
$total = $subtotal + $shipping;

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = sanitize($_POST['address']);
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Create Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_cost, shipping_address, status) VALUES (?, ?, ?, ?, 'paid')");
        $stmt->execute([$user_id, $total, $shipping, $address]);
        $order_id = $pdo->lastInsertId();

        // 2. Add Order Items & Update Stock
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['id'], $item['qty'], $item['price']]);

            if (!$item['is_digital']) {
                $upd_stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $upd_stmt->execute([$item['qty'], $item['id']]);
            }
        }

        $pdo->commit();

        // Clear Cart
        unset($_SESSION['cart']);
        
        $_SESSION['msg'] = "Order placed successfully! Order ID: MM-" . str_pad($order_id, 6, '0', STR_PAD_LEFT);
        $_SESSION['msg_type'] = "success";
        header("Location: confirmation.php?id=$order_id");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to process order. Please try again.";
    }
}
?>

<div class="checkout-portal" style="min-height: 100vh; background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.05), transparent), radial-gradient(circle at bottom right, rgba(168, 85, 247, 0.05), transparent); padding-top: 8rem; padding-bottom: 8rem;">
    <div class="container">
        <header style="margin-bottom: 5rem;">
            <h1 style="font-family: var(--font-heading); font-size: 3.5rem; letter-spacing: -0.05em; margin-bottom: 0.5rem;">Authorization <span style="color: var(--primary);">Portal</span></h1>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Securely complete your acquisition protocol.</p>
        </header>

        <div style="display: grid; grid-template-columns: 1fr 440px; gap: 5rem; align-items: flex-start;">
            <!-- Authorization Logic -->
            <div style="display: flex; flex-direction: column; gap: 3rem;">
                <form action="checkout.php" method="POST">
                    <div class="glass-panel" style="background: rgba(30, 41, 59, 0.3); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 3rem; padding: 4rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                        <div style="margin-bottom: 3.5rem;">
                            <h2 style="font-family: var(--font-heading); font-size: 1.75rem; color: #fff; margin-bottom: 1rem;">Fulfillment Protocol</h2>
                            <p style="color: var(--text-muted); font-size: 0.85rem;">Define the target destination for your physical instruments.</p>
                        </div>
                        
                        <div style="margin-bottom: 3.5rem;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1rem;">Shipping Intelligence</label>
                            <textarea name="address" required placeholder="Full street address, postal code, and contact coordinates..." style="width: 100%; padding: 1.5rem; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.5rem; color: #fff; font-family: inherit; font-size: 1rem; min-height: 150px; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--primary)'"></textarea>
                        </div>

                        <div style="margin-bottom: 3.5rem;">
                            <h2 style="font-family: var(--font-heading); font-size: 1.75rem; color: #fff; margin-bottom: 1.5rem;">Secure Gateway</h2>
                            <div style="padding: 2rem; background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.2); border-radius: 1.5rem; display: flex; align-items: center; gap: 1.5rem;">
                                <div style="font-size: 2rem;">💳</div>
                                <div>
                                    <p style="font-weight: 800; color: #fff; font-size: 0.9rem; margin-bottom: 0.25rem;">VERIFIED TRANSACTION</p>
                                    <p style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">Demonstration node active. Secure sandbox authorization will be executed.</p>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.5rem; border-radius: 1.5rem; font-weight: 800; font-size: 1rem; letter-spacing: 0.1em; text-transform: uppercase; box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.4);">EXECUTE AUTHORIZATION</button>
                    </div>
                </form>

                <div style="display: flex; gap: 2rem; align-items: center; padding: 0 2rem; color: var(--text-muted);">
                    <div style="display: flex; items-center; gap: 0.75rem;">
                        <span style="font-size: 1.25rem;">🛡️</span>
                        <span style="font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">SSL ENCRYPTED</span>
                    </div>
                    <div style="display: flex; items-center; gap: 0.75rem;">
                        <span style="font-size: 1.25rem;">🛰️</span>
                        <span style="font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">GLOBAL LOGISTICS</span>
                    </div>
                </div>
            </div>

            <!-- Allocation Summary -->
            <aside style="position: sticky; top: 120px;">
                <div class="glass-panel" style="background: rgba(30, 41, 59, 0.2); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 3rem; padding: 3rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.3);">
                    <h2 style="font-family: var(--font-heading); font-size: 1.5rem; color: #fff; margin-bottom: 2.5rem;">Investment Summary</h2>
                    
                    <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 2.5rem;">
                        <?php foreach ($cart_items as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem;">
                                <div>
                                    <div style="font-size: 0.9rem; font-weight: 700; color: #fff; line-height: 1.2;"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-top: 0.25rem;">QTY: <?php echo $item['qty']; ?></div>
                                </div>
                                <span style="font-weight: 800; color: #fff; font-size: 0.95rem;"><?php echo format_price($item['price'] * $item['qty']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="display: grid; gap: 1.25rem; margin-bottom: 2.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 2.5rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Core Allocation</span>
                            <span style="font-weight: 700; color: #fff;"><?php echo format_price($subtotal); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Fulfillment Logic</span>
                            <span style="font-weight: 700; color: #fff;"><?php echo $shipping > 0 ? format_price($shipping) : '<span style="color: var(--success);">N/A (FREE)</span>'; ?></span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                        <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Final Investment</span>
                        <span style="font-family: var(--font-heading); font-weight: 800; font-size: 2.25rem; color: #fff; line-height: 1;"><?php echo format_price($total); ?></span>
                    </div>

                    <?php if (count(array_filter($cart_items, fn($i) => $i['is_digital'])) > 0): ?>
                        <div style="margin-top: 3rem; background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.2); padding: 1.5rem; border-radius: 1.5rem; text-align: center;">
                            <p style="font-size: 0.7rem; color: #a855f7; font-weight: 800; text-transform: uppercase; line-height: 1.4;">Digital performance assets will be provisioned instantly in your secure vault.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
</div>

<style>
@keyframes authReveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@media (max-width: 1100px) {
    div[style*="grid-template-columns: 1fr 440px"] {
        grid-template-columns: 1fr !important;
    }
    aside {
        position: static !important;
        margin-top: 5rem;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
