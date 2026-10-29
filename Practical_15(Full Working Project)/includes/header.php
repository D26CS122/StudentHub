<?php
/**
 * StudentHub - Shared Header Include
 * Outputs the themed header matching pages/*.html design.
 * Uses css/style.css + js/script.js (the original HTML theme).
 *
 * Variables expected before inclusion:
 *   $pageTitle   – string (page <title>)
 *   $bodyClass   – string (body CSS classes, e.g. 'theme-teal dashboard-page')
 *   $activePage  – string (nav link to mark active, e.g. 'index','about','events',...)
 *   $authPage    – bool   (if true, uses auth-header layout instead of full navbar)
 *   $isProtected – bool   (if true, page requires login)
 */
require_once __DIR__ . '/security.php';

$pageTitle   = $pageTitle   ?? 'StudentHub | CHARUSAT Student Portal';
$bodyClass   = $bodyClass   ?? 'theme-teal';
$activePage  = $activePage  ?? '';
$authPage    = $authPage    ?? false;
$isProtected = $isProtected ?? false;
$basePath    = $basePath    ?? '';

// Logged-in state
$loggedIn  = !empty($_SESSION['user_id']);
$userName  = $_SESSION['user_name']  ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole  = $_SESSION['user_role']  ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= sh_e($pageTitle) ?></title>

  <meta name="description" content="StudentHub - The all-in-one portal for CHARUSAT students to manage academics, events, attendance, profiles and campus life.">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <!-- Main CSS -->
  <link rel="stylesheet" href="<?= sh_e($basePath) ?>css/style.css">
</head>

<body class="<?= sh_e($bodyClass) ?>">

  <!-- Floating Background -->
  <div class="bg-animation">
    <span class="ball ball1"></span>
    <span class="ball ball2"></span>
    <span class="ball ball3"></span>
    <span class="ball ball4"></span>
    <span class="ball ball5"></span>
  </div>

<?php if ($authPage): ?>
  <!-- ================= AUTH HEADER ================= -->
  <header class="header auth-header">
    <div class="container">
      <div class="header-wrapper">

        <!-- Logo -->
        <a href="<?= sh_e($basePath) ?>index.php" class="logo">
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

          <a href="<?= sh_e($basePath) ?>index.php" class="btn btn-outline auth-back">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Home
          </a>
        </div>

      </div>
    </div>
  </header>

<?php else: ?>
  <!-- ================= HEADER ================= -->
  <header class="header">
    <div class="container">
      <div class="header-wrapper">

        <!-- Logo -->
        <a href="<?= sh_e($basePath) ?>index.php" class="logo">
          <div class="logo-box">
            <i class="fa-solid fa-graduation-cap"></i>
          </div>
          <div class="logo-text">
            <h2>StudentHub</h2>
            <span>CHARUSAT Student Portal</span>
          </div>
        </a>

        <!-- Navigation -->
        <nav class="navbar">
          <a href="<?= sh_e($basePath) ?>index.php"<?= $activePage === 'index' ? ' class="active"' : '' ?>>Home</a>
          <a href="<?= sh_e($basePath) ?>about.php"<?= $activePage === 'about' ? ' class="active"' : '' ?>>About</a>
          <a href="<?= sh_e($basePath) ?>events.php"<?= $activePage === 'events' ? ' class="active"' : '' ?>>Events</a>
<?php if ($loggedIn): ?>
          <a href="<?= sh_e($basePath) ?>dashboard.php"<?= $activePage === 'dashboard' ? ' class="active"' : '' ?>>Dashboard</a>
          <a href="<?= sh_e($basePath) ?>profile.php"<?= $activePage === 'profile' ? ' class="active"' : '' ?>>Profile</a>
<?php endif; ?>
          <a href="<?= sh_e($basePath) ?>faq.php"<?= $activePage === 'faq' ? ' class="active"' : '' ?>>FAQ</a>
          <a href="<?= sh_e($basePath) ?>feedback.php"<?= $activePage === 'feedback' ? ' class="active"' : '' ?>>Feedback</a>
          <a href="<?= sh_e($basePath) ?>contact.php"<?= $activePage === 'contact' ? ' class="active"' : '' ?>>Contact</a>
<?php if ($loggedIn): ?>
          <a href="<?= sh_e($basePath) ?>logout.php">Logout</a>
<?php else: ?>
          <a href="<?= sh_e($basePath) ?>login.php"<?= $activePage === 'login' ? ' class="active"' : '' ?>>Login</a>
          <a href="<?= sh_e($basePath) ?>register.php"<?= $activePage === 'register' ? ' class="active"' : '' ?>>Register</a>
<?php endif; ?>
        </nav>

        <!-- Right Side -->
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

<?php if ($loggedIn && $userRole === 'admin'): ?>
          <a href="<?= sh_e($basePath) ?>admin_dashboard.php" class="btn btn-admin">
            <i class="fa-solid fa-shield-halved"></i>
            Admin
          </a>
<?php elseif (!$loggedIn): ?>
          <a href="<?= sh_e($basePath) ?>admin_dashboard.php" class="btn btn-admin">
            <i class="fa-solid fa-shield-halved"></i>
            Admin
          </a>
<?php else: ?>
          <a href="<?= sh_e($basePath) ?>logout.php" class="btn btn-outline">
            <i class="fa-solid fa-right-from-bracket"></i>
            Sign Out
          </a>
<?php endif; ?>
        </div>

      </div>
    </div>
  </header>
<?php endif; ?>
