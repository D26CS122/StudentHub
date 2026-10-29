<?php
/**
 * StudentHub - admin_dashboard.php
 * Administrator Dashboard fully integrated with MySQLi and the official StudentHub theme (css/style.css).
 * Features: RBAC verification, live statistics, Student CRUD, Event CRUD with poster upload, Audit Logs.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

sh_require_login();

// Enforce Administrator role restriction
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    $unauthMessage = "Access Denied: You are signed in as a Student. The Administrator Portal requires administrative privileges.";
    $redirectUrl = "student_dashboard.php";
    $redirectLabel = "Return to Student Dashboard";
    require __DIR__ . '/includes/unauthorized_view.php';
    exit;
}

$adminName  = $_SESSION['user_name'] ?? 'Administrator';
$adminEmail = $_SESSION['user_email'] ?? 'admin@studenthub.edu';

// CSV Export for Students (Practical 11)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="students_export_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Student ID', 'Name', 'Email', 'Mobile', 'Course', 'Year Level', 'Status']);
    $res = $db->query("SELECT id, student_id, name, email, mobile, course, year_level, status FROM students ORDER BY id ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($out, $row);
        }
    }
    fclose($out);
    exit;
}

$alertMessage = '';
$alertType    = '';

// Handle Server-Side Actions (Form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sh_verify_csrf();
    $action = (string)($_POST['action'] ?? '');

    // Action: Add Student
    if ($action === 'add_student') {
        $sid    = strtoupper(trim((string)($_POST['student_id'] ?? '')));
        $name   = trim((string)($_POST['name'] ?? ''));
        $email  = strtolower(trim((string)($_POST['email'] ?? '')));
        $mobile = trim((string)($_POST['mobile'] ?? ''));
        $course = trim((string)($_POST['course'] ?? ''));
        $year   = max(1, min(8, (int)($_POST['year_level'] ?? 1)));
        $newPw  = (string)($_POST['password'] ?? '');

        if ($sid === '' || mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $course === '') {
            $alertMessage = 'Please enter valid student details.';
            $alertType    = 'error';
        } elseif (strlen($newPw) < 8) {
            $alertMessage = 'Please set a login password of at least 8 characters for the student.';
            $alertType    = 'error';
        } else {
            try {
                $db->begin_transaction();
                $stmt = $db->prepare("INSERT INTO students (student_id, name, email, mobile, course, year_level, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $stmt->bind_param("sssssi", $sid, $name, $email, $mobile, $course, $year);
                $stmt->execute();
                $newId = $stmt->insert_id;
                $stmt->close();

                // Create the student's login account (Student ID or email + this password)
                sh_student_account_upsert($db, (int)$newId, $sid, $name, $email, $course, $year, $newPw);
                $db->commit();

                sh_audit($db, 'create', 'student', (int)$newId);
                $alertMessage = "Student {$name} ({$sid}) added. They can now log in with their Student ID or email.";
                $alertType    = 'success';
            } catch (mysqli_sql_exception $e) {
                $db->rollback();
                $alertMessage = ($e->getCode() === 1062) ? 'Student ID or email already exists in database.' : 'Database error: ' . $e->getMessage();
                $alertType    = 'error';
            }
        }
    }

    // Action: Delete Student
    elseif ($action === 'delete_student') {
        $delId = (int)($_POST['id'] ?? 0);
        if ($delId > 0) {
            sh_student_delete($db, $delId);   // removes the student record and their login account

            sh_audit($db, 'delete', 'student', $delId);
            $alertMessage = "Student record #{$delId} deleted successfully.";
            $alertType    = 'success';
        }
    }

    // Action: Add Event with Poster Validation (Practical 12)
    elseif ($action === 'add_event') {
        $title       = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $category    = trim((string)($_POST['category'] ?? 'General'));
        $eventDate   = str_replace('T', ' ', (string)($_POST['event_date'] ?? ''));
        $venue       = trim((string)($_POST['venue'] ?? ''));
        $capacity    = max(1, (int)($_POST['capacity'] ?? 100));
        $status      = (string)($_POST['status'] ?? 'published');
        $poster      = null;

        if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES['poster'];
            $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

            if ($f['error'] !== UPLOAD_ERR_OK) {
                $alertMessage = 'Poster file upload error. Please retry.';
                $alertType    = 'error';
            } elseif ($f['size'] > 2 * 1024 * 1024) {
                $alertMessage = 'Invalid file: Poster must not exceed 2 MB in file size.';
                $alertType    = 'error';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($f['tmp_name']);
                if (!isset($allowedMimes[$mime]) || @getimagesize($f['tmp_name']) === false) {
                    $alertMessage = 'Invalid file: Only JPG, PNG, or WebP images are allowed.';
                    $alertType    = 'error';
                } else {
                    $ext = $allowedMimes[$mime];
                    $poster = bin2hex(random_bytes(16)) . '.' . $ext;
                    $targetDir = __DIR__ . '/uploads/events/';
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0777, true);
                    }
                    if (!move_uploaded_file($f['tmp_name'], $targetDir . $poster)) {
                        $poster = null;
                        $alertMessage = 'Failed to save uploaded poster file.';
                        $alertType    = 'error';
                    }
                }
            }
        }

        if ($alertMessage === '' && ($title === '' || $description === '' || $eventDate === '' || $venue === '')) {
            $alertMessage = 'Please complete all required event fields.';
            $alertType    = 'error';
        }

        if ($alertMessage === '') {
            $uid = (int)$_SESSION['user_id'];
            $stmt = $db->prepare("INSERT INTO events (title, description, category, event_date, venue, capacity, status, poster, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssissi", $title, $description, $category, $eventDate, $venue, $capacity, $status, $poster, $uid);
            $stmt->execute();
            $newEvId = $stmt->insert_id;
            $stmt->close();

            sh_audit($db, 'create', 'event', (int)$newEvId);
            $alertMessage = "Event '{$title}' created successfully.";
            $alertType    = 'success';
        }
    }

    // Action: Delete Event
    elseif ($action === 'delete_event') {
        $evId = (int)($_POST['id'] ?? 0);
        if ($evId > 0) {
            $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
            $stmt->bind_param("i", $evId);
            $stmt->execute();
            $stmt->close();

            sh_audit($db, 'delete', 'event', $evId);
            $alertMessage = "Event #{$evId} deleted successfully.";
            $alertType    = 'success';
        }
    }
}

// Live Statistics
$totalStudents = (int)($db->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'] ?? 0);
$activeStudents = (int)($db->query("SELECT COUNT(*) AS c FROM students WHERE status = 'active'")->fetch_assoc()['c'] ?? 0);
$totalEvents = (int)($db->query("SELECT COUNT(*) AS c FROM events")->fetch_assoc()['c'] ?? 0);
$totalRegistrations = (int)($db->query("SELECT COUNT(*) AS c FROM registrations WHERE status = 'registered'")->fetch_assoc()['c'] ?? 0);
$totalFeedback = (int)($db->query("SELECT COUNT(*) AS c FROM feedback")->fetch_assoc()['c'] ?? 0);

// Fetch Students
$studentSearch = trim((string)($_GET['q'] ?? ''));
$courseFilter  = trim((string)($_GET['course'] ?? ''));

$sql = "SELECT * FROM students WHERE 1=1";
$params = [];
$types  = "";

if ($studentSearch !== '') {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR student_id LIKE ?)";
    $like = "%{$studentSearch}%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}
if ($courseFilter !== '') {
    $sql .= " AND course = ?";
    $params[] = $courseFilter;
    $types .= "s";
}
$sql .= " ORDER BY id DESC LIMIT 50";

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch Events
$events = $db->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);

// Fetch Recent Audit Logs
$auditLogs = $db->query("SELECT a.*, u.name AS user_name, u.email AS user_email FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);

// Fetch Feedback
$feedbackList = $db->query("SELECT * FROM feedback ORDER BY id DESC LIMIT 15")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudentHub Admin Portal - Manage students, events, announcements, feedback and analytics.">
    <title>Admin Dashboard | StudentHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-portal { display: flex !important; min-height: 100vh; }
        .admin-sidebar { width: 260px; flex-shrink: 0; }
        .admin-main { flex: 1; padding: 2rem; background: var(--canvas); min-width: 0; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 10px 14px; text-align: left; border-bottom: 1px solid var(--line); font-size: 0.88rem; }
        .admin-table th { background: var(--surface); color: var(--soft); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; }
        .badge-status { padding: 3px 8px; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-active { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-inactive { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .badge-role { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; font-size: 0.72rem; padding: 2px 6px; border-radius: 4px; }
        .admin-modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 1rem; }
        .admin-modal-card { background: #fff; width: min(100%, 560px); border-radius: 16px; padding: 2rem; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
    </style>
</head>

<body class="admin-body theme-teal" data-php-session="true">

<!-- =========================================================
     ADMIN PORTAL
========================================================= -->

<section class="admin-portal" id="adminPortal">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">
        <!-- BRAND -->
        <div class="admin-sidebar-brand" style="padding:1.5rem 1.25rem;border-bottom:1px solid var(--line);">
            <a href="admin_dashboard.php" class="admin-brand" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit;">
                <div class="admin-brand-logo" style="width:38px;height:38px;background:var(--blue);color:#fff;border-radius:10px;display:grid;place-items:center;">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div class="admin-brand-text">
                    <strong style="display:block;font-size:1.1rem;font-weight:800;color:var(--ink);">StudentHub</strong>
                    <span style="font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;">Admin Portal</span>
                </div>
            </a>
        </div>

        <!-- PROFILE -->
        <div class="admin-sidebar-profile" style="padding:1rem 1.25rem;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--line);">
            <div class="admin-sidebar-avatar" style="width:36px;height:36px;border-radius:50%;background:#ecfdf5;color:#059669;display:grid;place-items:center;">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="admin-sidebar-user" style="line-height:1.2;">
                <strong style="font-size:0.88rem;color:var(--ink);"><?= sh_e($adminName) ?></strong>
                <span style="font-size:0.75rem;color:#059669;display:block;"><i class="fa-solid fa-circle" style="font-size:0.5rem;"></i> Super Admin</span>
            </div>
        </div>

        <!-- NAVIGATION -->
        <nav class="admin-navigation" style="padding:1rem 0.75rem;">
            <span class="admin-navigation-title" style="padding:0.5rem 0.5rem;font-size:0.7rem;font-weight:700;color:var(--muted);letter-spacing:0.08em;">MAIN MENU</span>

            <button type="button" class="admin-nav-item is-active" data-tab="tab-dashboard" onclick="showTab('tab-dashboard', this)">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </button>

            <button type="button" class="admin-nav-item" data-tab="tab-students" onclick="showTab('tab-students', this)">
                <i class="fa-solid fa-user-graduate"></i>
                <span>Students</span>
                <b class="badge-role" style="margin-left:auto;"><?= $totalStudents ?></b>
            </button>

            <button type="button" class="admin-nav-item" data-tab="tab-events" onclick="showTab('tab-events', this)">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Events</span>
                <b class="badge-role" style="margin-left:auto;"><?= $totalEvents ?></b>
            </button>

            <button type="button" class="admin-nav-item" data-tab="tab-feedback" onclick="showTab('tab-feedback', this)">
                <i class="fa-solid fa-comments"></i>
                <span>Feedback</span>
                <b class="badge-role" style="margin-left:auto;"><?= $totalFeedback ?></b>
            </button>

            <button type="button" class="admin-nav-item" data-tab="tab-audit" onclick="showTab('tab-audit', this)">
                <i class="fa-solid fa-list-check"></i>
                <span>Audit Logs</span>
            </button>

            <span class="admin-navigation-title" style="padding:0.75rem 0.5rem 0.5rem;font-size:0.7rem;font-weight:700;color:var(--muted);letter-spacing:0.08em;display:block;">PORTAL SHORTCUTS</span>

            <a href="admin/index.php" class="admin-nav-item">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span>Admin Tools</span>
            </a>

            <a href="admin/users.php" class="admin-nav-item">
                <i class="fa-solid fa-users"></i>
                <span>All Users</span>
            </a>

            <a href="admin/registrations.php" class="admin-nav-item">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>Event Registrations</span>
            </a>

            <a href="index.php" class="admin-nav-item">
                <i class="fa-solid fa-house"></i>
                <span>Public Portal</span>
            </a>

            <a href="logout.php" class="admin-nav-item" style="color:#be123c;">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sign Out</span>
            </a>
        </nav>
    </aside>

    <!-- MAIN AREA -->
    <main class="admin-main">

        <!-- TOPBAR -->
        <header class="admin-topbar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;padding-bottom:1rem;border-bottom:1px solid var(--line);flex-wrap:wrap;gap:1rem;">
            <div>
                <span style="font-size:0.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;">StudentHub Administration</span>
                <h1 style="font-size:1.6rem;font-weight:800;color:var(--ink);margin:2px 0 0;">
                    <i class="fa-solid fa-shield-halved" style="color:#059669;"></i> Administrator Dashboard
                </h1>
            </div>

            <div style="display:flex;align-items:center;gap:12px;">
                <!-- Session Countdown -->
                <span style="font-size:0.82rem;color:var(--soft);padding:6px 12px;background:#fff;border:1px solid var(--line);border-radius:99px;">
                    <i class="fa-regular fa-clock" style="color:var(--blue);"></i>
                    Timeout: <strong id="adminTimer">30:00</strong>
                </span>

                <a href="logout.php" class="btn btn-outline" style="min-height:36px;font-size:0.84rem;color:#be123c;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </header>

<?php if ($alertMessage !== ''): ?>
        <div style="margin-bottom:1.5rem;padding:0.9rem 1.25rem;border-radius:10px;display:flex;align-items:center;gap:10px;<?= $alertType === 'success' ? 'background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;' : 'background:#fff1f2;border:1px solid #fecdd3;color:#be123c;' ?>">
            <i class="fa-solid <?= $alertType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
            <strong><?= sh_e($alertMessage) ?></strong>
        </div>
<?php endif; ?>

        <!-- =====================================================
             TAB: DASHBOARD OVERVIEW
        ====================================================== -->
        <div id="tab-dashboard" class="admin-tab-content">

            <!-- STAT CARDS -->
            <div class="admin-stat-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:14px;margin-bottom:1.75rem;">
                <article class="admin-stat-card" style="padding:1.25rem;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="admin-stat-icon green" style="width:48px;height:48px;border-radius:12px;background:#ecfdf5;color:#059669;display:grid;place-items:center;font-size:1.3rem;">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                        <div>
                            <span style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;font-weight:700;">Total Students</span>
                            <h2 style="font-size:1.6rem;font-weight:800;color:var(--ink);margin:0;"><?= $totalStudents ?></h2>
                            <small style="color:var(--muted);">Registered accounts</small>
                        </div>
                    </div>
                </article>

                <article class="admin-stat-card" style="padding:1.25rem;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="admin-stat-icon blue" style="width:48px;height:48px;border-radius:12px;background:#eff6ff;color:#2563eb;display:grid;place-items:center;font-size:1.3rem;">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div>
                            <span style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;font-weight:700;">Active Events</span>
                            <h2 style="font-size:1.6rem;font-weight:800;color:var(--ink);margin:0;"><?= $totalEvents ?></h2>
                            <small style="color:var(--muted);">Published in portal</small>
                        </div>
                    </div>
                </article>

                <article class="admin-stat-card" style="padding:1.25rem;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="admin-stat-icon orange" style="width:48px;height:48px;border-radius:12px;background:#fffbeb;color:#d97706;display:grid;place-items:center;font-size:1.3rem;">
                            <i class="fa-solid fa-ticket"></i>
                        </div>
                        <div>
                            <span style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;font-weight:700;">Registrations</span>
                            <h2 style="font-size:1.6rem;font-weight:800;color:var(--ink);margin:0;"><?= $totalRegistrations ?></h2>
                            <small style="color:var(--muted);">Event attendees</small>
                        </div>
                    </div>
                </article>

                <article class="admin-stat-card" style="padding:1.25rem;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="admin-stat-icon purple" style="width:48px;height:48px;border-radius:12px;background:#f3e8ff;color:#7c3aed;display:grid;place-items:center;font-size:1.3rem;">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                        <div>
                            <span style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;font-weight:700;">Feedback</span>
                            <h2 style="font-size:1.6rem;font-weight:800;color:var(--ink);margin:0;"><?= $totalFeedback ?></h2>
                            <small style="color:var(--muted);">Student suggestions</small>
                        </div>
                    </div>
                </article>
            </div>

            <!-- RBAC DEMO BARRIER VERIFICATION BOX -->
            <div class="admin-panel" style="margin-bottom:1.75rem;padding:1.5rem;background:#fff;border-left:4px solid #059669;border-radius:var(--radius);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;flex-wrap:wrap;gap:10px;">
                    <div>
                        <span class="section-badge" style="background:#ecfdf5;color:#065f46;">
                            <i class="fa-solid fa-shield-check"></i> Security Status: Verified Admin
                        </span>
                        <h2 style="font-size:1.15rem;font-weight:800;margin:6px 0 2px;">Role-Based Access Control (RBAC) Test Suite</h2>
                    </div>
                    <div style="font-family:monospace;font-size:0.8rem;background:#f1f5f9;padding:4px 8px;border-radius:6px;border:1px solid #cbd5e1;">
                        Session: <?= htmlspecialchars(session_id()) ?>
                    </div>
                </div>
                <p style="color:var(--soft);font-size:0.88rem;margin-bottom:1rem;">
                    Your account is validated with <strong>ADMIN</strong> permissions (<code>$_SESSION['user_role'] = 'admin'</code>).
                    To test unauthorized barrier restrictions for evaluation: click below to test an administrator attempting to open the Student Academic Dashboard.
                </p>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="student_dashboard.php" class="btn btn-outline" style="border-color:#fca5a5;color:#be123c;">
                        <i class="fa-solid fa-ban"></i> Attempt Access to Student Dashboard (Triggers 403 Forbidden Alert)
                    </a>
                    <a href="admin_dashboard.php?export=csv" class="btn btn-outline">
                        <i class="fa-solid fa-file-csv"></i> Export Student Records (CSV)
                    </a>
                    <button type="button" class="btn btn-primary" onclick="openModal('addStudentModal')">
                        <i class="fa-solid fa-user-plus"></i> Add New Student
                    </button>
                    <button type="button" class="btn btn-primary" onclick="openModal('addEventModal')">
                        <i class="fa-solid fa-calendar-plus"></i> Add New Event
                    </button>
                </div>
            </div>

            <!-- RECENT SYSTEM ACTIVITY / AUDIT LOGS -->
            <div class="admin-panel" style="background:#fff;padding:1.5rem;border-radius:var(--radius);margin-bottom:1.75rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                    <div>
                        <span style="font-size:0.75rem;color:var(--muted);text-transform:uppercase;font-weight:700;">System Security</span>
                        <h3 style="font-size:1.1rem;font-weight:700;margin:2px 0 0;">Recent Audit Trail Logs</h3>
                    </div>
                    <button type="button" class="btn btn-outline" style="min-height:30px;font-size:0.75rem;" onclick="showTab('tab-audit')">
                        View All Logs
                    </button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Action</th>
                                <th>Entity</th>
                                <th>Entity ID</th>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
<?php if (!empty($auditLogs)): ?>
<?php foreach (array_slice($auditLogs, 0, 7) as $log): ?>
                            <tr>
                                <td>#<?= (int)$log['id'] ?></td>
                                <td><span class="badge-role"><?= sh_e(strtoupper($log['action'])) ?></span></td>
                                <td><?= sh_e(ucfirst($log['entity'])) ?></td>
                                <td><?= $log['entity_id'] ? '#' . (int)$log['entity_id'] : '—' ?></td>
                                <td><?= sh_e($log['user_name'] ?? 'System') ?></td>
                                <td><code><?= sh_e($log['ip_address'] ?? '127.0.0.1') ?></code></td>
                                <td><?= sh_e($log['created_at']) ?></td>
                            </tr>
<?php endforeach; ?>
<?php else: ?>
                            <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:1.5rem;">No audit logs recorded yet.</td></tr>
<?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>


        <!-- =====================================================
             TAB: STUDENTS MANAGEMENT (Practical 11)
        ====================================================== -->
        <div id="tab-students" class="admin-tab-content" style="display:none;">

            <div class="admin-panel" style="background:#fff;padding:1.5rem;border-radius:var(--radius);margin-bottom:1.5rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h2 style="font-size:1.3rem;font-weight:800;color:var(--ink);margin:0;">Student Directory &amp; Records</h2>
                        <p style="color:var(--muted);font-size:0.86rem;margin:2px 0 0;">Manage student accounts, course assignments, and enrolment statuses.</p>
                    </div>

                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <a href="admin_dashboard.php?export=csv" class="btn btn-outline" style="min-height:38px;">
                            <i class="fa-solid fa-file-csv"></i> Export CSV
                        </a>
                        <button type="button" class="btn btn-primary" onclick="openModal('addStudentModal')" style="min-height:38px;">
                            <i class="fa-solid fa-user-plus"></i> Add Student
                        </button>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <form method="GET" action="admin_dashboard.php" style="display:flex;gap:10px;margin-bottom:1.25rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:240px;position:relative;">
                        <input type="text" name="q" value="<?= sh_e($studentSearch) ?>" placeholder="Search student name, ID or email..." style="padding-left:36px;width:100%;border:1px solid var(--line);border-radius:8px;padding:9px 12px 9px 36px;">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);"></i>
                    </div>

                    <select name="course" style="width:auto;min-width:180px;border:1px solid var(--line);border-radius:8px;padding:9px 12px;">
                        <option value="">All Courses</option>
                        <option value="Information Technology"<?= $courseFilter === 'Information Technology' ? ' selected' : '' ?>>Information Technology</option>
                        <option value="Computer Science"<?= $courseFilter === 'Computer Science' ? ' selected' : '' ?>>Computer Science</option>
                        <option value="Computer Engineering"<?= $courseFilter === 'Computer Engineering' ? ' selected' : '' ?>>Computer Engineering</option>
                        <option value="Electronics"<?= $courseFilter === 'Electronics' ? ' selected' : '' ?>>Electronics</option>
                        <option value="Mechanical"<?= $courseFilter === 'Mechanical' ? ' selected' : '' ?>>Mechanical</option>
                        <option value="Civil"<?= $courseFilter === 'Civil' ? ' selected' : '' ?>>Civil</option>
                    </select>

                    <button type="submit" class="btn btn-outline">
                        <i class="fa-solid fa-filter"></i> Apply Filter
                    </button>
<?php if ($studentSearch !== '' || $courseFilter !== ''): ?>
                    <a href="admin_dashboard.php" class="btn btn-outline">Clear</a>
<?php endif; ?>
                </form>

                <!-- STUDENTS TABLE -->
                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Mobile</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
<?php if (!empty($students)): ?>
<?php foreach ($students as $s): ?>
                            <tr>
                                <td><strong><?= sh_e($s['student_id']) ?></strong></td>
                                <td><?= sh_e($s['name']) ?></td>
                                <td><code><?= sh_e($s['email']) ?></code></td>
                                <td><?= sh_e($s['course']) ?></td>
                                <td>Year <?= (int)$s['year_level'] ?></td>
                                <td><?= sh_e($s['mobile'] ?: '—') ?></td>
                                <td>
                                    <span class="badge-status <?= $s['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= sh_e($s['status']) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <form method="POST" action="admin_dashboard.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student record?');">
                                        <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">
                                        <input type="hidden" name="action" value="delete_student">
                                        <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                        <button type="submit" class="btn btn-outline" style="min-height:30px;padding:3px 8px;font-size:0.75rem;color:#be123c;" title="Delete student">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
<?php endforeach; ?>
<?php else: ?>
                            <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--muted);">No matching students found.</td></tr>
<?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>


        <!-- =====================================================
             TAB: EVENTS MANAGEMENT (Practical 12)
        ====================================================== -->
        <div id="tab-events" class="admin-tab-content" style="display:none;">

            <div class="admin-panel" style="background:#fff;padding:1.5rem;border-radius:var(--radius);margin-bottom:1.5rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:12px;">
                    <div>
                        <h2 style="font-size:1.3rem;font-weight:800;color:var(--ink);margin:0;">Campus Events &amp; Workshops</h2>
                        <p style="color:var(--muted);font-size:0.86rem;margin:2px 0 0;">Create and publish events with poster validation (JPG/PNG/WebP, Max 2MB).</p>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="openModal('addEventModal')" style="min-height:38px;">
                        <i class="fa-solid fa-calendar-plus"></i> Add Event
                    </button>
                </div>

                <!-- EVENTS TABLE -->
                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Poster</th>
                                <th>Title &amp; Description</th>
                                <th>Category</th>
                                <th>Event Date</th>
                                <th>Venue</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
<?php if (!empty($events)): ?>
<?php foreach ($events as $ev): ?>
                            <tr>
                                <td style="width:60px;">
<?php if (!empty($ev['poster'])): ?>
                                    <img src="uploads/events/<?= sh_e($ev['poster']) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
<?php else: ?>
                                    <div style="width:48px;height:48px;background:var(--blue-wash);color:var(--blue);display:grid;place-items:center;border-radius:6px;">
                                        <i class="fa-solid fa-calendar-day"></i>
                                    </div>
<?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= sh_e($ev['title']) ?></strong>
                                    <p style="margin:2px 0 0;font-size:0.78rem;color:var(--muted);max-width:320px;"><?= sh_e(substr($ev['description'], 0, 90)) ?>...</p>
                                </td>
                                <td><span class="badge-role"><?= sh_e($ev['category']) ?></span></td>
                                <td><?= sh_e(date('d M Y, H:i', strtotime($ev['event_date']))) ?></td>
                                <td><?= sh_e($ev['venue']) ?></td>
                                <td><?= (int)$ev['capacity'] ?> seats</td>
                                <td>
                                    <span class="badge-status <?= $ev['status'] === 'published' ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= sh_e($ev['status']) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <form method="POST" action="admin_dashboard.php" style="display:inline;" onsubmit="return confirm('Delete this event?');">
                                        <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="id" value="<?= (int)$ev['id'] ?>">
                                        <button type="submit" class="btn btn-outline" style="min-height:30px;padding:3px 8px;font-size:0.75rem;color:#be123c;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
<?php endforeach; ?>
<?php else: ?>
                            <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--muted);">No events published.</td></tr>
<?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>


        <!-- =====================================================
             TAB: FEEDBACK
        ====================================================== -->
        <div id="tab-feedback" class="admin-tab-content" style="display:none;">

            <div class="admin-panel" style="background:#fff;padding:1.5rem;border-radius:var(--radius);margin-bottom:1.5rem;">
                <h2 style="font-size:1.3rem;font-weight:800;color:var(--ink);margin:0 0 1rem;">Student Feedback &amp; Suggestions</h2>

                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
<?php if (!empty($feedbackList)): ?>
<?php foreach ($feedbackList as $fb): ?>
                            <tr>
                                <td>#<?= (int)$fb['id'] ?></td>
                                <td><strong><?= sh_e($fb['name']) ?></strong></td>
                                <td><code><?= sh_e($fb['email']) ?></code></td>
                                <td><?= sh_e($fb['subject']) ?></td>
                                <td><p style="margin:0;font-size:0.82rem;max-width:350px;"><?= sh_e($fb['message']) ?></p></td>
                                <td><?= sh_e($fb['created_at']) ?></td>
                            </tr>
<?php endforeach; ?>
<?php else: ?>
                            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--muted);">No feedback records found.</td></tr>
<?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>


        <!-- =====================================================
             TAB: AUDIT LOGS
        ====================================================== -->
        <div id="tab-audit" class="admin-tab-content" style="display:none;">

            <div class="admin-panel" style="background:#fff;padding:1.5rem;border-radius:var(--radius);margin-bottom:1.5rem;">
                <h2 style="font-size:1.3rem;font-weight:800;color:var(--ink);margin:0 0 1rem;">Full Security Audit Trail Logs</h2>

                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Action</th>
                                <th>Entity</th>
                                <th>Entity ID</th>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
<?php if (!empty($auditLogs)): ?>
<?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td><strong>#<?= (int)$log['id'] ?></strong></td>
                                <td><span class="badge-role"><?= sh_e(strtoupper($log['action'])) ?></span></td>
                                <td><?= sh_e(ucfirst($log['entity'])) ?></td>
                                <td><?= $log['entity_id'] ? '#' . (int)$log['entity_id'] : '—' ?></td>
                                <td><?= sh_e($log['user_name'] ?? 'System') ?></td>
                                <td><code><?= sh_e($log['ip_address'] ?? '127.0.0.1') ?></code></td>
                                <td><?= sh_e($log['created_at']) ?></td>
                            </tr>
<?php endforeach; ?>
<?php else: ?>
                            <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--muted);">No logs available.</td></tr>
<?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </main>

</section>


<!-- =========================================================
     MODAL: ADD STUDENT (Practical 11)
========================================================= -->

<div class="admin-modal-backdrop" id="addStudentModal">
    <div class="admin-modal-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <div>
                <span class="section-badge"><i class="fa-solid fa-user-plus"></i> Practical 11</span>
                <h3 style="margin:4px 0 0;font-size:1.25rem;font-weight:800;">Add New Student</h3>
            </div>
            <button type="button" class="btn btn-outline" style="min-height:30px;padding:4px 8px;" onclick="closeModal('addStudentModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="admin_dashboard.php">
            <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">
            <input type="hidden" name="action" value="add_student">

            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" placeholder="e.g. 25IT055" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter student full name" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div class="form-group">
                <label>Official Email</label>
                <input type="email" name="email" placeholder="student@charusat.edu.in" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div class="form-group">
                <label>Mobile Number</label>
                <input type="tel" name="mobile" placeholder="9898000000" style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div class="form-group">
                <label>Course / Department</label>
                <select name="course" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
                    <option value="Information Technology">Information Technology</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Computer Engineering">Computer Engineering</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Mechanical">Mechanical</option>
                    <option value="Civil">Civil</option>
                </select>
            </div>

            <div class="form-group">
                <label>Year Level (1 - 4)</label>
                <input type="number" name="year_level" min="1" max="8" value="2" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div class="form-group">
                <label>Login Password (min 8 characters)</label>
                <input type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Student logs in with ID or email + this password" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('addStudentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Student</button>
            </div>
        </form>
    </div>
</div>


<!-- =========================================================
     MODAL: ADD EVENT WITH POSTER UPLOAD (Practical 12)
========================================================= -->

<div class="admin-modal-backdrop" id="addEventModal">
    <div class="admin-modal-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <div>
                <span class="section-badge"><i class="fa-solid fa-calendar-plus"></i> Practical 12</span>
                <h3 style="margin:4px 0 0;font-size:1.25rem;font-weight:800;">Create Campus Event</h3>
            </div>
            <button type="button" class="btn btn-outline" style="min-height:30px;padding:4px 8px;" onclick="closeModal('addEventModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="admin_dashboard.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">
            <input type="hidden" name="action" value="add_event">

            <div class="form-group">
                <label>Event Title</label>
                <input type="text" name="title" placeholder="e.g. AI Hackathon 2026" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
                    <option value="Workshop">Workshop</option>
                    <option value="Competition">Competition</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Festival">Festival</option>
                    <option value="Networking">Networking</option>
                    <option value="Community">Community</option>
                </select>
            </div>

            <div class="form-group">
                <label>Event Date &amp; Time</label>
                <input type="datetime-local" name="event_date" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div class="form-group">
                <label>Campus Venue</label>
                <input type="text" name="venue" placeholder="e.g. CS Lab 1 / Auditorium" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div class="form-group">
                <label>Capacity</label>
                <input type="number" name="capacity" min="1" value="100" required style="border:1px solid var(--line);border-radius:8px;padding:9px;">
            </div>

            <div class="form-group">
                <label>Event Poster (JPG, PNG, WebP &bull; Max 2 MB)</label>
                <input type="file" name="poster" accept="image/jpeg,image/png,image/webp" style="border:1px solid var(--line);border-radius:8px;padding:9px;">
                <small class="input-hint">Server validates mime type, dimensions, and size limit &le; 2MB.</small>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Describe the event objectives and prerequisites..." required style="border:1px solid var(--line);border-radius:8px;padding:9px;"></textarea>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeModal('addEventModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Publish Event</button>
            </div>
        </form>
    </div>
</div>


<!-- JS -->
<script src="js/script.js"></script>

<script>
    // Tab switching handler
    function showTab(tabId, btn) {
        document.querySelectorAll('.admin-tab-content').forEach(function(el) {
            el.style.display = 'none';
        });
        const target = document.getElementById(tabId);
        if (target) {
            target.style.display = 'block';
        }
        document.querySelectorAll('.admin-nav-item').forEach(function(b) {
            b.classList.remove('is-active');
        });
        if (btn) {
            btn.classList.add('is-active');
        }
    }

    // Modal controls
    function openModal(id) {
        const m = document.getElementById(id);
        if (m) m.style.display = 'flex';
    }
    function closeModal(id) {
        const m = document.getElementById(id);
        if (m) m.style.display = 'none';
    }

    // Session timer countdown
    let adminRemaining = 1800;
    const adminTimerEl = document.getElementById('adminTimer');
    setInterval(function() {
        if (adminRemaining <= 0) {
            window.location.href = 'timeout.php';
            return;
        }
        adminRemaining--;
        const m = Math.floor(adminRemaining / 60);
        const s = adminRemaining % 60;
        if (adminTimerEl) {
            adminTimerEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        }
    }, 1000);
</script>

</body>
</html>
