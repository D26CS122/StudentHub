<?php
/**
 * StudentHub - Practical 9: Process Registration
 * Handles:
 * 1. Empty-field validation
 * 2. Invalid email validation
 * 3. Password mismatch validation
 * 4. Duplicate email / username (student ID) validation
 * 5. Password stored in hashed format using password_hash()
 * 6. Successful database insertion into MySQL using MySQLi prepared statements
 * 7. Successful user registration response (JSON for AJAX & HTML for standard POST)
 */

require_once __DIR__ . '/db_connect.php';

// Detect if request expects JSON (Fetch/AJAX)
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

function sendResponse($ok, $message, $extra = [], $statusCode = 200) {
    global $isAjax;
    http_response_code($statusCode);

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge([
            'ok' => $ok,
            'success' => $ok,
            'message' => $message,
            'error' => $ok ? null : $message
        ], $extra));
        exit;
    }

    // Direct Form POST Fallback - StudentHub Styled HTML Response
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $ok ? 'Registration Successful' : 'Registration Error'; ?> | StudentHub</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
        <link rel="stylesheet" href="../css/style.css">
        <style>
            .result-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }
            .result-card {
                background: #ffffff;
                max-width: 540px;
                width: 100%;
                border-radius: 16px;
                padding: 2.5rem;
                box-shadow: 0 10px 40px rgba(0,0,0,0.08);
                text-align: center;
            }
            .result-icon {
                width: 72px;
                height: 72px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem auto;
                font-size: 2rem;
            }
            .result-icon.success {
                background: #e6f7f3;
                color: #0d9488;
            }
            .result-icon.error {
                background: #fee2e2;
                color: #dc2626;
            }
            .result-actions {
                display: flex;
                gap: 1rem;
                justify-content: center;
                margin-top: 2rem;
                flex-wrap: wrap;
            }
        </style>
    </head>
    <body class="theme-teal">
        <div class="result-container">
            <div class="result-card">
                <div class="result-icon <?php echo $ok ? 'success' : 'error'; ?>">
                    <i class="fa-solid <?php echo $ok ? 'fa-check' : 'fa-triangle-exclamation'; ?>"></i>
                </div>
                <h2><?php echo $ok ? 'Registration Successful!' : 'Registration Failed'; ?></h2>
                <p style="margin: 1rem 0; color: #475569; font-size: 1.05rem;">
                    <?php echo htmlspecialchars($message); ?>
                </p>
                <div class="result-actions">
                    <?php if ($ok): ?>
                        <a href="view_registrations.php" class="btn btn-outline">
                            <i class="fa-solid fa-database"></i> View Registrations in DB
                        </a>
                        <a href="../pages/login.html" class="btn btn-primary">
                            <i class="fa-solid fa-arrow-right"></i> Proceed to Login
                        </a>
                    <?php else: ?>
                        <a href="../pages/register.html" class="btn btn-primary">
                            <i class="fa-solid fa-arrow-left"></i> Back to Registration Form
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method. Only POST is accepted.', [], 405);
}

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    $err = function_exists('getDbConnectionError') ? getDbConnectionError() : 'Database connection error';
    sendResponse(false, $err, [], 500);
}

// 1. Retrieve & sanitize inputs
$name            = trim($_POST['name'] ?? '');
$studentId       = strtoupper(trim($_POST['studentId'] ?? ''));
$email           = trim($_POST['email'] ?? '');
$semester        = trim($_POST['semester'] ?? '');
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// 2. Empty-field validation
if ($name === '' || $studentId === '' || $email === '' || $password === '' || $confirmPassword === '') {
    sendResponse(false, 'Please fill in all required fields (Name, Student ID, Email, and Password).', [], 400);
}

// 3. Student ID format check (CHARUSAT format, e.g. 25IT049)
if (!preg_match('/^\d{2}[A-Za-z]{2}\d{3}$/', $studentId)) {
    sendResponse(false, 'Enter a valid Student ID format (e.g., 25IT049).', [], 400);
}

// 4. Invalid email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Please enter a valid email address (e.g. yourid@charusat.edu.in).', [], 400);
}

// 5. Password minimum length check
if (strlen($password) < 8) {
    sendResponse(false, 'Password must be at least 8 characters long.', [], 400);
}

// 6. Password mismatch validation
if ($password !== $confirmPassword) {
    sendResponse(false, 'Passwords do not match. Please re-enter them.', [], 400);
}

// 7. Duplicate email / username (student ID) validation in MySQL database
$checkStmt = $conn->prepare("SELECT id, student_id, email FROM `users` WHERE `student_id` = ? OR `email` = ? LIMIT 1");
if (!$checkStmt) {
    sendResponse(false, 'Database query preparation failed: ' . $conn->error, [], 500);
}

$checkStmt->bind_param("ss", $studentId, $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult && $checkResult->num_rows > 0) {
    $existing = $checkResult->fetch_assoc();
    $checkStmt->close();

    if (strcasecmp($existing['student_id'], $studentId) === 0) {
        sendResponse(false, "A student with Student ID '{$studentId}' is already registered.", [], 409);
    }
    if (strcasecmp($existing['email'], $email) === 0) {
        sendResponse(false, "An account with the email '{$email}' already exists.", [], 409);
    }
}
$checkStmt->close();

// 8. Password stored in hashed format in MySQL database using password_hash()
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Auto-derive department from Student ID
$dept = '';
if (preg_match('/^\d{2}([A-Za-z]{2})/', $studentId, $m)) {
    $code = strtoupper($m[1]);
    $deptMap = [
        'IT' => 'Information Technology',
        'CE' => 'Computer Engineering',
        'CO' => 'Computer Engineering',
        'CS' => 'Computer Science & Engineering',
        'EC' => 'Electronics & Communication',
        'EE' => 'Electrical Engineering',
        'ME' => 'Mechanical Engineering',
        'CL' => 'Civil Engineering',
        'CV' => 'Civil Engineering',
        'CH' => 'Chemical Engineering',
        'AI' => 'AI & Machine Learning'
    ];
    $dept = $deptMap[$code] ?? $code;
}

// 9. Successful database insertion using MySQLi prepared statements
$insertStmt = $conn->prepare("INSERT INTO `users` (`name`, `student_id`, `email`, `semester`, `department`, `password`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, NOW())");
if (!$insertStmt) {
    sendResponse(false, 'Database insert preparation failed: ' . $conn->error, [], 500);
}

$insertStmt->bind_param("ssssss", $name, $studentId, $email, $semester, $dept, $hashedPassword);

if (!$insertStmt->execute()) {
    $err = $insertStmt->error;
    $insertStmt->close();
    sendResponse(false, 'Database insertion failed: ' . $err, [], 500);
}

$insertedId = $insertStmt->insert_id;
$insertStmt->close();
$conn->close();

// 10. Successful user registration response
sendResponse(true, 'Account created successfully in MySQL database! Password securely hashed.', [
    'user' => [
        'db_id' => $insertedId,
        'id' => $studentId,
        'name' => $name,
        'email' => $email,
        'department' => $dept,
        'semester' => $semester
    ]
], 200);
?>
