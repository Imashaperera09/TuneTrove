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

<div class="collection-spotlight" style="min-height: 100vh; background: #020617;">
    <!-- Adaptive Collection Hero -->
    <section style="position: relative; min-height: 35vh; display: flex; align-items: center; overflow: hidden; background: #0f172a; padding: 2.5rem 0;">
        <div style="position: absolute; inset: 0; background-image: url('/TuneTrove/user/assets/images/<?php echo !empty($category['image_url']) ? htmlspecialchars($category['image_url']) : ''; ?>'); background-size: cover; background-position: center; opacity: 0.3; filter: blur(20px) brightness(0.5); transform: scale(1.1);"></div>
        
        <div class="container" style="position: relative; z-index: 10;">
            <div style="max-width: 800px;">
                <nav style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; color: var(--primary);">
                    <a href="categories.php" style="color: inherit; text-decoration: none; opacity: 0.6;">Vault</a>
                    <span style="opacity: 0.3;">/</span>
                    <span><?php echo htmlspecialchars($category_name); ?></span>
                </nav>
                
                <h1 style="font-family: var(--font-heading); font-size: 3.5rem; letter-spacing: -0.05em; line-height: 0.9; margin-bottom: 1rem; color: #fff;">
                    The <span style="color: var(--primary);"><?php echo htmlspecialchars($category_name); ?></span> <br>Collection
                </h1>
                
                <p style="font-size: 1rem; color: rgba(255,255,255,0.7); line-height: 1.5; max-width: 600px;">
                    <?php echo htmlspecialchars($category['description']); ?>
                </p>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 2rem; align-items: center;">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 1.15rem; font-weight: 800; color: #fff;"><?php echo count($products); ?></span>
                        <span style="font-size: 0.55rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Curated Pieces</span>
                    </div>
                    <div style="height: 20px; width: 1px; background: rgba(255,255,255,0.1);"></div>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 1.15rem; font-weight: 800; color: #fff;"><?php echo $icons[$category_name] ?? '📦'; ?></span>
                        <span style="font-size: 0.55rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">Family Signature</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Animated Background Element -->
        <div style="position: absolute; right: 2%; bottom: 5%; font-size: 14rem; opacity: 0.2; color: var(--primary); filter: drop-shadow(0 0 40px rgba(37, 99, 235, 0.25)); font-family: var(--font-heading); pointer-events: none; transform: rotate(-5deg); z-index: 1;" class="reveal">
            <?php echo $icons[$category_name] ?? 'M'; ?>
        </div>
    </section>

    <!-- Collection Mosaic -->
    <section style="padding: 8rem 0; background: radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.05), transparent 70%);">
        <div class="container">
            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 10rem 2rem; background: rgba(30, 41, 59, 0.2); border-radius: 3rem; border: 1px dashed rgba(255, 255, 255, 0.05);">
                    <h2 style="font-family: var(--font-heading); font-size: 2rem; color: #fff; margin-bottom: 1rem;">Vault Empty</h2>
                    <p style="color: var(--text-muted);">This collection is currently being curated. Check back soon for new arrivals.</p>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 3rem;">
                    <?php foreach ($products as $p): ?>
                        <div class="mosaic-card reveal">
                            <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                                <div class="mosaic-img-wrap" style="height: 400px; background: rgba(15, 23, 42, 0.4); border-radius: 2rem; overflow: hidden; position: relative;">
                                    <?php if ($p['image_url']): ?>
                                        <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($p['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);" class="product-img">
                                    <?php else: ?>
                                        <div style="font-size: 6rem; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; opacity: 0.2;" class="product-img"><?php echo $icons[$category_name] ?? '🎸'; ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="mosaic-overlay" style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(2, 6, 23, 0.9) 0%, transparent 60%); opacity: 0.8; transition: opacity 0.4s ease;"></div>
                                    
                                    <div style="position: absolute; bottom: 2rem; left: 2rem; right: 2rem;">
                                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($p['brand']); ?></div>
                                        <h3 style="font-family: var(--font-heading); font-size: 1.5rem; color: #fff; line-height: 1.1;"><?php echo htmlspecialchars($p['name']); ?></h3>
                                    </div>
                                    
                                    <?php if ($p['is_digital']): ?>
                                        <div style="position: absolute; top: 1.5rem; right: 1.5rem; background: rgba(168, 85, 247, 0.2); backdrop-filter: blur(10px); color: #c084fc; padding: 0.5rem 1rem; border-radius: 999px; font-size: 0.6rem; font-weight: 900; letter-spacing: 0.1em;">DIGITAL VAULT</div>
                                    <?php endif; ?>
                                </div>
                                <div style="padding: 1.5rem 0; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 800; color: #fff;"><?php echo format_price($p['price']); ?></span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 2px;">DISCOVER →</span>
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
    transform: translateY(-10px);
}
.mosaic-card:hover .product-img {
    transform: scale(1.05);
}
.mosaic-card:hover .mosaic-overlay {
    opacity: 1;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

@media (max-width: 768px) {
    h1[style*="font-size: 5rem"] { font-size: 3rem !important; }
    section[style*="height: 60vh"] { height: auto !important; padding: 10rem 0 5rem !important; }
}
</style>

<?php require_once '../includes/footer.php'; ?>
