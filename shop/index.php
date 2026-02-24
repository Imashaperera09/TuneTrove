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
$cat_stmt = $pdo->query("SELECT name FROM categories WHERE parent_id IS NULL");
$all_categories = $cat_stmt->fetchAll();
?>

<div class="shop-showroom" style="min-height: 100vh; background: radial-gradient(circle at 10% 10%, rgba(37, 99, 235, 0.05), transparent), radial-gradient(circle at 90% 90%, rgba(168, 85, 247, 0.05), transparent); padding-top: 8rem; padding-bottom: 8rem;">
    <div class="container">
        <div style="display: flex; gap: 4rem; align-items: flex-start;">
            
            <!-- Elegant Sidebar -->
            <aside style="width: 300px; position: sticky; top: 120px;">
                <div style="background: rgba(30, 41, 59, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 2rem; padding: 2.5rem; box-shadow: 0 20px 50px -10px rgba(0,0,0,0.3);">
                    <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.25rem; color: var(--primary);">🎚️</span> FILTERS
                    </h3>
                    
                    <form action="index.php" method="GET">
                        <div style="margin-bottom: 2.5rem;">
                            <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1rem;">Search Collection</label>
                            <div style="position: relative;">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Brand, name..." style="width: 100%; padding: 1rem 1rem 1rem 3rem; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255,255,255,0.05); border-radius: 1rem; color: #fff; font-family: inherit; font-size: 0.9rem; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255,255,255,0.05)'">
                                <span style="position: absolute; left: 1.15rem; top: 50%; transform: translateY(-50%); opacity: 0.4;">🔍</span>
                            </div>
                        </div>

                        <div style="margin-bottom: 2.5rem;">
                            <label style="display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1.25rem;">Collections</label>
                            <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="index.php" class="filter-nav-link <?php echo !$category ? 'active' : ''; ?>">
                                    All Masterpieces
                                </a>
                                <?php foreach ($all_categories as $cat): ?>
                                    <a href="index.php?cat=<?php echo urlencode($cat['name']); ?>" class="filter-nav-link <?php echo $category === $cat['name'] ? 'active' : ''; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.15rem; border-radius: 1.25rem; font-weight: 800; font-size: 0.85rem; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 1.25rem; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3);">Apply Logic</button>
                        <a href="index.php" style="display: block; text-align: center; font-size: 0.75rem; color: var(--text-muted); text-decoration: none; font-weight: 700; letter-spacing: 0.05em;">RESET ALL</a>
                    </form>
                </div>
            </aside>

            <!-- Showroom Display -->
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 4rem;">
                    <div>
                        <h1 style="font-family: var(--font-heading); font-size: 3rem; letter-spacing: -0.04em; margin-bottom: 0.5rem;">
                            <?php echo $category ? htmlspecialchars($category) : 'The <span style="color: var(--primary);">Full</span> Collection'; ?>
                        </h1>
                        <p style="color: var(--text-muted); font-size: 1.1rem;"><?php echo count($products); ?> exquisite items curated for precision.</p>
                    </div>
                    
                    <form action="index.php" method="GET" style="display: flex; align-items: center; gap: 1.25rem; background: rgba(30, 41, 59, 0.4); padding: 0.65rem 1.25rem; border-radius: 1rem; border: 1px solid rgba(255,255,255,0.05);">
                        <?php if ($category): ?>
                            <input type="hidden" name="cat" value="<?php echo htmlspecialchars($category); ?>">
                        <?php endif; ?>
                        <span style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;">Order by:</span>
                        <select name="sort" onchange="this.form.submit()" style="background: transparent; border: none; color: #fff; font-family: inherit; font-weight: 700; font-size: 0.85rem; outline: none; cursor: pointer;">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?> style="background: #1e293b;">Newest Arrivals</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?> style="background: #1e293b;">Price: Ascending</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?> style="background: #1e293b;">Price: Descending</option>
                        </select>
                    </form>
                </div>

                <div class="category-grid" style="gap: 3rem; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                    <?php if (empty($products)): ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 8rem 2rem; background: rgba(30, 41, 59, 0.2); border-radius: 3rem; border: 1px dashed rgba(255, 255, 255, 0.05); animation: authReveal 1s ease;">
                            <div style="font-size: 4rem; margin-bottom: 2rem; opacity: 0.3;">🎻</div>
                            <h2 style="font-family: var(--font-heading); font-size: 2rem; color: #fff; margin-bottom: 1rem;">No Masterpieces Found</h2>
                            <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto 3rem;">We couldn't find any instruments matching your refined search criteria. Try adjusting your filters.</p>
                            <a href="index.php" class="btn btn-primary" style="padding: 1rem 2.5rem; border-radius: 1.25rem; font-weight: 800;">View All Products</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <div class="product-card" style="position: relative;">
                                <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                                    <div style="height: 320px; background: rgba(15, 23, 42, 0.4); position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                        <?php if ($p['image_url']): ?>
                                            <img src="/TuneTrove/assets/images/<?php echo htmlspecialchars($p['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);" class="product-img-hover">
                                        <?php else: ?>
                                            <div style="font-size: 4.5rem; opacity: 1; transition: transform 0.6s ease;" class="product-img-hover">🎸</div>
                                        <?php endif; ?>
                                        
                                        <?php if ($p['is_digital']): ?>
                                            <div style="position: absolute; top: 1.5rem; right: 1.5rem; background: rgba(168, 85, 247, 0.2); backdrop-filter: blur(8px); border: 1px solid rgba(168, 85, 247, 0.3); color: #c084fc; padding: 0.4rem 0.8rem; border-radius: 0.75rem; font-size: 0.65rem; font-weight: 900; letter-spacing: 0.1em; text-transform: uppercase;">Digital Asset</div>
                                        <?php endif; ?>
                                        
                                        <div class="card-overlay" style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(15, 23, 42, 0.8), transparent); opacity: 0; transition: opacity 0.4s ease;"></div>
                                    </div>
                                    <div style="padding: 2rem; background: rgba(30, 41, 59, 0.2);">
                                        <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($p['brand']); ?></div>
                                        <h3 style="font-family: var(--font-heading); font-size: 1.35rem; margin-bottom: 1.25rem; color: #fff; line-height: 1.2;"><?php echo htmlspecialchars($p['name']); ?></h3>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-family: var(--font-heading); font-weight: 800; font-size: 1.5rem; color: #fff;"><?php echo format_price($p['price']); ?></span>
                                            <span style="font-size: 0.7rem; color: var(--text-muted); background: rgba(255,255,255,0.03); padding: 0.35rem 0.65rem; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.05);">VERIFIED AUTHENTIC</span>
                                        </div>
                                    </div>
                                </a>
                                <div style="padding: 0 2rem 2rem; background: rgba(30, 41, 59, 0.2);">
                                    <form action="cart.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <button type="submit" class="btn btn-primary showroom-buy-btn" style="width: 100%; padding: 1rem; border-radius: 1rem; font-weight: 800; letter-spacing: 0.05em; font-size: 0.8rem;">RESERVE NOW</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.filter-nav-link {
    display: block;
    padding: 0.85rem 1.25rem;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 600;
    border-radius: 0.85rem;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.filter-nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.03);
    transform: translateX(5px);
}
.filter-nav-link.active {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3);
}

.product-card {
    background: rgba(30, 41, 59, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 2rem;
    overflow: hidden;
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.product-card:hover {
    transform: translateY(-15px);
    border-color: var(--primary);
    box-shadow: 0 40px 80px -20px rgba(0,0,0,0.6);
}
.product-card:hover .product-img-hover {
    transform: scale(1.1);
}
.product-card:hover .card-overlay {
    opacity: 1;
}

.shop-showroom .btn-primary {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.showroom-buy-btn:hover {
    background: #fff;
    color: var(--primary);
    transform: scale(1.02);
}

@keyframes authReveal {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 1024px) {
    .container { padding: 0 2rem; }
    aside { display: none; }
}
</style>

<?php require_once '../includes/footer.php'; ?>
