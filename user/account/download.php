<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../includes/db.php';
require_once '../includes/functions.php';
if (!is_logged_in()) { redirect('/TuneTrove/user/auth/login.php', 'Please login.', 'error'); }

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$user_id = $_SESSION['user_id'];

// Verify Order
$stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();
if (!$order || !in_array($order['status'], ['paid', 'shipped', 'completed'])) {
    redirect("view_order.php?id=$order_id", 'Invalid order or payment not completed.', 'error');
}

// Get File
$stmt = $pdo->prepare("SELECT file_path FROM digital_products WHERE product_id = ?");
$stmt->execute([$product_id]);
$dp = $stmt->fetch();
if (!$dp) redirect("view_order.php?id=$order_id", 'File not found.', 'error');

$f = dirname(__DIR__, 2) . '/' . ltrim($dp['file_path'], '/');
if (!file_exists($f)) redirect("view_order.php?id=$order_id", 'File missing.', 'error');

// Clean and Stream
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.basename($f).'"');
header('Content-Length: ' . filesize($f));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
$fp = fopen($f, 'rb');
fpassthru($fp);
fclose($fp);
exit;
