<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch main categories...
try {
    $order = ['Guitars', 'Keyboards', 'Drums & Percussion', 'Wind Instruments', 'String Instruments', 'Accessories', 'Digital Sheet Music'];
    $placeholders = implode(',', array_fill(0, count($order), '?'));
    $sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY FIELD(name, $placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($order);
    $categories = $stmt->fetchAll();
} catch (PDOException $e) { $categories = []; }

// Fetch active deals for Featured Section
$today = date('Y-m-d');
$deals_stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.is_deal=1 AND p.discount_percent > 0 AND (p.deal_start_date IS NULL OR p.deal_start_date <= '$today') AND (p.deal_end_date IS NULL OR p.deal_end_date >= '$today') ORDER BY p.discount_percent DESC LIMIT 3");
$featured_deals = $deals_stmt->fetchAll();

// Fetch recent orders if logged in
$recent_orders = [];
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
    $orders_stmt->execute([$user_id]);
    $recent_orders = $orders_stmt->fetchAll();
}
?>

<!-- Blue Premium Hero Section -->
<section class="marketing-hero" style="background: radial-gradient(circle at 0% 0%, #002d5a 0%, var(--background) 100%); padding: 3rem 0; overflow: hidden; color: white; position: relative; border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('/TuneTrove/user/assets/images/mesh-glow.png') no-repeat center center/cover; opacity: 0.1; pointer-events: none;"></div>
    <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; position: relative; z-index: 2;">
        <div class="reveal">
            <p style="text-transform: uppercase; font-size: 0.85rem; font-weight: 800; color: var(--accent); letter-spacing: 0.3em; margin-bottom: 1.5rem;">The Premium Experience</p>
            <h1 style="font-family: var(--font-heading); font-size: 5rem; font-weight: 800; line-height: 1; margin-bottom: 2.5rem; letter-spacing: -0.04em;">Elevate Your <br><span style="color: var(--primary); text-shadow: 0 0 30px rgba(14, 165, 233, 0.3);">Sonic</span> Signature</h1>
            <p style="font-size: 1.25rem; margin-bottom: 3.5rem; color: #94a3b8; line-height: 1.6; max-width: 550px;">Hand-selected masterpieces from the world's most prestigious luthiers. Precision-crafted for the most discerning musicians.</p>
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                <a href="/TuneTrove/user/shop/" class="btn" style="background: var(--primary); color: white; padding: 1.25rem 3.5rem; font-size: 1.1rem; font-weight: 800; border-radius: 4px; text-transform: uppercase; box-shadow: 0 10px 30px rgba(14, 165, 233, 0.4);">Browse Collection</a>
            </div>
        </div>
        <div class="reveal" style="position: relative; height: 350px; display: flex; align-items: center; justify-content: center;">
            <div style="position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, transparent 70%);"></div>
            <?php if (file_exists('assets/images/hero.png')): ?>
                <img src="/TuneTrove/user/assets/images/hero.png" style="width: 100%; transform: rotate(-5deg); filter: drop-shadow(0 40px 100px rgba(0,0,0,0.6));" alt="Premium Instrument">
            <?php else: ?>
                <div style="font-size: 14rem; opacity: 0.3; filter: drop-shadow(0 0 50px var(--primary));">🎻</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (!empty($featured_deals)): ?>
