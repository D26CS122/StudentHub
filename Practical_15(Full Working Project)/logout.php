<?php
/**
 * StudentHub - logout.php
 * Secure logout: destroys session, removes cookies, and audits action.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

if (!empty($_SESSION['user_id'])) {
    try {
        sh_audit($db, 'logout', 'user', (int)$_SESSION['user_id']);
    } catch (Throwable $e) {}
}

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

header('Location: login.php?msg=logout');
exit;
