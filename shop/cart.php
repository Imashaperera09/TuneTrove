<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

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

<div class="cart-stage" style="min-height: 100vh; background: radial-gradient(circle at 10% 10%, rgba(37, 99, 235, 0.05), transparent), radial-gradient(circle at 90% 90%, rgba(168, 85, 247, 0.05), transparent); padding-top: 8rem; padding-bottom: 8rem;">
    <div class="container">
        <header style="margin-bottom: 5rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-family: var(--font-heading); font-size: 3.5rem; letter-spacing: -0.05em; margin-bottom: 0.5rem;">Your <span style="color: var(--primary);">Acquisition</span></h1>
                <p style="color: var(--text-muted); font-size: 1.1rem;">Review your selected masterpieces before checkout.</p>
            </div>
            <a href="shop/" style="background: rgba(255,255,255,0.05); padding: 0.75rem 1.5rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.1); color: #fff; text-decoration: none; font-size: 0.8rem; font-weight: 800; letter-spacing: 0.05em;">← CONTINUE BROWSING</a>
        </header>

        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 10rem 2rem; background: rgba(30, 41, 59, 0.2); border-radius: 4rem; border: 1px dashed rgba(255, 255, 255, 0.05); animation: authReveal 1s ease;">
                <div style="font-size: 5rem; margin-bottom: 2.5rem; opacity: 0.3;">📦</div>
                <h2 style="font-family: var(--font-heading); font-size: 2.5rem; color: #fff; margin-bottom: 1rem;">Vault is Empty</h2>
                <p style="color: var(--text-muted); max-width: 450px; margin: 0 auto 3.5rem; font-size: 1.1rem;">Explore our curated collection of world-class instruments to find your perfect sonic signature.</p>
                <a href="shop/" class="btn btn-primary" style="padding: 1.25rem 3.5rem; border-radius: 1.5rem; font-weight: 800; letter-spacing: 0.05em;">ENTER SHOWROOM</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 1fr 400px; gap: 4rem; align-items: flex-start;">
                <!-- Acquisition List -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="glass-panel" style="background: rgba(30, 41, 59, 0.3); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 2.5rem; padding: 2.5rem; display: grid; grid-template-columns: 120px 1fr 150px 100px 40px; align-items: center; gap: 2.5rem; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateX(10px)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.05)'; this.style.transform='none'">
                            <!-- Item Visual -->
                            <div style="aspect-ratio: 1/1; background: rgba(15, 23, 42, 0.5); border-radius: 1.5rem; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                <?php if ($item['image_url']): ?>
                                    <img src="/TuneTrove/assets/images/<?php echo htmlspecialchars($item['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <span style="font-size: 2.5rem;">🎸</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Item Identity -->
                            <div>
                                <div style="font-size: 0.65rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['brand']); ?></div>
                <h3 style="font-family: var(--font-heading); font-size: 1.125rem; margin-bottom: 0.5rem;"><a href="product.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($item['name']); ?></a></h3>
                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;"><?php echo $item['is_digital'] ? 'Digital Performance Asset' : 'Physical Professional Grade'; ?></div>
                            </div>

                            <!-- Price Point -->
                            <div style="text-align: right;">
                                <div style="font-family: var(--font-heading); font-weight: 800; font-size: 1.5rem; color: #fff;"><?php echo format_price($item['price']); ?></div>
                                <span style="font-size: 0.6rem; color: var(--text-muted); font-weight: 700;">UNIT PRICE</span>
                            </div>

                            <!-- Allocation -->
                            <div>
                                <?php if ($item['is_digital']): ?>
                                    <div style="padding: 0.5rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 0.75rem; text-align: center; color: var(--text-muted); font-size: 0.8rem; font-weight: 800;">1</div>
                                <?php else: ?>
                                    <form action="cart.php" method="POST" style="position: relative;">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="action" value="update">
                                        <input type="number" name="quantity" value="<?php echo $item['qty']; ?>" min="1" onchange="this.form.submit()" style="width: 100%; padding: 0.85rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 1rem; color: #fff; font-family: inherit; font-size: 1rem; font-weight: 800; text-align: center; outline: none;">
                                    </form>
                                <?php endif; ?>
                            </div>

                            <!-- Expel -->
                            <form action="cart.php" method="POST" style="text-align: right;">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--error); width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-family: inherit; font-weight: 800; font-size: 0.75rem; transition: all 0.2s;">×</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Acquisition Intelligence -->
                <aside style="position: sticky; top: 120px;">
                    <div class="glass-panel" style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(20px); padding: 3rem; border-radius: 3rem; border: 1px solid rgba(255, 255, 255, 0.05); box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                        <h2 style="font-family: var(--font-heading); font-size: 1.75rem; color: #fff; margin-bottom: 2.5rem; letter-spacing: -0.02em;">Acquisition Summary</h2>
                        
                        <div style="display: grid; gap: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 2.5rem; margin-bottom: 2.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Subtotal Allocation</span>
                                <span style="font-weight: 700; color: #fff; font-size: 1.1rem;"><?php echo format_price($subtotal); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Priority Fulfillment</span>
                                <span style="font-weight: 700; color: #fff; font-size: 1.1rem;"><?php echo $shipping > 0 ? format_price($shipping) : '<span style="color: var(--success);">FREE</span>'; ?></span>
                            </div>
                            <?php if ($shipping > 0): ?>
                                <div style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.1); padding: 1rem; border-radius: 1rem; text-align: center;">
                                    <p style="font-size: 0.7rem; color: var(--primary); font-weight: 800; text-transform: uppercase;">Allocation needed for VIP fulfillment: <span style="color: #fff;"><?php echo format_price(100 - $subtotal); ?></span></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3.5rem;">
                            <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Total Investment</span>
                            <span style="font-family: var(--font-heading); font-weight: 800; font-size: 2.5rem; color: #fff; line-height: 1;"><?php echo format_price($total); ?></span>
                        </div>

                        <a href="checkout.php" class="btn btn-primary" style="width: 100%; padding: 1.5rem; border-radius: 1.5rem; text-align: center; font-size: 1rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.4);">AUTHORIZE ACQUISITION</a>
                        
                        <div style="margin-top: 2.5rem; text-align: center;">
                            <p style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; line-height: 1.6;">Taxes and verified fulfillment protocols are calculated during the authorization phase.</p>
                        </div>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@keyframes authReveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  opacity: 1;
}
@media (max-width: 1024px) {
    div[style*="grid-template-columns: 1fr 400px"] {
        grid-template-columns: 1fr !important;
    }
    aside {
        position: static !important;
    }
}
</style>

<?php require_once '../includes/header.php'; ?>
<?php // Relative change: the original file at the end was including footer
require_once '../includes/footer.php'; ?>
