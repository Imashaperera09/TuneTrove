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

<!-- Full Page Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1 class="reveal">Experience <br><span class="logo-accent">Pure Sound</span></h1>
        <p class="reveal">From classical craftsmanship to modern digital innovation. Discover the world's finest musical instruments curated for the true artist.</p>
        <div class="hero-actions reveal" style="display: flex; gap: 1.5rem; justify-content: center; align-items: center; flex-wrap: wrap;">
            <a href="/TuneTrove/shop/" class="btn btn-primary" style="padding: 1.5rem 4rem; font-size: 1.25rem;">Explore Collection</a>
            <a href="/TuneTrove/categories.php" class="btn" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 1.5rem 2.5rem; font-size: 1.125rem;">The Collections</a>
        </div>
    </div>
</section>

<!-- Values Section (Why Us) -->
<section style="padding: 6rem 0; background: var(--background);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 3rem;">
            <div class="reveal" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1.5rem;">🎻</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 1rem;">Expert Curation</h3>
                <p style="color: var(--text-muted);">Every instrument is hand-selected and inspected by our team of master luthiers and musicians.</p>
            </div>
            <div class="reveal" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1.5rem;">🚚</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 1rem;">Precision Delivery</h3>
                <p style="color: var(--text-muted);">Temperature-controlled shipping to ensure your instrument arrives in perfect tuning and condition.</p>
            </div>
            <div class="reveal" style="text-align: center; padding: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1.5rem;">🎵</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 1rem;">Lifetime Support</h3>
                <p style="color: var(--text-muted);">Professional setup and maintenance services included with every premium instrument purchase.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section style="padding: 6rem 0; background: #020617;">
    <div class="container">
        <div style="margin-bottom: 4rem; text-align: center;">
            <h2 class="section-title reveal" style="font-size: 3rem;">Browse Collections</h2>
            <p class="reveal" style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">Select a category to explore our professional-grade inventory.</p>
        </div>
        
        <div class="category-grid">
            <?php 
            $icons = [
                'Guitars' => '🎸',
                'Keyboards' => '🎹',
                'Drums & Percussion' => '🥁',
                'Wind Instruments' => '🎷',
                'String Instruments' => '🎻',
                'Accessories' => '🔌',
                'Digital Sheet Music' => '📑'
            ];
            ?>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $cat): ?>
                    <a href="/TuneTrove/shop/?cat=<?php echo urlencode($cat['name']); ?>" class="category-card reveal">
                        <div class="category-img">
                            <?php echo $icons[$cat['name']] ?? '📦'; ?>
                        </div>
                        <div class="category-info">
                            <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                            <p><?php echo htmlspecialchars($cat['description']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback Static Cards -->
                <a href="/TuneTrove/shop/?cat=Guitars" class="category-card reveal">
                    <div class="category-img">🎸</div>
                    <div class="category-info">
                        <h3>Guitars</h3>
                        <p>Acoustic, Electric, and Bass guitars from top brands.</p>
                    </div>
                </a>
                <a href="/TuneTrove/shop/?cat=Keyboards" class="category-card reveal">
                    <div class="category-img">🎹</div>
                    <div class="category-info">
                        <h3>Keyboards</h3>
                        <p>Digital pianos, synthesizers, and modern MIDI gear.</p>
                    </div>
                </a>
                <a href="/TuneTrove/shop/?cat=Drums%20%26%20Percussion" class="category-card reveal">
                    <div class="category-img">🥁</div>
                    <div class="category-info">
                        <h3>Percussion</h3>
                        <p>Acoustic sets and electronic drum kits for any stage.</p>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="reveal" style="margin: 6rem 0; position: relative;">
    <div class="container">
        <div style="background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%); padding: 5rem; border-radius: var(--radius); text-align: center; box-shadow: var(--shadow-lg); overflow: hidden; position: relative;">
             <!-- Decorative elements -->
             <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
             <div style="position: absolute; bottom: -50px; left: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
             
             <h2 style="font-family: var(--font-heading); font-size: 3rem; margin-bottom: 1.5rem; position: relative; z-index: 1;">Free Shipping Over £100</h2>
             <p style="font-size: 1.25rem; color: rgba(255,255,255,0.8); margin-bottom: 3rem; max-width: 600px; margin-left: auto; margin-right: auto; position: relative; z-index: 1;">Upgrade your sound today. We provide secure, insured shipping on all premium instruments worldwide.</p>
             <a href="/TuneTrove/shop/" class="btn" style="background: white; color: var(--primary); padding: 1.25rem 3rem; font-size: 1.125rem; position: relative; z-index: 1;">Browse Full Inventory</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
