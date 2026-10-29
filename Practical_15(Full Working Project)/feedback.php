<?php
/**
 * StudentHub — Share Feedback Page
 * Dynamic PHP Integration with identical HTML/CSS theme
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

$isLoggedIn = !empty($_SESSION['user_id']);
$userRole   = $_SESSION['user_role'] ?? '';
$userName   = $_SESSION['user_name'] ?? '';
$userEmail  = $_SESSION['user_email'] ?? '';
$userId     = $isLoggedIn ? (int)$_SESSION['user_id'] : null;

$feedbackSuccess = false;
$feedbackError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
              (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    // CSRF verification
    if (!empty($_POST['csrf'])) {
        sh_verify_csrf();
    }

    $name     = trim($_POST['feedbackName'] ?? $_POST['name'] ?? '');
    $email    = strtolower(trim($_POST['feedbackEmail'] ?? $_POST['email'] ?? ''));
    $category = trim($_POST['feedbackCategory'] ?? $_POST['category'] ?? 'General');
    $subject  = trim($_POST['feedbackSubject'] ?? $_POST['subject'] ?? '');
    $message  = trim($_POST['feedbackMessage'] ?? $_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $feedbackError = 'Please fill in all required fields (Name, Email, Subject, and Feedback).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedbackError = 'Please provide a valid email address.';
    } elseif (mb_strlen($message) < 5) {
        $feedbackError = 'Please provide a slightly more detailed message.';
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        if ($rating >= 1 && $rating <= 5) { $message = "Rating: {$rating}/5\n\n" . $message; }
        $fullSubject = !empty($category) && $category !== 'Select a category' ? "[$category] $subject" : $subject;

        $stmt = $db->prepare("INSERT INTO feedback (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $userId, $name, $email, $fullSubject, $message);
        if ($stmt->execute()) {
            $feedbackId = $stmt->insert_id;
            $stmt->close();
            sh_audit($db, 'create', 'feedback', $feedbackId);
            $feedbackSuccess = true;

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => true, 'success' => true, 'message' => 'Thank you! Your feedback has been received.']);
                exit;
            }
        } else {
            $stmt->close();
            $feedbackError = 'Could not save feedback. Please try again.';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => $feedbackError]);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Share Feedback | StudentHub</title>

  <meta name="description"
        content="Tell us what you think about StudentHub. Rate your experience, choose a category and share suggestions to help us improve the CHARUSAT student portal.">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap"
        rel="stylesheet">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <link rel="stylesheet" href="css/style.css">

</head>


<body class="theme-teal feedback-page">


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
          <a href="feedback.php" class="active">Feedback</a>
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


  <!-- ================= FEEDBACK ================= -->

  <main class="feedback-shell">


    <!-- ---------- HERO ---------- -->

    <section class="feedback-hero">

      <div class="container">

        <div class="feedback-hero-inner">
          <span class="section-badge">
            <i class="fa-solid fa-comment-dots"></i>
            Your Voice Matters
          </span>
          <h1>Help us make StudentHub <span>even better</span></h1>
          <p>Your feedback shapes every update. Rate your experience, tell us what's working, and share ideas — it only takes a minute.</p>
        </div>

      </div>

    </section>


    <!-- ---------- BODY ---------- -->

    <section class="section feedback-listing">

      <div class="container">

        <div class="feedback-grid">


          <!-- ===== FORM ===== -->

          <div class="dash-panel feedback-card">

            <div class="dash-panel-head">
              <div>
                <span>Feedback Form</span>
                <h2>Share your thoughts</h2>
              </div>
              <i class="fa-solid fa-pen-to-square dash-panel-tag-icon"></i>
            </div>

            <?php if ($feedbackSuccess): ?>
              <div class="form-message success" role="status" style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;padding:1rem 1.25rem;border-radius:10px;background:rgba(13,148,136,0.12);color:#0d9488;border:1px solid rgba(13,148,136,0.3);">
                <i class="fa-solid fa-circle-check" style="font-size:1.3rem;"></i>
                <div><strong>Thank you!</strong> Your feedback has been successfully submitted and recorded. Our team reviews all suggestions.</div>
              </div>
            <?php elseif (!empty($feedbackError)): ?>
              <div class="form-message error" role="alert" style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;padding:1rem 1.25rem;border-radius:10px;background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.3);">
                <i class="fa-solid fa-circle-exclamation" style="font-size:1.3rem;"></i>
                <div><strong>Notice:</strong> <?= sh_e($feedbackError) ?></div>
              </div>
            <?php endif; ?>

            <form class="feedback-form" id="feedbackForm" method="POST" action="feedback.php" novalidate>
              <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">

              <div class="form-group">
                <label id="ratingLabel">How would you rate your experience?</label>
                <div class="rating" id="feedbackRating" data-value="5" role="radiogroup" aria-labelledby="ratingLabel">
                  <button type="button" class="rating-star" data-value="1" aria-label="1 star"><i class="fa-solid fa-star"></i></button>
                  <button type="button" class="rating-star" data-value="2" aria-label="2 stars"><i class="fa-solid fa-star"></i></button>
                  <button type="button" class="rating-star" data-value="3" aria-label="3 stars"><i class="fa-solid fa-star"></i></button>
                  <button type="button" class="rating-star" data-value="4" aria-label="4 stars"><i class="fa-solid fa-star"></i></button>
                  <button type="button" class="rating-star" data-value="5" aria-label="5 stars"><i class="fa-solid fa-star"></i></button>
                  <span class="rating-caption" id="ratingCaption">Tap a star to rate</span>
                </div>
              </div>

              <div class="form-group">
                <label for="feedbackCategory"><i class="fa-solid fa-tag"></i> Category</label>
                <div class="input-wrapper select-wrapper">
                  <i class="fa-solid fa-tag"></i>
                  <select id="feedbackCategory" name="feedbackCategory">
                    <option value="">Select a category</option>
                    <option selected>Academics &amp; Dashboard</option>
                    <option>Events</option>
                    <option>Website &amp; Design</option>
                    <option>Technical Issue</option>
                    <option>Suggestion</option>
                    <option>Other</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="feedbackSubject"><i class="fa-solid fa-heading"></i> Subject</label>
                <div class="input-wrapper">
                  <i class="fa-solid fa-heading"></i>
                  <input type="text" id="feedbackSubject" name="feedbackSubject" placeholder="A short summary" required>
                </div>
              </div>

              <div class="form-group">
                <label for="feedbackMessage"><i class="fa-solid fa-message"></i> Your Feedback</label>
                <div class="input-wrapper is-textarea">
                  <i class="fa-solid fa-message"></i>
                  <textarea id="feedbackMessage" name="feedbackMessage" rows="4" placeholder="Tell us what you love or what we can improve…" required></textarea>
                </div>
              </div>

              <div class="form-row">

                <div class="form-group">
                  <label for="feedbackName"><i class="fa-solid fa-user"></i> Name</label>
                  <div class="input-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="feedbackName" name="feedbackName" value="<?= sh_e($userName) ?>" placeholder="Your name" required>
                  </div>
                </div>

                <div class="form-group">
                  <label for="feedbackEmail"><i class="fa-solid fa-envelope"></i> Email</label>
                  <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" id="feedbackEmail" name="feedbackEmail" value="<?= sh_e($userEmail) ?>" placeholder="you@charusat.edu.in" required>
                  </div>
                </div>

              </div>

              <div class="form-message" id="feedbackFormMessage" role="status" aria-live="polite"></div>

              <div class="feedback-form-actions">
                <button type="reset" class="btn btn-outline">
                  <i class="fa-solid fa-rotate-left"></i>
                  Clear
                </button>
                <button type="submit" class="btn btn-primary">
                  <i class="fa-solid fa-paper-plane"></i>
                  Submit Feedback
                </button>
              </div>

            </form>

          </div>


          <!-- ===== SIDE ===== -->

          <aside class="feedback-side">

            <div class="dash-panel">
              <div class="dash-panel-head">
                <div>
                  <span>Why it matters</span>
                  <h2>Your feedback in action</h2>
                </div>
              </div>

              <ul class="feedback-points">
                <li class="feedback-point">
                  <span class="feedback-point-icon teal"><i class="fa-solid fa-lightbulb"></i></span>
                  <div>
                    <strong>Shapes new features</strong>
                    <span>The most-requested ideas go straight onto our roadmap.</span>
                  </div>
                </li>
                <li class="feedback-point">
                  <span class="feedback-point-icon violet"><i class="fa-solid fa-bug"></i></span>
                  <div>
                    <strong>Fixes issues faster</strong>
                    <span>Reporting a bug helps us catch and resolve it quickly.</span>
                  </div>
                </li>
                <li class="feedback-point">
                  <span class="feedback-point-icon amber"><i class="fa-solid fa-thumbs-up"></i></span>
                  <div>
                    <strong>Improves everyone's experience</strong>
                    <span>Small suggestions add up to a better portal for all students.</span>
                  </div>
                </li>
              </ul>
            </div>

            <div class="dash-panel feedback-stats">
              <div class="feedback-stat">
                <strong>4.8<small>/5</small></strong>
                <span>Average rating</span>
              </div>
              <div class="feedback-stat">
                <strong>1,240</strong>
                <span>Responses</span>
              </div>
              <div class="feedback-stat">
                <strong>95<small>%</small></strong>
                <span>Ideas reviewed</span>
              </div>
            </div>

          </aside>

        </div>


        <!-- ===== RECENT REVIEWS ===== -->

        <div class="feedback-reviews">

          <div class="section-header">
            <span class="section-badge"><i class="fa-solid fa-quote-left"></i> What students say</span>
            <h2>Recent feedback</h2>
          </div>

          <div class="feedback-review-grid">

            <div class="feedback-review">
              <div class="feedback-review-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
              <p>"The dashboard makes it so easy to track my attendance and deadlines. I check it every single morning now."</p>
              <div class="feedback-review-author">
                <span class="feedback-review-avatar">A</span>
                <div>
                  <strong>Aarav S.</strong>
                  <span>Computer Engineering</span>
                </div>
              </div>
            </div>

            <div class="feedback-review">
              <div class="feedback-review-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
              <p>"Love the dark Midnight Blue theme for late-night study sessions. The whole portal feels premium and fast."</p>
              <div class="feedback-review-author">
                <span class="feedback-review-avatar">P</span>
                <div>
                  <strong>Priya M.</strong>
                  <span>Information Technology</span>
                </div>
              </div>
            </div>

            <div class="feedback-review">
              <div class="feedback-review-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
              </div>
              <p>"Registering for events used to be a hassle. Now it's just a couple of clicks — brilliant improvement."</p>
              <div class="feedback-review-author">
                <span class="feedback-review-avatar">R</span>
                <div>
                  <strong>Rohan D.</strong>
                  <span>Mechanical Engineering</span>
                </div>
              </div>
            </div>

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