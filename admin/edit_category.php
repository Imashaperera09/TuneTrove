<?php
require_once 'includes/admin-header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: categories.php");
    exit();
}

// Fetch Category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    header("Location: categories.php");
    exit();
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $description, $id]);
    header("Location: categories.php?msg=Category updated successfully");
    exit();
}
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <a href="categories.php" style="color: var(--admin-primary); text-decoration: none; font-weight: 600;">← Back to Categories</a>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Edit Category: <?php echo htmlspecialchars($category['name']); ?></h3>
        </div>
        <div style="padding: 2rem;">
            <form method="POST" style="display: grid; gap: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Category Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                </div>

                <div>
                    <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; font-weight: 600;">Category Description</label>
                    <textarea name="description" rows="5" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;"><?php echo htmlspecialchars($category['description']); ?></textarea>
                </div>

                <div style="padding-top: 1.5rem; border-top: 1px solid var(--admin-border); display: flex; justify-content: flex-end; gap: 1rem;">
                    <a href="categories.php" style="padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; color: var(--admin-text-muted); font-weight: 600;">Cancel</a>
                    <button type="submit" name="update_category" style="background: var(--admin-primary); color: white; border: none; padding: 0.75rem 2rem; border-radius: 0.5rem; cursor: pointer; font-weight: 700; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

    </div>
</main>
</body>
</html>
