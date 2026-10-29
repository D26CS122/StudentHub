<?php
/**
 * StudentHub - dashboard.php
 * Protected entry point. Redirects students to student_dashboard.php
 * and administrators to admin_dashboard.php.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/security.php';

// Ensure user is authenticated
sh_require_login();

if (($_SESSION['user_role'] ?? '') === 'admin') {
    header('Location: admin_dashboard.php');
} else {
    header('Location: student_dashboard.php');
}
exit;
