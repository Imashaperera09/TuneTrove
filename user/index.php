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

<!-- Blue Premium Hero Section -->
<section class="marketing-hero" style="background: radial-gradient(circle at 0% 0%, #002d5a 0%, var(--background) 100%); padding: 3rem 0; overflow: hidden; color: white; position: relative; border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('/TuneTrove/user/assets/images/mesh-glow.png') no-repeat center center/cover; opacity: 0.1; pointer-events: none;"></div>
    <div class="container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; position: relative; z-index: 2;">
        <div class="reveal">
            <p style="text-transform: uppercase; font-size: 0.85rem; font-weight: 800; color: var(--accent); letter-spacing: 0.3em; margin-bottom: 1.5rem;">The Premium Experience</p>
            <h1 style="font-family: var(--font-heading); font-size: 5rem; font-weight: 800; line-height: 1; margin-bottom: 2.5rem; letter-spacing: -0.04em;">Elevate Your <br><span style="color: var(--primary); text-shadow: 0 0 30px rgba(14, 165, 233, 0.3);">Sonic</span> Signature</h1>
            <p style="font-size: 1.25rem; margin-bottom: 3.5rem; color: #94a3b8; line-height: 1.6; max-width: 550px;">Hand-selected masterpieces from the world's most prestigious luthiers. Precision-crafted for the most discerning musicians.</p>
            <div style="display: flex; gap: 1.5rem; align-items: center;">
                <a href="/TuneTrove/user/shop/" class="btn" style="background: var(--primary); color: white; padding: 1.25rem 3.5rem; font-size: 1.1rem; font-weight: 800; border-radius: 4px; text-transform: uppercase; box-shadow: 0 10px 30px rgba(14, 165, 233, 0.4);">Browse Collection</a>
            </div>
        </div>
        <div class="reveal" style="position: relative; height: 350px; display: flex; align-items: center; justify-content: center;">
            <div style="position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(14, 165, 233, 0.1) 0%, transparent 70%);"></div>
            <?php if (file_exists('assets/images/hero.png')): ?>
                <img src="/TuneTrove/user/assets/images/hero.png" style="width: 100%; transform: rotate(-5deg); filter: drop-shadow(0 40px 100px rgba(0,0,0,0.6));" alt="Premium Instrument">
            <?php else: ?>
                <div style="font-size: 14rem; opacity: 0.3; filter: drop-shadow(0 0 50px var(--primary));">🎻</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section style="padding: 6rem 0 6rem; background: #060b1e;">
    <div class="container">
        <div style="margin-bottom: 6rem; text-align: left; border-left: 4px solid var(--primary); padding-left: 2.5rem;">
            <h2 class="reveal" style="font-family: var(--font-heading); font-size: 4rem; font-weight: 800; color: #fff; margin-bottom: 1rem; letter-spacing: -0.04em;">Shop by Category</h2>
            <p style="font-size: 1.25rem; color: #94a3b8;">Explore our vast selection of premium musical instruments and catalog.</p>
        </div>

        <style>
        @keyframes scrollHorizontal {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-50% - 1rem)); } /* -50% of the total width including gap */
        }
        .category-marquee-container {
            width: 100%;
            overflow: hidden;
            position: relative;
            padding: 2rem 0;
            display: flex;
        }
        .category-marquee {
            display: flex;
            gap: 2rem;
            animation: scrollHorizontal 40s linear infinite;
            width: max-content;
        }
        .category-marquee:hover {
            animation-play-state: paused;
        }
        .category-card-mini {
            width: 260px; /* Reduced size */
            flex-shrink: 0;
        }
        /* Fade edges for slick look */
        .category-marquee-container::before,
        .category-marquee-container::after {
            content: "";
            position: absolute;
            top: 0;
            width: 10%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
        }
        .category-marquee-container::before {
            left: 0;
            background: linear-gradient(to right, #060b1e 0%, transparent 100%);
        }
        .category-marquee-container::after {
            right: 0;
            background: linear-gradient(to left, #060b1e 0%, transparent 100%);
        }
        </style>

        <div class="category-marquee-container">
            <div class="category-marquee">
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
                // Duplicate array for seamless looping
                $displayCategories = array_merge($categories, $categories);
                ?>
                <?php if (!empty($displayCategories)): ?>
                    <?php foreach ($displayCategories as $cat): ?>
                        <a href="/TuneTrove/user/shop/collection.php?name=<?php echo urlencode($cat['name']); ?>" 
                           class="category-card-mini" 
                           style="text-decoration: none; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 1rem; overflow: hidden; display: flex; flex-direction: column; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);"
                           onmouseover="this.style.transform='translateY(-10px)'; this.style.borderColor='rgba(14, 165, 233, 0.4)'; this.style.background='rgba(14, 165, 233, 0.03)';"
                           onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255, 255, 255, 0.05)'; this.style.background='rgba(255, 255, 255, 0.02)';"
                        >
                            <div style="height: 180px; background: rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                                <?php if (!empty($cat['image_url'])): ?>
                                    <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($cat['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;">
                                <?php else: ?>
                                    <div style="font-size: 4rem; opacity: 0.5; filter: drop-shadow(0 0 20px var(--primary));"><?php echo $icons[$cat['name']] ?? '📦'; ?></div>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 1.5rem;">
                                <h3 style="font-family: var(--font-heading); font-size: 1.25rem; color: #fff; margin-bottom: 0.5rem; font-weight: 800; letter-spacing: -0.01em;"><?php echo htmlspecialchars($cat['name']); ?></h3>
                                <p style="color: #64748b; line-height: 1.5; font-size: 0.9rem; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?php echo htmlspecialchars($cat['description']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Values Section moved above footer -->
<section style="padding: 4rem 0 2rem; background: var(--background);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 4rem;">
            <div class="reveal" style="text-align: center; padding: 3rem; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--primary);">💎</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 1rem; color: #fff;">The Expert Curation</h3>
                <p style="color: #94a3b8; font-size: 1.1rem; line-height: 1.6;">Every instrument is hand-selected and inspected by our team of master luthiers.</p>
            </div>
            <div class="reveal" style="text-align: center; padding: 3rem; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--primary);">🛰️</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 1rem; color: #fff;">Precision Delivery</h3>
                <p style="color: #94a3b8; font-size: 1.1rem; line-height: 1.6;">Climate-controlled shipping ensure your instrument arrives in perfect tuning.</p>
            </div>
            <div class="reveal" style="text-align: center; padding: 3rem; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1rem;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; color: var(--primary);">🎧</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 1rem; color: #fff;">Lifetime Support</h3>
                <p style="color: #94a3b8; font-size: 1.1rem; line-height: 1.6;">Access to professional setup and expert guidance throughout your musical journey.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
