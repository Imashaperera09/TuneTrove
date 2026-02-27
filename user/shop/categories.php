<?php
require_once '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Fetch all main categories with custom ordering
try {
    $order = ['Guitars', 'Keyboards', 'Drums & Percussion', 'Wind Instruments', 'String Instruments', 'Accessories', 'Digital Sheet Music'];
    $placeholders = implode(',', array_fill(0, count($order), '?'));
    $sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY FIELD(name, $placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($order);
    $main_categories = $stmt->fetchAll();
} catch (PDOException $e) {
} catch (PDOException $e) {
    $main_categories = [];
}

// Fetch some recent products to display below categories
try {
    $recent_stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 8");
    $recent_products = $recent_stmt->fetchAll();
} catch (PDOException $e) {
    $recent_products = [];
}
?>

<div style="background: var(--background); min-height: 100vh; padding-top: 0; padding-bottom: 8rem;">
    <div class="container">
        <!-- Breadcrumb / Header -->
        <div style="text-align: center; margin-bottom: 4rem;">
            <h1 style="font-family: var(--font-heading); font-size: 3.5rem; font-weight: 800; color: #fff; letter-spacing: -0.04em; margin: 0;">Shop by <span style="color: var(--primary);">Category</span></h1>
            <div style="width: 60px; height: 4px; background: var(--primary); margin: 2rem auto 0; border-radius: 999px;"></div>
        </div>
        
        <style>
            .category-row-single { display: flex; flex-wrap: nowrap; gap: 1rem; justify-content: center; align-items: stretch; }
            .category-card { min-width: 180px; max-width: 220px; flex: 0 0 180px; text-decoration: none; color: inherit; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 1rem; overflow: hidden; transition: all 0.4s cubic-bezier(0.16,1,0.3,1); height: 100%; display: flex; flex-direction: column; box-shadow: 0 2px 12px 0 rgba(0,0,0,0.07); }
            .category-card:hover { transform: translateY(-6px) scale(1.03); border-color: rgba(14,165,233,0.4); background: rgba(14,165,233,0.03); box-shadow: 0 6px 24px 0 rgba(14,165,233,0.10); }
            .category-card .cat-img { height: 90px; background: rgba(0, 0, 0, 0.18); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; border-bottom: 1px solid rgba(255,255,255,0.03); }
            .category-card .cat-img img { width: 100%; height: 100%; object-fit: cover; opacity: 0.85; }
            .category-card .cat-img .icon { font-size: 2rem; opacity: 0.5; filter: drop-shadow(0 0 10px var(--primary)); text-align: center; }
            .category-card .cat-body { padding: 0.7rem 0.7rem 0.5rem 0.7rem; flex: 1; display: flex; flex-direction: column; }
            .category-card h2 { font-family: var(--font-heading); font-size: 0.98rem; font-weight: 800; color: #fff; margin-bottom: 0.4rem; letter-spacing: -0.02em; }
            .category-card p { color: #64748b; margin-bottom: 0.5rem; line-height: 1.5; font-size: 0.85rem; }
            .category-card .explore { margin-top: auto; display: flex; align-items: center; gap: 0.4rem; color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; }
            @media (max-width: 900px) { .category-row-single { flex-wrap: wrap; } }
        </style>

        <div class="category-row-single">
            <?php 
            $icons = [
                'Guitars' => '🎸',
                'Keyboards' => '🎹',
                'Drums & Percussion' => '🥁',
                'Wind Instruments' => ' saxophone',
                'String Instruments' => '🎻',
                'Accessories' => '🔌',
                'Digital Sheet Music' => '📑'
            ];
            ?>
            <?php if (!empty($main_categories)): ?>
                <?php foreach ($main_categories as $cat): ?>
                    <a class="category-card" href="collection.php?name=<?php echo urlencode($cat['name']); ?>">
                        <div class="cat-img">
                            <?php if (!empty($cat['image_url'])): ?>
                                <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($cat['image_url']); ?>">
                            <?php else: ?>
                                <span class="icon"><?php echo $icons[$cat['name']] ?? '📦'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="cat-body">
                            <h2><?php echo htmlspecialchars($cat['name']); ?></h2>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                  <div style="width:100%; text-align: center; padding: 6rem 0; background: rgba(255, 255, 255, 0.02); border-radius: 1rem; border: 2px dashed rgba(255, 255, 255, 0.05);">
                    <p style="color: #64748b; font-size: 1.1rem;">No categories found. Our curators are currently preparing the selection.</p>
                  </div>
            <?php endif; ?>
        </div>
        </div>

        <!-- Featured Selection -->
        <?php if (!empty($recent_products)): ?>
            <div style="margin-top: 4rem; text-align: center; margin-bottom: 2rem;">
                <h2 style="font-family: var(--font-heading); font-size: 3rem; weight: 800; color: #fff; letter-spacing: -0.02em; margin: 0;">Featured <span style="color: var(--primary);">Selection</span></h2>
                <div style="width: 60px; height: 4px; background: var(--primary); margin: 2rem auto 0; border-radius: 999px;"></div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
                <?php foreach ($recent_products as $p): ?>
                    <div class="product-card-pro" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; overflow: hidden; display: flex; flex-direction: column; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.borderColor='rgba(14, 165, 233, 0.4)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.05)'; this.style.transform='translateY(0)';" >
                        <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit; flex: 1; display: flex; flex-direction: column;">
                            <div style="height: 180px; background: rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: center; overflow: hidden; border-bottom: 1px solid rgba(255, 255, 255, 0.03); position: relative;">
                                <?php if (has_active_deal($p)): ?>
                                    <div style="position: absolute; top: 1rem; right: 1rem; background: var(--primary); color: #fff; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.7rem; font-weight: 800; z-index: 10;">
                                        SAVE <?php echo get_deal_percent($p); ?>%
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($p['image_url'])): ?>
                                    <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($p['image_url']); ?>" style="max-width: 90%; max-height: 90%; object-fit: contain; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.4));">
                                <?php else: ?>
                                    <div style="font-size: 5rem; opacity: 0.2; filter: drop-shadow(0 0 20px var(--primary));">🎻</div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 2rem; flex: 1; display: flex; flex-direction: column;">
                                <p style="font-size: 0.75rem; font-weight: 800; color: var(--accent); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 0.05em;"><?php echo htmlspecialchars($p['brand']); ?></p>
                                <h3 style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem; flex: 1; line-height: 1.4; letter-spacing: -0.01em;">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </h3>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                    <span style="font-size: 1.5rem; font-weight: 800; color: #fff;">
                                        <?php 
                                        $eff_price = get_effective_price($p);
                                        if ($eff_price < $p['price']): ?>
                                            <span style="color: var(--primary);">£<?php echo number_format($eff_price, 2); ?></span>
                                            <span style="font-size: 0.9rem; color: #64748b; text-decoration: line-through; margin-left: 0.5rem;">£<?php echo number_format($p['price'], 2); ?></span>
                                        <?php else: ?>
                                            £<?php echo number_format($p['price'], 2); ?>
                                        <?php endif; ?>
                                    </span>
                                    <span style="font-size: 0.75rem; color: #4ade80; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">In Stock</span>
                                </div>
                            </div>
                        </a>
                        <div style="padding: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.03); background: rgba(0, 0, 0, 0.1);">
                            <form action="cart_actions.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary" style="padding: 0.75rem; border-radius: 4px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.65rem;">Add to Cart</button>
                                    <button type="submit" name="buy_now" value="1" style="padding: 0.75rem; border-radius: 4px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.65rem; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); color: #fff; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='rgba(0,0,0,0.3)'">Buy Now</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 4rem;">
                <a href="/TuneTrove/user/shop/" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 800; border-radius: 0.5rem; text-transform: uppercase; letter-spacing: 0.1em;">View All Instruments</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
