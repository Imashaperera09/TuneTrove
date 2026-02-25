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

<div style="background: var(--background); min-height: 100vh; padding-top: 5rem; padding-bottom: 8rem;">
    <div class="container">
        <!-- Breadcrumb / Header -->
        <div style="text-align: center; margin-bottom: 5rem;">
            <p style="text-transform: uppercase; font-size: 0.85rem; font-weight: 800; color: var(--accent); letter-spacing: 0.3em; margin-bottom: 1rem;">The Virtual Vault</p>
            <h1 style="font-family: var(--font-heading); font-size: 4rem; weight: 800; color: #fff; letter-spacing: -0.04em; margin: 0;">Shop by <span style="color: var(--primary);">Category</span></h1>
            <div style="width: 60px; height: 4px; background: var(--primary); margin: 2rem auto; border-radius: 999px;"></div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.5rem;">
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
                    <a href="collection.php?name=<?php echo urlencode($cat['name']); ?>" 
                       style="text-decoration: none; color: inherit; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 1rem; overflow: hidden; display: flex; flex-direction: column; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); height: 100%;"
                       onmouseover="this.style.transform='translateY(-10px)'; this.style.borderColor='rgba(14, 165, 233, 0.4)'; this.style.background='rgba(14, 165, 233, 0.03)';"
                       onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='rgba(255, 255, 255, 0.05)'; this.style.background='rgba(255, 255, 255, 0.02)';"
                    >
                        <div style="height: 240px; background: rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; border-bottom: 1px solid rgba(255, 255, 255, 0.03);">
                            <?php if (!empty($cat['image_url'])): ?>
                                <img src="/TuneTrove/user/assets/images/<?php echo htmlspecialchars($cat['image_url']); ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;">
                            <?php else: ?>
                                <div style="font-size: 6rem; opacity: 0.5; filter: drop-shadow(0 0 30px var(--primary)); text-align: center;"><?php echo $icons[$cat['name']] ?? '📦'; ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 3rem; flex: 1; display: flex; flex-direction: column;">
                            <h2 style="font-family: var(--font-heading); font-size: 1.85rem; font-weight: 800; color: #fff; margin-bottom: 1rem; letter-spacing: -0.02em;"><?php echo htmlspecialchars($cat['name']); ?></h2>
                            <p style="color: #64748b; margin-bottom: 2.5rem; line-height: 1.7; font-size: 1.05rem;"><?php echo htmlspecialchars($cat['description']); ?></p>
                            <div style="margin-top: auto; display: flex; align-items: center; gap: 0.75rem; color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.1em;">
                                Explore Collection <span style="font-size: 1.2rem;">→</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                  <div style="grid-column: 1 / -1; text-align: center; padding: 10rem 0; background: rgba(255, 255, 255, 0.02); border-radius: 1rem; border: 2px dashed rgba(255, 255, 255, 0.05);">
                    <p style="color: #64748b; font-size: 1.25rem;">No categories found. Our curators are currently preparing the selection.</p>
                  </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
