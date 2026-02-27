<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$category = isset($_GET['cat']) ? $_GET['cat'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build Query
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($category) {
    if ($category === 'Digital') {
        $query .= " AND p.is_digital = 1";
    } else {
        $query .= " AND c.name = ?";
        $params[] = $category;
    }
}

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.brand LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

switch ($sort) {
    case 'price_low': $query .= " ORDER BY p.price ASC"; break;
    case 'price_high': $query .= " ORDER BY p.price DESC"; break;
    default: $query .= " ORDER BY p.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for sidebar
$order = ['Guitars', 'Keyboards', 'Drums & Percussion', 'Wind Instruments', 'String Instruments', 'Accessories', 'Digital Sheet Music'];
$placeholders = implode(',', array_fill(0, count($order), '?'));
$cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE parent_id IS NULL ORDER BY FIELD(name, $placeholders)");
$cat_stmt->execute($order);
$all_categories = $cat_stmt->fetchAll();
?>

<div class="shop-showroom" style="min-height: 100vh; background: var(--background); padding-top: 0.5rem; padding-bottom: 8rem;">
    <div class="container">
        <!-- Removed Breadcrumbs-style header for minimal look -->
        <div style="margin-bottom: 2.5rem;">
            <h1 style="font-family: var(--font-heading); font-size: 3.5rem; letter-spacing: -0.04em; color: #fff; margin-top: 0;">
                <?php echo $category ? htmlspecialchars($category) : 'The <span style="color: var(--primary);">Complete</span> Catalog'; ?>
            </h1>
        </div>

        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 4rem; align-items: start;">
            <!-- Sidebar -->
            <aside style="position: sticky; top: 100px;">
                <div style="background: var(--surface); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 0.5rem; padding: 2.5rem;">
                    <!-- Removed 'Narrow Results' heading -->
                    <form action="index.php" method="GET">
                        <div style="margin-bottom: 2rem;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 1rem;">Category</label>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <a href="index.php" style="text-decoration: none; font-size: 0.95rem; color: <?php echo !$category ? 'var(--primary)' : '#94a3b8'; ?>; font-weight: <?php echo !$category ? '700' : '500'; ?>; transition: all 0.2s;">All Items</a>
                                <?php foreach ($all_categories as $cat): ?>
                                    <a href="index.php?cat=<?php echo urlencode($cat['name']); ?>" style="text-decoration: none; font-size: 0.95rem; color: <?php echo $category === $cat['name'] ? 'var(--primary)' : '#94a3b8'; ?>; font-weight: <?php echo $category === $cat['name'] ? '700' : '500'; ?>; transition: all 0.2s;">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div style="margin-bottom: 2rem; border-top: 1px solid rgba(255, 255, 255, 0.03); padding-top: 2rem;">
                            <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 1rem;">Sort Order</label>
                            <select name="sort" onchange="this.form.submit()" style="width: 100%; padding: 0.85rem; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 4px; background: rgba(0, 0, 0, 0.2); color: #fff; font-size: 0.9rem;">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>What's New</option>
                                <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                        </div>
                        <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </aside>
            <!-- Product Display Grid -->
            <div>
                <?php if (empty($products)): ?>
                    <div style="text-align: center; padding: 10rem 0; background: #f8f9fa; border-radius: 0.5rem; border: 2px dashed #eee;">
                        <span style="font-size: 4rem; opacity: 0.3; display: block; margin-bottom: 1rem;">🔍</span>
                        <h2 style="font-family: var(--font-heading); color: #333;">No Gear Found</h2>
                        <p style="color: #666;">Try adjusting your filters or search keywords.</p>
                        <a href="index.php" style="color: var(--primary); font-weight: 700;">View All Inventory</a>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
                        <?php foreach ($products as $p): ?>
                            <div class="product-card-pro" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 0.5rem; overflow: hidden; display: flex; flex-direction: column; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.borderColor='rgba(14, 165, 233, 0.4)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.05)'; this.style.transform='translateY(0)';" >
                                <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit; flex: 1; display: flex; flex-direction: column;">
                                    <div style="height: 180px; background: rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: center; overflow: hidden; border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
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
                                                <?php if (!empty($p['sale_price'])): ?>
                                                    <span style="color: var(--primary);">$<?php echo number_format($p['sale_price'], 2); ?></span>
                                                    <span style="font-size: 0.9rem; color: #64748b; text-decoration: line-through; margin-left: 0.5rem;">$<?php echo number_format($p['price'], 2); ?></span>
                                                <?php else: ?>
                                                    $<?php echo number_format($p['price'], 2); ?>
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
