<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch main categories for the homepage with custom ordering
try {
    $order = ['Guitars', 'Keyboards', 'Drums & Percussion', 'Wind Instruments', 'String Instruments', 'Accessories', 'Digital Sheet Music'];
    $placeholders = implode(',', array_fill(0, count($order), '?'));
    $sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY FIELD(name, $placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($order);
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>

<!-- Sweetwater Style Hero Section -->
<section class="marketing-hero" style="background: linear-gradient(135deg, #f15a24 0%, #d4145a 100%); padding: 5rem 0; overflow: hidden; color: white;">
    <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
        <div class="reveal">
            <h1 style="font-family: var(--font-heading); font-size: 5.5rem; font-weight: 800; line-height: 0.9; margin-bottom: 2.5rem; letter-spacing: -0.04em; text-transform: uppercase;">Software <br>& Recording Sale</h1>
            <p style="font-size: 1.5rem; margin-bottom: 3.5rem; opacity: 0.95; line-height: 1.3; max-width: 500px;">Huge selection, instant savings. Up to 80% off plug-ins, mics, interfaces, monitors, and more.</p>
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                <a href="/TuneTrove/user/shop/" class="btn" style="background: white; color: #d4145a; padding: 1.25rem 3.5rem; font-size: 1.25rem; font-weight: 800; border-radius: 0.25rem; text-transform: uppercase;">Shop Now</a>
                <span style="font-weight: 700; font-size: 1.1rem; border-bottom: 2px solid white; cursor: pointer;">View the Offers</span>
            </div>
        </div>
        <div class="reveal" style="position: relative; height: 400px; display: flex; align-items: center; justify-content: center;">
            <div style="font-size: 15rem; opacity: 0.2; transform: rotate(-15deg);">🎧</div>
            <img src="/TuneTrove/user/assets/images/hero.png" style="position: absolute; width: 140%; transform: translateX(-10%); filter: drop-shadow(0 40px 80px rgba(0,0,0,0.4));" alt="Pro Audio Gear">
        </div>
    </div>
</section>

<!-- Financing Ticker -->
<div style="background: #000; color: #fff; padding: 1.25rem 0; text-align: center; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 4px solid var(--primary);">
    <div class="container">
        UP TO 48-MONTH SPECIAL FINANCING* ON QUALIFYING BRANDS &nbsp; <a href="#" style="color: #63b3ed; text-decoration: underline; margin-left: 1rem;">Learn More</a> &nbsp; <a href="#" style="color: #63b3ed; text-decoration: underline; margin-left:1rem;">Shop the Offers</a>
    </div>
</div>

<!-- Values Section -->
<section style="padding: 10rem 0; background: #fff;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 4rem;">
            <div class="reveal" style="text-align: center; padding: 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--primary);">🎻</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 1rem; color: #333;">The Expert Curation</h3>
                <p style="color: #666; font-size: 1.1rem;">Every instrument is hand-selected and inspected by our team of master luthiers.</p>
            </div>
            <div class="reveal" style="text-align: center; padding: 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--primary);">🚚</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 1rem; color: #333;">Precision Delivery</h3>
                <p style="color: #666; font-size: 1.1rem;">Climate-controlled shipping ensure your instrument arrives in perfect tuning.</p>
            </div>
            <div class="reveal" style="text-align: center; padding: 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--primary);">🎵</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 1rem; color: #333;">Lifetime Support</h3>
                <p style="color: #666; font-size: 1.1rem;">Access to professional setup and expert guidance throughout your musical journey.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section style="padding: 10rem 0; background: #f8f9fa; border-top: 1px solid #eee;">
    <div class="container">
        <div style="margin-bottom: 6rem; text-align: left; border-left: 8px solid var(--primary); padding-left: 2rem;">
            <h2 class="reveal" style="font-family: var(--font-heading); font-size: 3.5rem; font-weight: 800; color: #333; margin-bottom: 1rem; letter-spacing: -0.03em;">Shop by Category</h2>
            <p style="font-size: 1.25rem; color: #666;">Explore our vast selection of premium musical instruments and gear.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
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
                    <a href="/TuneTrove/user/shop/collection.php?name=<?php echo urlencode($cat['name']); ?>" 
                       class="reveal" 
                       style="text-decoration: none; background: white; border: 1px solid #eee; border-radius: 0.5rem; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.3s, box-shadow 0.3s;"
                       onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 20px rgba(0,0,0,0.05)';"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        <div style="height: 200px; background: #fff; display: flex; align-items: center; justify-content: center; position: relative;">
                            <?php if (!empty($cat['image_url'])): ?>
                                <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($cat['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="font-size: 5rem;"><?php echo $icons[$cat['name']] ?? '📦'; ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 2rem;">
                            <h3 style="font-family: var(--font-heading); font-size: 1.5rem; color: var(--primary); margin-bottom: 0.5rem; font-weight: 700;"><?php echo htmlspecialchars($cat['name']); ?></h3>
                            <p style="color: #666; line-height: 1.5; font-size: 0.95rem;"><?php echo htmlspecialchars($cat['description']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
