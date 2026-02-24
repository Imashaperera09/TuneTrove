<?php
/**
 * Core utility functions for Melody Masters
 */

/**
 * Format price to currency
 */
function format_price($amount) {
    return '£' . number_format($amount, 2);
}

/**
 * Calculate shipping cost based on total amount
 */
function calculate_shipping($total) {
    if ($total > 100) {
        return 0;
    }
    return 10.00; // Standard shipping fee
}

/**
 * Redirect with optional message
 */
function redirect($url, $msg = '', $type = 'success') {
    if ($msg) {
        $_SESSION['msg'] = $msg;
        $_SESSION['msg_type'] = $type;
    }
    header("Location: $url");
    exit();
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input));
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check user role
 */
function has_role($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}
?>
