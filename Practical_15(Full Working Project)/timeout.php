<?php
/**
 * StudentHub - timeout.php
 * Session timeout notification & destruction handler.
 * Styled with the official StudentHub theme (css/style.css).
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Securely clear all session variables and destroy session
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="refresh" content="6;url=login.php?error=timeout">
  <title>Session Timeout | StudentHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>

<body class="theme-teal">

  <!-- Floating Background -->
  <div class="bg-animation">
    <span class="ball ball1"></span>
    <span class="ball ball2"></span>
    <span class="ball ball3"></span>
    <span class="ball ball4"></span>
    <span class="ball ball5"></span>
  </div>

  <!-- Header -->
  <header class="header auth-header">
    <div class="container">
      <div class="header-wrapper">
        <a href="index.php" class="logo">
          <div class="logo-box">
            <i class="fa-solid fa-graduation-cap"></i>
          </div>
          <div class="logo-text">
            <h2>StudentHub</h2>
            <span>CHARUSAT Student Portal</span>
          </div>
        </a>
        <div class="header-right">
          <a href="index.php" class="btn btn-outline auth-back">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Home
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Timeout Card -->
  <main class="auth-section">
    <div class="container">
      <div class="auth-wrapper" style="max-width:480px;margin:0 auto;">
        <div class="auth-card" style="text-align:center;">
          <div class="auth-card-icon" style="background:#fef3c7;color:#d97706;margin:0 auto 1.25rem;">
            <i class="fa-regular fa-clock" style="font-size:1.8rem;"></i>
          </div>

          <span class="section-badge" style="background:#fef3c7;color:#b45309;margin-bottom:0.75rem;">
            <i class="fa-solid fa-hourglass-end"></i> Inactivity Expiry
          </span>

          <h1 style="font-size:1.65rem;margin-bottom:0.5rem;color:var(--ink);">Session Timed Out</h1>
          <p style="color:var(--soft);margin-bottom:1.25rem;line-height:1.6;">
            Your session expired due to inactivity. To protect your academic account, all active session data has been securely destroyed.
          </p>

          <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:1rem;margin-bottom:1.5rem;text-align:left;font-size:0.86rem;color:#92400e;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <strong>Security Protection:</strong> Session tokens are invalidated to prevent unauthorized access on shared campus lab workstations.
          </div>

          <p style="color:var(--muted);font-size:0.9rem;margin-bottom:1.5rem;">
            Redirecting to login in <strong id="countdown" style="color:var(--blue);">6</strong> seconds...
          </p>

          <a href="login.php?error=timeout" class="btn btn-primary" style="width:100%;justify-content:center;">
            <i class="fa-solid fa-right-to-bracket"></i>
            Sign In Again
          </a>
        </div>
      </div>
    </div>
  </main>

  <footer class="auth-footer">
    <div class="container">
      <p>© <span data-year></span> StudentHub — CHARUSAT. All rights reserved.</p>
    </div>
  </footer>

  <script src="js/script.js"></script>
  <script>
    let secondsLeft = 6;
    const countdownEl = document.getElementById('countdown');
    const interval = setInterval(() => {
      secondsLeft--;
      if (countdownEl) countdownEl.textContent = secondsLeft;
      if (secondsLeft <= 0) {
        clearInterval(interval);
        window.location.href = 'login.php?error=timeout';
      }
    }, 1000);
  </script>
</body>
</html>
