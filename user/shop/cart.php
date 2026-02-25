<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Handle Actions (Ajax or POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = (int)$_POST['product_id'];

    if ($action === 'remove') {
        unset($_SESSION['cart'][$product_id]);
    }

    if ($action === 'update') {
        $quantity = (int)$_POST['quantity'];
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }

    header("Location: cart.php");
    exit();
}

// Fetch Cart items details
$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $line_total = $p['price'] * $qty;
        $subtotal += $line_total;
        $cart_items[] = array_merge($p, ['qty' => $qty, 'line_total' => $line_total]);
    }
}

$shipping = calculate_shipping($subtotal);
$total = $subtotal + $shipping;
?>

<div style="background: var(--background); min-height: 100vh; padding-top: 5rem; padding-bottom: 8rem;">
    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 2.5rem;">
            <p style="text-transform: uppercase; font-size: 0.8rem; font-weight: 800; color: var(--accent); letter-spacing: 0.3em; margin-bottom: 1rem;">Transaction Portal</p>
            <h1 style="font-family: var(--font-heading); font-size: 3.5rem; letter-spacing: -0.04em; color: #fff; margin: 0;">Review Your <span style="color: var(--primary);">Selection</span></h1>
            <p style="color: #64748b; font-size: 1.15rem; margin-top: 1rem;">Prepare your curated gear for final acquisition.</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 10rem 2rem; background: rgba(255, 255, 255, 0.01); border: 2px dashed rgba(255, 255, 255, 0.05); border-radius: 1rem;">
                <div style="font-size: 6rem; margin-bottom: 2.5rem; opacity: 0.2; filter: drop-shadow(0 0 30px var(--primary));">🛒</div>
                <h2 style="font-family: var(--font-heading); font-size: 2.5rem; color: #fff; margin-bottom: 1.5rem; letter-spacing: -0.02em;">Your vault is empty</h2>
                <p style="color: #64748b; max-width: 500px; margin: 0 auto 4rem; font-size: 1.2rem; line-height: 1.6;">Your collection awaits. Explore our masterworks to find your next musical signature.</p>
                <a href="/TuneTrove/user/shop/" class="btn btn-primary" style="padding: 1.25rem 4rem; font-weight: 800; font-size: 1rem; border-radius: 0.5rem; text-transform: uppercase; letter-spacing: 0.1em;">Browse Collection</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 400px; gap: 4rem; align-items: flex-start;">
                
                <!-- Items Table-like List -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 1rem; padding: 2.5rem; display: grid; grid-template-columns: 120px 1fr 140px 120px 40px; align-items: center; gap: 3rem; transition: all 0.3s;" onmouseover="this.style.borderColor='rgba(14, 165, 233, 0.2)'" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.05)'">
                            <!-- Image -->
                            <div style="height: 120px; background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 1rem;">
                                <?php if ($item['image_url']): ?>
                                    <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($item['image_url']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));">
                                <?php else: ?>
                                    <div style="font-size: 3rem; opacity: 0.2;">🎻</div>
                                <?php endif; ?>
                            </div>

                            <!-- Name & Brand -->
                            <div>
                                <p style="font-size: 0.75rem; font-weight: 800; color: var(--accent); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.1em;"><?php echo htmlspecialchars($item['brand']); ?></p>
                                <h3 style="font-family: var(--font-heading); font-size: 1.35rem; font-weight: 800; color: #fff; margin: 0; letter-spacing: -0.01em;">
                                    <a href="product.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($item['name']); ?></a>
                                </h3>
                            </div>

                            <!-- Price -->
                            <div style="text-align: right;">
                                <span style="font-size: 1.5rem; font-weight: 800; color: #fff;">$<?php echo number_format($item['price'], 2); ?></span>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['qty']; ?>" min="1" onchange="this.form.submit()" style="width: 100%; padding: 0.75rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; text-align: center; font-weight: 700; font-size: 1.1rem;">
                                </form>
                            </div>

                            <!-- Remove -->
                            <form action="cart.php" method="POST" style="text-align: right;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" style="background: none; border: none; color: #64748b; font-size: 2rem; cursor: pointer; padding: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'">&times;</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <aside style="position: sticky; top: 120px;">
                    <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; padding: 4rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                        <h2 style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 2rem; margin-bottom: 2.5rem; color: #fff; letter-spacing: -0.02em;">Order Summary</h2>
                        
                        <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 3rem;">
                            <div style="display: flex; justify-content: space-between; font-size: 1.05rem; color: #94a3b8;">
                                <span>Subtotal</span>
                                <span style="font-weight: 800; color: #fff;">$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 1.05rem; color: #94a3b8;">
                                <span>Shipping</span>
                                <span style="font-weight: 800; color: #4ade80;"><?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'COMPLIMENTARY'; ?></span>
                            </div>
                            <?php if ($shipping > 0): ?>
                                <div style="background: rgba(14, 165, 233, 0.05); border: 1px solid rgba(14, 165, 233, 0.1); padding: 1.25rem; border-radius: 0.5rem; font-size: 0.9rem; color: var(--primary); text-align: center; line-height: 1.4;">
                                    Add <strong>$<?php echo number_format(100 - $subtotal, 2); ?></strong> more for <strong>FREE Shipping</strong>!
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid rgba(255, 255, 255, 0.03); padding-top: 3rem; margin-bottom: 4rem;">
                            <span style="font-weight: 800; color: #64748b; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.1em; padding-bottom: 0.5rem;">Estimated Total</span>
                            <span style="font-size: 3rem; font-weight: 800; color: #fff; line-height: 1; letter-spacing: -0.04em;">$<?php echo number_format($total, 2); ?></span>
                        </div>

                        <a href="checkout.php" class="btn btn-primary" style="width: 100%; padding: 1.5rem; border-radius: 0.5rem; text-align: center; font-size: 1.1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; box-shadow: 0 15px 40px -10px rgba(14, 165, 233, 0.4);">Proceed to Checkout</a>
                        
                        <div style="margin-top: 2.5rem; text-align: center; color: #475569; font-size: 0.8rem;">
                            <p>Premium insurance & tracking included. Safe & secure payment.</p>
                        </div>
                    </div>

                    <!-- Trust Box -->
                    <div style="margin-top: 2rem; background: rgba(255, 255, 255, 0.01); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; padding: 2.5rem; display: flex; align-items: center; gap: 1.5rem;">
                        <span style="font-size: 2.5rem; filter: drop-shadow(0 0 15px rgba(14, 165, 233, 0.3));">🛡️</span>
                        <div>
                            <p style="font-weight: 800; font-size: 1rem; color: #fff; margin-bottom: 0.25rem;">Secure Acquisition</p>
                            <p style="font-size: 0.85rem; color: #64748b;">Enterprise-grade 256-bit encryption.</p>
                        </div>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
