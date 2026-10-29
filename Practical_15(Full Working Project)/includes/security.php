<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
  ini_set('session.use_strict_mode','1');
  session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','samesite'=>'Lax','path'=>'/']);
  session_start();
}
const SH_TIMEOUT = 1800;
if (!empty($_SESSION['last_activity']) && time()-(int)$_SESSION['last_activity'] > SH_TIMEOUT) {
  $_SESSION=[]; session_regenerate_id(true); header('Location: '.sh_base_prefix().'login.php?error=timeout'); exit;
}
$_SESSION['last_activity']=time();
function sh_e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function sh_csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function sh_verify_csrf(): void { $v=$_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''); if (!is_string($v)||!hash_equals(sh_csrf(),$v)) { http_response_code(419); exit('Invalid or expired security token. Refresh and try again.'); } }
/* Path prefix back to the project root: '' for root pages, '../' for admin/ and api/ pages. */
function sh_base_prefix(): string {
  $dir  = realpath(dirname((string)($_SERVER['SCRIPT_FILENAME'] ?? ''))) ?: '';
  $root = realpath(__DIR__ . '/..') ?: '';
  return ($dir !== '' && $root !== '' && $dir !== $root) ? '../' : '';
}
/* Only allow redirecting back to known StudentHub pages (prevents open-redirect abuse). */
function sh_safe_redirect(string $page): string {
  $page = basename(trim($page));
  $allowed = ['index.php','about.php','events.php','dashboard.php','student_dashboard.php','admin_dashboard.php','profile.php','faq.php','feedback.php','contact.php'];
  return in_array($page, $allowed, true) ? $page : '';
}
function sh_require_login(): void {
  if (!empty($_SESSION['user_id'])) return;
  $prefix = sh_base_prefix();
  $query  = 'error=session';
  if ($prefix === '' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $back = sh_safe_redirect((string)($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($back !== '') $query .= '&redirect=' . urlencode($back);
  }
  header('Location: ' . $prefix . 'login.php?' . $query);
  exit;
}
function sh_require_admin(): void {
  sh_require_login();
  if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    $prefix        = sh_base_prefix();
    $assetPrefix   = $prefix;
    $unauthMessage = 'Access Denied: Administrator privileges are required for this area.';
    $redirectUrl   = $prefix . 'student_dashboard.php';
    $redirectLabel = 'Return to Student Dashboard';
    require __DIR__ . '/unauthorized_view.php';
    exit;
  }
}
function sh_audit(mysqli $db, string $action, string $entity, ?int $entityId=null): void {
  $uid=$_SESSION['user_id']??null; $ip=substr((string)($_SERVER['REMOTE_ADDR']??''),0,45);
  $s=$db->prepare('INSERT INTO audit_logs(user_id,action,entity,entity_id,ip_address) VALUES(?,?,?,?,?)');
  $s->bind_param('issis',$uid,$action,$entity,$entityId,$ip); $s->execute(); $s->close();
}

/**
 * Create or update the LOGIN account (users table) that belongs to a student record,
 * and link it through students.user_id. Returns the user id (0 when no account exists
 * and no password was supplied). Callers should wrap this in try/catch + transaction.
 */
function sh_student_account_upsert(mysqli $db, int $studentRowId, string $studentId, string $name, string $email, string $course, int $year, string $password = ''): int {
  $userId = 0;
  if ($studentRowId > 0) {
    $q = $db->prepare('SELECT user_id FROM students WHERE id = ?');
    $q->bind_param('i', $studentRowId); $q->execute();
    $row = $q->get_result()->fetch_assoc(); $q->close();
    $userId = (int)($row['user_id'] ?? 0);
  }
  if ($userId === 0) {
    $q = $db->prepare("SELECT id FROM users WHERE role = 'student' AND (student_id = ? OR email = ?) LIMIT 1");
    $q->bind_param('ss', $studentId, $email); $q->execute();
    $row = $q->get_result()->fetch_assoc(); $q->close();
    $userId = (int)($row['id'] ?? 0);
  }
  $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : '';
  if ($userId > 0) {
    if ($hash !== '') {
      $u = $db->prepare("UPDATE users SET name=?, email=?, student_id=?, course=?, department=?, year_level=?, password=? WHERE id=? AND role='student'");
      $u->bind_param('sssssisi', $name, $email, $studentId, $course, $course, $year, $hash, $userId);
    } else {
      $u = $db->prepare("UPDATE users SET name=?, email=?, student_id=?, course=?, department=?, year_level=? WHERE id=? AND role='student'");
      $u->bind_param('sssssii', $name, $email, $studentId, $course, $course, $year, $userId);
    }
    $u->execute(); $u->close();
  } elseif ($hash !== '') {
    $u = $db->prepare("INSERT INTO users(name,student_id,email,password,role,course,department,year_level,status) VALUES(?,?,?,?,'student',?,?,?,'active')");
    $u->bind_param('ssssssi', $name, $studentId, $email, $hash, $course, $course, $year);
    $u->execute(); $userId = (int)$u->insert_id; $u->close();
  }
  if ($userId > 0 && $studentRowId > 0) {
    $l = $db->prepare('UPDATE students SET user_id = ? WHERE id = ?');
    $l->bind_param('ii', $userId, $studentRowId); $l->execute(); $l->close();
  }
  return $userId;
}

/** Delete a student record AND its login account, so a deleted student can no longer sign in. */
function sh_student_delete(mysqli $db, int $studentRowId): void {
  $q = $db->prepare('SELECT user_id FROM students WHERE id = ?');
  $q->bind_param('i', $studentRowId); $q->execute();
  $row = $q->get_result()->fetch_assoc(); $q->close();
  $d = $db->prepare('DELETE FROM students WHERE id = ?');
  $d->bind_param('i', $studentRowId); $d->execute(); $d->close();
  $userId = (int)($row['user_id'] ?? 0);
  if ($userId > 0) {
    $d = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $d->bind_param('i', $userId); $d->execute(); $d->close();
  }
}

