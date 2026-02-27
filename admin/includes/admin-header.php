<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'superadmin')) {
    header("Location: /TuneTrove/admin/login.php");
    exit();
}

require_once __DIR__ . '/../../user/includes/db.php';
require_once __DIR__ . '/../../user/includes/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuneTrove | Admin Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-ui.css">
    <style>
        .icon { width: 20px; height: 20px; vertical-align: middle; }
        .sidebar { overflow-y: auto; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <span style="color: var(--admin-primary);">🎵</span> TuneTrove
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><span>📊</span> Dashboard</a></li>
                <li><a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>"><span>📦</span> Products</a></li>
                <li><a href="categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>"><span>📁</span> Categories</a></li>
                <li><a href="orders.php" class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>"><span>🛒</span> Orders</a></li>
                <li><a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>"><span>👥</span> Users</a></li>
                <li><a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>"><span>⚙️</span> Settings</a></li>
            </ul>
        </nav>
        <div style="padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); margin-top: auto;">
            <a href="/TuneTrove/user/" style="color: var(--admin-sidebar-text); text-decoration: none; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem;">
                <span>⬅️</span> Back to Shop
            </a>
            <a href="logout.php" style="color: var(--admin-sidebar-text); text-decoration: none; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem;">
                <span>🚪</span> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content wrapper starts here, ends in individual pages -->
    <main class="main-content">
        <header class="top-bar">
            <div class="page-title">
                <h1><?php 
                    switch($current_page) {
                        case 'products.php': echo 'Product Management'; break;
                        case 'categories.php': echo 'Category Management'; break;
                        case 'orders.php': echo 'Order Management'; break;
                        case 'users.php': echo 'User Management'; break;
                        case 'settings.php': echo 'System Settings'; break;
                        default: echo 'Dashboard Overview';
                    }
                ?></h1>
                <p style="color: var(--admin-text-muted);">Welcome back, administrator.</p>
            </div>
            <div class="user-profile" style="display: flex; align-items: center; gap: 1rem;">
                <div style="text-align: right;">
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin User'); ?></div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-muted); text-transform: capitalize;"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Admin'); ?></div>
                </div>
                <div style="width: 40px; height: 40px; background: var(--admin-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </div>
            </div>
        </header>
