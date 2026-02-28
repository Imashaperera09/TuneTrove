<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/functions.php';

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
    $effective_price = get_effective_price($p);
    $subtotal += $effective_price * $qty;
    $cart_items[] = array_merge($p, ['qty' => $qty, 'price' => $effective_price]);
}

$shipping = calculate_shipping($subtotal);
$total = $subtotal + $shipping;

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = sanitize($_POST['address']);
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        $payment_method = $_POST['payment_method'] ?? 'card';
        $status = ($payment_method === 'card') ? 'paid' : 'pending_payment';

        // 1. Create Order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_cost, shipping_address, status, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $total, $shipping, $address, $status, $payment_method]);
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
        $error = "Failed to process order: " . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<style>
    main { padding-top: 1.5rem !important; }
</style>

<div style="background: var(--background); min-height: 100vh; padding-top: 0; padding-bottom: 8rem;">
    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 1.5rem;">
            <h1 style="font-family: var(--font-heading); font-size: 3.5rem; letter-spacing: -0.04em; color: #fff; margin: 0;">Finalize Your <span style="color: var(--primary);">Acquisition</span></h1>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 450px; gap: 3rem; align-items: flex-start;">
            
            <!-- Left: Checkout Form -->
            <div>
                <form action="checkout.php" method="POST">
                    <?php if (isset($error)): ?>
                        <div style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.3); border-radius: 0.75rem; padding: 1.25rem 1.5rem; margin-bottom: 2rem; color: #ef4444; font-weight: 700; font-size: 0.95rem;">
                            ⚠️ <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; padding: 3.5rem; margin-bottom: 4rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                        <h2 style="font-family: var(--font-heading); font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 2.5rem; letter-spacing: -0.02em;">Shipping Archive</h2>
                        
                        <div style="margin-bottom: 3rem;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.1em;">Delivery Particulars</label>
                            <textarea name="address" required placeholder="Street Address, Suite, City, State, Zip Code..." style="width: 100%; padding: 1.25rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-family: inherit; font-size: 1.05rem; min-height: 150px; transition: all 0.2s; outline: none; line-height: 1.6;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'"></textarea>
                        </div>

                        <!-- Payment Section -->
                        <div style="background: var(--surface); border: 1px solid rgba(14, 165, 233, 0.1); border-radius: 1rem; padding: 2.5rem; margin-bottom: 2rem; margin-top: 2rem;">
                            <h2 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 2rem; letter-spacing: -0.02em;">Payment Method</h2>
                            <div style="margin-bottom: 2rem;">
                                <label style="font-weight: 800; color: #fff; margin-right: 2rem;">
                                    <input type="radio" name="payment_method" value="card" checked style="margin-right: 0.5rem;"> Credit/Debit Card
                                </label>
                                <label style="font-weight: 800; color: #fff;">
                                    <input type="radio" name="payment_method" value="cod" style="margin-right: 0.5rem;"> Cash on Delivery
                                </label>
                            </div>
                            <div id="card-details" style="display: block; margin-top: 1.5rem;">
                                <div style="margin-bottom: 1.5rem;">
                                    <div style="margin-bottom: 1rem;">
                                        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.08em;">Card Number</label>
                                        <input type="text" name="card_number" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19" class="card-input" style="width: 100%; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.15); color: #fff; font-size: 1.1rem; font-family: monospace; letter-spacing: 0.1em;">
                                    </div>
                                    <div style="margin-bottom: 1rem;">
                                        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.08em;">Name on Card</label>
                                        <input type="text" name="card_name" id="card_name" placeholder="John Doe" class="card-input" style="width: 100%; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.15); color: #fff; font-size: 1.1rem;">
                                    </div>
                                    <div style="display: flex; gap: 1rem;">
                                        <div style="flex: 1;">
                                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.08em;">Expiry</label>
                                            <input type="text" name="card_expiry" id="card_expiry" placeholder="MM/YY" maxlength="5" class="card-input" style="width: 100%; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.15); color: #fff; font-size: 1.1rem; font-family: monospace;">
                                        </div>
                                        <div style="flex: 1;">
                                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.08em;">CVC</label>
                                            <input type="password" name="card_cvc" id="card_cvc" placeholder="•••" maxlength="4" class="card-input" style="width: 100%; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.15); color: #fff; font-size: 1.1rem; font-family: monospace;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem 2rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.95rem; letter-spacing: 0.05em; text-transform: uppercase; margin-top: 3rem; box-shadow: 0 10px 30px -10px rgba(14, 165, 233, 0.4);">Place Order</button>
                    </div>
                </form>

                <div style="display: flex; gap: 3rem; padding: 0 1rem; color: #475569;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.25rem;">🔒</span>
                        <span style="font-size: 0.7rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase;">SSL Secured</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.25rem;">📦</span>
                        <span style="font-size: 0.7rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase;">Inspected & Dispatched</span>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <aside style="position: sticky; top: 100px;">
                <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; padding: 3rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                    <h2 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 800; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 1.5rem; margin-bottom: 2rem; color: #fff; letter-spacing: -0.02em;">Manifest</h2>
                    
                    <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 2.5rem;">
                        <?php foreach ($cart_items as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem;">
                                <div style="flex: 1;">
                                    <div style="font-size: 1rem; font-weight: 800; color: #fff; line-height: 1.4;"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.4rem; text-transform: uppercase; letter-spacing: 0.05em;">Units: <?php echo $item['qty']; ?></div>
                                </div>
                                <span style="font-weight: 800; color: #fff; font-size: 1rem;">$<?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 3rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #94a3b8;">
                            <span>Subtotal</span>
                            <span style="font-weight: 800; color: #fff;">$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.95rem; color: #94a3b8;">
                            <span>Shipping</span>
                            <span style="font-weight: 800; color: #4ade80;">COMPLIMENTARY</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid rgba(255, 255, 255, 0.03); padding-top: 2.5rem;">
                        <span style="font-weight: 800; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em;">Total Value</span>
                        <span style="font-size: 2.5rem; font-weight: 800; color: #fff; line-height: 1; letter-spacing: -0.03em;">$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
// Toggle card details and required attributes based on payment method
function togglePaymentMethod(method) {
    const cardDetails = document.getElementById('card-details');
    const cardInputs = document.querySelectorAll('.card-input');
    if (method === 'card') {
        cardDetails.style.display = 'block';
        cardInputs.forEach(i => i.setAttribute('required', 'required'));
    } else {
        cardDetails.style.display = 'none';
        cardInputs.forEach(i => i.removeAttribute('required'));
    }
}

// Set initial state
togglePaymentMethod('card');

// Listen for radio changes
document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
    radio.addEventListener('change', function() { togglePaymentMethod(this.value); });
});

// Auto-format card number: groups of 4
document.getElementById('card_number').addEventListener('input', function(e) {
    let val = this.value.replace(/\D/g, '').substring(0, 16);
    this.value = val.match(/.{1,4}/g)?.join(' ') || val;
});

// Auto-format expiry MM/YY
document.getElementById('card_expiry').addEventListener('input', function(e) {
    let val = this.value.replace(/\D/g, '').substring(0, 4);
    if (val.length >= 3) val = val.substring(0,2) + '/' + val.substring(2);
    this.value = val;
});
</script>

<?php require_once '../includes/footer.php'; ?>
