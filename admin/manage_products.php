<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Access Control
if (!is_logged_in() || (!has_role('admin') && !has_role('staff'))) {
    redirect('/TuneTrove/auth/login.php', 'Restricted access.', 'error');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    redirect('manage_products.php', 'Product deleted successfully.');
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = sanitize($_POST['name']);
    $brand = sanitize($_POST['brand']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;
    $description = sanitize($_POST['description']);

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE products SET name=?, brand=?, category_id=?, price=?, stock_quantity=?, is_digital=?, description=? WHERE id=?");
        $stmt->execute([$name, $brand, $category_id, $price, $stock, $is_digital, $description, $id]);
        $msg = "Product updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, brand, category_id, price, stock_quantity, is_digital, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $brand, $category_id, $price, $stock, $is_digital, $description]);
        $msg = "Product added successfully.";
    }
    redirect('manage_products.php', $msg);
}

// Fetch Categories
$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $cat_stmt->fetchAll();

// Fetch Products
$prod_stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$products = $prod_stmt->fetchAll();

$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch();
}
?>

<div class="container" style="padding-top: 2rem;">
    <div style="margin-bottom: 2rem;">
        <a href="dashboard.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">← Back to Dashboard</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 3rem; align-items: flex-start;">
        <!-- Product List -->
        <div style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h2 style="font-family: var(--font-heading); margin-bottom: 2rem;">In-Stock Products</h2>
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                            <th style="padding: 1rem 0;">Product</th>
                            <th style="padding: 1rem 0;">Category</th>
                            <th style="padding: 1rem 0;">Price</th>
                            <th style="padding: 1rem 0;">Stock</th>
                            <th style="padding: 1rem 0; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 1rem 0;">
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($p['brand']); ?></div>
                                </td>
                                <td style="padding: 1rem 0; font-size: 0.875rem;"><?php echo htmlspecialchars($p['category_name']); ?></td>
                                <td style="padding: 1rem 0; font-weight: 600;"><?php echo format_price($p['price']); ?></td>
                                <td style="padding: 1rem 0;">
                                    <?php if ($p['is_digital']): ?>
                                        <span style="color: var(--accent); font-weight: 700;">DIGITAL</span>
                                    <?php else: ?>
                                        <span style="color: <?php echo $p['stock_quantity'] < 5 ? 'var(--error)' : 'inherit'; ?>; font-weight: <?php echo $p['stock_quantity'] < 5 ? '700' : '400'; ?>;">
                                            <?php echo $p['stock_quantity']; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem 0; text-align: right;">
                                    <a href="?edit=<?php echo $p['id']; ?>" style="color: var(--primary); text-decoration: none; margin-right: 1rem;">Edit</a>
                                    <a href="?delete=<?php echo $p['id']; ?>" onclick="return confirm('Are you sure?')" style="color: var(--error); text-decoration: none;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Form -->
        <aside style="background: var(--surface); padding: 2.5rem; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); position: sticky; top: 100px;">
            <h2 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 2rem;"><?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?></h2>
            
            <form action="manage_products.php" method="POST">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600;">Product Name</label>
                    <input type="text" name="name" value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600;">Brand</label>
                    <input type="text" name="brand" value="<?php echo $edit_product ? htmlspecialchars($edit_product['brand']) : ''; ?>" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600;">Category</label>
                    <select name="category_id" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); background: white;">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_product && $edit_product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600;">Price</label>
                        <input type="number" name="price" step="0.01" value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600;">Stock</label>
                        <input type="number" name="stock" value="<?php echo $edit_product ? $edit_product['stock_quantity'] : '0'; ?>" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_digital" value="1" <?php echo ($edit_product && $edit_product['is_digital']) ? 'checked' : ''; ?>>
                        This is a Digital Product
                    </label>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; font-weight: 600;">Description</label>
                    <textarea name="description" rows="4" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); font-family: inherit;"><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;"><?php echo $edit_product ? 'Update Product' : 'Add Product'; ?></button>
                <?php if ($edit_product): ?>
                    <a href="manage_products.php" style="display: block; text-align: center; margin-top: 1rem; font-size: 0.875rem; color: var(--text-muted); text-decoration: none;">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </aside>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
