<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect contextually
if (isset($_GET['source']) && $_GET['source'] === 'admin') {
     header("Location: login.php");
} else {
    // We'll just default to taking them back to the admin login page from the admin dashboard
    header("Location: login.php");
}
exit();
