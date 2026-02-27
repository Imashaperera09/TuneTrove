<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$category_name = isset($_GET['name']) ? $_GET['name'] : null;

if (!$category_name) {
    redirect('categories.php', 'Collection not found.', 'error');
}

// Fetch category details
$stmt = $pdo->prepare("SELECT * FROM categories WHERE name = ?");
$stmt->execute([$category_name]);
$category = $stmt->fetch();

if (!$category) {
    redirect('categories.php', 'Collection not found.', 'error');
}

// Fetch products in this category
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                      JOIN categories c ON p.category_id = c.id 
                      WHERE c.name = ? OR c.parent_id = ?
                      ORDER BY p.created_at DESC");
$stmt->execute([$category_name, $category['id']]);
$products = $stmt->fetchAll();

$icons = [
    'Guitars' => '🎸',
    'Keyboards' => '🎹',
    'Drums & Percussion' => '🥁',
    'Wind Instruments' => ' saxophone',
    'String Instruments' => '🎻',
    'Accessories' => '🔌',
    'Digital Sheet Music' => '📑'
];
?>

<div class="collection-spotlight" style="min-height: 100vh; background: var(--background);">
    <!-- Removed Adaptive Collection Hero section for minimal look -->

    <!-- Collection Mosaic -->
    <section style="padding: 1.5rem 0; background: radial-gradient(circle at 50% 0%, rgba(14, 165, 233, 0.05), transparent 70%);">
        <div class="container">
            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 8rem 2rem; background: rgba(255, 255, 255, 0.01); border-radius: 2rem; border: 1px dashed rgba(255, 255, 255, 0.05);">
                    <h2 style="font-family: var(--font-heading); font-size: 2.5rem; color: #fff; margin-bottom: 1.5rem; letter-spacing: -0.02em;">Vault Curating</h2>
                    <p style="color: #64748b; font-size: 1.1rem;">This collection is currently being appraised. Check back soon for the unveiling.</p>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem;">
                    <?php foreach ($products as $p): ?>
                        <div class="mosaic-card reveal">
                            <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                                <div class="mosaic-img-wrap" style="height: 400px; background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.03); border-radius: 1.5rem; overflow: hidden; position: relative; transition: all 0.5s; display: flex; align-items: center; justify-content: center; padding: 2rem;">
                                    <?php if ($p['image_url']): ?>
                                        <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($p['image_url']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1); filter: drop-shadow(0 20px 40px rgba(0,0,0,0.5));" class="product-img">
                                    <?php else: ?>
                                        <div style="font-size: 8rem; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0.1;" class="product-img"><?php echo $icons[$category_name] ?? '🎸'; ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="mosaic-overlay" style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(2, 6, 23, 0.95) 0%, transparent 60%); opacity: 0.8; transition: opacity 0.4s ease;"></div>
                                    
                                    <div style="position: absolute; bottom: 3rem; left: 3rem; right: 3rem;">
                                        <div style="font-size: 0.8rem; font-weight: 800; color: var(--accent); text-transform: uppercase; letter-spacing: 0.2em; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($p['brand']); ?></div>
                                        <h3 style="font-family: var(--font-heading); font-size: 2rem; color: #fff; line-height: 1.1; letter-spacing: -0.03em;"><?php echo htmlspecialchars($p['name']); ?></h3>
                                    </div>
                                    
                                    <?php if ($p['is_digital']): ?>
                                        <div style="position: absolute; top: 2rem; right: 2rem; background: rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); backdrop-filter: blur(12px); color: var(--primary); padding: 0.6rem 1.25rem; border-radius: 4px; font-size: 0.7rem; font-weight: 900; letter-spacing: 0.2em; text-transform: uppercase;">Digital High-Fidelity</div>
                                    <?php endif; ?>
                                </div>
                                <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-family: var(--font-heading); font-size: 2.25rem; font-weight: 800; color: #fff; letter-spacing: -0.04em;"><?php echo format_price($p['price']); ?></span>
                                    <span style="font-size: 0.8rem; color: var(--accent); font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; border-bottom: 2px solid var(--primary); padding-bottom: 4px;">Acquire →</span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
.mosaic-card {
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.mosaic-card:hover {
    transform: translateY(-15px);
}
.mosaic-card:hover .mosaic-img-wrap {
    border-color: rgba(14, 165, 233, 0.3);
    box-shadow: 0 40px 100px -20px rgba(0,0,0,0.6);
}
.mosaic-card:hover .product-img {
    transform: scale(1.08);
}
.mosaic-card:hover .mosaic-overlay {
    opacity: 1;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

@media (max-width: 768px) {
    h1[style*="font-size: 4.5rem"] { font-size: 3rem !important; }
    section[style*="min-height: 40vh"] { min-height: auto !important; padding: 6rem 0 3rem !important; }
}
</style>

<?php require_once '../includes/footer.php'; ?>
