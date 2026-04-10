<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('TUNETROVE_ADMIN_SESSION');
    session_start();
}

// Check if user is logged in and has access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin', 'staff'])) {
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
        <div class="sidebar-header" style="padding: 2rem 1.5rem; text-align: left; display: flex; flex-direction: column; gap: 0.5rem;">
            <div style="font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 0.75rem; color: #fff; letter-spacing: -0.02em;">
                <span style="color: var(--admin-primary); font-size: 1.75rem;">🎵</span> TuneTrove
            </div>
            <div>
                <?php 
                $role = $_SESSION['user_role'] ?? 'staff';
                $badge_color = in_array($role, ['admin', 'superadmin']) ? '#6366f1' : '#10b981';
                $role_label  = in_array($role, ['admin', 'superadmin']) ? '🛡️ Admin' : '👤 Staff';
                ?>
                <span style="background: <?php echo $badge_color; ?>15; color: <?php echo $badge_color; ?>; border: 1px solid <?php echo $badge_color; ?>33; padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                    <?php echo $role_label; ?>
                </span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><span>📊</span> Dashboard</a></li>
                <li><a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>"><span>📦</span> Products</a></li>
                <li><a href="categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>"><span>📁</span> Categories</a></li>
                <li><a href="orders.php" class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>"><span>🛒</span> Orders</a></li>
                <li>
                    <a href="deals.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'deals.php' ? 'active' : ''; ?>">
                        <span class="icon">🏷️</span> Promotions
                    </a>
                </li>
                <li><a href="reviews.php" class="<?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>"><span>⭐</span> Reviews</a></li>
                <?php if (in_array($_SESSION['user_role'], ['admin', 'superadmin'])): ?>
                <li><a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>"><span>👥</span> Users</a></li>
                <li><a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>"><span>⚙️</span> Settings</a></li>
                <?php endif; ?>
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
                        case 'deals.php': echo 'Deals Management'; break;
                        case 'reviews.php': echo 'Customer Reviews'; break;
                        case 'users.php': echo 'User Management'; break;
                        case 'settings.php': echo 'System Settings'; break;
                        default: echo 'Dashboard Overview';
                    }
                ?></h1>
                <p style="color: var(--admin-text-muted);">Welcome back, <?php echo $_SESSION['user_role'] === 'staff' ? 'staff member' : 'administrator'; ?>.</p>
            </div>
            <a href="settings.php" class="user-profile" style="display: flex; align-items: center; gap: 1rem; text-decoration: none; color: inherit; transition: all 0.2s; cursor: pointer; padding: 0.5rem; border-radius: 0.75rem;" onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='transparent'; this.style.transform='translateY(0)'">
                <div style="text-align: right;">
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin User'); ?></div>
                    <div style="font-size: 0.75rem; color: var(--admin-text-muted); text-transform: capitalize;"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Admin'); ?></div>
                </div>
                <div style="width: 40px; height: 40px; background: var(--admin-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </div>
            </a>
        </header>
