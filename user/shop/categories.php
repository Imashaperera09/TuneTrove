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

<div style="background: var(--background); min-height: 100vh; padding-top: 0.5rem; padding-bottom: 8rem;">
    <div class="container">
        <!-- Breadcrumb / Header -->
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <p style="text-transform: uppercase; font-size: 0.85rem; font-weight: 800; color: var(--accent); letter-spacing: 0.3em; margin-bottom: 1rem;">The Virtual Vault</p>
            <h1 style="font-family: var(--font-heading); font-size: 4rem; weight: 800; color: #fff; letter-spacing: -0.04em; margin: 0;">Shop by <span style="color: var(--primary);">Category</span></h1>
            <div style="width: 60px; height: 4px; background: var(--primary); margin: 2rem auto; border-radius: 999px;"></div>
        </div>
        
        <style>
            .category-row-single { display: flex; flex-wrap: nowrap; gap: 1rem; justify-content: center; align-items: stretch; }
            .category-card { min-width: 180px; max-width: 220px; flex: 0 0 180px; text-decoration: none; color: inherit; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 1rem; overflow: hidden; transition: all 0.4s cubic-bezier(0.16,1,0.3,1); height: 100%; display: flex; flex-direction: column; box-shadow: 0 2px 12px 0 rgba(0,0,0,0.07); }
            .category-card:hover { transform: translateY(-6px) scale(1.03); border-color: rgba(14,165,233,0.4); background: rgba(14,165,233,0.03); box-shadow: 0 6px 24px 0 rgba(14,165,233,0.10); }
            .category-card .cat-img { height: 90px; background: rgba(0, 0, 0, 0.18); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; border-bottom: 1px solid rgba(255,255,255,0.03); }
            .category-card .cat-img img { width: 100%; height: 100%; object-fit: cover; opacity: 0.85; }
            .category-card .cat-img .icon { font-size: 2rem; opacity: 0.5; filter: drop-shadow(0 0 10px var(--primary)); text-align: center; }
            .category-card .cat-body { padding: 0.7rem 0.7rem 0.5rem 0.7rem; flex: 1; display: flex; flex-direction: column; }
            .category-card h2 { font-family: var(--font-heading); font-size: 0.98rem; font-weight: 800; color: #fff; margin-bottom: 0.4rem; letter-spacing: -0.02em; }
            .category-card p { color: #64748b; margin-bottom: 0.5rem; line-height: 1.5; font-size: 0.85rem; }
            .category-card .explore { margin-top: auto; display: flex; align-items: center; gap: 0.4rem; color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; }
            @media (max-width: 900px) { .category-row-single { flex-wrap: wrap; } }
        </style>

        <div class="category-row-single">
            <?php 
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
            <?php if (!empty($main_categories)): ?>
                <?php foreach ($main_categories as $cat): ?>
                    <a class="category-card" href="collection.php?name=<?php echo urlencode($cat['name']); ?>">
                        <div class="cat-img">
                            <?php if (!empty($cat['image_url'])): ?>
                                <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($cat['image_url']); ?>">
                            <?php else: ?>
                                <span class="icon"><?php echo $icons[$cat['name']] ?? '📦'; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="cat-body">
                            <h2><?php echo htmlspecialchars($cat['name']); ?></h2>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                  <div style="width:100%; text-align: center; padding: 6rem 0; background: rgba(255, 255, 255, 0.02); border-radius: 1rem; border: 2px dashed rgba(255, 255, 255, 0.05);">
                    <p style="color: #64748b; font-size: 1.1rem;">No categories found. Our curators are currently preparing the selection.</p>
                  </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
