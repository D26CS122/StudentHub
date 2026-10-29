<?php
/**
 * StudentHub — Help Center & FAQ Page
 * Dynamic PHP Integration with identical HTML/CSS theme
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

$isLoggedIn = !empty($_SESSION['user_id']);
$userRole   = $_SESSION['user_role'] ?? '';
$userName   = $_SESSION['user_name'] ?? '';

// Load FAQs from data/faqs.json
$faqsFile = __DIR__ . '/data/faqs.json';
$rawFaqs = [];
if (file_exists($faqsFile)) {
    $rawFaqs = json_decode(file_get_contents($faqsFile), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Help Center &amp; FAQ | StudentHub</title>

  <meta name="description"
        content="Frequently asked questions about StudentHub — accounts, login, dashboard, attendance, events and support for CHARUSAT students.">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap"
        rel="stylesheet">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <link rel="stylesheet" href="css/style.css">

</head>


<body class="theme-teal faq-page">


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
          <a href="faq.php" class="active">FAQ</a>
          <a href="feedback.php">Feedback</a>
          <a href="contact.php">Contact</a>
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


  <!-- ================= FAQ ================= -->

  <main class="faq-shell">


    <!-- ---------- HERO ---------- -->

    <section class="faq-hero">

      <div class="container">

        <div class="faq-hero-inner">

          <span class="section-badge">
            <i class="fa-solid fa-circle-question"></i>
            Help Center
          </span>

          <h1>How can we <span>help you?</span></h1>

          <p>Search our knowledge base or browse common questions about using StudentHub.</p>

          <div class="faq-search">
            <div class="input-wrapper">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" id="faqSearch" placeholder="Search questions… e.g. password, attendance, events"
                     aria-label="Search frequently asked questions" autocomplete="off">
            </div>
          </div>

        </div>

      </div>

    </section>


    <!-- ---------- GROUPS ---------- -->

    <section class="section faq-listing">

      <div class="container">

        <div class="faq-groups">


          <!-- Getting Started -->
          <div class="faq-group">

            <h2 class="faq-group-title"><i class="fa-solid fa-rocket"></i> Getting Started</h2>

            <div class="faq-list">

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>What is StudentHub?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>StudentHub is CHARUSAT's digital campus portal. It brings your attendance, CGPA, assignments, events, profile and campus announcements together in one clean, easy-to-use dashboard.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>Who can use StudentHub?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Any enrolled CHARUSAT student can use StudentHub with their university enrollment ID. Faculty and administrators manage the portal through a separate secure admin console.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>Is StudentHub free to use?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Yes. StudentHub is completely free for all CHARUSAT students as part of your university services — there are no charges or subscriptions of any kind.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>Which devices are supported?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>StudentHub is fully responsive and works on laptops, tablets and phones across all modern browsers such as Chrome, Edge, Firefox and Safari.</p>
                  </div>
                </div>
              </div>

            </div>

          </div>


          <!-- Account & Login -->
          <div class="faq-group">

            <h2 class="faq-group-title"><i class="fa-solid fa-user-lock"></i> Account &amp; Login</h2>

            <div class="faq-list">

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>How do I create an account?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Head to the Register page, fill in your name, university email and a secure password, accept the terms, and you're set. You can then sign in from the Login page.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>I forgot my password — what do I do?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>On the Login page, use the "Forgot password?" link to reset it via your registered university email. If you still can't get in, reach out through the Contact page.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>How do I switch between the Teal and Blue themes?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Use the Campus Teal / Midnight Blue switch in the top navigation bar. Your choice is remembered on this device, so every page opens in your preferred look.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>Can I change my registered email?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>You can update your contact email from the Profile page under "Edit Personal Information". Your university enrollment ID, however, stays fixed.</p>
                  </div>
                </div>
              </div>

            </div>

          </div>


          <!-- Academics & Dashboard -->
          <div class="faq-group">

            <h2 class="faq-group-title"><i class="fa-solid fa-chart-line"></i> Academics &amp; Dashboard</h2>

            <div class="faq-list">

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>Where can I see my attendance and CGPA?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Your Dashboard shows a live overview — overall attendance, subject-wise breakdown, current CGPA, assignment progress and your upcoming deadlines, all in one view.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>How often is my dashboard data updated?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Academic data such as attendance and marks is refreshed by your department after each session or assessment, so your dashboard always reflects the latest records.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>What do the progress bars represent?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Each bar is a quick visual of a metric — attendance percentage, assignment completion, or profile strength — so you can gauge your standing at a glance.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>Can I download my academic records?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Official transcripts are issued by the university's examination cell. StudentHub gives you an always-available summary view of the same records for your reference.</p>
                  </div>
                </div>
              </div>

            </div>

          </div>


          <!-- Events & Support -->
          <div class="faq-group">

            <h2 class="faq-group-title"><i class="fa-solid fa-headset"></i> Events &amp; Support</h2>

            <div class="faq-list">

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>How do I register for a campus event?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Open the Events page, filter by category to find what interests you, and hit "Register" on any event. You'll be prompted to sign in to confirm your spot.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>How will I know about new events?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>New events and announcements appear on your Dashboard and the Events page as soon as they're published, so you never miss a hackathon, workshop or fest.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>How do I contact support?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>Visit the Contact page to send us a message, or email the support desk directly. We typically respond within one working day.</p>
                  </div>
                </div>
              </div>

              <div class="faq-item">
                <button class="faq-question" type="button" aria-expanded="false">
                  <span>How can I submit feedback?</span>
                  <i class="fa-solid fa-chevron-down faq-icon"></i>
                </button>
                <div class="faq-answer">
                  <div class="faq-answer-inner">
                    <p>We'd love to hear from you. Use the Feedback page to rate your experience and share suggestions — your input directly shapes future updates.</p>
                  </div>
                </div>
              </div>

            </div>

          </div>


        </div>


        <p class="faq-empty" id="faqEmpty" hidden>
          <i class="fa-solid fa-magnifying-glass"></i>
          No questions match your search. Try a different keyword, or
          <a href="contact.php">contact support</a>.
        </p>


        <!-- Support CTA -->
        <div class="faq-cta">
          <div class="faq-cta-icon"><i class="fa-solid fa-comments"></i></div>
          <div class="faq-cta-body">
            <h2>Still have questions?</h2>
            <p>Can't find what you're looking for? Our support team is here to help.</p>
          </div>
          <div class="faq-cta-actions">
            <a href="contact.php" class="btn btn-primary">
              <i class="fa-solid fa-envelope"></i>
              Contact Support
            </a>
            <a href="feedback.php" class="btn btn-outline">
              <i class="fa-solid fa-star"></i>
              Send Feedback
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