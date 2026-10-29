<?php
/**
 * StudentHub - register.php
 * Secure user registration with MySQLi prepared statements and password_hash().
 * Fully themed matching pages/register.html layout and CSS.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($isAjax) { ini_set('display_errors', '0'); }
set_exception_handler(function (Throwable $e) use ($isAjax): void {
    $msg = ($e instanceof mysqli_sql_exception && (int)$e->getCode() === 1062)
        ? 'That Student ID or email is already registered. Please log in instead.'
        : 'Server error while creating the account. Check that MySQL is running and database.sql is imported.';
    if ($isAjax) {
        if (!headers_sent()) { header('Content-Type: application/json; charset=utf-8'); http_response_code(500); }
        echo json_encode(['ok' => false, 'success' => false, 'error' => $msg, 'message' => $msg]);
    } else {
        echo '<p style="font-family:sans-serif;padding:2rem">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . ' <a href="register.php">Back to registration</a></p>';
    }
});

$errorMsg   = '';
$successMsg = '';

$nameVal      = '';
$studentIdVal = '';
$emailVal     = '';
$semesterVal  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF if present in POST
    if (!empty($_POST['csrf'])) {
        $token = (string)$_POST['csrf'];
        if (!hash_equals(sh_csrf(), $token)) {
            $errorMsg = 'Security token invalid or expired. Please refresh and try again.';
        }
    }

    $name            = trim((string)($_POST['name'] ?? ''));
    $studentId       = strtoupper(trim((string)($_POST['studentId'] ?? $_POST['student_id'] ?? '')));
    $email           = strtolower(trim((string)($_POST['email'] ?? '')));
    $semester        = trim((string)($_POST['semester'] ?? ''));
    $password        = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirmPassword'] ?? $_POST['confirm_password'] ?? '');
    $terms           = !empty($_POST['terms']);

    $nameVal      = $name;
    $studentIdVal = $studentId;
    $emailVal     = $email;
    $semesterVal  = $semester;

    if ($errorMsg === '') {
        // 1. Validation
        if ($name === '' || $studentId === '' || $email === '' || $password === '' || $confirmPassword === '') {
            $errorMsg = 'Please fill in all required fields (Name, Student ID, Email, and Password).';
        } elseif (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{3,29}$/', $studentId)) {
            $errorMsg = 'Enter a valid Student ID (4-30 letters/numbers, e.g., 25IT049).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Please enter a valid CHARUSAT email address.';
        } elseif (strlen($password) < 8) {
            $errorMsg = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirmPassword) {
            $errorMsg = 'Passwords do not match. Please re-enter them.';
        } elseif (!$terms) {
            $errorMsg = 'Please accept the StudentHub terms and policies to proceed.';
        } else {
            // 2. Check for duplicate email or student_id in users table
            $checkStmt = $db->prepare("SELECT id, student_id, email FROM users WHERE student_id = ? OR email = ? LIMIT 1");
            $checkStmt->bind_param("ss", $studentId, $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult && $checkResult->num_rows > 0) {
                $existing = $checkResult->fetch_assoc();
                $checkStmt->close();

                if (strcasecmp($existing['student_id'] ?? '', $studentId) === 0) {
                    $errorMsg = "A student with Student ID '{$studentId}' is already registered.";
                } else {
                    $errorMsg = "An account with the email '{$email}' already exists.";
                }
            } else {
                $checkStmt->close();

                // 3. Derive department from Student ID
                $dept = 'Information Technology';
                if (preg_match('/^[A-Za-z]?\d{2}([A-Za-z]{2})/', $studentId, $m)) {
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
                    $dept = $deptMap[$code] ?? $dept;
                }

                // 4. Secure password hashing
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $yearLevel = (int)($semester !== '' ? ceil((int)$semester / 2) : 1);

                // 5. Insert into users table
                $insertStmt = $db->prepare("INSERT INTO users (name, student_id, email, password, role, course, department, semester, year_level, status, created_at) VALUES (?, ?, ?, ?, 'student', ?, ?, ?, ?, 'active', NOW())");
                $insertStmt->bind_param("sssssssi", $name, $studentId, $email, $hashedPassword, $dept, $dept, $semester, $yearLevel);

                if ($insertStmt->execute()) {
                    $newUserId = $insertStmt->insert_id;
                    $insertStmt->close();

                    // Also sync into students table for CRUD
                    $sStmt = $db->prepare("INSERT IGNORE INTO students (user_id, student_id, name, email, course, year_level, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                    $sStmt->bind_param("issssi", $newUserId, $studentId, $name, $email, $dept, $yearLevel);
                    $sStmt->execute();
                    $sStmt->close();

                    // If a students row already existed for this ID/email without a login, attach this account to it
                    $lStmt = $db->prepare("UPDATE students SET user_id = ? WHERE user_id IS NULL AND (student_id = ? OR email = ?)");
                    $lStmt->bind_param("iss", $newUserId, $studentId, $email);
                    $lStmt->execute();
                    $lStmt->close();

                    // Log audit event
                    try {
                        sh_audit($db, 'register', 'user', $newUserId);
                    } catch (Throwable $e) {}

                    // Auto-login registered student and regenerate session ID
                    session_regenerate_id(true);
                    $_SESSION['user_id']       = $newUserId;
                    $_SESSION['user_name']     = $name;
                    $_SESSION['user_email']    = $email;
                    $_SESSION['user_role']     = 'student';
                    $_SESSION['student_id']    = $studentId;
                    $_SESSION['course']        = $dept;
                    $_SESSION['last_activity'] = time();
                    $_SESSION['login_time']    = time();

                    if ($isAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode([
                            'ok' => true,
                            'success' => true,
                            'message' => 'Account created successfully! Redirecting...',
                            'redirect' => 'student_dashboard.php?registered=1'
                        ]);
                        exit;
                    }

                    header('Location: student_dashboard.php?registered=1');
                    exit;
                } else {
                    $errorMsg = 'Database registration failed: ' . $insertStmt->error;
                    $insertStmt->close();
                }
            }
        }
    }

    if ($isAjax && $errorMsg !== '') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'success' => false,
            'error' => $errorMsg,
            'message' => $errorMsg
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Create Student Account | StudentHub</title>

  <meta name="description"
        content="Create your StudentHub account and manage your CHARUSAT academic journey, events, achievements and student profile.">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap"
        rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <!-- Main CSS -->
  <link rel="stylesheet" href="css/style.css">

</head>


<body class="theme-teal">


  <!-- ================= FLOATING BACKGROUND ================= -->

  <div class="bg-animation">
    <span class="ball ball1"></span>
    <span class="ball ball2"></span>
    <span class="ball ball3"></span>
    <span class="ball ball4"></span>
    <span class="ball ball5"></span>
  </div>


  <!-- ================= HEADER ================= -->

  <header class="header auth-header">

    <div class="container">

      <div class="header-wrapper">

        <!-- Logo -->
        <a href="index.php" class="logo">

          <div class="logo-box">
            <i class="fa-solid fa-graduation-cap"></i>
          </div>

          <div class="logo-text">
            <h2>StudentHub</h2>
            <span>CHARUSAT Student Portal</span>
          </div>

        </a>

        <!-- Right side: theme switcher + home -->
        <div class="header-right">

          <div class="theme-switcher">

            <button class="theme-btn active" data-theme="teal" type="button">
              <span class="dot teal"></span>
              Campus Teal
            </button>

            <button class="theme-btn" data-theme="blue" type="button">
              <span class="dot blue"></span>
              Midnight Blue
            </button>

          </div>

          <a href="index.php" class="btn btn-outline auth-back">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Home
          </a>

        </div>

      </div>

    </div>

  </header>


  <!-- ================= REGISTER SECTION ================= -->

  <main class="auth-section">

    <div class="container">

      <div class="auth-wrapper register-wrapper">


        <!-- ---------- LEFT: INTRO ---------- -->

        <div class="auth-intro">

          <span class="section-badge">
            <i class="fa-solid fa-user-plus"></i>
            Join StudentHub
          </span>

          <h1>
            Build your
            <span>student profile</span>
          </h1>

          <p>
            Create your StudentHub account and manage your academic
            journey, campus activities, achievements and portfolio —
            everything a CHARUSAT student needs, in one modern portal.
          </p>

          <div class="auth-features">

            <div class="auth-feature">
              <div class="auth-feature-icon">
                <i class="fa-solid fa-chart-line"></i>
              </div>
              <div>
                <h3>Track your progress</h3>
                <p>Monitor attendance, CGPA and academics.</p>
              </div>
            </div>

            <div class="auth-feature">
              <div class="auth-feature-icon">
                <i class="fa-solid fa-calendar-days"></i>
              </div>
              <div>
                <h3>Explore campus</h3>
                <p>Discover events, workshops and activities.</p>
              </div>
            </div>

            <div class="auth-feature">
              <div class="auth-feature-icon">
                <i class="fa-solid fa-award"></i>
              </div>
              <div>
                <h3>Showcase achievements</h3>
                <p>Build your professional student portfolio.</p>
              </div>
            </div>

          </div>

          <div class="auth-trust">

            <div class="auth-trust-item">
              <strong>Free</strong>
              <span>For all students</span>
            </div>

            <div class="auth-trust-item">
              <strong>2 min</strong>
              <span>To get started</span>
            </div>

            <div class="auth-trust-item">
              <strong>Secure</strong>
              <span>Role-based access</span>
            </div>

          </div>

        </div>


        <!-- ---------- RIGHT: REGISTER CARD ---------- -->

        <div class="auth-card register-card">

          <div class="auth-card-header">

            <div class="auth-card-icon">
              <i class="fa-solid fa-user-plus"></i>
            </div>

            <h2>Create account</h2>
            <p>Register as a CHARUSAT student.</p>

          </div>

<?php if ($errorMsg !== ''): ?>
          <div class="form-message error" style="display:flex;align-items:center;gap:8px;margin-bottom:1rem;padding:0.75rem 1rem;background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;color:#be123c;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= sh_e($errorMsg) ?></span>
          </div>
<?php endif; ?>


          <form id="studentRegisterForm" class="auth-form" action="register.php" method="POST" novalidate>
            <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">

            <!-- Full name -->
            <div class="form-group">

              <label for="studentName">
                <i class="fa-solid fa-user"></i>
                Full name
              </label>

              <div class="input-wrapper">
                <i class="fa-solid fa-user"></i>
                <input type="text"
                       id="studentName"
                       name="name"
                       value="<?= sh_e($nameVal) ?>"
                       placeholder="Enter your full name"
                       autocomplete="name"
                       required>
              </div>

            </div>

            <!-- Student ID -->
            <div class="form-group">

              <label for="registerStudentId">
                <i class="fa-solid fa-id-card"></i>
                Student ID
              </label>

              <div class="input-wrapper">
                <i class="fa-solid fa-id-badge"></i>
                <input type="text"
                       id="registerStudentId"
                       name="studentId"
                       value="<?= sh_e($studentIdVal) ?>"
                       placeholder="e.g. 25IT049"
                       required>
              </div>

              <small class="input-hint">Your department is detected automatically from your Student ID.</small>

            </div>

            <!-- Email -->
            <div class="form-group">

              <label for="studentEmail">
                <i class="fa-solid fa-envelope"></i>
                CHARUSAT email
              </label>

              <div class="input-wrapper">
                <i class="fa-solid fa-at"></i>
                <input type="email"
                       id="studentEmail"
                       name="email"
                       value="<?= sh_e($emailVal) ?>"
                       placeholder="yourid@charusat.edu.in"
                       autocomplete="email"
                       required>
              </div>

              <small class="input-hint">Use your official CHARUSAT email address.</small>

            </div>

            <!-- Semester -->
            <div class="form-group">

              <label for="semester">
                <i class="fa-solid fa-layer-group"></i>
                Current semester
              </label>

              <div class="input-wrapper select-wrapper">
                <i class="fa-solid fa-list-ol"></i>
                <select id="semester" name="semester">
                  <option value="">Select your semester (optional)</option>
                  <option value="1"<?= $semesterVal === '1' ? ' selected' : '' ?>>Semester 1</option>
                  <option value="2"<?= $semesterVal === '2' ? ' selected' : '' ?>>Semester 2</option>
                  <option value="3"<?= $semesterVal === '3' ? ' selected' : '' ?>>Semester 3</option>
                  <option value="4"<?= $semesterVal === '4' ? ' selected' : '' ?>>Semester 4</option>
                  <option value="5"<?= $semesterVal === '5' ? ' selected' : '' ?>>Semester 5</option>
                  <option value="6"<?= $semesterVal === '6' ? ' selected' : '' ?>>Semester 6</option>
                  <option value="7"<?= $semesterVal === '7' ? ' selected' : '' ?>>Semester 7</option>
                  <option value="8"<?= $semesterVal === '8' ? ' selected' : '' ?>>Semester 8</option>
                </select>
              </div>

              <small class="input-hint">You can change this later from your profile.</small>

            </div>

            <!-- Password -->
            <div class="form-group">

              <label for="registerPassword">
                <i class="fa-solid fa-lock"></i>
                Password
              </label>

              <div class="input-wrapper has-toggle">
                <i class="fa-solid fa-key"></i>
                <input type="password"
                       id="registerPassword"
                       name="password"
                       placeholder="Create a strong password"
                       autocomplete="new-password"
                       minlength="8"
                       required>
                <button type="button"
                        class="password-toggle"
                        data-target="registerPassword"
                        aria-label="Show password">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>

              <div class="password-strength" id="passwordStrength">
                <div class="password-strength-track">
                  <div class="password-strength-fill"></div>
                </div>
                <span class="password-strength-label">Use 8+ characters with letters, numbers &amp; symbols.</span>
              </div>

            </div>

            <!-- Confirm password -->
            <div class="form-group">

              <label for="confirmPassword">
                <i class="fa-solid fa-lock"></i>
                Confirm password
              </label>

              <div class="input-wrapper has-toggle">
                <i class="fa-solid fa-check-double"></i>
                <input type="password"
                       id="confirmPassword"
                       name="confirmPassword"
                       placeholder="Re-enter your password"
                       autocomplete="new-password"
                       required>
                <button type="button"
                        class="password-toggle"
                        data-target="confirmPassword"
                        aria-label="Show password">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>

            </div>

            <!-- Terms -->
            <div class="form-options">
              <label class="remember-me">
                <input type="checkbox" id="terms" name="terms" value="1" required>
                <span>I agree to the
                  <a href="faq.php" target="_blank" rel="noopener">StudentHub terms</a>
                  and policies.</span>
              </label>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary auth-submit">
              <span>Create student account</span>
              <i class="fa-solid fa-arrow-right"></i>
            </button>

            <!-- Message -->
            <div id="registerMessage" class="form-message" aria-live="polite"></div>

          </form>


          <div class="auth-divider">
            <span>Already have an account?</span>
          </div>

          <a href="login.php" class="btn btn-outline auth-register">
            <i class="fa-solid fa-right-to-bracket"></i>
            Sign in instead
          </a>

          <div class="admin-login-link">
            <span>Are you an administrator?</span>
            <a href="admin_dashboard.php">
              <i class="fa-solid fa-shield-halved"></i>
              Go to Admin Portal
            </a>
          </div>

        </div>

      </div>

    </div>

  </main>


  <!-- ================= FOOTER ================= -->

  <footer class="auth-footer">
    <div class="container">
      <p>© <span data-year></span> StudentHub — CHARUSAT. All rights reserved.</p>
    </div>
  </footer>


  <!-- JS -->
  <script src="js/script.js"></script>

</body>

</html>
