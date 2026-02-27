<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Ensure user is logged in
if (!is_logged_in()) {
    redirect('/TuneTrove/user/auth/login.php', 'Please login to download files.', 'error');
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$user_id = $_SESSION['user_id'];

if (!$order_id || !$product_id) {
    redirect('index.php', 'Invalid download request.', 'error');
}

// 1. Verify that the order belongs to the user and is in a valid state
$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('index.php', 'Order not found.', 'error');
}

if (!in_array($order['status'], ['paid', 'shipped', 'completed'])) {
    redirect("view_order.php?id=$order_id", 'Payment must be completed to download digital assets.', 'error');
}

// 2. Verify that the product is actually part of this order
$stmt = $pdo->prepare("SELECT quantity FROM order_items WHERE order_id = ? AND product_id = ?");
$stmt->execute([$order_id, $product_id]);
$order_item = $stmt->fetch();

if (!$order_item) {
    redirect("view_order.php?id=$order_id", 'Product not found in this order.', 'error');
}

// 3. Look up the digital file path
$stmt = $pdo->prepare("SELECT file_path FROM digital_products WHERE product_id = ?");
$stmt->execute([$product_id]);
$digital_product = $stmt->fetch();

if (!$digital_product || empty($digital_product['file_path'])) {
    redirect("view_order.php?id=$order_id", 'Digital file not found. Please contact support.', 'error');
}

// 4. Determine absolute file path and verify it exists
$base_path = dirname(__DIR__, 2); // Goes up two directories from user/account to root
$absolute_path = $base_path . '/' . ltrim($digital_product['file_path'], '/');

if (!file_exists($absolute_path)) {
    // Also try checking relative to htdocs
    $alternative_path = '../../' . ltrim($digital_product['file_path'], '/');
    if (!file_exists($alternative_path)) {
        redirect("view_order.php?id=$order_id", 'The file is temporarily unavailable. Please contact support.', 'error');
    } else {
        $absolute_path = $alternative_path;
    }
}

// 5. Serve the file for download securely
$file_name = basename($absolute_path);
$mime_type = mime_content_type($absolute_path) ?: 'application/octet-stream';

// If it's a ZIP or PDF, force download, otherwise try inline (optional)
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($absolute_path));

// Clear output buffer and read file
if (ob_get_length()) ob_end_clean();
readfile($absolute_path);
exit;
