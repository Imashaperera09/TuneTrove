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
            <a href="/TuneTrove/shop/categories.php" class="btn" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); color: white; padding: 1.5rem 2.5rem; font-size: 1.125rem;">The Collections</a>
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
<section style="padding: 10rem 0; background: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.05), transparent 40%), #020617; overflow: hidden;">
    <div class="container">
        <div style="margin-bottom: 5rem; display: flex; justify-content: space-between; align-items: flex-end; gap: 3rem; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <span style="color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; font-size: 0.875rem; display: block; margin-bottom: 1rem;" class="reveal">Explore Your Passion</span>
                <h2 class="section-title reveal" style="text-align: left; margin-bottom: 1.5rem; line-height: 1.1;">Browse <br>Collections</h2>
                <p class="reveal" style="color: var(--text-muted); max-width: 450px; font-size: 1.125rem; line-height: 1.6;">Exquisite instruments for every stage of your musical journey. Hand-picked and meticulously tested for the professional artist.</p>
            </div>
            <div class="reveal" style="margin-bottom: 1rem;">
                <a href="/TuneTrove/shop/categories.php" class="btn" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem 2rem; border-radius: 1rem; text-decoration: none; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='var(--primary)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">View All Categories →</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="category-grid-scroll">
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
                        <div class="category-img" <?php echo !empty($cat['image_url']) ? 'style="background-image: url(\'/TuneTrove/assets/images/' . htmlspecialchars($cat['image_url']) . '\');"' : ''; ?>>
                            <?php if (empty($cat['image_url'])): ?>
                                <span><?php echo $icons[$cat['name']] ?? '📦'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="category-info">
                            <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                            <p><?php echo htmlspecialchars($cat['description']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback Static Cards (Edit style tags to add your images) -->
                <a href="/TuneTrove/shop/?cat=Guitars" class="category-card reveal">
                    <div class="category-img" style="background-image: url('/TuneTrove/assets/images/guitar.');">
                        <span>🎸</span>
                    </div>
                    <div class="category-info">
                        <h3>Guitars</h3>
                        <p>Acoustic, Electric, and Bass guitars from top brands.</p>
                    </div>
                </a>
                <a href="/TuneTrove/shop/?cat=Keyboards" class="category-card reveal">
                    <div class="category-img" style="background-image: url('/TuneTrove/assets/images/keyboard_cat.jpg');">
                        <span>🎹</span>
                    </div>
                    <div class="category-info">
                        <h3>Keyboards</h3>
                        <p>Digital pianos, synthesizers, and modern MIDI gear.</p>
                    </div>
                </a>
                <a href="/TuneTrove/shop/?cat=Drums%20%26%20Percussion" class="category-card reveal">
                    <div class="category-img" style="background-image: url('/TuneTrove/assets/images/drum_cat.jpg');">
                        <span>🥁</span>
                    </div>
                    <div class="category-info">
                        <h3>Percussion</h3>
                        <p>Acoustic sets and electronic drum kits for any stage.</p>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>



<?php require_once 'includes/footer.php'; ?>
