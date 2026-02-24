<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch all main categories and their subcategories
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name");
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
                <div style="background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; display: flex; flex-direction: column;">
                    <div style="padding: 3rem; text-align: center; background: #f1f5f9; border-bottom: 1px solid var(--border);">
                        <span style="font-size: 4rem;">📦</span>
                    </div>
                    <div style="padding: 2rem; flex: 1; display: flex; flex-direction: column;">
                        <h2 style="font-family: var(--font-heading); margin-bottom: 1rem;"><?php echo htmlspecialchars($cat['name']); ?></h2>
                        <p style="color: var(--text-muted); margin-bottom: 2rem; line-height: 1.6;"><?php echo htmlspecialchars($cat['description']); ?></p>
                        
                        <div style="margin-top: auto;">
                            <a href="/TuneTrove/shop/?cat=<?php echo urlencode($cat['name']); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">View All <?php echo htmlspecialchars($cat['name']); ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
             <div style="grid-column: 1 / -1; text-align: center; padding: 5rem; background: var(--surface); border-radius: var(--radius); border: 1px dashed var(--border);">
                <p style="color: var(--text-muted);">No categories found. Please run <a href="seed.php">seed.php</a> to populate the database.</p>
             </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
