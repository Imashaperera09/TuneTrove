<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

if (!is_logged_in()) {
    redirect('/TuneTrove/user/auth/login.php', 'Please login to proceed with checkout.', 'error');
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
        
        $_SESSION['msg'] = "Order placed successfully! Order ID: TT-" . str_pad($order_id, 6, '0', STR_PAD_LEFT);
        $_SESSION['msg_type'] = "success";
        header("Location: confirmation.php?id=$order_id");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to process order. Please try again.";
    }
}
?>

<div style="background: var(--background); min-height: 100vh; padding-top: 5rem; padding-bottom: 8rem;">
    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 2.5rem;">
            <p style="text-transform: uppercase; font-size: 0.8rem; font-weight: 800; color: var(--accent); letter-spacing: 0.3em; margin-bottom: 1rem;">Secure Channel</p>
            <h1 style="font-family: var(--font-heading); font-size: 4rem; letter-spacing: -0.04em; color: #fff; margin: 0;">Finalize Your <span style="color: var(--primary);">Acquisition</span></h1>
            <p style="color: #64748b; font-size: 1.15rem; margin-top: 1rem;">Authorize your order and specify your premium delivery location.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 450px; gap: 5rem; align-items: flex-start;">
            
            <!-- Left: Checkout Form -->
            <div>
                <form action="checkout.php" method="POST">
                    <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; padding: 4rem; margin-bottom: 4rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                        <h2 style="font-family: var(--font-heading); font-size: 2.25rem; font-weight: 800; color: #fff; margin-bottom: 3rem; letter-spacing: -0.03em;">Shipping Archive</h2>
                        
                        <div style="margin-bottom: 4rem;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.1em;">Delivery Particulars</label>
                            <textarea name="address" required placeholder="Street Address, Suite, City, State, Zip Code. Phone Number for courier contact." style="width: 100%; padding: 1.5rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-family: inherit; font-size: 1.1rem; min-height: 180px; transition: all 0.2s; outline: none; line-height: 1.6;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'"></textarea>
                            <p style="font-size: 0.85rem; color: #475569; margin-top: 1rem; line-height: 1.5;">Include any technical instructions or gated access codes for our logistics team.</p>
                        </div>

                        <h2 style="font-family: var(--font-heading); font-size: 2.25rem; font-weight: 800; color: #fff; margin-top: 5rem; margin-bottom: 3rem; letter-spacing: -0.03em;">Transfer Method</h2>
                        <div style="background: rgba(14, 165, 233, 0.03); border: 1px solid rgba(14, 165, 233, 0.1); border-radius: 1rem; padding: 3rem; display: flex; align-items: flex-start; gap: 2.5rem;">
                            <div style="font-size: 3.5rem; filter: drop-shadow(0 0 20px rgba(14, 165, 233, 0.3));">💎</div>
                            <div>
                                <p style="font-weight: 800; color: #fff; font-size: 1.1rem; margin-bottom: 0.5rem; letter-spacing: 0.05em;">SECURE ACQUISITION PROTOCOL</p>
                                <p style="font-size: 0.95rem; color: #94a3b8; line-height: 1.6;">This transaction is protected by enterprise-grade encryption. As this is a curated demonstration, your order will be authorized instantly.</p>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.75rem; border-radius: 0.5rem; font-weight: 800; font-size: 1.25rem; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 5rem; box-shadow: 0 15px 40px -10px rgba(14, 165, 233, 0.4);">Authorize Acquisition</button>
                    </div>
                </form>

                <div style="display: flex; gap: 3rem; padding: 0 1rem; color: #999;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.5rem;">🔒</span>
                        <span style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">256-Bit SSL Secured</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.5rem;">📦</span>
                        <span style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">Inspected & Dispatched</span>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <aside style="position: sticky; top: 120px;">
                <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; padding: 3.5rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                    <h2 style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 2rem; margin-bottom: 2.5rem; color: #fff; letter-spacing: -0.02em;">Manifest</h2>
                    
                    <div style="display: flex; flex-direction: column; gap: 2rem; margin-bottom: 3rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 3rem;">
                        <?php foreach ($cart_items as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem;">
                                <div style="flex: 1;">
                                    <div style="font-size: 1.05rem; font-weight: 800; color: #fff; line-height: 1.4;"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Units: <?php echo $item['qty']; ?></div>
                                </div>
                                <span style="font-weight: 800; color: #fff; font-size: 1.1rem;">$<?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 3.5rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 1rem; color: #94a3b8;">
                            <span>Subtotal</span>
                            <span style="font-weight: 800; color: #fff;">$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 1rem; color: #94a3b8;">
                            <span>Shipping</span>
                            <span style="font-weight: 800; color: #4ade80;">COMPLIMENTARY</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid rgba(255, 255, 255, 0.03); padding-top: 3rem;">
                        <span style="font-weight: 800; color: #64748b; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; padding-bottom: 0.5rem;">Total Value</span>
                        <span style="font-size: 3rem; font-weight: 800; color: #fff; line-height: 1; letter-spacing: -0.04em;">$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
