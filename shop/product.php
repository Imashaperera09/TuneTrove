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

// Check if current user can review (must have bought the product)
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

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$can_review) {
        redirect("product.php?id=$id", "You must purchase this product before reviewing.", "error");
    }
    if ($has_reviewed) {
        redirect("product.php?id=$id", "You have already reviewed this product.", "error");
    }

    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['review_text']);

    $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $user_id, $rating, $comment]);

    redirect("product.php?id=$id", "Thank you for your review!");
}
?>

<div class="product-gallery-view" style="min-height: 100vh; background: radial-gradient(circle at 100% 0%, rgba(37, 99, 235, 0.05), transparent), radial-gradient(circle at 0% 100%, rgba(168, 85, 247, 0.05), transparent); padding-top: 8rem; padding-bottom: 8rem;">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav style="margin-bottom: 3.5rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: var(--text-muted);">
            <a href="index.php" style="color: inherit; text-decoration: none; transition: color 0.3s;"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <span style="opacity: 0.3;">/</span>
            <span style="color: var(--primary);"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>

        <div style="display: grid; grid-template-columns: 1fr 1.1fr; gap: 6rem; align-items: start;">
            <!-- Product Visual Gallery -->
            <div style="position: sticky; top: 120px;">
                <div class="glass-panel" style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.05); border-radius: 3rem; overflow: hidden; position: relative; aspect-ratio: 1/1.1; display: flex; align-items: center; justify-content: center; box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);">
                    <?php if ($product['image_url']): ?>
                        <img src="/TuneTrove/assets/images/<?php echo htmlspecialchars($product['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="font-size: 10rem; transform: rotate(-15deg); filter: drop-shadow(0 20px 40px rgba(0,0,0,0.4));" class="floating-asset">🎸</div>
                    <?php endif; ?>
                    
                    <div style="position: absolute; bottom: 2rem; left: 2rem; display: flex; gap: 0.75rem;">
                        <span style="background: rgba(37, 99, 235, 0.1); backdrop-filter: blur(8px); border: 1px solid rgba(37, 99, 235, 0.3); color: var(--primary); padding: 0.5rem 1rem; border-radius: 999px; font-size: 0.7rem; font-weight: 800;">MASTERPIECE GRADE</span>
                        <?php if ($product['is_digital']): ?>
                            <span style="background: rgba(168, 85, 247, 0.1); backdrop-filter: blur(8px); border: 1px solid rgba(168, 85, 247, 0.3); color: #a855f7; padding: 0.5rem 1rem; border-radius: 999px; font-size: 0.7rem; font-weight: 800;">DIGITAL ASSET</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Product Intelligence -->
            <div>
                <div style="margin-bottom: 3.5rem;">
                    <p style="color: var(--primary); font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.75rem; margin-bottom: 1.25rem;"><?php echo htmlspecialchars($product['brand']); ?></p>
                    <h1 style="font-family: var(--font-heading); font-size: 4rem; line-height: 1; letter-spacing: -0.04em; color: #fff; margin-bottom: 1.5rem; text-shadow: 0 4px 10px rgba(0,0,0,0.2);"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div style="display: flex; align-items: center; gap: 2rem;">
                        <span style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; color: #fff;"><?php echo format_price($product['price']); ?></span>
                        <div style="height: 24px; width: 1px; background: rgba(255,255,255,0.1);"></div>
                        <?php if ($product['is_digital']): ?>
                            <span style="color: var(--success); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 8px; height: 8px; background: var(--success); border-radius: 50%; box-shadow: 0 0 10px var(--success);"></div> INSTANT ACCESS
                            </span>
                        <?php elseif ($product['stock_quantity'] > 0): ?>
                            <span style="color: var(--success); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 8px; height: 8px; background: var(--success); border-radius: 50%; box-shadow: 0 0 10px var(--success);"></div> IN STOCK (<?php echo $product['stock_quantity']; ?>)
                            </span>
                        <?php else: ?>
                            <span style="color: var(--error); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 8px; height: 8px; background: var(--error); border-radius: 50%; box-shadow: 0 0 10px var(--error);"></div> SOLD OUT
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="glass-panel" style="background: rgba(30, 41, 59, 0.2); border: 1px solid rgba(255,255,255,0.03); border-radius: 2rem; padding: 2.5rem; margin-bottom: 3.5rem;">
                    <h3 style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1rem;">Architectural details</h3>
                    <p style="color: rgba(255,255,255,0.7); line-height: 1.8; font-size: 1.05rem;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <form action="cart.php" method="POST" style="display: flex; gap: 1.5rem;">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <?php if (!$product['is_digital'] && $product['stock_quantity'] > 0): ?>
                        <div style="width: 120px; position: relative;">
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width: 100%; padding: 1.4rem; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.25rem; color: #fff; font-family: inherit; font-size: 1rem; font-weight: 800; text-align: center; outline: none;">
                            <span style="position: absolute; right: 1rem; top: 0.5rem; font-size: 0.6rem; color: var(--text-muted); font-weight: 800;">QTY</span>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1.4rem; border-radius: 1.25rem; font-weight: 800; font-size: 1rem; letter-spacing: 0.1em; text-transform: uppercase; box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.4);" <?php echo (!$product['is_digital'] && $product['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                        <?php echo $product['is_digital'] ? 'Acquire Digital Asset' : 'Add to Collection'; ?>
                    </button>
                </form>

                <div style="margin-top: 4rem; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
                    <div style="display: flex; gap: 1.25rem; align-items: center;">
                        <div style="width: 50px; height: 50px; background: rgba(30, 41, 59, 0.4); border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">🛰️</div>
                        <div>
                            <h4 style="font-size: 0.85rem; color: #fff; margin-bottom: 0.25rem;">Priority Transit</h4>
                            <p style="font-size: 0.75rem; color: var(--text-muted);">Secure Global Fulfillment</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1.25rem; align-items: center;">
                        <div style="width: 50px; height: 50px; background: rgba(30, 41, 59, 0.4); border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">💎</div>
                        <div>
                            <h4 style="font-size: 0.85rem; color: #fff; margin-bottom: 0.25rem;">Verified Authentic</h4>
                            <p style="font-size: 0.75rem; color: var(--text-muted);">Certified Masterpiece</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Engine -->
        <section style="margin-top: 10rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 4rem; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 2rem;">
                <div>
                    <h2 style="font-family: var(--font-heading); font-size: 2.5rem; letter-spacing: -0.02em; color: #fff;">Owner <span style="color: var(--primary);">Narratives</span></h2>
                    <p style="color: var(--text-muted); margin-top: 0.5rem;"><?php echo count($reviews); ?> performance reports from our elite community.</p>
                </div>
                <?php if ($can_review && !$has_reviewed): ?>
                    <button onclick="document.getElementById('review-form-panel').style.display='block'; this.style.display='none';" class="btn btn-primary" style="padding: 1rem 2rem; border-radius: 1rem; font-weight: 800; font-size: 0.85rem;">SUBMIT NARRATIVE</button>
                <?php endif; ?>
            </div>

            <?php if ($can_review && !$has_reviewed): ?>
                <div id="review-form-panel" style="display: none; background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(20px); padding: 4rem; border-radius: 3rem; border: 1px solid var(--primary); margin-bottom: 5rem; animation: authReveal 0.6s ease;">
                    <h3 style="font-family: var(--font-heading); font-size: 1.75rem; color: #fff; margin-bottom: 2.5rem;">Contribution Protocol</h3>
                    <form action="product.php?id=<?php echo $id; ?>" method="POST">
                        <div style="margin-bottom: 2.5rem;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.25rem;">Performance Rating</label>
                            <select name="rating" required style="width: 100%; max-width: 400px; padding: 1.25rem; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.25rem; color: #fff; font-family: inherit; font-weight: 700; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--primary)'">
                                <option value="5">EXCEPTIONAL (5/5)</option>
                                <option value="4">PRECISION (4/5)</option>
                                <option value="3">STANDARD (3/5)</option>
                                <option value="2">SUBPAR (2/5)</option>
                                <option value="1">DEFECTIVE (1/5)</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 3.5rem;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.25rem;">Detailed Intelligence</label>
                            <textarea name="review_text" required placeholder="Describe the sonic signature and build quality..." style="width: 100%; padding: 1.5rem; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.5rem; color: #fff; font-family: inherit; font-size: 1.05rem; min-height: 180px; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--primary)'"></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary" style="padding: 1.25rem 4rem; border-radius: 1.25rem; font-weight: 800; letter-spacing: 0.1em;">TRANSMIT NARRATIVE</button>
                    </form>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 2.5rem;">
                <?php if (empty($reviews)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 6rem; background: rgba(30, 41, 59, 0.1); border-radius: 2rem; color: var(--text-muted);">
                        No performance reports found. Be the first to verify this instrument.
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="glass-panel" style="background: rgba(30, 41, 59, 0.4); padding: 3rem; border-radius: 2.5rem; border: 1px solid rgba(255,255,255,0.05); transition: transform 0.3s ease;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                                <div>
                                    <div style="font-weight: 800; font-size: 1.1rem; color: #fff; margin-bottom: 0.25rem;">@<?php echo strtoupper(htmlspecialchars($rev['username'])); ?></div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></div>
                                </div>
                                <div style="color: var(--primary); font-size: 1.1rem; letter-spacing: 0.2em;">
                                    <?php echo str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']); ?>
                                </div>
                            </div>
                            <p style="color: rgba(255,255,255,0.8); line-height: 1.7; font-size: 1rem;"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<style>
@keyframes floating-asset {
    0%, 100% { transform: translateY(0) rotate(-15deg); }
    50% { transform: translateY(-20px) rotate(-10deg); }
}
.floating-asset { animation: floating-asset 4s ease-in-out infinite; }

@keyframes authReveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 1024px) {
    div[style*="grid-template-columns: 1fr 1.1fr"] {
        grid-template-columns: 1fr !important;
    }
    div[style*="position: sticky"] {
        position: static !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
