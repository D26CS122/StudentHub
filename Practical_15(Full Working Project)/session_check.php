<?php
/**
 * StudentHub - session_check.php
 * Course: ITUE203 - Web Development Frameworks (Practical 10)
 * Centralized session security & validation handler.
 * Included at the top of every protected page.
 *
 * Verifies:
 *  1. Active session existence (Authentication)
 *  2. Inactivity timeout (Session Expiration)
 *  3. Role-based access control (Authorization)
 */

if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 300); // 5 minutes inactivity timeout
}

function sessionCheck(string $requiredRole = 'any'): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 1. Session Existence: Must be logged in
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php?error=session");
        exit;
    }

    // 2. Session Inactivity Timeout Check
    $now = time();
    $timedOut = false;

    // Allow manual timeout simulation for testing / demonstration via ?test_timeout=1
    if (isset($_GET['test_timeout']) && $_GET['test_timeout'] == '1') {
        $timedOut = true;
    } elseif (isset($_SESSION['last_active'])) {
        $inactiveSeconds = $now - $_SESSION['last_active'];
        if ($inactiveSeconds > SESSION_TIMEOUT) {
            $timedOut = true;
        }
    }

    if ($timedOut) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        header("Location: timeout.php");
        exit;
    }

    // Refresh last active timestamp
    $_SESSION['last_active'] = $now;

    // 3. Role-Based Access Control (RBAC)
    if ($requiredRole !== 'any') {
        $currentUserRole = $_SESSION['user_role'] ?? 'unknown';
        if ($currentUserRole !== $requiredRole) {
            header("Location: dashboard.php?access=denied&page=" . urlencode($requiredRole));
            exit;
        }
    }
}
?>
