<?php
/**
 * StudentHub - authenticate.php
 * Validates credentials using MySQLi and password_verify()
 * Regenerates session ID and enforces role-based redirection.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Page the visitor originally wanted (whitelisted) - used to send them back after login
$next = sh_safe_redirect((string)($_POST['redirect'] ?? ''));
$rq   = $next !== '' ? '&redirect=' . urlencode($next) : '';

// Verify CSRF token if provided by form
if (!empty($_POST['csrf'])) {
    $token = (string)$_POST['csrf'];
    if (!hash_equals(sh_csrf(), $token)) {
        header('Location: login.php?error=invalid' . $rq);
        exit;
    }
}

$loginInput = trim((string)($_POST['studentId'] ?? $_POST['email'] ?? $_POST['username'] ?? ''));
$password   = (string)($_POST['password'] ?? '');

if ($loginInput === '' || $password === '') {
    header('Location: login.php?error=empty&email=' . urlencode($loginInput) . $rq);
    exit;
}

$emailInput = strtolower($loginInput);

// Query user by email OR student_id using prepared statement
$stmt = $db->prepare("SELECT id, name, email, password, role, student_id, course, status FROM users WHERE email = ? OR student_id = ? LIMIT 1");
$idInput = strtoupper($loginInput);
$stmt->bind_param('ss', $emailInput, $idInput);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password'])) {
    header('Location: login.php?error=invalid&email=' . urlencode($loginInput) . $rq);
    exit;
}

// Security: Regenerate session ID upon successful authentication
session_regenerate_id(true);

$_SESSION['user_id']       = (int)$user['id'];
$_SESSION['user_name']     = $user['name'];
$_SESSION['user_email']    = $user['email'];
$_SESSION['user_role']     = $user['role'];
$_SESSION['student_id']    = $user['student_id'] ?? '';
$_SESSION['course']        = $user['course'] ?? '';
$_SESSION['last_activity'] = time();
$_SESSION['login_time']    = time();

// Record last login timestamp
$updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$updateStmt->bind_param('i', $user['id']);
$updateStmt->execute();
$updateStmt->close();

// Record audit log
try {
    sh_audit($db, 'login', 'user', (int)$user['id']);
} catch (Throwable $e) {
    // Non-fatal if audit table is offline
}

// Role-based redirection:
// Student -> student_dashboard.php
// Admin   -> admin_dashboard.php
$home = ($user['role'] === 'admin') ? 'admin_dashboard.php' : 'student_dashboard.php';
if ($next === '' || ($next === 'admin_dashboard.php' && $user['role'] !== 'admin') || ($next === 'student_dashboard.php' && $user['role'] === 'admin')) {
    $next = $home;
}
header('Location: ' . $next);
exit;
