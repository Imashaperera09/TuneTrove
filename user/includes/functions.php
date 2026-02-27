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
    if ($total >= 100) {
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

/**
 * Get the effective (deal) price for a product.
 * Returns discounted price if a deal is currently active, else original price.
 */
function get_effective_price($product) {
    if (empty($product['is_deal']) || empty($product['discount_percent']) || $product['discount_percent'] <= 0) {
        return (float)$product['price'];
    }

    $today = date('Y-m-d');
    $start = $product['deal_start_date'];
    $end   = $product['deal_end_date'];

    // Check if deal is active (no dates = always active, or within range)
    $active = (!$start && !$end) ||
              (!$start && $today <= $end) ||
              (!$end   && $today >= $start) ||
              ($today >= $start && $today <= $end);

    if ($active) {
        $discount = (float)$product['discount_percent'];
        return round((float)$product['price'] * (1 - $discount / 100), 2);
    }

    return (float)$product['price'];
}

/**
 * Check if a product currently has an active deal.
 */
function has_active_deal($product) {
    return get_effective_price($product) < (float)$product['price'];
}

/**
 * Get deal discount percentage for a product (0 if no active deal).
 */
function get_deal_percent($product) {
    if (has_active_deal($product)) {
        return (int)$product['discount_percent'];
    }
    return 0;
}
