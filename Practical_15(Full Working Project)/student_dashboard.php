<?php
/**
 * StudentHub - student_dashboard.php
 * Role-protected student dashboard preserving 100% of the frontend HTML theme and structure.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

sh_require_login();

// RBAC: Only students are authorized to access this page
if (($_SESSION['user_role'] ?? '') !== 'student') {
    // Admin attempting to access student dashboard
    http_response_code(403);
    $unauthMessage = "Access Denied: You are signed in as an Administrator. The Student Academic Dashboard is reserved for student accounts only.";
    $redirectUrl = "admin_dashboard.php";
    $redirectLabel = "Return to Admin Dashboard";
    require __DIR__ . '/includes/unauthorized_view.php';
    exit;
}

$uid = (int)$_SESSION['user_id'];

// Retrieve latest user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $uid);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

$studentName     = $student['name'] ?? $_SESSION['user_name'] ?? 'Student';
$studentId       = $student['student_id'] ?? $_SESSION['student_id'] ?? '25IT049';
$studentEmail    = $student['email'] ?? $_SESSION['user_email'] ?? '25it049@charusat.edu.in';
$studentCourse   = $student['course'] ?? $student['department'] ?? 'Information Technology';
$studentSemester = $student['semester'] ?? '3';
if ($studentSemester === '' || $studentSemester === '0') $studentSemester = '3';
$initials        = strtoupper(substr($studentName, 0, 2));

// Count student registrations
$regCount = 0;
try {
    $regStmt = $db->prepare("SELECT COUNT(*) AS c FROM registrations WHERE user_id = ? AND status = 'registered'");
    $regStmt->bind_param("i", $uid);
    $regStmt->execute();
    $regCount = (int)$regStmt->get_result()->fetch_assoc()['c'];
    $regStmt->close();
} catch (Throwable $e) {}

$isRegisteredSuccess = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Student Dashboard | StudentHub</title>

  <meta name="description"
        content="Your StudentHub dashboard — attendance, CGPA, assignments, today's schedule, events and achievements for CHARUSAT students.">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap"
        rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <!-- Main CSS -->
  <link rel="stylesheet" href="css/style.css">

</head>


<body class="theme-teal dashboard-page" data-php-session="true">


  <!-- ================= FLOATING BACKGROUND ================= -->

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

        <!-- Navigation -->
        <nav class="navbar">
          <a href="index.php">Home</a>
          <a href="about.php">About</a>
          <a href="events.php">Events</a>
          <a href="student_dashboard.php" class="active">Dashboard</a>
          <a href="profile.php">Profile</a>
          <a href="faq.php">FAQ</a>
          <a href="feedback.php">Feedback</a>
          <a href="contact.php">Contact</a>
          <a href="logout.php">Logout</a>
        </nav>

        <!-- Right side -->
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

          <!-- Session countdown pill -->
          <span style="font-size:0.8rem;color:var(--muted);padding:6px 10px;background:var(--surface);border:1px solid var(--line);border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
            <i class="fa-regular fa-clock" style="color:var(--blue);"></i>
            <span id="sessionTimerText">30:00</span>
          </span>

          <a href="logout.php" class="btn btn-outline">
            <i class="fa-solid fa-right-from-bracket"></i>
            Sign Out
          </a>

        </div>

      </div>

    </div>

  </header>


  <!-- ================= DASHBOARD ================= -->

  <main class="dash-shell">

    <div class="container">

<?php if ($isRegisteredSuccess): ?>
      <div style="margin-top:1.5rem;padding:1rem 1.25rem;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;display:flex;align-items:center;gap:12px;color:#065f46;">
        <i class="fa-solid fa-circle-check" style="font-size:1.5rem;"></i>
        <div>
          <strong>Welcome to StudentHub!</strong> Your student account has been created and securely authenticated.
        </div>
      </div>
<?php endif; ?>

      <!-- ---------- WELCOME HERO ---------- -->

      <section class="dash-hero">

        <div class="dash-hero-main">

          <span class="section-badge">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Semester <?= sh_e($studentSemester) ?> &bull; <?= sh_e($studentCourse) ?></span>
          </span>

          <h1>
            Welcome back,
            <span><?= sh_e(explode(' ', $studentName)[0]) ?></span>
          </h1>

          <p>
            Here's your academic snapshot for this semester —
            attendance, results, deadlines and today's classes,
            all in one place.
          </p>

          <div class="dash-identity">

            <div class="dash-identity-avatar"><?= sh_e($initials) ?></div>

            <div class="dash-identity-info">
              <strong><?= sh_e($studentName) ?></strong>
              <span><?= sh_e($studentId) ?> &bull; <?= sh_e($studentCourse) ?></span>
            </div>

            <a href="profile.php" class="btn btn-outline dash-identity-btn">
              <i class="fa-solid fa-user-pen"></i>
              View Profile
            </a>

          </div>

        </div>

        <div class="dash-hero-ring">

          <div class="dash-ring" data-progress="91" style="--dash-ring-val:91;">
            <div class="dash-ring-inner">
              <strong>91%</strong>
              <span>Attendance</span>
            </div>
          </div>

          <p class="dash-ring-note">
            <i class="fa-solid fa-circle-info"></i>
            Attendance is updated by your faculty
          </p>

        </div>

      </section>


      <!-- ---------- STAT CARDS ---------- -->

      <section class="dash-stats">

        <!-- Attendance -->
        <article class="dash-stat-card">
          <div class="dash-stat-head">
            <div class="dash-stat-icon teal">
              <i class="fa-solid fa-calendar-check"></i>
            </div>
          </div>
          <span class="dash-stat-label">Attendance</span>
          <h2>91%</h2>
          <div class="dash-bar">
            <span class="dash-bar-fill" style="width:91%;"></span>
          </div>
        </article>

        <!-- CGPA -->
        <article class="dash-stat-card">
          <div class="dash-stat-head">
            <div class="dash-stat-icon violet">
              <i class="fa-solid fa-chart-line"></i>
            </div>
          </div>
          <span class="dash-stat-label">Current CGPA</span>
          <h2>8.82<small>/10</small></h2>
          <div class="dash-bar">
            <span class="dash-bar-fill" style="width:88%;"></span>
          </div>
        </article>

        <!-- Registrations / Events -->
        <article class="dash-stat-card">
          <div class="dash-stat-head">
            <div class="dash-stat-icon amber">
              <i class="fa-solid fa-award"></i>
            </div>
          </div>
          <span class="dash-stat-label">Event Registrations</span>
          <h2><?= $regCount ?><small> registered</small></h2>
          <div class="dash-bar">
            <span class="dash-bar-fill" style="width:<?= min(100, $regCount * 25) ?>%;"></span>
          </div>
        </article>

        <!-- Profile strength -->
        <article class="dash-stat-card">
          <div class="dash-stat-head">
            <div class="dash-stat-icon rose">
              <i class="fa-solid fa-id-badge"></i>
            </div>
          </div>
          <span class="dash-stat-label">Profile Strength</span>
          <h2>85%</h2>
          <div class="dash-bar">
            <span class="dash-bar-fill" style="width:85%;"></span>
          </div>
        </article>

      </section>


      <!-- ---------- RBAC EVALUATION DEMO BOX ---------- -->

      <div class="dash-panel" style="margin-bottom:1.5rem;border-left:4px solid var(--blue);">
        <div class="dash-panel-head">
          <div>
            <span>Role-Based Access Control</span>
            <h2>Security Verification: RBAC Access Test</h2>
          </div>
        </div>
        <p style="margin-bottom:1rem;color:var(--soft);">
          As an authenticated <strong>Student</strong>, your role is securely bound in PHP session (<code>$_SESSION['user_role'] = 'student'</code>).
          Test accessing the restricted Administrator Dashboard below to verify that unauthorized access is immediately blocked with an unauthorized message.
        </p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <a href="admin_dashboard.php" class="btn btn-outline" style="border-color:#fca5a5;color:#be123c;">
            <i class="fa-solid fa-shield-halved"></i>
            Test Access to Admin Dashboard (Triggers Unauthorized Message)
          </a>
          <a href="timeout.php" class="btn btn-outline">
            <i class="fa-solid fa-clock"></i>
            Test Session Timeout Handler
          </a>
          <a href="logout.php" class="btn btn-outline">
            <i class="fa-solid fa-right-from-bracket"></i>
            Secure Logout
          </a>
        </div>
      </div>


      <!-- ---------- MAIN GRID ---------- -->

      <section class="dash-grid">


        <!-- LEFT COLUMN -->
        <div class="dash-col-main">


          <!-- Attendance by subject -->
          <div class="dash-panel">

            <div class="dash-panel-head">
              <div>
                <span>Academics</span>
                <h2>Enrolled Courses &amp; Practicals</h2>
              </div>
              <a href="profile.php" class="dash-panel-link">
                Details <i class="fa-solid fa-arrow-right"></i>
              </a>
            </div>

            <div class="dash-subjects">

              <div class="dash-subject">
                <div>
                  <strong>ITUE203 · Web Development Frameworks</strong>
                  <p style="margin:2px 0 0;font-size:0.8rem;color:var(--muted);">CSPIT Dept of IT &bull; Practical 15 Final Integration</p>
                </div>
                <div style="text-align:right;">
                  <strong style="color:var(--blue);">96%</strong>
                  <span style="font-size:0.75rem;color:var(--mint);display:block;">Regular</span>
                </div>
              </div>

              <div class="dash-subject">
                <div>
                  <strong>IT204 · Database Management Systems</strong>
                  <p style="margin:2px 0 0;font-size:0.8rem;color:var(--muted);">MySQLi &amp; Relational Architecture</p>
                </div>
                <div style="text-align:right;">
                  <strong style="color:var(--blue);">92%</strong>
                  <span style="font-size:0.75rem;color:var(--mint);display:block;">Regular</span>
                </div>
              </div>

              <div class="dash-subject">
                <div>
                  <strong>IT205 · Operating Systems &amp; Concurrency</strong>
                  <p style="margin:2px 0 0;font-size:0.8rem;color:var(--muted);">Process Scheduling &amp; Virtual Memory</p>
                </div>
                <div style="text-align:right;">
                  <strong style="color:var(--blue);">88%</strong>
                  <span style="font-size:0.75rem;color:var(--mint);display:block;">Regular</span>
                </div>
              </div>

              <div class="dash-subject">
                <div>
                  <strong>IT206 · Computer Networks &amp; Security</strong>
                  <p style="margin:2px 0 0;font-size:0.8rem;color:var(--muted);">TCP/IP &amp; Web Security</p>
                </div>
                <div style="text-align:right;">
                  <strong style="color:var(--blue);">90%</strong>
                  <span style="font-size:0.75rem;color:var(--mint);display:block;">Regular</span>
                </div>
              </div>

            </div>

          </div>


          <!-- Upcoming deadlines -->
          <div class="dash-panel">

            <div class="dash-panel-head">
              <div>
                <span>To-do</span>
                <h2>Upcoming Deadlines</h2>
              </div>
              <a href="events.php" class="dash-panel-link">
                Events <i class="fa-solid fa-arrow-right"></i>
              </a>
            </div>

            <div class="dash-deadlines">

              <div class="dash-deadline">
                <div class="dash-deadline-date">
                  <span class="d">15</span>
                  <span class="m">NOV</span>
                </div>
                <div class="dash-deadline-body">
                  <strong>Practical 15 Final Integration Report</strong>
                  <span>HTML/PHP Integration &bull; XAMPP MySQLi lab submission</span>
                </div>
                <span class="dash-chip due-soon">Active</span>
              </div>

              <div class="dash-deadline">
                <div class="dash-deadline-date">
                  <span class="d">20</span>
                  <span class="m">NOV</span>
                </div>
                <div class="dash-deadline-body">
                  <strong>Campus Hackathon Prototype Submission</strong>
                  <span>CHARUSAT Innovation Center</span>
                </div>
                <span class="dash-chip">Upcoming</span>
              </div>

              <div class="dash-deadline">
                <div class="dash-deadline-date">
                  <span class="d">28</span>
                  <span class="m">NOV</span>
                </div>
                <div class="dash-deadline-body">
                  <strong>Cyber Security CTF Workshop</strong>
                  <span>Ethical Hacking Lab</span>
                </div>
                <span class="dash-chip">Upcoming</span>
              </div>

            </div>

          </div>

        </div>


        <!-- RIGHT COLUMN -->
        <div class="dash-col-side">


          <!-- Today's schedule -->
          <div class="dash-panel">

            <div class="dash-panel-head">
              <div>
                <span>Today</span>
                <h2>Class Schedule</h2>
              </div>
              <span class="dash-panel-tag">Active</span>
            </div>

            <div class="dash-timeline">

              <div class="dash-slot done">
                <span class="dash-slot-time">09:00</span>
                <div class="dash-slot-body">
                  <strong>Web Frameworks Lab</strong>
                  <span><i class="fa-solid fa-location-dot"></i> CS Lab 1</span>
                </div>
              </div>

              <div class="dash-slot now">
                <span class="dash-slot-time">11:00</span>
                <div class="dash-slot-body">
                  <strong>DBMS Lab Practical</strong>
                  <span><i class="fa-solid fa-location-dot"></i> Lab 2</span>
                </div>
                <span class="dash-chip live">Now</span>
              </div>

              <div class="dash-slot">
                <span class="dash-slot-time">01:30</span>
                <div class="dash-slot-body">
                  <strong>Operating Systems</strong>
                  <span><i class="fa-solid fa-location-dot"></i> Room 305</span>
                </div>
              </div>

              <div class="dash-slot">
                <span class="dash-slot-time">03:00</span>
                <div class="dash-slot-body">
                  <strong>Faculty Mentoring</strong>
                  <span><i class="fa-solid fa-location-dot"></i> Cabin 12</span>
                </div>
              </div>

            </div>

          </div>


          <!-- Academic standing -->
          <div class="dash-panel">

            <div class="dash-panel-head">
              <div>
                <span>Performance</span>
                <h2>Academic Standing</h2>
              </div>
            </div>

            <div class="dash-badge-grid">

              <div class="dash-badge">
                <i class="fa-solid fa-ranking-star"></i>
                <strong>Top 5%</strong>
                <span>Class Rank</span>
              </div>

              <div class="dash-badge">
                <i class="fa-solid fa-book-open"></i>
                <strong>6</strong>
                <span>Subjects</span>
              </div>

              <div class="dash-badge">
                <i class="fa-solid fa-layer-group"></i>
                <strong>Sem <?= sh_e($studentSemester) ?></strong>
                <span>Semester</span>
              </div>

              <div class="dash-badge">
                <i class="fa-solid fa-circle-check"></i>
                <strong>Enrolled</strong>
                <span>Status</span>
              </div>

            </div>

          </div>

        </div>

      </section>


      <!-- ---------- QUICK ACTIONS ---------- -->

      <section class="dash-grid dash-grid-flip">

        <!-- Quick actions -->
        <div class="dash-panel dash-col-main">

          <div class="dash-panel-head">
            <div>
              <span>Shortcuts</span>
              <h2>Quick Actions</h2>
            </div>
          </div>

          <div class="dash-actions">

            <a href="profile.php" class="dash-action">
              <div class="dash-action-icon teal"><i class="fa-solid fa-user-graduate"></i></div>
              <div>
                <strong>My Profile</strong>
                <span>Edit details &amp; portfolio</span>
              </div>
              <i class="fa-solid fa-arrow-right"></i>
            </a>

            <a href="events.php" class="dash-action">
              <div class="dash-action-icon violet"><i class="fa-solid fa-calendar-days"></i></div>
              <div>
                <strong>Events</strong>
                <span>Register for activities</span>
              </div>
              <i class="fa-solid fa-arrow-right"></i>
            </a>

            <a href="feedback.php" class="dash-action">
              <div class="dash-action-icon amber"><i class="fa-solid fa-comments"></i></div>
              <div>
                <strong>Feedback</strong>
                <span>Share suggestions</span>
              </div>
              <i class="fa-solid fa-arrow-right"></i>
            </a>

            <a href="contact.php" class="dash-action">
              <div class="dash-action-icon rose"><i class="fa-solid fa-headset"></i></div>
              <div>
                <strong>Support</strong>
                <span>Contact administration</span>
              </div>
              <i class="fa-solid fa-arrow-right"></i>
            </a>

          </div>

        </div>


        <!-- Profile completion -->
        <div class="dash-panel dash-col-side dash-achievements">

          <div class="dash-panel-head">
            <div>
              <span>Get started</span>
              <h2>Complete Your Profile</h2>
            </div>
          </div>

          <p class="dash-nudge-text">
            A complete profile helps your faculty and classmates recognise you.
            Add your contact details and a short bio to personalise your dashboard.
          </p>

          <div class="dash-nudge-meter">
            <div class="dash-bar">
              <span class="dash-bar-fill" style="width:85%;"></span>
            </div>
            <span class="dash-nudge-pct">
              <span>85%</span> complete
            </span>
          </div>

          <a href="profile.php" class="btn btn-primary dash-achievements-btn">
            <i class="fa-solid fa-user-pen"></i>
            Complete my profile
          </a>

        </div>

      </section>

    </div>

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
            <li><a href="student_dashboard.php">Dashboard</a></li>
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
          <p><strong>Name:</strong> <?= sh_e($studentName) ?></p>
          <p><strong>ID:</strong> <?= sh_e($studentId) ?></p>
          <p><strong>Email:</strong> <?= sh_e($studentEmail) ?></p>
          <p><strong>Department:</strong> <?= sh_e($studentCourse) ?></p>
        </div>

      </div>

      <div class="footer-bottom">
        <p>© <span data-year></span> StudentHub — CHARUSAT. Designed for academic excellence.</p>
      </div>

    </div>

  </footer>


  <!-- JS -->
  <script src="js/script.js"></script>

  <script>
    // 30-minute session countdown
    let remaining = 1800;
    const timerEl = document.getElementById('sessionTimerText');
    setInterval(function() {
      if (remaining <= 0) {
        window.location.href = 'timeout.php';
        return;
      }
      remaining--;
      const m = Math.floor(remaining / 60);
      const s = remaining % 60;
      if (timerEl) {
        timerEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
      }
    }, 1000);
  </script>

</body>

</html>