<!-- Featured Deals Section -->
<section style="padding: 6rem 0; background: #060b1e; position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; right: 0; width: 600px; height: 600px; background: radial-gradient(circle, rgba(14, 165, 233, 0.05) 0%, transparent 70%); pointer-events: none;"></div>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 4rem;">
            <div>
                <p style="text-transform: uppercase; font-size: 0.75rem; font-weight: 800; color: var(--accent); letter-spacing: 0.3em; margin-bottom: 1rem;">Limited Time</p>
                <h2 style="font-family: var(--font-heading); font-size: 3.5rem; font-weight: 800; color: #fff; letter-spacing: -0.04em; margin: 0;">Featured <span style="color: var(--primary);">Deals</span></h2>
            </div>
            <a href="/TuneTrove/user/shop/deals.php" class="btn btn-primary" style="padding: 1rem 2.5rem; border-radius: 4px; font-weight: 700; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 0.1em; box-shadow: 0 10px 20px rgba(14, 165, 233, 0.2);">Explore All Deals</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2.5rem;">
            <?php foreach ($featured_deals as $d): 
                $deal_price = round($d['price'] * (1 - $d['discount_percent'] / 100), 2);
            ?>
            <div class="reveal" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 1.5rem; overflow: hidden; display: flex; transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.transform='translateY(-10px)'; this.style.borderColor='rgba(14, 165, 233, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255, 255, 255, 0.05)';" >
                <div style="width: 150px; background: rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; padding: 1.5rem; position: relative;">
                    <div style="position: absolute; top: 0.75rem; left: 0.75rem; background: var(--primary); color: #fff; padding: 0.2rem 0.5rem; font-size: 0.6rem; font-weight: 900; border-radius: 4px; z-index: 5;">
                        -<?php echo (int)$d['discount_percent']; ?>%
                    </div>
                    <?php if ($d['image_url']): ?>
                        <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($d['image_url']); ?>" style="max-width: 100%; max-height: 120px; object-fit: contain; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.4));">
                    <?php else: ?>
                        <div style="font-size: 4rem; opacity: 0.2;">🎻</div>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; padding: 2rem; display: flex; flex-direction: column; justify-content: center;">
                    <p style="font-size: 0.7rem; font-weight: 800; color: var(--accent); text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.1em;"><?php echo htmlspecialchars($d['brand']); ?></p>
                    <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 1rem; line-height: 1.3;"><?php echo htmlspecialchars($d['name']); ?></h3>
                    <div style="display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.5rem;">
                        <span style="font-size: 1.5rem; font-weight: 800; color: #fff;">£<?php echo number_format($deal_price, 2); ?></span>
                        <span style="font-size: 0.9rem; color: #64748b; text-decoration: line-through;">£<?php echo number_format($d['price'], 2); ?></span>
                    </div>
                    <a href="/TuneTrove/user/shop/product.php?id=<?php echo $d['id']; ?>" style="text-align: center; background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; padding: 0.75rem; border-radius: 4px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.08); transition: all 0.2s;" onmouseover="this.style.background='var(--primary)'; this.style.borderColor='var(--primary)';" onmouseout="this.style.background='rgba(255,255,255,0.03)'; this.style.borderColor='rgba(255,255,255,0.08)';">View Deal</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (is_logged_in() && !empty($recent_orders)): ?>
<!-- Recent Activity Section -->
<section style="padding: 4rem 0; background: #080d21; border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;">
            <div>
                <p style="text-transform: uppercase; font-size: 0.7rem; font-weight: 800; color: var(--accent); letter-spacing: 0.2em; margin-bottom: 0.75rem;">Your Journey</p>
                <h2 style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; color: #fff; letter-spacing: -0.03em;">Recent <span style="color: var(--primary);">Acquisitions</span></h2>
            </div>
            <a href="/TuneTrove/user/account/index.php" style="color: #64748b; text-decoration: none; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid rgba(100, 116, 139, 0.3); padding-bottom: 4px; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#64748b'">View All Orders</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
            <?php foreach ($recent_orders as $order): ?>
                <a href="/TuneTrove/user/account/view_order.php?id=<?php echo $order['id']; ?>" style="text-decoration: none; display: block;">
                    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 1rem; padding: 2rem; transition: all 0.3s; position: relative; overflow: hidden;" onmouseover="this.style.background='rgba(14, 165, 233, 0.03)'; this.style.borderColor='rgba(14, 165, 233, 0.2)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.02)'; this.style.borderColor='rgba(255, 255, 255, 0.05)';">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                            <div>
                                <p style="font-size: 0.7rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.25rem;">Order Reference</p>
                                <p style="font-family: var(--font-heading); font-weight: 800; color: #fff; font-size: 1.25rem;">TT-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                            </div>
                            <span style="padding: 0.25rem 0.6rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 4px; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; color: #fff; letter-spacing: 0.05em;">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <p style="color: #64748b; font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                            </div>
                            <p style="font-weight: 800; color: var(--primary); font-size: 1.25rem;">$<?php echo number_format($order['total_amount'], 2); ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories Section -->
