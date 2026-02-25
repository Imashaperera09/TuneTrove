<?php
require_once 'includes/db.php';

echo "Seeding database...\n";

try {
    // Basic Products
    $products = [
        ['Guitars', 'Fender Stratocaster', 'Fender', 'Classic electric guitar with three single-coil pickups.', 1200.00, 5, 0, 'strat.jpg'],
        ['Guitars', 'Gibson Les Paul', 'Gibson', 'Iconic electric guitar known for its thick tone and sustain.', 2500.00, 3, 0, 'lespaul.jpg'],
        ['Keyboards', 'Yamaha P-125', 'Yamaha', 'Compact digital piano with authentic piano performance.', 650.00, 10, 0, 'yamaha_p125.jpg'],
        ['Drums & Percussion', 'Roland TD-17KVX', 'Roland', 'Electronic drum kit with professional sound engine.', 1600.00, 4, 0, 'roland_drums.jpg'],
        ['Accessories', 'Ernie Ball Super Slinky', 'Ernie Ball', 'Nickel wound electric guitar strings.', 10.00, 100, 0, 'strings.jpg'],
        ['Digital Sheet Music', 'Queen - Bohemian Rhapsody', 'Various', 'Digital download for piano and vocals.', 5.99, 0, 1, 'queen_sheet.pdf']
    ];

    $stmt = $pdo->prepare("INSERT INTO products (category_id, name, brand, description, price, stock_quantity, is_digital, image_url) VALUES ((SELECT id FROM categories WHERE name = ? LIMIT 1), ?, ?, ?, ?, ?, ?, ?)");

    foreach ($products as $p) {
        $stmt->execute($p);
        $productId = $pdo->lastInsertId();
        
        // If digital, add to digital_products table
        if ($p[6] == 1) {
            $pdo->prepare("INSERT INTO digital_products (product_id, file_path) VALUES (?, ?)")->execute([$productId, 'downloads/' . $p[7]]);
        }
        echo "Inserted: " . $p[1] . "\n";
    }

    echo "Seeding completed successfully!";

} catch (PDOException $e) {
    echo "Error seeding database: " . $e->getMessage();
}
?>
