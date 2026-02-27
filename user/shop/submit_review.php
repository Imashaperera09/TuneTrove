<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $user_id = $_SESSION['user_id'];

    if ($product_id && $rating >= 1 && $rating <= 5 && !empty($comment)) {
        // Verify user can review (has purchased)
        $stmt = $pdo->prepare("SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status IN ('paid', 'shipped', 'completed')");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->fetch()) {
            // Check if already reviewed
            $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            
            if (!$stmt->fetch()) {
                // Insert review
                $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$product_id, $user_id, $rating, $comment]);
                redirect("product.php?id=$product_id", "Thank you for your review!", "success");
            } else {
                redirect("product.php?id=$product_id", "You have already reviewed this product.", "error");
            }
        } else {
            redirect("product.php?id=$product_id", "You can only review products you have purchased.", "error");
        }
    } else {
        redirect("product.php?id=$product_id", "Please provide a valid rating and comment.", "error");
    }
} else {
    redirect("../index.php", "Unauthorized access.", "error");
}
?>
