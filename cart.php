<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = (int)$_POST['product_id'];

    if ($action === 'add') {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        // Fetch product to verify stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            
            $_SESSION['msg'] = "Added " . htmlspecialchars($product['name']) . " to cart!";
            $_SESSION['msg_type'] = "success";
        }
    }

    if ($action === 'remove') {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['msg'] = "Item removed from cart.";
        $_SESSION['msg_type'] = "success";
    }

    if ($action === 'update') {
        $quantity = (int)$_POST['quantity'];
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        $_SESSION['msg'] = "Cart updated.";
        $_SESSION['msg_type'] = "success";
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

<div class="container" style="padding-top: 2rem;">
    <h1 style="font-family: var(--font-heading); margin-bottom: 2.5rem;">Shopping Cart</h1>

    <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 5rem; background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border);">
            <span style="font-size: 4rem; display: block; margin-bottom: 2rem;">🛒</span>
            <h2 style="margin-bottom: 1rem;">Your cart is empty</h2>
            <p style="color: var(--text-muted); margin-bottom: 2.5rem;">Looks like you haven't added anything to your cart yet.</p>
            <a href="/TuneTrove/shop/" class="btn btn-primary">Go to Shop</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 3rem; align-items: flex-start;">
            <!-- Cart Items List -->
            <div style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
                <div style="display: grid; gap: 2rem;">
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display: grid; grid-template-columns: 100px 1fr 120px 120px 40px; align-items: center; gap: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 2rem;">
                            <!-- Img Placeholder -->
                            <div style="width: 100px; height: 100px; background: #f1f5f9; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                🎸
                            </div>
                            
                            <!-- Product Info -->
                            <div>
                                <h3 style="font-family: var(--font-heading); font-size: 1.125rem; margin-bottom: 0.5rem;"><a href="shop/product.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                                <p style="font-size: 0.875rem; color: var(--text-muted);"><?php echo htmlspecialchars($item['brand']); ?> <?php echo $item['is_digital'] ? '(Digital)' : ''; ?></p>
                            </div>

                            <!-- Price -->
                            <div style="font-weight: 600; font-size: 1.125rem;">
                                <?php echo format_price($item['price']); ?>
                            </div>

                            <!-- Quantity -->
                            <div>
                                <?php if ($item['is_digital']): ?>
                                    <span style="color: var(--text-muted); font-size: 0.875rem;">Qty: 1</span>
                                <?php else: ?>
                                    <form action="cart.php" method="POST" style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="action" value="update">
                                        <input type="number" name="quantity" value="<?php echo $item['qty']; ?>" min="1" onchange="this.form.submit()" style="width: 60px; padding: 0.4rem; border-radius: 0.4rem; border: 1px solid var(--border);">
                                    </form>
                                <?php endif; ?>
                            </div>

                            <!-- Remove -->
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--error);">×</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 2rem;">
                    <a href="shop/" style="color: var(--primary); text-decoration: none; font-weight: 500;">← Continue Shopping</a>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <aside style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); position: sticky; top: 100px;">
                <h2 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 2rem;">Order Summary</h2>
                
                <div style="display: grid; gap: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                        <span>Subtotal</span>
                        <span><?php echo format_price($subtotal); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                        <span>Shipping</span>
                        <span><?php echo $shipping > 0 ? format_price($shipping) : 'FREE'; ?></span>
                    </div>
                    <?php if ($shipping > 0): ?>
                        <p style="font-size: 0.75rem; color: var(--primary); text-align: center;">Spend <?php echo format_price(100 - $subtotal); ?> more for free shipping!</p>
                    <?php endif; ?>
                </div>

                <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.5rem; margin-bottom: 2rem;">
                    <span>Total</span>
                    <span><?php echo format_price($total); ?></span>
                </div>

                <a href="checkout.php" class="btn btn-primary" style="width: 100%; padding: 1rem; text-align: center; font-size: 1.125rem;">Proceed to Checkout</a>
                
                <p style="margin-top: 1.5rem; font-size: 0.75rem; color: var(--text-muted); text-align: center;">Taxes and shipping calculated at checkout</p>
            </aside>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
