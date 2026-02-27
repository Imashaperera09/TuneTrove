<?php
require_once 'includes/admin-header.php';

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Optional: Check if products exist for this category
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $checkStmt->execute([$id]);
    $count = $checkStmt->fetchColumn();
    
    if ($count > 0) {
        header("Location: categories.php?error=Cannot delete category: contains products");
        exit();
    }
    
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: categories.php?msg=Category deleted");
    exit();
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'cat_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $target = '../user/assets/images/' . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_url = $filename;
        }
    }
    $stmt = $pdo->prepare("INSERT INTO categories (name, description, image_url) VALUES (?, ?, ?)");
    $stmt->execute([$name, $desc, $image_url]);
    header("Location: categories.php?msg=Category added");
    exit();
}

// Fetch Categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>

        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div class="content-card" style="flex: 1; min-width: 300px;">
                <div class="card-header"><h3 class="card-title">Add Category</h3></div>
                <div style="padding: 1.5rem;">
                    <form method="POST" enctype="multipart/form-data" style="display: grid; gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">Category Name</label>
                            <input type="text" name="name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">Description</label>
                            <textarea name="description" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;"></textarea>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">Category Image</label>
                            <input type="file" name="image" accept="image/*" style="width: 100%;">
                        </div>
                        <button type="submit" name="add_category" style="background: var(--admin-primary); color: white; border: none; padding: 1rem; border-radius: 0.5rem; font-weight:600; cursor:pointer;">Add Category</button>
                    </form>
                </div>
            </div>

            <div class="content-card" style="flex: 2; min-width: 400px;">
                <div class="card-header"><h3 class="card-title">Categories</h3></div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                <td>
                                    <div style="display: flex; gap: 1rem;">
                                        <a href="edit_category.php?id=<?php echo $cat['id']; ?>" style="color: var(--admin-primary); text-decoration: none; font-weight: 600;">Edit</a>
                                        <a href="?delete=<?php echo $cat['id']; ?>" onclick="return confirm('Are you sure you want to delete this category?')" style="color: var(--admin-danger); text-decoration: none; font-weight: 600;">Delete</a>
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
