<?php
require_once 'includes/admin-header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: products.php");
    exit();
}

// Fetch Product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit();
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'] ?: null;
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $sale_price = $_POST['sale_price'] ?: null;
    $image_url = $product['image_url'];

    // Handle Image Upload (if new image provided)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../user/assets/images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('prod_') . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Optional: delete old image file if it exists
            $image_url = $file_name;
        }
    }

    $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, stock_quantity = ?, brand = ?, description = ?, sale_price = ?, image_url = ? WHERE id = ?");
    $stmt->execute([$name, $category_id, $price, $stock, $brand, $description, $sale_price, $image_url, $id]);
    header("Location: products.php?msg=Product updated successfully");
    exit();
}

// Fetch Categories for dropdown
$catStmt = $pdo->query("SELECT id, name FROM categories");
$categories = $catStmt->fetchAll();
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <a href="products.php" style="color: var(--admin-primary); text-decoration: none; font-weight: 600;">← Back to Products</a>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Edit Product: <?php echo htmlspecialchars($product['name']); ?></h3>
        </div>
        <div style="padding: 2rem;">
            <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Product Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Price ($)</label>
                        <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Sale Price ($) <small style="color: var(--admin-text-muted); font-weight: 400;">(Optional)</small></label>
                        <input type="number" step="0.01" name="sale_price" value="<?php echo $product['sale_price']; ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Stock Quantity</label>
                        <input type="number" name="stock" value="<?php echo $product['stock_quantity']; ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Category</label>
                        <select name="category_id" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Brand / Manufacturer</label>
                    <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Product Description</label>
                    <textarea name="description" rows="5" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Product Image</label>
                    <?php if ($product['image_url']): ?>
                        <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem;">
                            <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($product['image_url']); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 0.5rem; border: 1px solid var(--admin-border);">
                            <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Current Image: <?php echo htmlspecialchars($product['image_url']); ?></span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" style="width: 100%; padding: 0.5rem; font-size: 0.875rem;">
                    <p style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 0.5rem;">Uploading a new image will replace the existing one.</p>
                </div>

                <div style="padding-top: 1.5rem; border-top: 1px solid var(--admin-border); display: flex; justify-content: flex-end; gap: 1rem;">
                    <a href="products.php" style="padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; color: var(--admin-text-muted); font-weight: 600;">Cancel</a>
                    <button type="submit" name="update_product" style="background: var(--admin-primary); color: white; border: none; padding: 0.75rem 2rem; border-radius: 0.5rem; cursor: pointer; font-weight: 700; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);">Update Archival Piece</button>
                </div>
            </form>
        </div>
    </div>
</div>

    </div>
</main>
</body>
</html>
