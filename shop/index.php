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

<div class="container" style="padding-top: 2rem;">
    <div style="display: flex; gap: 3rem;">
        <!-- Filters Sidebar -->
        <aside style="width: 260px;">
            <div style="background: var(--surface); padding: 2rem; border-radius: var(--radius); border: 1px solid var(--border); position: sticky; top: 100px;">
                <h3 style="font-family: var(--font-heading); margin-bottom: 1.5rem;">Filters</h3>
                
                <form action="index.php" method="GET">
                    <div style="margin-bottom: 2rem;">
                        <h4 style="margin-bottom: 1rem; font-size: 0.875rem; text-transform: uppercase; color: var(--text-muted);">Search</h4>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products..." style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <h4 style="margin-bottom: 1rem; font-size: 0.875rem; text-transform: uppercase; color: var(--text-muted);">Category</h4>
                        <ul style="list-style: none;">
                            <li style="margin-bottom: 0.5rem;"><a href="index.php" style="text-decoration: none; color: <?php echo !$category ? 'var(--primary)' : 'var(--text-muted)'; ?>; font-weight: <?php echo !$category ? '600' : '400'; ?>;">All Products</a></li>
                            <?php foreach ($all_categories as $cat): ?>
                                <li style="margin-bottom: 0.5rem;">
                                    <a href="index.php?cat=<?php echo urlencode($cat['name']); ?>" style="text-decoration: none; color: <?php echo $category === $cat['name'] ? 'var(--primary)' : 'var(--text-muted)'; ?>; font-weight: <?php echo $category === $cat['name'] ? '600' : '400'; ?>;">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Apply Filters</button>
                    <a href="index.php" style="display: block; text-align: center; margin-top: 1rem; font-size: 0.875rem; color: var(--text-muted); text-decoration: none;">Clear All</a>
                </form>
            </div>
        </aside>

        <!-- Product Grid -->
        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
                <h2 style="font-family: var(--font-heading);">
                    <?php echo $category ? htmlspecialchars($category) : 'All Products'; ?>
                    <span style="font-size: 1rem; color: var(--text-muted); margin-left: 1rem; font-family: var(--font-sans);"><?php echo count($products); ?> items</span>
                </h2>
                
                <form action="index.php" method="GET" style="display: flex; align-items: center; gap: 1rem;">
                    <?php if ($category): ?>
                        <input type="hidden" name="cat" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                    <label style="font-size: 0.875rem; color: var(--text-muted);">Sort by:</label>
                    <select name="sort" onchange="this.form.submit()" style="padding: 0.5rem; border-radius: 0.5rem; border: 1px solid var(--border); background: var(--surface);">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </form>
            </div>

            <div class="category-grid" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
                <?php if (empty($products)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 5rem; background: var(--surface); border-radius: var(--radius); border: 1px dashed var(--border);">
                        <h3 style="color: var(--text-muted);">No products found matching your criteria.</h3>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <div class="product-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; transition: all 0.3s; display: flex; flex-direction: column;">
                            <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit;">
                                <div style="height: 200px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; position: relative;">
                                    <span style="font-size: 3rem;">🎸</span>
                                    <?php if ($p['is_digital']): ?>
                                        <span style="position: absolute; top: 1rem; right: 1rem; background: var(--accent); color: white; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.7rem; font-weight: bold;">DIGITAL</span>
                                    <?php endif; ?>
                                </div>
                                <div style="padding: 1.5rem; flex: 1;">
                                    <p style="font-size: 0.75rem; color: var(--primary); font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($p['brand']); ?></p>
                                    <h3 style="font-family: var(--font-heading); font-size: 1.125rem; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($p['name']); ?></h3>
                                    <p style="font-weight: 700; font-size: 1.25rem; color: var(--text);"><?php echo format_price($p['price']); ?></p>
                                </div>
                            </a>
                            <div style="padding: 0 1.5rem 1.5rem;">
                                <form action="../cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.6rem;">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
