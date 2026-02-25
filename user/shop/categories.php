<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

// Fetch all main categories with custom ordering
try {
    $order = ['Guitars', 'Keyboards', 'Drums & Percussion', 'Wind Instruments', 'String Instruments', 'Accessories', 'Digital Sheet Music'];
    $placeholders = implode(',', array_fill(0, count($order), '?'));
    $sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY FIELD(name, $placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($order);
    $main_categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $main_categories = [];
}
?>

<div class="container" style="padding-top: 3rem;">
    <h1 style="font-family: var(--font-heading); font-size: 3rem; text-align: center; margin-bottom: 3rem;">Browse by Category</h1>
    
    <div class="category-grid">
        <?php if (!empty($main_categories)): ?>
            <?php foreach ($main_categories as $cat): ?>
                <a href="collection.php?name=<?php echo urlencode($cat['name']); ?>" class="category-card" style="text-decoration: none; color: inherit;">
                    <div class="category-img" <?php echo !empty($cat['image_url']) ? 'style="background-image: url(\'/TuneTrove/user/assets/images/' . htmlspecialchars($cat['image_url']) . '\');"' : ''; ?>>
                        <?php if (empty($cat['image_url'])): ?>
                             <span style="font-size: 3.5rem;">📦</span>
                        <?php endif; ?>
                    </div>
                    <div class="category-info" style="display: flex; flex-direction: column; flex: 1;">
                        <h2 style="font-family: var(--font-heading); margin-bottom: 0.75rem; color: var(--text);"><?php echo htmlspecialchars($cat['name']); ?></h2>
                        <p style="color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5; font-size: 0.95rem;"><?php echo htmlspecialchars($cat['description']); ?></p>
                        <div style="margin-top: auto; padding: 0.75rem; background: var(--primary); color: white; text-align: center; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem;">
                            View Collection
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
             <div style="grid-column: 1 / -1; text-align: center; padding: 5rem; background: var(--surface); border-radius: var(--radius); border: 1px dashed var(--border);">
                <p style="color: var(--text-muted);">No categories found. Please run <a href="seed.php">seed.php</a> to populate the database.</p>
             </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
