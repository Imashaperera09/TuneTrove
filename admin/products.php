<?php
require_once 'includes/admin-header.php';

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: products.php?msg=Product deleted");
    exit();
}

// Handle Add Product (Simplified for demo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'] ?: null;
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, stock_quantity, brand, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $category_id, $price, $stock, $brand, $description]);
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
                    <form method="POST" style="display: grid; gap: 1rem;">
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
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Brand</label>
                            <input type="text" name="brand" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 500;">Description</label>
                            <textarea name="description" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;"></textarea>
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
                                <td>
                                    <a href="?delete=<?php echo $p['id']; ?>" onclick="return confirm('Are you sure?')" style="color: var(--admin-danger); text-decoration: none; font-weight: 600;">Delete</a>
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
