<?php
require_once 'includes/admin-header.php';

// Handle Product Deletion (Admin only)
if (isset($_GET['delete'])) {
    if (!in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
        header("Location: products.php?error=You do not have permission to delete products.");
        exit();
    }
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: products.php?msg=Product deleted");
    exit();
}

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'] ?: null;
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $sale_price = $_POST['sale_price'] ?: null;
    $is_deal = isset($_POST['is_deal']) ? 1 : 0;
    $image_url = '';

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../user/assets/images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('prod_') . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = $file_name;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, stock_quantity, brand, description, sale_price, image_url, is_deal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $category_id, $price, $stock, $brand, $description, $sale_price, $image_url, $is_deal]);
    header("Location: products.php?msg=Product added successfully");
    exit();
}

// Fetch Categories for dropdown
$catStmt = $pdo->query("SELECT id, name FROM categories");
$categories = $catStmt->fetchAll();

// Fetch Products
$prodStmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $prodStmt->fetchAll();
?>

        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem; flex-wrap: wrap;">
            
            <!-- Add Product Form -->
            <div class="content-card" style="flex: 1; min-width: 350px;">
                <div class="card-header">
                    <h3 class="card-title">Add New Product</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Product Name</label>
                            <input type="text" name="name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Price ($)</label>
                                <input type="number" step="0.01" name="price" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Stock</label>
                                <input type="number" name="stock" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Category</label>
                            <select name="category_id" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Brand</label>
                                <input type="text" name="brand" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Sale Price ($) <small style="color: grey;">(Opt)</small></label>
                                <input type="number" step="0.01" name="sale_price" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Description</label>
                            <textarea name="description" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;"></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Product Image</label>
                            <input type="file" name="image" accept="image/*" style="width: 100%; padding: 0.5rem; font-size: 0.875rem;">
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; font-size: 0.875rem; margin-bottom: 1rem; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" name="is_deal" style="margin-right: 0.5rem; width: 1.2rem; height: 1.2rem;"> Mark as Deal
                            </label>
                        </div>
                        <button type="submit" name="add_product" style="background: var(--admin-primary); color: white; border: none; padding: 1rem; border-radius: 0.5rem; cursor: pointer; font-weight: 600;">Add Product</button>
                    </form>
                </div>
            </div>

            <!-- Products List -->
            <div class="content-card" style="flex: 2; min-width: 600px;">
                <div class="card-header">
                    <h3 class="card-title">Existing Products</h3>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Deal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['category_name'] ?: 'N/A'); ?></td>
                                <td>$<?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo $p['stock_quantity']; ?></td>
                                <td><?php echo $p['is_deal'] ? '<span style="color: #10b981; font-weight: 600;">Yes</span>' : '<span style="color: #64748b;">No</span>'; ?></td>
                                <td>
                                    <div style="display: flex; gap: 1rem;">
                                        <a href="edit_product.php?id=<?php echo $p['id']; ?>" style="color: var(--admin-primary); text-decoration: none; font-weight: 600;">Edit</a>
                                        <?php if (in_array($_SESSION['user_role'], ['admin', 'superadmin'])): ?>
                                        <a href="?delete=<?php echo $p['id']; ?>" onclick="return confirm('Are you sure you want to delete this archival piece?')" style="color: var(--admin-danger); text-decoration: none; font-weight: 600;">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
