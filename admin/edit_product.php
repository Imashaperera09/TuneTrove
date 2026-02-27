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

// Fetch existing digital asset if any
$digital_stmt = $pdo->prepare("SELECT * FROM digital_products WHERE product_id = ?");
$digital_stmt->execute([$id]);
$digital_asset = $digital_stmt->fetch();


// Handle Deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_image'])) {
        if ($product['image_url']) {
            $old_file = __DIR__ . '/../user/assets/images/' . $product['image_url'];
            if (file_exists($old_file)) unlink($old_file);
            $pdo->prepare("UPDATE products SET image_url = NULL WHERE id = ?")->execute([$id]);
            header("Location: edit_product.php?id=$id&msg=Image deleted");
            exit();
        }
    }
    if (isset($_POST['delete_digital_asset'])) {
        if ($digital_asset) {
            $old_file = __DIR__ . '/../' . $digital_asset['file_path'];
            if (file_exists($old_file)) unlink($old_file);
            $pdo->prepare("DELETE FROM digital_products WHERE product_id = ?")->execute([$id]);
            header("Location: edit_product.php?id=$id&msg=Digital asset deleted");
            exit();
        }
    }
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
    $is_deal = isset($_POST['is_deal']) ? 1 : 0;
    $image_url = $product['image_url'];

    // Handle Asset Uploads (Multi-file)
    if (isset($_FILES['assets'])) {
        foreach ($_FILES['assets']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['assets']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['assets']['name'][$key];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $safe_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", $file_name);
                
                $img_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $doc_exts = ['pdf', 'zip'];

                if (in_array($file_ext, $img_exts)) {
                    // Update Product Image
                    $upload_dir = __DIR__ . '/../user/assets/images/';
                    if (move_uploaded_file($tmp_name, $upload_dir . $safe_name)) {
                        $image_url = $safe_name;
                    }
                } elseif (in_array($file_ext, $doc_exts) && $product['is_digital']) {
                    // Update Digital Asset
                    $download_dir = __DIR__ . '/../user/assets/downloads/';
                    if (!is_dir($download_dir)) mkdir($download_dir, 0777, true);
                    
                    if (move_uploaded_file($tmp_name, $download_dir . $safe_name)) {
                        // Delete old digital record & file
                        if ($digital_asset) {
                            $old_file = __DIR__ . '/../' . $digital_asset['file_path'];
                            if (file_exists($old_file)) unlink($old_file);
                            $pdo->prepare("DELETE FROM digital_products WHERE product_id = ?")->execute([$id]);
                        }
                        // Insert new
                        $pdo->prepare("INSERT INTO digital_products (product_id, file_path) VALUES (?, ?)")
                            ->execute([$id, 'user/assets/downloads/' . $safe_name]);
                    }
                }
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, stock_quantity = ?, brand = ?, description = ?, sale_price = ?, image_url = ?, is_deal = ? WHERE id = ?");
    $stmt->execute([$name, $category_id, $price, $stock, $brand, $description, $sale_price, $image_url, $is_deal, $id]);
    
    header("Location: products.php?msg=Product updated successfully");
    exit();
}
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
                    <label style="display: flex; align-items: center; font-size: 0.875rem; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_deal" style="margin-right: 0.5rem; width: 1.2rem; height: 1.2rem;" <?php echo $product['is_deal'] ? 'checked' : ''; ?>> Mark as Deal
                    </label>
                </div>

                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Product Assets (Image & Digital File)</label>
                    
                    <?php if ($product['image_url']): ?>
                        <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.03); padding: 0.5rem; border-radius: 0.5rem; width: fit-content;">
                            <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($product['image_url']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 0.4rem; border: 1px solid var(--admin-border);">
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Thumbnail: <?php echo htmlspecialchars($product['image_url']); ?></span>
                                <button type="submit" name="delete_image" onclick="return confirm('Remove this image?')" style="background: #ef4444; color: white; border: none; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer; width: fit-content;">× Remove Image</button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($product['is_digital'] && $digital_asset): ?>
                        <div style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: rgba(14, 165, 233, 0.1); border-radius: 0.5rem; border: 1px solid rgba(14, 165, 233, 0.2); display: flex; align-items: center; justify-content: space-between; gap: 1rem; width: fit-content; min-width: 300px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span style="font-size: 1.25rem;">📄</span>
                                <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary);">Asset: <?php echo basename($digital_asset['file_path']); ?></span>
                            </div>
                            <button type="submit" name="delete_digital_asset" onclick="return confirm('Remove this digital file?')" style="background: #ef4444; color: white; border: none; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; cursor: pointer;">× Remove File</button>
                        </div>
                    <?php endif; ?>

                    <input type="file" name="assets[]" multiple accept="image/*,.pdf,.zip" style="width: 100%; padding: 0.5rem; font-size: 0.875rem;">
                    <p style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 0.5rem;">To replace or add files, click **Choose Files** above. To remove an existing one, use the **Remove** button.</p>
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
