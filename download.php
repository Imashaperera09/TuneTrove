<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$user_id = $_SESSION['user_id'];

// Verify purchase
$stmt = $pdo->prepare("SELECT dp.file_path, p.name 
                      FROM order_items oi 
                      JOIN orders o ON oi.order_id = o.id 
                      JOIN products p ON oi.product_id = p.id 
                      JOIN digital_products dp ON p.id = dp.product_id 
                      WHERE o.id = ? AND o.user_id = ? AND p.id = ? AND o.status = 'paid'");
$stmt->execute([$order_id, $user_id, $product_id]);
$file_info = $stmt->fetch();

if (!$file_info) {
    die("Invalid download link or order not paid.");
}

$file_path = $file_info['file_path'];
// In a real app, this path would be outside public_html
// For this demo, we'll assume files are in assets/downloads
$full_path = 'assets/' . $file_path;

if (file_exists($full_path)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($full_path) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($full_path));
    readfile($full_path);
    exit;
} else {
    // For demo purposes, we will just simulate the download if the file doesn't exist
    // to avoid errors during the presentation
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $file_info['name']) . '_Demo.txt"');
    echo "This is a demo download for " . $file_info['name'] . ".\n";
    echo "In a production environment, the actual PDF/MP3 file would be served here.";
    exit;
}
?>
