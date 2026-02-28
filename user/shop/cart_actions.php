<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('TUNETROVE_USER_SESSION');
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $action = isset($_POST['buy_now']) ? 'buy' : 'add';

    if ($product_id > 0) {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add or update quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }

        // Handle redirection
        if ($action === 'buy') {
            header("Location: checkout.php");
            exit();
        } else {
            // User feedback: Redirect to cart page as requested
            redirect('cart.php', 'Masterpiece added to your collection.');
        }
    }
}

// Fallback
header("Location: index.php");
exit();
