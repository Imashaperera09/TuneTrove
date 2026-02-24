<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

if (!is_logged_in()) {
    redirect('/TuneTrove/auth/login.php', 'Please login to proceed with checkout.', 'error');
}

if (empty($_SESSION['cart'])) {
    redirect('shop/index.php', 'Your cart is empty.', 'error');
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

<div class="container" style="padding-top: 2rem;">
    <h1 style="font-family: var(--font-heading); margin-bottom: 2.5rem;">Checkout</h1>

    <div style="display: grid; grid-template-columns: 1fr 420px; gap: 4rem; align-items: flex-start;">
        <!-- Checkout Form -->
        <div style="background: var(--surface); padding: 3rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <form action="checkout.php" method="POST">
                <h2 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 2rem;">Shipping Information</h2>
                
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.75rem; font-weight: 500;">Shipping Address</label>
                    <textarea name="address" required placeholder="Enter your full street address, city, and zip code" style="width: 100%; padding: 1rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit; min-height: 120px;"></textarea>
                </div>

                <h2 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 2rem;">Payment Method</h2>
                <div style="padding: 1.5rem; background: #f8fafc; border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 3rem;">
                    <p style="font-weight: 600; margin-bottom: 0.5rem;">💳 Credit Card (Mock Payment)</p>
                    <p style="font-size: 0.875rem; color: var(--text-muted);">This is a demonstration store. No real payment will be processed.</p>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.25rem; font-size: 1.125rem;">Complete Purchase</button>
            </form>
        </div>

        <!-- Order Summary -->
        <aside>
            <div style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
                <h2 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 2rem;">Your Order</h2>
                
                <div style="display: grid; gap: 1.5rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1.5rem;">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display: flex; justify-content: space-between; gap: 1rem; font-size: 0.875rem;">
                            <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['qty']; ?></span>
                            <span style="font-weight: 600;"><?php echo format_price($item['price'] * $item['qty']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="display: grid; gap: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                        <span>Subtotal</span>
                        <span><?php echo format_price($subtotal); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                        <span>Shipping</span>
                        <span><?php echo $shipping > 0 ? format_price($shipping) : 'FREE'; ?></span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.5rem;">
                    <span>Total</span>
                    <span><?php echo format_price($total); ?></span>
                </div>
            </div>

            <div style="margin-top: 2rem; padding: 1.5rem; background: #eff6ff; border-radius: var(--radius); border: 1px solid #dbeafe; display: flex; gap: 1rem; align-items: center;">
                <span style="font-size: 1.5rem;">🎁</span>
                <p style="font-size: 0.875rem; color: #1e40af;">Digital sheet music will be available for download instantly after purchase!</p>
            </div>
        </aside>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
