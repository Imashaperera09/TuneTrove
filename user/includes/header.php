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
    <link rel="stylesheet" href="/TuneTrove/user/assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content-row">
                <div class="logo">
                    <a href="/TuneTrove/user/">
                        <span class="logo-accent">Tune</span>Trove
                    </a>
                </div>
                
                <nav class="main-nav-links">
                    <ul>
                        <li><a href="/TuneTrove/user/shop/categories.php">Categories</a></li>
                        <li><a href="/TuneTrove/user/shop/?sale=1">Deals</a></li>
                        <li><a href="/TuneTrove/user/shop/?sort=newest">What's New</a></li>
                        <li><a href="/TuneTrove/user/shop/?type=used">Used Gear</a></li>
                    </ul>
                </nav>

                <div class="search-container">
                    <form action="/TuneTrove/user/shop/" method="GET">
                        <input type="text" name="search" class="search-input" placeholder="Search gear...">
                        <button type="submit" class="search-btn">🔍</button>
                    </form>
                </div>

                <div class="header-actions">
                    <?php if (is_logged_in()): ?>
                        <a href="/TuneTrove/user/account/index.php" class="account-link">Account</a>
                        <a href="/TuneTrove/user/auth/logout.php" class="account-link">Logout</a>
                    <?php else: ?>
                        <a href="/TuneTrove/user/auth/login.php" class="account-link">Login</a>
                    <?php endif; ?>
                    <a href="/TuneTrove/user/shop/cart.php" class="cart-link">
                        <span class="cart-icon">🛒</span>
                        <span class="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                    </a>
                </div>
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

<script>
    // Global Header & Scroll Logic
    window.addEventListener('scroll', () => {
        const header = document.querySelector('.main-header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Global Reveal Observer
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, { threshold: 0.1 });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
    });
</script>
