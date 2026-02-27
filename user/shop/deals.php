<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Fetch products marked as deals
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.is_deal = 1
          ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll();
?>

<div class="deals-showroom" style="min-height: 100vh; background: var(--background); padding-top: 0.5rem; padding-bottom: 8rem;">
    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 2.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.03); padding-bottom: 1.5rem;">
            <p style="text-transform: uppercase; font-size: 0.8rem; font-weight: 800; color: var(--accent); letter-spacing: 0.3em; margin-bottom: 1rem;">Exclusive Archive</p>
            <h1 style="font-family: var(--font-heading); font-size: 4rem; letter-spacing: -0.04em; color: #fff; margin: 0;">Archive <span style="color: var(--primary);">Deals</span></h1>
        </div>

        <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 10rem 2rem; background: rgba(255, 255, 255, 0.01); border-radius: 2rem; border: 1px dashed rgba(255, 255, 255, 0.05);">
                <h2 style="font-family: var(--font-heading); font-size: 2.5rem; color: #fff; margin-bottom: 1.5rem; letter-spacing: -0.02em;">Curation in Progress</h2>
                <p style="color: #64748b; font-size: 1.1rem;">New archive deals are being appraised. Check back soon for the unveiling.</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 3rem; padding: 1.25rem 3rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 4px;">Browse Full Collection</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(600px, 1fr)); gap: 4rem; align-items: start;">
                <?php foreach ($products as $p): ?>
                    <div class="deal-card reveal" style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1.5rem; overflow: hidden; position: relative; transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: row; align-items: stretch;">
                        <!-- Discount Badge -->
                        <?php 
                        $savings = $p['price'] - $p['sale_price'];
                        $percent = round(($savings / $p['price']) * 100);
                        ?>
                        <div style="position: absolute; top: 2rem; left: 2rem; background: var(--primary); color: white; padding: 0.5rem 1rem; font-size: 0.75rem; font-weight: 900; letter-spacing: 0.1em; border-radius: 4px; z-index: 10;">
                            SAVE <?php echo $percent; ?>%
                        </div>

                        <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit; display: block; flex: 1;">
                            <div style="height: 100%; min-width: 320px; background: transparent; display: flex; align-items: center; justify-content: center; position: relative; border-bottom: none; overflow: hidden; padding: 2rem;">
                                <?php if ($p['image_url']): ?>
                                    <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($p['image_url']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.5)); transition: transform 0.8s ease;" class="deal-img">
                                <?php else: ?>
                                    <div style="font-size: 8rem; opacity: 0.1; filter: drop-shadow(0 0 20px var(--primary));">🎸</div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 3rem 3rem 0 3rem;">
                            <p style="font-size: 0.8rem; font-weight: 800; color: var(--accent); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1rem;"><?php echo htmlspecialchars($p['brand']); ?></p>
                            <h3 style="font-family: var(--font-heading); font-size: 1.75rem; color: #fff; margin-bottom: 2rem; line-height: 1.2; letter-spacing: -0.02em;"><?php echo htmlspecialchars($p['name']); ?></h3>
                            <div style="display: flex; align-items: baseline; gap: 1.5rem;">
                                <span style="font-size: 2.25rem; font-weight: 800; color: #fff; letter-spacing: -0.04em;">$<?php echo number_format($p['sale_price'], 2); ?></span>
                                <span style="font-size: 1.15rem; color: #64748b; text-decoration: line-through; font-weight: 600;">$<?php echo number_format($p['price'], 2); ?></span>
                            </div>
                            <div style="padding: 2rem 0 0 0;">
                                <form action="cart_actions.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary" style="padding: 1rem; border-radius: 4px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.75rem;">Add to Cart</button>
                                        <button type="submit" name="buy_now" value="1" style="padding: 1rem; border-radius: 4px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.75rem; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); color: #fff; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='rgba(235, 235, 235, 0.2)'" onmouseout="this.style.background='rgba(0,0,0,0.3)'; this.style.borderColor='rgba(255,255,255,0.05)'">Buy Now</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Make category cards on homepage link to their respective collection pages
window.addEventListener('DOMContentLoaded', function() {
    var categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(function(card) {
        var category = card.getAttribute('data-category');
        if (category) {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function() {
                window.location.href = '/TuneTrove/user/shop/collection.php?name=' + encodeURIComponent(category);
            });
        }
    });
});
</script>

<style>
.deal-card {
    display: flex;
    flex-direction: row;
    align-items: stretch;
}
.deal-card:hover .deal-img {
    transform: scale(1.1);
}
</style>

<?php require_once '../includes/footer.php'; ?>
