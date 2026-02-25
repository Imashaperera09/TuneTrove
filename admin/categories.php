<?php
require_once 'includes/admin-header.php';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    
    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $desc]);
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
                    <form method="POST" style="display: grid; gap: 1rem;">
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">Category Name</label>
                            <input type="text" name="name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem;">Description</label>
                            <textarea name="description" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 0.5rem;"></textarea>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cat['description']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