<section style="padding: 6rem 0 6rem; background: #060b1e;">
    <div class="container">
        <div style="margin-bottom: 6rem; text-align: left; border-left: 4px solid var(--primary); padding-left: 2.5rem;">
            <h2 class="reveal" style="font-family: var(--font-heading); font-size: 4rem; font-weight: 800; color: #fff; margin-bottom: 1rem; letter-spacing: -0.04em;">Shop by Category</h2>
            <p style="font-size: 1.25rem; color: #94a3b8;">Explore our vast selection of premium musical instruments and catalog.</p>
        </div>

        <style>
        @keyframes scrollHorizontal {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-50% - 1rem)); } /* -50% of the total width including gap */
        }
        .category-marquee-container {
            width: 100%;
            overflow: hidden;
            position: relative;
            padding: 2rem 0;
            display: flex;
        }
        .category-marquee {
            display: flex;
            gap: 2rem;
            animation: scrollHorizontal 40s linear infinite;
            width: max-content;
        }
        .category-marquee:hover {
            animation-play-state: paused;
        }
        .category-card-mini {
            width: 260px; /* Reduced size */
            flex-shrink: 0;
        }
        /* Fade edges for slick look */
        .category-marquee-container::before,
        .category-marquee-container::after {
            content: "";
            position: absolute;
            top: 0;
            width: 10%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
        }
        .category-marquee-container::before {
            left: 0;
            background: linear-gradient(to right, #060b1e 0%, transparent 100%);
        }
        .category-marquee-container::after {
            right: 0;
            background: linear-gradient(to left, #060b1e 0%, transparent 100%);
        }
        </style>

        <div class="category-marquee-container">
            <div class="category-marquee">
                <?php 
                $icons = [
                    'Guitars' => '🎸',
                    'Keyboards' => '🎹',
                    'Drums & Percussion' => '🥁',
                    'Wind Instruments' => '🎷',
                    'String Instruments' => '🎻',
                    'Accessories' => '🔌',
                    'Digital Sheet Music' => '📑'
                ];
                // Duplicate array for seamless looping
                $displayCategories = array_merge($categories, $categories);
                ?>
                <?php if (!empty($displayCategories)): ?>
                    <?php foreach ($displayCategories as $cat): ?>
                        <a href="/TuneTrove/user/shop/collection.php?name=<?php echo urlencode($cat['name']); ?>" 
                           class="category-card-mini" 
                           style="text-decoration: none; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 1rem; overflow: hidden; display: flex; flex-direction: column; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);"
                           onmouseover="this.style.transform='translateY(-10px)'; this.style.borderColor='rgba(14, 165, 233, 0.4)'; this.style.background='rgba(14, 165, 233, 0.03)';"
                           onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255, 255, 255, 0.05)'; this.style.background='rgba(255, 255, 255, 0.02)';"
                        >
                            <div style="height: 180px; background: rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                                <?php if (!empty($cat['image_url'])): ?>
                                    <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($cat['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;">
                                <?php else: ?>
                                    <div style="font-size: 4rem; opacity: 0.5; filter: drop-shadow(0 0 20px var(--primary));"><?php echo $icons[$cat['name']] ?? '📦'; ?></div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 1.5rem;">
                                <h3 style="font-family: var(--font-heading); font-size: 1.25rem; color: #fff; margin-bottom: 0.5rem; font-weight: 800; letter-spacing: -0.01em;"><?php echo htmlspecialchars($cat['name']); ?></h3>
                                <p style="color: #64748b; line-height: 1.5; font-size: 0.9rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?php echo htmlspecialchars($cat['description']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Values Section moved above footer -->
<section style="padding: 4rem 0 2rem; background: var(--background);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 4rem;">
            <div class="reveal" style="text-align: center; padding: 3rem; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--primary);">💎</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 1rem; color: #fff;">The Expert Curation</h3>
                <p style="color: #94a3b8; font-size: 1.1rem; line-height: 1.6;">Every instrument is hand-selected and inspected by our team of master luthiers.</p>
            </div>
            <div class="reveal" style="text-align: center; padding: 3rem; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--primary);">🛰️</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 1rem; color: #fff;">Precision Delivery</h3>
                <p style="color: #94a3b8; font-size: 1.1rem; line-height: 1.6;">Climate-controlled shipping ensure your instrument arrives in perfect tuning.</p>
            </div>
            <div class="reveal" style="text-align: center; padding: 3rem; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--primary);">🎧</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 1rem; color: #fff;">Lifetime Support</h3>
                <p style="color: #94a3b8; font-size: 1.1rem; line-height: 1.6;">Access to professional setup and expert guidance throughout your musical journey.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
