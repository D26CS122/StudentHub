<?php
/**
 * StudentHub — Contact Us Page
 * Dynamic PHP Integration with identical HTML/CSS theme
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

$isLoggedIn = !empty($_SESSION['user_id']);
$userRole   = $_SESSION['user_role'] ?? '';
$userName   = $_SESSION['user_name'] ?? '';
$userEmail  = $_SESSION['user_email'] ?? '';
$userId     = $isLoggedIn ? (int)$_SESSION['user_id'] : null;

$contactSuccess = false;
$contactError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['csrf'])) {
        sh_verify_csrf();
    }

    $name    = trim($_POST['contactName'] ?? '');
    $email   = strtolower(trim($_POST['contactEmail'] ?? ''));
    $subject = trim($_POST['contactSubject'] ?? '');
    $message = trim($_POST['contactMessage'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $contactError = 'Please complete all fields (Name, Email, Subject, and Message).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactError = 'Please provide a valid email address.';
    } elseif (mb_strlen($message) < 10) {
        $contactError = 'Message must be at least 10 characters long.';
    } else {
        $fullSubject = "[Contact] $subject";
        $stmt = $db->prepare("INSERT INTO feedback (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $userId, $name, $email, $fullSubject, $message);
        if ($stmt->execute()) {
            $feedbackId = $stmt->insert_id;
            $stmt->close();
            sh_audit($db, 'create', 'feedback', $feedbackId);
            $contactSuccess = true;
        } else {
            $stmt->close();
            $contactError = 'Could not send your message. Please try again or email help@charusat.edu.in directly.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Contact Us | StudentHub</title>

  <meta name="description"
        content="Reach the StudentHub help desk — email, phone, campus address and office hours. Send a message and we'll reply within two working days.">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap"
        rel="stylesheet">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <link rel="stylesheet" href="css/style.css">

</head>


<body class="theme-teal contact-page">


  <div class="bg-animation">
    <span class="ball ball1"></span>
    <span class="ball ball2"></span>
    <span class="ball ball3"></span>
    <span class="ball ball4"></span>
    <span class="ball ball5"></span>
  </div>


  <!-- ================= HEADER ================= -->

  <header class="header">

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

        <nav class="navbar">
          <a href="index.php">Home</a>
          <a href="about.php">About</a>
          <a href="events.php">Events</a>
          <a href="dashboard.php">Dashboard</a>
          <a href="profile.php">Profile</a>
          <a href="faq.php">FAQ</a>
          <a href="feedback.php">Feedback</a>
          <a href="contact.php" class="active">Contact</a>
          <?php if (!$isLoggedIn): ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
          <?php else: ?>
            <a href="logout.php">Logout</a>
          <?php endif; ?>
        </nav>

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

          <?php if ($userRole === 'admin'): ?>
            <a href="admin_dashboard.php" class="btn btn-admin">
              <i class="fa-solid fa-shield-halved"></i>
              Admin
            </a>
          <?php elseif ($isLoggedIn): ?>
            <a href="student_dashboard.php" class="btn btn-primary" style="padding:0.45rem 1rem;font-size:0.85rem;">
              <i class="fa-solid fa-user"></i>
              Dashboard
            </a>
          <?php else: ?>
            <a href="login.php" class="btn btn-admin">
              <i class="fa-solid fa-shield-halved"></i>
              Sign In
            </a>
          <?php endif; ?>

        </div>

      </div>

    </div>

  </header>


  <!-- ================= CONTACT ================= -->

  <main class="contact-shell">


    <!-- ---------- HERO ---------- -->

    <section class="contact-hero">

      <div class="container">

        <div class="contact-hero-inner">
          <span class="section-badge">
            <i class="fa-solid fa-headset"></i>
            We're here to help
          </span>
          <h1>Get in touch with <span>StudentHub</span></h1>
          <p>
            Questions about your account, attendance, events or anything else?
            Send us a message and the student help desk will reply within two
            working days.
          </p>
        </div>

      </div>

    </section>


    <!-- ---------- BODY ---------- -->

    <section class="section contact-listing">

      <div class="container">

        <div class="contact-grid">


          <!-- ===== FORM ===== -->

          <div class="dash-panel contact-card">

            <div class="dash-panel-head">
              <div>
                <span>Send a message</span>
                <h2>How can we help?</h2>
              </div>
              <i class="fa-solid fa-paper-plane dash-panel-tag-icon"></i>
            </div>

            <?php if ($contactSuccess): ?>
              <div class="form-message success" role="status" style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;padding:1rem 1.25rem;border-radius:10px;background:rgba(13,148,136,0.12);color:#0d9488;border:1px solid rgba(13,148,136,0.3);">
                <i class="fa-solid fa-circle-check" style="font-size:1.3rem;"></i>
                <div><strong>Message Delivered!</strong> Thank you for reaching out. The CHARUSAT Student Services desk has received your ticket and will respond to your email.</div>
              </div>
            <?php elseif (!empty($contactError)): ?>
              <div class="form-message error" role="alert" style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;padding:1rem 1.25rem;border-radius:10px;background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.3);">
                <i class="fa-solid fa-circle-exclamation" style="font-size:1.3rem;"></i>
                <div><strong>Notice:</strong> <?= sh_e($contactError) ?></div>
              </div>
            <?php endif; ?>

            <form class="contact-form" id="contactForm" method="POST" action="contact.php" novalidate>
              <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">

              <div class="form-row">

                <div class="form-group">
                  <label for="contactName"><i class="fa-solid fa-user"></i> Full Name</label>
                  <div class="input-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="contactName" name="contactName" value="<?= sh_e($userName) ?>" placeholder="Your name" required>
                  </div>
                </div>

                <div class="form-group">
                  <label for="contactEmail"><i class="fa-solid fa-envelope"></i> Email</label>
                  <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" id="contactEmail" name="contactEmail" value="<?= sh_e($userEmail) ?>" placeholder="you@charusat.edu.in" required>
                  </div>
                </div>

              </div>

              <div class="form-group">
                <label for="contactSubject"><i class="fa-solid fa-tag"></i> Subject</label>
                <div class="input-wrapper select-wrapper">
                  <i class="fa-solid fa-tag"></i>
                  <select id="contactSubject" name="contactSubject" required>
                    <option value="">Select a subject</option>
                    <option>Login or account access</option>
                    <option>Attendance or CGPA</option>
                    <option>Results &amp; transcripts</option>
                    <option>Event registration</option>
                    <option>Technical problem</option>
                    <option>Something else</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="contactMessage"><i class="fa-solid fa-message"></i> Message</label>
                <div class="input-wrapper is-textarea">
                  <i class="fa-solid fa-message"></i>
                  <textarea id="contactMessage" name="contactMessage" rows="5" placeholder="Tell us how we can help. Include your enrollment ID if it's about your account…" required></textarea>
                </div>
              </div>

              <div class="form-message" id="contactFormMessage" role="status" aria-live="polite"></div>

              <div class="contact-form-actions">
                <button type="reset" class="btn btn-outline">
                  <i class="fa-solid fa-rotate-left"></i>
                  Clear
                </button>
                <button type="submit" class="btn btn-primary">
                  <i class="fa-solid fa-paper-plane"></i>
                  Send Message
                </button>
              </div>

            </form>

          </div>


          <!-- ===== INFO ===== -->

          <aside class="contact-info">

            <a href="mailto:help@charusat.edu.in" class="dash-panel contact-info-card">
              <span class="contact-info-icon teal"><i class="fa-solid fa-envelope"></i></span>
              <div>
                <h4>Email us</h4>
                <p>help@charusat.edu.in</p>
                <span class="contact-info-sub">Replies within 2 working days</span>
              </div>
            </a>

            <a href="tel:+917900000000" class="dash-panel contact-info-card">
              <span class="contact-info-icon violet"><i class="fa-solid fa-phone"></i></span>
              <div>
                <h4>Call the help desk</h4>
                <p>+91 79 0000 0000</p>
                <span class="contact-info-sub">Extension 214</span>
              </div>
            </a>

            <div class="dash-panel contact-info-card">
              <span class="contact-info-icon amber"><i class="fa-solid fa-location-dot"></i></span>
              <div>
                <h4>Visit us</h4>
                <p>Student Services, Academic Block A</p>
                <span class="contact-info-sub">CHARUSAT Campus, Changa, Anand 388421</span>
              </div>
            </div>

            <div class="dash-panel contact-info-card">
              <span class="contact-info-icon sky"><i class="fa-solid fa-clock"></i></span>
              <div>
                <h4>Office hours</h4>
                <p>Mon–Fri · 9:00 AM – 5:00 PM</p>
                <span class="contact-info-sub">Saturday · 9:00 AM – 1:00 PM</span>
              </div>
            </div>

          </aside>

        </div>


        <!-- ===== HELP SHORTCUTS ===== -->

        <div class="contact-help">

          <div class="section-header">
            <span class="section-badge"><i class="fa-solid fa-life-ring"></i> Faster answers</span>
            <h2>Prefer to help yourself?</h2>
          </div>

          <div class="contact-help-grid">

            <a href="faq.php" class="dash-panel contact-help-card">
              <span class="contact-help-icon"><i class="fa-solid fa-circle-question"></i></span>
              <h3>Browse the FAQ</h3>
              <p>Answers to the most common questions about accounts, attendance and events.</p>
              <span class="contact-help-link">Open FAQ <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="feedback.php" class="dash-panel contact-help-card">
              <span class="contact-help-icon"><i class="fa-solid fa-star"></i></span>
              <h3>Share feedback</h3>
              <p>Have an idea or spotted a bug? Rate your experience and tell us what to improve.</p>
              <span class="contact-help-link">Give feedback <i class="fa-solid fa-arrow-right"></i></span>
            </a>

            <a href="login.php" class="dash-panel contact-help-card">
              <span class="contact-help-icon"><i class="fa-solid fa-right-to-bracket"></i></span>
              <h3>Account access</h3>
              <p>Trouble signing in? Reset your password from the login page in a few clicks.</p>
              <span class="contact-help-link">Go to login <i class="fa-solid fa-arrow-right"></i></span>
            </a>

          </div>

        </div>

      </div>

    </section>

  </main>


  <!-- ================= FOOTER ================= -->

  <footer class="footer">

    <div class="container">

      <div class="footer-grid">

        <div class="footer-brand">
          <a href="index.php" class="logo">
            <div class="logo-box">
              <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="logo-text">
              <h2>StudentHub</h2>
              <span>CHARUSAT Student Portal</span>
            </div>
          </a>
          <p>
            The digital campus companion for CHARUSAT students to manage
            academics, events, profiles and campus life.
          </p>
        </div>

        <div>
          <h4>Quick Links</h4>
          <ul>
            <li><a href="about.php">About</a></li>
            <li><a href="events.php">Events</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="profile.php">Profile</a></li>
          </ul>
        </div>

        <div>
          <h4>Support</h4>
          <ul>
            <li><a href="faq.php">FAQ</a></li>
            <li><a href="feedback.php">Feedback</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="admin_dashboard.php">Admin</a></li>
          </ul>
        </div>

        <div>
          <h4>Student Information</h4>
          <p><strong>Name:</strong> Tirth Ladani</p>
          <p><strong>ID:</strong> 25IT049</p>
          <p><strong>Email:</strong> 25it049@charusat.edu.in</p>
          <p><strong>Department:</strong> Information Technology</p>
        </div>

      </div>

      <div class="footer-bottom">
        <p>© <span data-year><?= date('Y') ?></span> StudentHub — CHARUSAT. Designed for academic excellence.</p>
      </div>

    </div>

  </footer>


  <script src="js/script.js"></script>

</body>

</html>