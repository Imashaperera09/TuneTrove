<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch main categories for the homepage
try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL LIMIT 6");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>

<section class="hero">
    <div class="container">
        <h1>Master Your Sound with <br><span class="logo-accent">Melody Masters</span></h1>
        <p>Explore our curated collection of premium instruments, from classic acoustic guitars to cutting-edge digital sheet music.</p>
        <div class="hero-actions">
            <a href="/TuneTrove/shop/" class="btn btn-primary">Start Shopping</a>
            <a href="/TuneTrove/categories.php" class="btn" style="color: var(--text-muted)">Browse Categories</a>
        </div>
    </div>
</section>

<section class="featured-categories">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <a href="/TuneTrove/shop/?cat=<?php echo urlencode($cat['name']); ?>" class="category-card">
                        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <p><?php echo htmlspecialchars($cat['description']); ?></p>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="/TuneTrove/shop/?cat=Guitars" class="category-card">
                    <h3>Guitars</h3>
                    <p>Acoustic, Electric, and Bass guitars</p>
                </a>
                <a href="/TuneTrove/shop/?cat=Keyboards" class="category-card">
                    <h3>Keyboards</h3>
                    <p>Digital pianos and synthesizers</p>
                </a>
                <a href="/TuneTrove/shop/?cat=Drums%20%26%20Percussion" class="category-card">
                    <h3>Drums & Percussion</h3>
                    <p>Drums and percussion sets</p>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="promotions" style="margin-top: 4rem; padding: 4rem 0; background: var(--text); color: white; border-radius: var(--radius);">
    <div class="container" style="text-align: center;">
        <h2 style="font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 1rem;">Free Shipping on Orders Over £100</h2>
        <p style="color: #94a3b8; font-size: 1.1rem; margin-bottom: 2rem;">Upgrade your gear today and save on delivery costs.</p>
        <a href="/TuneTrove/shop/" class="btn btn-primary">View All Products</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
