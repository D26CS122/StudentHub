<?php
/**
 * StudentHub - login.php
 * Fully themed login page matching pages/login.html design.
 * Validates inputs and submits to authenticate.php via POST.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/security.php';

// If already authenticated, redirect to appropriate role-based dashboard
// Page the visitor originally wanted (e.g. events.php) - whitelisted by sh_safe_redirect()
$redirectVal = sh_safe_redirect((string)($_GET['redirect'] ?? ''));

if (!empty($_SESSION['user_id'])) {
    $isAdminUser = (($_SESSION['user_role'] ?? '') === 'admin');
    if ($redirectVal !== '' && !($redirectVal === 'admin_dashboard.php' && !$isAdminUser) && !($redirectVal === 'student_dashboard.php' && $isAdminUser)) {
        header('Location: ' . $redirectVal);
    } elseif ($isAdminUser) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: student_dashboard.php');
    }
    exit;
}

$error = (string)($_GET['error'] ?? '');
$msg   = (string)($_GET['msg'] ?? '');

$errorMessages = [
    'empty'        => 'Please enter both your Student ID or email and your password.',
    'invalid'      => 'Invalid Student ID / email or password. Please try again.',
    'session'      => 'Access Denied: Please sign in to access your protected dashboard.',
    'timeout'      => 'Your session has expired due to 30 minutes of inactivity. Please sign in again.',
    'unauthorized' => 'Access Denied: You do not have permission to view that privileged area.',
    'access'       => 'Access Denied: Restricted page. Administrative privileges required.',
];

$successMessages = [
    'logout'     => 'You have been securely signed out. Session destroyed.',
    'registered' => 'Registration complete! You can now log in with your credentials.',
];

$errorMsg   = $errorMessages[$error] ?? '';
$successMsg = $successMessages[$msg] ?? '';
$emailVal   = htmlspecialchars((string)($_GET['email'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Student Login | StudentHub</title>

  <meta name="description"
        content="Sign in to StudentHub — the CHARUSAT student portal for dashboard, attendance, events, achievements and profile.">

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


  <!-- ================= LOGIN SECTION ================= -->

  <main class="auth-section">

    <div class="container">

      <div class="auth-wrapper">


        <!-- ---------- LEFT: INTRO ---------- -->

        <div class="auth-intro">

          <span class="section-badge">
            <i class="fa-solid fa-user-graduate"></i>
            Student Portal
          </span>

          <h1>
            Welcome back to
            <span>StudentHub</span>
          </h1>

          <p>
            Sign in to reach your academic dashboard, attendance, CGPA,
            campus events, achievements and student profile — all from
            one place, built for CHARUSAT students.
          </p>

          <div class="auth-features">

            <div class="auth-feature">
              <div class="auth-feature-icon">
                <i class="fa-solid fa-chart-line"></i>
              </div>
              <div>
                <h3>Academic Dashboard</h3>
                <p>Track attendance, CGPA and assignments in real time.</p>
              </div>
            </div>

            <div class="auth-feature">
              <div class="auth-feature-icon">
                <i class="fa-solid fa-calendar-check"></i>
              </div>
              <div>
                <h3>Campus Events</h3>
                <p>Register for hackathons, workshops and tech fests.</p>
              </div>
            </div>

            <div class="auth-feature">
              <div class="auth-feature-icon">
                <i class="fa-solid fa-award"></i>
              </div>
              <div>
                <h3>Your Achievements</h3>
                <p>Showcase projects, certificates and internships.</p>
              </div>
            </div>

          </div>

          <div class="auth-trust">

            <div class="auth-trust-item">
              <strong>1,200+</strong>
              <span>Active students</span>
            </div>

            <div class="auth-trust-item">
              <strong>50+</strong>
              <span>Campus events</span>
            </div>

            <div class="auth-trust-item">
              <strong>95%</strong>
              <span>Avg. attendance</span>
            </div>

          </div>

        </div>


        <!-- ---------- RIGHT: LOGIN CARD ---------- -->

        <div class="auth-card">

          <div class="auth-card-header">

            <div class="auth-card-icon">
              <i class="fa-solid fa-right-to-bracket"></i>
            </div>

            <h2>Student Login</h2>
            <p>Sign in to continue to your portal.</p>

          </div>

<?php if ($errorMsg !== ''): ?>
          <div class="form-message error" style="display:flex;align-items:center;gap:8px;margin-bottom:1rem;padding:0.75rem 1rem;background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;color:#be123c;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= sh_e($errorMsg) ?></span>
          </div>
<?php endif; ?>

<?php if ($successMsg !== ''): ?>
          <div class="form-message success" style="display:flex;align-items:center;gap:8px;margin-bottom:1rem;padding:0.75rem 1rem;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;color:#047857;">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= sh_e($successMsg) ?></span>
          </div>
<?php endif; ?>


          <form id="studentLoginForm" class="auth-form" action="authenticate.php" method="POST" novalidate>
            <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">
            <input type="hidden" name="redirect" value="<?= sh_e($redirectVal) ?>">

            <!-- Student ID -->
            <div class="form-group">

              <label for="studentId">
                <i class="fa-solid fa-id-card"></i>
                Student ID or email
              </label>

              <div class="input-wrapper">
                <i class="fa-solid fa-user"></i>
                <input type="text"
                       id="studentId"
                       name="studentId"
                       value="<?= $emailVal ?>"
                       placeholder="Student ID or email"
                       autocomplete="username"
                       required>
              </div>

            </div>

            <!-- Password -->
            <div class="form-group">

              <div class="label-row">
                <label for="studentPassword">
                  <i class="fa-solid fa-lock"></i>
                  Password
                </label>
                <a href="#" class="forgot-password">Forgot password?</a>
              </div>

              <div class="input-wrapper has-toggle">
                <i class="fa-solid fa-key"></i>
                <input type="password"
                       id="studentPassword"
                       name="password"
                       placeholder="Enter your password"
                       autocomplete="current-password"
                       required>
                <button type="button"
                        class="password-toggle"
                        data-target="studentPassword"
                        aria-label="Show password">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>

            </div>

            <!-- Remember -->
            <div class="form-options">
              <label class="remember-me">
                <input type="checkbox" id="rememberMe" name="rememberMe">
                <span>Keep me signed in</span>
              </label>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary auth-submit">
              <span>Login to StudentHub</span>
              <i class="fa-solid fa-arrow-right"></i>
            </button>

            <!-- Message -->
            <div id="loginMessage" class="form-message" aria-live="polite"></div>

          </form>


          <div class="auth-divider">
            <span>New to StudentHub?</span>
          </div>

          <a href="register.php" class="btn btn-outline auth-register">
            <i class="fa-solid fa-user-plus"></i>
            Create a student account
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
