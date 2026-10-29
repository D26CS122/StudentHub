<?php
/**
 * StudentHub — Campus Events Page
 * Dynamic PHP Integration with identical HTML/CSS theme
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

$isLoggedIn = !empty($_SESSION['user_id']);
$userRole   = $_SESSION['user_role'] ?? '';
$userName   = $_SESSION['user_name'] ?? '';
$userId     = (int)($_SESSION['user_id'] ?? 0);

// Handle Event Registration via POST
$regMessage = '';
$regError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_event') {
    if (!$isLoggedIn) {
        header('Location: login.php?error=session');
        exit;
    }
    sh_verify_csrf();
    $eventId = (int)($_POST['event_id'] ?? 0);

    if ($eventId > 0) {
        // Check if event exists and is published
        $stmt = $db->prepare("SELECT id, title, capacity FROM events WHERE id = ? AND status = 'published'");
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $eventData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($eventData) {
            // Check capacity
            $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM registrations WHERE event_id = ? AND status = 'registered'");
            $countStmt->bind_param('i', $eventId);
            $countStmt->execute();
            $countRes = $countStmt->get_result()->fetch_assoc();
            $countStmt->close();

            if ($countRes['total'] >= $eventData['capacity']) {
                $regError = 'Sorry, this event has reached full capacity.';
            } else {
                $ins = $db->prepare("INSERT INTO registrations (user_id, event_id, status) VALUES (?, ?, 'registered') ON DUPLICATE KEY UPDATE status = 'registered'");
                $ins->bind_param('ii', $userId, $eventId);
                if ($ins->execute()) {
                    sh_audit($db, 'register', 'event', $eventId);
                    $ins->close();
                    header('Location: events.php?registered=1');
                    exit;
                } else {
                    $regError = 'Registration could not be completed. Please try again.';
                    $ins->close();
                }
            }
        } else {
            $regError = 'Event not found or is currently closed.';
        }
    }
}

// Fetch user's registered events if logged in
$userRegistered = [];
if ($isLoggedIn) {
    $uStmt = $db->prepare("SELECT event_id FROM registrations WHERE user_id = ? AND status = 'registered'");
    $uStmt->bind_param('i', $userId);
    $uStmt->execute();
    $uRes = $uStmt->get_result();
    while ($row = $uRes->fetch_assoc()) {
        $userRegistered[(int)$row['event_id']] = true;
    }
    $uStmt->close();
}

// Query all published events from MySQL
$events = [];
$res = $db->query("
    SELECT e.*, 
           (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = 'registered') AS attendees
    FROM events e 
    WHERE e.status = 'published' 
    ORDER BY e.event_date ASC
");
if ($res) {
    $events = $res->fetch_all(MYSQLI_ASSOC);
}

// Fallback to sample events if database has none
if (empty($events)) {
    $events = [
        [
            'id' => 1,
            'title' => 'TechFest 2026 — Innovation Summit',
            'description' => "CHARUSAT's flagship technology festival returns bigger than ever. Two days of hackathons, robotics showcases, expert keynotes and a startup pitch arena.",
            'category' => 'Technical',
            'event_date' => '2026-10-10 09:00:00',
            'venue' => 'Main Auditorium',
            'capacity' => 500,
            'attendees' => 412
        ],
        [
            'id' => 2,
            'title' => 'AI Hackathon 2026',
            'description' => 'Build AI-powered solutions and compete with students across Gujarat in a 24-hour sprint.',
            'category' => 'Technical',
            'event_date' => '2026-08-20 09:00:00',
            'venue' => 'CHARUSAT Auditorium',
            'capacity' => 150,
            'attendees' => 120
        ],
        [
            'id' => 3,
            'title' => 'Spandan Cultural Night',
            'description' => 'An evening of dance, drama, music and celebration under the stars with fellow students.',
            'category' => 'Cultural',
            'event_date' => '2026-09-05 18:00:00',
            'venue' => 'Open Air Theatre',
            'capacity' => 400,
            'attendees' => 380
        ],
        [
            'id' => 4,
            'title' => 'Cloud & DevOps Workshop',
            'description' => 'Hands-on training with Docker, Kubernetes and modern CI/CD pipelines led by industry engineers.',
            'category' => 'Workshop',
            'event_date' => '2026-09-12 10:00:00',
            'venue' => 'CS Lab 3',
            'capacity' => 80,
            'attendees' => 64
        ],
        [
            'id' => 5,
            'title' => 'Annual Sports Meet',
            'description' => 'Track, field and team events — represent your department and go for gold.',
            'category' => 'Sports',
            'event_date' => '2026-10-02 07:00:00',
            'venue' => 'Main Ground',
            'capacity' => 300,
            'attendees' => 210
        ]
    ];
}

$featuredEvent = $events[0] ?? null;

// Helper to categorize for CSS class
function getCatClass($category) {
    $c = strtolower(trim($category));
    if (strpos($c, 'tech') !== false || strpos($c, 'comp') !== false || strpos($c, 'ai') !== false) return 'technical';
    if (strpos($c, 'cult') !== false || strpos($c, 'fest') !== false || strpos($c, 'music') !== false) return 'cultural';
    if (strpos($c, 'sport') !== false || strpos($c, 'athlet') !== false) return 'sports';
    if (strpos($c, 'work') !== false || strpos($c, 'sem') !== false || strpos($c, 'train') !== false) return 'workshop';
    return 'technical';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Campus Events | StudentHub</title>

  <meta name="description"
        content="Discover upcoming CHARUSAT campus events — hackathons, cultural nights, workshops, sports and seminars. Filter by category and register.">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap"
        rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <!-- Main CSS -->
  <link rel="stylesheet" href="css/style.css">

</head>


<body class="theme-teal events-page">


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
          <a href="events.php" class="active">Events</a>
          <a href="dashboard.php">Dashboard</a>
          <a href="profile.php">Profile</a>
          <a href="faq.php">FAQ</a>
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
              <?= sh_e(explode(' ', trim($userName))[0] ?: 'Dashboard') ?>
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


  <!-- ================= NOTIFICATIONS ================= -->
  <?php if (isset($_GET['registered'])): ?>
    <div class="container" style="margin-top:1.5rem;">
      <div class="auth-message success" style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.25rem;border-radius:12px;background:rgba(13,148,136,0.12);border:1px solid rgba(13,148,136,0.3);color:#0d9488;">
        <i class="fa-solid fa-circle-check" style="font-size:1.3rem;"></i>
        <div>
          <strong>Registration Confirmed!</strong> Your spot for this campus event is booked. View your tickets in the <a href="student_dashboard.php" style="text-decoration:underline;color:inherit;font-weight:600;">Student Dashboard</a>.
        </div>
      </div>
    </div>
  <?php elseif (!empty($regError)): ?>
    <div class="container" style="margin-top:1.5rem;">
      <div class="auth-message error" style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.25rem;border-radius:12px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#ef4444;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:1.3rem;"></i>
        <div><strong>Registration Notice:</strong> <?= sh_e($regError) ?></div>
      </div>
    </div>
  <?php endif; ?>


  <!-- ================= EVENTS MAIN ================= -->

  <main class="events-shell">


    <!-- ---------- HERO ---------- -->

    <section class="events-hero">

      <div class="container">

        <div class="events-hero-inner">

          <span class="section-badge">
            <i class="fa-solid fa-calendar-star"></i>
            Campus Events
          </span>

          <h1>Discover what's happening<br><span>across campus</span></h1>

          <p>
            From hackathons and workshops to cultural nights and sports meets —
            explore every CHARUSAT event in one place, filter by what you love,
            and reserve your spot in seconds.
          </p>

          <div class="events-hero-stats">

            <div class="events-hero-stat">
              <strong><?= count($events) ?></strong>
              <span>Upcoming Events</span>
            </div>

            <div class="events-hero-stat">
              <strong>4</strong>
              <span>This Month</span>
            </div>

            <div class="events-hero-stat">
              <strong>5</strong>
              <span>Categories</span>
            </div>

            <div class="events-hero-stat">
              <strong>Open</strong>
              <span>Registrations</span>
            </div>

          </div>

        </div>

      </div>

    </section>


    <!-- ---------- LISTING ---------- -->

    <section class="section events-listing">

      <div class="container">

        <?php if ($featuredEvent): 
            $fCatClass = getCatClass($featuredEvent['category']);
            $fTime = strtotime($featuredEvent['event_date']);
            $fIsRegistered = isset($userRegistered[(int)$featuredEvent['id']]);
        ?>
        <!-- Featured -->
        <article class="event-featured" data-category="<?= sh_e($fCatClass) ?>">

          <div class="event-featured-media">
            <span class="event-featured-icon"><i class="fa-solid fa-rocket"></i></span>
            <span class="event-featured-flag">Featured</span>
          </div>

          <div class="event-featured-body">

            <div class="event-featured-tags">
              <span class="event-tag <?= sh_e($fCatClass) ?>"><?= sh_e($featuredEvent['category']) ?></span>
              <span class="event-featured-date"><i class="fa-solid fa-calendar-day"></i> <?= date('d F Y', $fTime) ?></span>
            </div>

            <h2><?= sh_e($featuredEvent['title']) ?></h2>

            <p><?= sh_e($featuredEvent['description']) ?></p>

            <div class="event-featured-meta">
              <span><i class="fa-solid fa-location-dot"></i> <?= sh_e($featuredEvent['venue']) ?></span>
              <span><i class="fa-solid fa-clock"></i> <?= date('g:i A', $fTime) ?></span>
              <span><i class="fa-solid fa-users"></i> <?= (int)$featuredEvent['attendees'] ?> attending</span>
            </div>

            <div class="event-featured-actions">
              <?php if (!$isLoggedIn): ?>
                <a href="login.php?redirect=events.php" class="btn btn-primary">
                  <i class="fa-solid fa-ticket"></i>
                  Sign in to Register
                </a>
              <?php elseif ($fIsRegistered): ?>
                <span class="btn btn-outline" style="border-color:#0d9488;color:#0d9488;cursor:default;">
                  <i class="fa-solid fa-circle-check"></i> Registered
                </span>
              <?php else: ?>
                <form method="POST" action="events.php" style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">
                  <input type="hidden" name="action" value="register_event">
                  <input type="hidden" name="event_id" value="<?= (int)$featuredEvent['id'] ?>">
                  <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-ticket"></i> Register Now
                  </button>
                </form>
              <?php endif; ?>
              <a href="contact.php" class="btn btn-outline">Learn More</a>
            </div>

          </div>

        </article>
        <?php endif; ?>


        <!-- Filters -->
        <div class="events-toolbar">

          <div>
            <h2 class="events-toolbar-title">All Upcoming Events</h2>
            <p class="events-toolbar-sub">Filter by category to find your next experience.</p>
          </div>

          <div class="events-filter" role="tablist" aria-label="Filter events by category">
            <button class="events-filter-btn active" type="button" data-filter="all">All</button>
            <button class="events-filter-btn" type="button" data-filter="technical">Technical</button>
            <button class="events-filter-btn" type="button" data-filter="cultural">Cultural</button>
            <button class="events-filter-btn" type="button" data-filter="sports">Sports</button>
            <button class="events-filter-btn" type="button" data-filter="workshop">Workshops</button>
          </div>

        </div>


        <!-- Grid -->
        <div class="events-grid events-collection">

          <?php foreach ($events as $idx => $ev): 
              $catClass = getCatClass($ev['category']);
              $timestamp = strtotime($ev['event_date']);
              $day = date('d', $timestamp);
              $month = strtoupper(date('M', $timestamp));
              $isReg = isset($userRegistered[(int)$ev['id']]);
          ?>
            <div class="event-card" data-category="<?= sh_e($catClass) ?>">
              <div class="event-date">
                <span class="day"><?= sh_e($day) ?></span>
                <span class="month"><?= sh_e($month) ?></span>
              </div>
              <div class="event-content">
                <span class="event-tag <?= sh_e($catClass) ?>"><?= sh_e($ev['category']) ?></span>
                <h3><?= sh_e($ev['title']) ?></h3>
                <p><?= sh_e($ev['description']) ?></p>
                <div class="event-meta">
                  <span><i class="fa-solid fa-location-dot"></i> <?= sh_e($ev['venue']) ?></span>
                  <span><i class="fa-solid fa-clock"></i> <?= date('g:i A', $timestamp) ?></span>
                </div>
                
                <?php if (!$isLoggedIn): ?>
                  <a href="login.php?redirect=events.php" class="btn btn-outline">Register</a>
                <?php elseif ($isReg): ?>
                  <span class="btn btn-outline" style="border-color:#0d9488;color:#0d9488;cursor:default;">
                    <i class="fa-solid fa-check"></i> Registered
                  </span>
                <?php else: ?>
                  <form method="POST" action="events.php" style="display:inline;">
                    <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">
                    <input type="hidden" name="action" value="register_event">
                    <input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>">
                    <button type="submit" class="btn btn-outline" style="cursor:pointer;">Register</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>

        </div>

        <p class="events-empty" id="eventsEmpty" hidden>
          <i class="fa-solid fa-calendar-xmark"></i>
          No events in this category yet — check back soon.
        </p>

      </div>

    </section>


    <!-- ---------- CTA ---------- -->

    <section class="section events-cta-section">

      <div class="container">

        <div class="events-cta">
          <div class="events-cta-icon"><i class="fa-solid fa-bullhorn"></i></div>
          <h2>Hosting a club or department event?</h2>
          <p>Submit your event details and reach thousands of CHARUSAT students through StudentHub.</p>
          <a href="contact.php" class="btn btn-primary btn-large">
            <i class="fa-solid fa-paper-plane"></i>
            Submit an Event
          </a>
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


  <!-- ================= SCRIPT ================= -->
  <script src="js/script.js"></script>

</body>

</html>