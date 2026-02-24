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

<div class="container" style="padding-top: 2rem;">
    <nav style="margin-bottom: 2rem; font-size: 0.875rem; color: var(--text-muted);">
        <a href="index.php" style="color: inherit; text-decoration: none;">Shop</a> &nbsp;/&nbsp;
        <a href="index.php?cat=<?php echo urlencode($product['category_name']); ?>" style="color: inherit; text-decoration: none;"><?php echo htmlspecialchars($product['category_name']); ?></a> &nbsp;/&nbsp;
        <span style="color: var(--text);"><?php echo htmlspecialchars($product['name']); ?></span>
    </nav>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; margin-bottom: 5rem;">
        <!-- Product Image -->
        <div style="background: white; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; display: flex; align-items: center; justify-content: center; height: 500px;">
             <span style="font-size: 8rem;">🎸</span>
        </div>

        <!-- Product Info -->
        <div>
            <p style="color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;"><?php echo htmlspecialchars($product['brand']); ?></p>
            <h1 style="font-family: var(--font-heading); font-size: 3rem; line-height: 1.1; margin-bottom: 1.5rem;"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <span style="font-size: 2rem; font-weight: 800; color: var(--text);"><?php echo format_price($product['price']); ?></span>
                <?php if ($product['is_digital']): ?>
                     <span style="background: var(--accent); color: white; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: bold;">DIGITAL DOWNLOAD</span>
                <?php elseif ($product['stock_quantity'] > 0): ?>
                     <span style="color: var(--success); font-weight: 600;">● In Stock (<?php echo $product['stock_quantity']; ?> left)</span>
                <?php else: ?>
                     <span style="color: var(--error); font-weight: 600;">● Out of Stock</span>
                <?php endif; ?>
            </div>

            <div style="padding: 2rem; background: #f8fafc; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 2.5rem;">
                <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text);">Product Description</h3>
                <p style="color: var(--text-muted); line-height: 1.7;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <form action="../cart.php" method="POST" style="display: flex; gap: 1rem;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="action" value="add">
                
                <?php if (!$product['is_digital']): ?>
                    <div style="width: 100px;">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 1rem;" <?php echo (!$product['is_digital'] && $product['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                    <?php echo $product['is_digital'] ? 'Purchase Download' : 'Add to Shopping Cart'; ?>
                </button>
            </form>

            <div style="margin-top: 3rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 1.5rem;">🚚</span>
                    <div>
                        <h4 style="font-size: 0.875rem;">Fast Shipping</h4>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">Free over £100</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 1.5rem;">🛡️</span>
                    <div>
                        <h4 style="font-size: 0.875rem;">Secure Checkout</h4>
                        <p style="font-size: 0.75rem; color: var(--text-muted);">100% Secure Payment</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <section>
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem;">
            <h2 style="font-family: var(--font-heading);">Customer Reviews</h2>
            <?php if ($can_review && !$has_reviewed): ?>
                <button onclick="document.getElementById('review-form').style.display='block'; this.style.display='none';" class="btn btn-primary">Write a Review</button>
            <?php endif; ?>
        </div>

        <?php if ($can_review && !$has_reviewed): ?>
            <div id="review-form" style="display: none; background: var(--surface); padding: 3rem; border-radius: var(--radius); border: 2px solid var(--primary); margin-bottom: 3rem;">
                <h3 style="margin-bottom: 2rem;">Share your thoughts</h3>
                <form action="product.php?id=<?php echo $id; ?>" method="POST">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Rating</label>
                        <select name="rating" required style="width: 200px; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); background: white;">
                            <option value="5">5 Stars - Excellent</option>
                            <option value="4">4 Stars - Very Good</option>
                            <option value="3">3 Stars - Good</option>
                            <option value="2">2 Stars - Poor</option>
                            <option value="1">1 Star - Terrible</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 2rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Your Experience</label>
                        <textarea name="review_text" required placeholder="What did you like or dislike about this instrument?" style="width: 100%; padding: 1rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit; min-height: 150px;"></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-primary" style="padding: 1rem 3rem;">Submit My Review</button>
                </form>
            </div>
        <?php elseif ($has_reviewed): ?>
             <div style="background: #f1f5f9; padding: 1rem 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; color: var(--text-muted); font-size: 0.875rem;">
                You have already shared your feedback for this product. Thank you!
             </div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <p style="color: var(--text-muted); italic; background: white; padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border);">No reviews yet. Be the first to share your experience!</p>
        <?php else: ?>
            <div style="display: grid; gap: 1.5rem;">
                <?php foreach ($reviews as $rev): ?>
                    <div style="background: white; padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span style="font-weight: 700;">@<?php echo htmlspecialchars($rev['username']); ?></span>
                            <span style="color: var(--accent);">
                                <?php echo str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']); ?>
                            </span>
                        </div>
                        <p style="color: var(--text-muted);"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
                        <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 1rem;"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once '../includes/footer.php'; ?>
