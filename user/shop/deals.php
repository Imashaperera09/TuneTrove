<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$today = date('Y-m-d');

// Fetch active deals only
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_deal = 1 
      AND p.discount_percent > 0
      AND (p.deal_start_date IS NULL OR p.deal_start_date <= '$today')
      AND (p.deal_end_date IS NULL OR p.deal_end_date >= '$today')
    ORDER BY p.discount_percent DESC
");
$products = $stmt->fetchAll();
?>

<style>
/* Deals page styles */
.deal-card { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.deal-card:hover { transform: translateY(-4px); box-shadow: 0 40px 80px -20px rgba(0,0,0,0.5); }
.deal-card:hover .deal-img { transform: scale(1.06); }
.countdown-unit { display: flex; flex-direction: column; align-items: center; min-width: 48px; }
.countdown-num { font-family: var(--font-heading); font-size: 1.5rem; font-weight: 800; color: #fff; }
.countdown-lbl { font-size: 0.6rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 2px; }
</style>

<div style="background: var(--background); min-height: 100vh; padding-top: 2rem; padding-bottom: 8rem;">
    <div class="container">

        <!-- Header -->
        <div style="margin-bottom: 2.5rem; border-bottom: 1px solid rgba(255,255,255,0.04); padding-bottom: 1.5rem;">
            <p style="text-transform: uppercase; font-size: 0.75rem; font-weight: 800; color: var(--accent); letter-spacing: 0.3em; margin-bottom: 0.75rem;">Limited Time Offers</p>
            <h1 style="font-family: var(--font-heading); font-size: 3.5rem; letter-spacing: -0.04em; color: #fff; margin: 0;">Today's <span style="color: var(--primary);">Deals</span></h1>
            <p style="color: #64748b; margin-top: 0.75rem; font-size: 1rem;">Exclusive discounts — updated regularly.</p>
        </div>

        <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 8rem 2rem; background: rgba(255,255,255,0.01); border-radius: 2rem; border: 1px dashed rgba(255,255,255,0.05);">
                <div style="font-size: 4rem; margin-bottom: 1.5rem;">🏷️</div>
                <h2 style="font-family: var(--font-heading); font-size: 2rem; color: #fff; margin-bottom: 1rem;">No Active Deals Right Now</h2>
                <p style="color: #64748b; font-size: 1rem; margin-bottom: 2.5rem;">Check back soon — new exclusive deals are added regularly.</p>
                <a href="index.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-weight: 700; text-transform: uppercase; border-radius: 4px;">Browse All Products</a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <?php foreach ($products as $p):
                    $deal_price  = round($p['price'] * (1 - $p['discount_percent'] / 100), 2);
                    $savings     = $p['price'] - $deal_price;
                    $end_date    = $p['deal_end_date'];
                    $ends_soon   = $end_date && (strtotime($end_date) - strtotime($today)) <= 7 * 86400 && strtotime($end_date) >= strtotime($today);
                ?>
                <div class="deal-card" style="background: var(--surface); border: 1px solid rgba(255,255,255,0.04); border-radius: 1.5rem; overflow: hidden; display: flex; flex-direction: row; align-items: stretch;">

                    <!-- Image -->
                    <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; flex: 0 0 380px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.25); padding: 2.5rem; position: relative; overflow: hidden;">
                        <?php if ($p['image_url']): ?>
                            <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($p['image_url']); ?>" class="deal-img" style="max-width: 100%; max-height: 280px; object-fit: contain; filter: drop-shadow(0 20px 40px rgba(0,0,0,0.5)); transition: transform 0.6s ease;">
                        <?php else: ?>
                            <div style="font-size: 8rem; opacity: 0.1;">🎸</div>
                        <?php endif; ?>
                        <!-- Discount Badge -->
                        <div style="position: absolute; top: 1.5rem; left: 1.5rem; background: var(--primary); color: #fff; padding: 0.4rem 1rem; font-size: 0.8rem; font-weight: 900; letter-spacing: 0.1em; border-radius: 999px; z-index: 10;">
                            SAVE <?php echo (int)$p['discount_percent']; ?>%
                        </div>
                    </a>

                    <!-- Info -->
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 3rem;">
                        <p style="font-size: 0.75rem; font-weight: 800; color: var(--accent); text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($p['brand']); ?></p>
                        <h2 style="font-family: var(--font-heading); font-size: 1.75rem; color: #fff; margin-bottom: 1.5rem; line-height: 1.2; letter-spacing: -0.02em;"><?php echo htmlspecialchars($p['name']); ?></h2>

                        <!-- Pricing -->
                        <div style="display: flex; align-items: baseline; gap: 1.5rem; margin-bottom: 0.5rem;">
                            <span style="font-size: 2.25rem; font-weight: 800; color: #fff; letter-spacing: -0.04em;">£<?php echo number_format($deal_price, 2); ?></span>
                            <span style="font-size: 1.1rem; color: #475569; text-decoration: line-through; font-weight: 600;">£<?php echo number_format($p['price'], 2); ?></span>
                        </div>
                        <p style="color: #4ade80; font-size: 0.85rem; font-weight: 700; margin-bottom: 2rem;">You save £<?php echo number_format($savings, 2); ?></p>

                        <!-- Countdown if ending soon -->
                        <?php if ($ends_soon): ?>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.15); border-radius: 0.75rem; padding: 1rem 1.5rem;">
                            <span style="font-size: 1.1rem;">⏳</span>
                            <span style="font-size: 0.8rem; color: #ef4444; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;">Deal ends <?php echo date('M j', strtotime($end_date)); ?></span>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;" id="countdown-<?php echo $p['id']; ?>" data-end="<?php echo $end_date; ?>T23:59:59">
                                <div class="countdown-unit"><span class="countdown-num cd-h">--</span><span class="countdown-lbl">hrs</span></div>
                                <span style="color:#ef4444; font-weight:800;">:</span>
                                <div class="countdown-unit"><span class="countdown-num cd-m">--</span><span class="countdown-lbl">min</span></div>
                                <span style="color:#ef4444; font-weight:800;">:</span>
                                <div class="countdown-unit"><span class="countdown-num cd-s">--</span><span class="countdown-lbl">sec</span></div>
                            </div>
                        </div>
                        <?php elseif ($end_date): ?>
                        <p style="color: #64748b; font-size: 0.8rem; margin-bottom: 2rem;">Deal valid until <?php echo date('M j, Y', strtotime($end_date)); ?></p>
                        <?php endif; ?>

                        <!-- Actions -->
                        <form action="cart_actions.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" name="add_to_cart" class="btn btn-primary" style="padding: 0.85rem 2rem; border-radius: 4px; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.08em;">Add to Cart</button>
                                <button type="submit" name="buy_now" value="1" style="padding: 0.85rem 2rem; border-radius: 4px; font-weight: 700; text-transform: uppercase; font-size: 0.8rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); color: #fff; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">Buy Now</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Countdown timers
document.querySelectorAll('[id^="countdown-"]').forEach(function(el) {
    var endTime = new Date(el.getAttribute('data-end')).getTime();
    function update() {
        var now = new Date().getTime();
        var diff = endTime - now;
        if (diff <= 0) { el.innerHTML = '<span style="color:#ef4444;font-weight:800;font-size:0.85rem;">Ended</span>'; return; }
        var h = Math.floor(diff / 3600000);
        var m = Math.floor((diff % 3600000) / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        el.querySelector('.cd-h').textContent = String(h).padStart(2,'0');
        el.querySelector('.cd-m').textContent = String(m).padStart(2,'0');
        el.querySelector('.cd-s').textContent = String(s).padStart(2,'0');
    }
    update();
    setInterval(update, 1000);
});
</script>

<?php require_once '../includes/footer.php'; ?>
