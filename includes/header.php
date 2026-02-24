<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melody Masters - Premier Music Instrument Shop</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- Main Style -->
    <link rel="stylesheet" href="/TuneTrove/assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container header-container">
            <div class="logo">
                <a href="/TuneTrove/">
                    <span class="logo-accent">Melody</span>Masters
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="/TuneTrove/">Home</a></li>
                    <li><a href="/TuneTrove/shop/">Shop</a></li>
                    <li><a href="/TuneTrove/categories.php">Categories</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="/TuneTrove/account.php">My Account</a></li>
                        <?php if (has_role('admin') || has_role('staff')): ?>
                            <li><a href="/TuneTrove/admin/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="/TuneTrove/auth/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/TuneTrove/auth/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="header-actions">
                <a href="/TuneTrove/cart.php" class="cart-link">
                    <span class="cart-icon">🛒</span>
                    <span class="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                </a>
            </div>
        </div>
    </header>
    <main>
        <div class="container content-container">
            <?php if (isset($_SESSION['msg'])): ?>
                <div class="alert alert-<?php echo $_SESSION['msg_type']; ?>">
                    <?php 
                    echo $_SESSION['msg']; 
                    unset($_SESSION['msg']);
                    unset($_SESSION['msg_type']);
                    ?>
                </div>
            <?php endif; ?>
