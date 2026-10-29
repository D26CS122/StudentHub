<?php
/**
 * StudentHub - unauthorized_view.php
 * Reusable unauthorized access page styled with the existing StudentHub theme.
 */
declare(strict_types=1);

$ap     = (string)($assetPrefix ?? '');   // '' from root pages, '../' from admin/ pages
$msg   = $unauthMessage ?? "Access Denied: You do not have permission to view this protected area.";
$rUrl  = $redirectUrl ?? ($ap . "index.php");
$rText = $redirectLabel ?? "Return to Safety";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Unauthorized Access | StudentHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($ap, ENT_QUOTES, 'UTF-8') ?>css/style.css">
</head>

<body class="theme-teal">

  <div class="bg-animation">
    <span class="ball ball1"></span>
    <span class="ball ball2"></span>
    <span class="ball ball3"></span>
    <span class="ball ball4"></span>
    <span class="ball ball5"></span>
  </div>

  <header class="header auth-header">
    <div class="container">
      <div class="header-wrapper">
        <a href="<?= htmlspecialchars($ap, ENT_QUOTES, 'UTF-8') ?>index.php" class="logo">
          <div class="logo-box">
            <i class="fa-solid fa-graduation-cap"></i>
          </div>
          <div class="logo-text">
            <h2>StudentHub</h2>
            <span>CHARUSAT Student Portal</span>
          </div>
        </a>
        <div class="header-right">
          <a href="<?= htmlspecialchars($ap, ENT_QUOTES, 'UTF-8') ?>index.php" class="btn btn-outline auth-back">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Home
          </a>
        </div>
      </div>
    </div>
  </header>

  <main class="auth-section">
    <div class="container">
      <div class="auth-wrapper" style="max-width:540px;margin:0 auto;">
        <div class="auth-card" style="text-align:center;">
          <div class="auth-card-icon" style="background:#fee2e2;color:#dc2626;margin:0 auto 1.25rem;">
            <i class="fa-solid fa-shield-halved" style="font-size:1.8rem;"></i>
          </div>

          <span class="section-badge" style="background:#fee2e2;color:#b91c1c;margin-bottom:0.75rem;">
            <i class="fa-solid fa-ban"></i> 403 Forbidden
          </span>

          <h1 style="font-size:1.65rem;margin-bottom:0.5rem;color:var(--ink);">Unauthorized Access</h1>
          <p style="color:var(--soft);margin-bottom:1.5rem;line-height:1.6;">
            <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
          </p>

          <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;padding:1rem;margin-bottom:1.5rem;text-align:left;font-size:0.88rem;color:#9f1239;">
            <div style="font-weight:700;margin-bottom:4px;display:flex;align-items:center;gap:6px;">
              <i class="fa-solid fa-lock"></i> Role-Based Access Control (RBAC) Enforcement
            </div>
            <div>
              Your current authenticated role is <strong><?= htmlspecialchars(strtoupper($_SESSION['user_role'] ?? 'GUEST'), ENT_QUOTES, 'UTF-8') ?></strong>.
              Protected pages strictly enforce role barriers using server-side session checks.
            </div>
          </div>

          <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
            <a href="<?= htmlspecialchars($rUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">
              <i class="fa-solid fa-arrow-left"></i>
              <?= htmlspecialchars($rText, ENT_QUOTES, 'UTF-8') ?>
            </a>
            <a href="<?= htmlspecialchars($ap, ENT_QUOTES, 'UTF-8') ?>logout.php" class="btn btn-outline">
              <i class="fa-solid fa-right-from-bracket"></i>
              Switch Account
            </a>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="auth-footer">
    <div class="container">
      <p>© <span data-year></span> StudentHub — CHARUSAT. All rights reserved.</p>
    </div>
  </footer>

  <script src="<?= htmlspecialchars($ap, ENT_QUOTES, 'UTF-8') ?>js/script.js"></script>
</body>
</html>
