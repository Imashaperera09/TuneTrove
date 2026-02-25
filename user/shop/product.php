<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    redirect('index.php', 'Product not found.', 'error');
}

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('index.php', 'Product not found.', 'error');
}

// Get reviews
$rev_stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE product_id = ? ORDER BY created_at DESC");
$rev_stmt->execute([$id]);
$reviews = $rev_stmt->fetchAll();

// Check if current user can review
$can_review = false;
$has_reviewed = false;
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    
    // Check purchase
    $stmt = $pdo->prepare("SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status IN ('paid', 'shipped', 'completed')");
    $stmt->execute([$user_id, $id]);
    if ($stmt->fetch()) {
        $can_review = true;
    }

    // Check if already reviewed
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $id]);
    if ($stmt->fetch()) {
        $has_reviewed = true;
    }
}
?>

<div style="background: var(--background); min-height: 100vh; padding-top: 4rem; padding-bottom: 8rem;">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav style="margin-bottom: 3rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
            <a href="index.php" style="color: inherit; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='inherit'">Shop</a>
            <span style="opacity: 0.3;">/</span>
            <a href="categories.php" style="color: inherit; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='inherit'">Categories</a>
            <span style="opacity: 0.3;">/</span>
            <span style="color: var(--primary);"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <div style="display: grid; grid-template-columns: 1fr 450px; gap: 6rem; align-items: start;">
            <!-- Left: Image & Specs -->
            <div>
                <div style="background: rgba(0, 0, 0, 0.2); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; padding: 4rem; text-align: center; margin-bottom: 4rem; display: flex; align-items: center; justify-content: center; min-height: 500px;">
                    <?php if ($product['image_url']): ?>
                        <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($product['image_url']); ?>" style="max-width: 90%; height: auto; filter: drop-shadow(0 20px 50px rgba(0,0,0,0.5));">
                    <?php else: ?>
                        <div style="font-size: 14rem; opacity: 0.1; filter: drop-shadow(0 0 40px var(--primary));">🎻</div>
                    <?php endif; ?>
                </div>

                <div style="border-top: 1px solid rgba(255, 255, 255, 0.03); padding-top: 5rem;">
                    <h2 style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; color: #fff; margin-bottom: 2.5rem; letter-spacing: -0.03em;">Technical Details</h2>
                    <div style="background: rgba(255, 255, 255, 0.01); border: 1px solid rgba(255, 255, 255, 0.02); border-radius: 1rem; padding: 3.5rem;">
                        <p style="color: #94a3b8; line-height: 1.8; font-size: 1.15rem; white-space: pre-wrap;"><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Right: Action Box -->
            <aside style="position: sticky; top: 120px;">
                <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem; padding: 3.5rem; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                    <p style="color: var(--accent); font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.75rem; margin-bottom: 1.25rem;"><?php echo htmlspecialchars($product['brand']); ?></p>
                    <h1 style="font-family: var(--font-heading); font-size: 2.75rem; font-weight: 800; color: #fff; line-height: 1.15; margin-bottom: 2rem; letter-spacing: -0.02em;"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div style="margin-bottom: 3rem;">
                        <span style="font-size: 3.5rem; font-weight: 800; color: #fff; display: block; margin-bottom: 0.75rem; letter-spacing: -0.04em;">$<?php echo number_format($product['price'], 2); ?></span>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span style="background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.2); padding: 0.4rem 0.8rem; border-radius: 4px; font-weight: 800; font-size: 0.75rem; letter-spacing: 0.1em; display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 6px; height: 6px; background: #4ade80; border-radius: 50%;"></div> IN STOCK
                                </span>
                            <?php else: ?>
                                <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 0.4rem 0.8rem; border-radius: 4px; font-weight: 800; font-size: 0.75rem; letter-spacing: 0.1em;">OUT OF STOCK</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form action="cart_actions.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        
                        <div style="margin-bottom: 2rem;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 0.75rem; letter-spacing: 0.1em;">Select Quantity</label>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width: 100%; padding: 1.15rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; background: rgba(0, 0, 0, 0.2); color: #fff; font-weight: 700; font-size: 1.1rem; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255, 255, 255, 0.05)'">
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.5rem; border-radius: 0.5rem; font-size: 1.1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; box-shadow: 0 15px 40px -10px rgba(14, 165, 233, 0.4);" <?php echo $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                            Add to Cart
                        </button>
                    </form>
                    
                    <div style="margin-top: 4rem; padding-top: 3rem; border-top: 1px solid rgba(255, 255, 255, 0.03);">
                         <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 2rem;">
                             <div style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🚚</div>
                             <div>
                                 <p style="font-weight: 800; font-size: 0.95rem; color: #fff; margin-bottom: 0.25rem;">Global Transit</p>
                                 <p style="font-size: 0.85rem; color: #64748b;">Premium climate-controlled shipping.</p>
                             </div>
                         </div>
                         <div style="display: flex; gap: 1.5rem; align-items: center;">
                             <div style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">🛡️</div>
                             <div>
                                 <p style="font-weight: 800; font-size: 0.95rem; color: #fff; margin-bottom: 0.25rem;">2-Year Warranty</p>
                                 <p style="font-size: 0.85rem; color: #64748b;">Full coverage on all masterpieces.</p>
                             </div>
                         </div>
                    </div>
                </div>

                <div style="margin-top: 2rem; background: var(--primary); color: white; border-radius: 1rem; padding: 2.5rem; display: flex; align-items: center; gap: 2rem; box-shadow: 0 10px 30px rgba(14, 165, 233, 0.2);">
                    <div style="font-size: 3rem;">🎧</div>
                    <div>
                        <p style="font-weight: 800; font-size: 1rem; margin-bottom: 0.25rem;">Speak with a Luthier</p>
                        <p style="font-size: 0.85rem; opacity: 0.9;">Professional consultation (800) 222-4700</p>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Reviews -->
        <div style="margin-top: 10rem;">
             <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 2.5rem;">
                 <div>
                    <h2 style="font-family: var(--font-heading); font-size: 3rem; font-weight: 800; color: #fff; margin-top: 0; letter-spacing: -0.04em;">Performance <span style="color: var(--primary);">Reports</span></h2>
                    <p style="color: #64748b; font-size: 1.1rem;"><?php echo count($reviews); ?> curated insights from world-class musicians.</p>
                 </div>
             </div>

             <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
                <?php if (empty($reviews)): ?>
                    <p style="grid-column: 1 / -1; color: #888; font-style: italic;">Be the first to share your experience with this instrument.</p>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div style="background: rgba(255, 255, 255, 0.01); padding: 3rem; border-radius: 1rem; border: 1px solid rgba(255, 255, 255, 0.03);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 1.5rem;">
                                <div>
                                    <span style="font-weight: 800; color: #fff; font-size: 1.1rem;">@<?php echo htmlspecialchars($rev['username']); ?></span>
                                    <p style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.25rem;"><?php echo date('M Y', strtotime($rev['created_at'])); ?></p>
                                </div>
                                <div style="color: var(--primary); font-size: 1.25rem; letter-spacing: 0.2em;">
                                    <?php echo str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']); ?>
                                </div>
                            </div>
                            <p style="color: #94a3b8; line-height: 1.8; font-size: 1.1rem; font-style: italic;">"<?php echo nl2br(htmlspecialchars($rev['comment'])); ?>"</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
             </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
