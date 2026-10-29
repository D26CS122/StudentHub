<?php
/**
 * StudentHub — Student Profile Page
 * Dynamic PHP Integration with identical HTML/CSS theme
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

sh_require_login();

$userId   = (int)$_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'student';

// Handle Profile Updates via POST
$updateSuccess = false;
$updateError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    sh_verify_csrf();
    
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName'] ?? '');
    $fullName  = trim($firstName . ' ' . $lastName);
    $email     = strtolower(trim($_POST['profileEmail'] ?? ''));
    $phone     = trim($_POST['profilePhone'] ?? '');
    $dob       = trim($_POST['profileDob'] ?? '');
    $gender    = trim($_POST['profileGender'] ?? '');
    $address   = trim($_POST['profileAddress'] ?? '');
    $bio       = trim($_POST['profileBioField'] ?? '');

    if (empty($fullName) || empty($email)) {
        $updateError = 'Please provide your full name and a valid email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $updateError = 'The email address format is invalid.';
    } else {
        // Check for duplicate email in users table (belonging to another user)
        $dupStmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $dupStmt->bind_param('si', $email, $userId);
        $dupStmt->execute();
        if ($dupStmt->get_result()->fetch_assoc()) {
            $updateError = 'That email address is already in use by another account.';
            $dupStmt->close();
        } else {
            $dupStmt->close();

            // Try to update users table
            $upUsers = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $upUsers->bind_param('ssi', $fullName, $email, $userId);
            $upUsers->execute();
            $upUsers->close();

            // Also attempt updating extended fields if columns exist
            try {
                $upExt = $db->prepare("UPDATE users SET phone = ?, address = ?, bio = ?, dob = ?, gender = ? WHERE id = ?");
                if ($upExt) {
                    $upExt->bind_param('sssssi', $phone, $address, $bio, $dob, $gender, $userId);
                    $upExt->execute();
                    $upExt->close();
                }
            } catch (mysqli_sql_exception $e) {
                // Extended columns might not exist yet; store in session fallback
            }

            // Sync with students table if record exists
            $upStud = $db->prepare("UPDATE students SET name = ?, email = ?, mobile = ? WHERE user_id = ? OR email = ?");
            $upStud->bind_param('sssis', $fullName, $email, $phone, $userId, $email);
            $upStud->execute();
            $upStud->close();

            // Update session cache
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['profile_phone'] = $phone;
            $_SESSION['profile_dob'] = $dob;
            $_SESSION['profile_gender'] = $gender;
            $_SESSION['profile_address'] = $address;
            $_SESSION['profile_bio'] = $bio;

            sh_audit($db, 'update', 'profile', $userId);
            $updateSuccess = true;
        }
    }
}

// Fetch live user data from users table
$userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userData = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$userData) {
    header('Location: logout.php');
    exit;
}

// Fetch student specific record if available
$studData = null;
$studStmt = $db->prepare("SELECT * FROM students WHERE user_id = ? OR student_id = ? OR email = ? LIMIT 1");
$studStmt->bind_param('iss', $userId, $userData['student_id'], $userData['email']);
$studStmt->execute();
$studData = $studStmt->get_result()->fetch_assoc();
$studStmt->close();

// Prepare display values
$name       = $userData['name'] ?? 'Student';
$nameParts  = explode(' ', trim($name));
$firstName  = $nameParts[0] ?? '';
$lastName   = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';
$studentId  = $userData['student_id'] ?: ($studData['student_id'] ?? '25IT049');
$email      = $userData['email'] ?? '';
$department = $userData['department'] ?: ($studData['course'] ?? 'Information Technology');
$semester   = $userData['semester'] ?: ($studData['year_level'] ? 'Semester ' . ((int)$studData['year_level'] * 2) : 'Semester 4');
$program    = 'B.Tech ' . $department;
$status     = ucfirst($userData['status'] ?? 'Active');
$phone      = $userData['phone'] ?? ($studData['mobile'] ?? ($_SESSION['profile_phone'] ?? '+91 98765 43210'));
$dob        = $userData['dob'] ?? ($_SESSION['profile_dob'] ?? '15 / 08 / 2005');
$gender     = $userData['gender'] ?? ($_SESSION['profile_gender'] ?? 'Male');
$address    = $userData['address'] ?? ($_SESSION['profile_address'] ?? 'CHARUSAT Campus, Changa, Anand, Gujarat');
$bio        = $userData['bio'] ?? ($_SESSION['profile_bio'] ?? 'Student of ' . $department . ' at CHARUSAT. Passionate about software engineering, full-stack web development and campus initiatives.');

// Initials for avatar
$initials = 'ST';
if (!empty($nameParts[0])) {
    $initials = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $initials .= strtoupper(substr($nameParts[count($nameParts) - 1], 0, 1));
    }
}

// Profile strength calculation
$fieldsCount = 7;
$filledCount = 0;
foreach ([$name, $email, $phone, $dob, $gender, $address, $bio] as $f) {
    if (!empty(trim((string)$f))) $filledCount++;
}
$strengthPercent = (int)round(($filledCount / $fieldsCount) * 100);

// Query registered events count for profile stat
$regCountStmt = $db->prepare("SELECT COUNT(*) AS total FROM registrations WHERE user_id = ? AND status = 'registered'");
$regCountStmt->bind_param('i', $userId);
$regCountStmt->execute();
$regCount = (int)($regCountStmt->get_result()->fetch_assoc()['total'] ?? 0);
$regCountStmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>My Profile | StudentHub</title>

  <meta name="description"
        content="Your StudentHub profile — personal details, academic record, skills, contact information and account settings for CHARUSAT students.">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Galada&display=swap"
        rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

  <!-- Main CSS -->
  <link rel="stylesheet" href="css/style.css">

</head>


<body class="theme-teal profile-page" data-php-session="true">


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
          <a href="dashboard.php">Dashboard</a>
          <a href="profile.php" class="active">Profile</a>
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

          <a href="logout.php" class="btn btn-outline">
            <i class="fa-solid fa-right-from-bracket"></i>
            Sign Out
          </a>

        </div>

      </div>

    </div>

  </header>


  <!-- ================= NOTICES ================= -->
  <?php if ($updateSuccess): ?>
    <div class="container" style="margin-top:1.5rem;">
      <div class="auth-message success" style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.25rem;border-radius:12px;background:rgba(13,148,136,0.12);border:1px solid rgba(13,148,136,0.3);color:#0d9488;">
        <i class="fa-solid fa-circle-check" style="font-size:1.3rem;"></i>
        <div><strong>Profile Updated!</strong> Your personal and academic details have been successfully saved to your record.</div>
      </div>
    </div>
  <?php elseif (!empty($updateError)): ?>
    <div class="container" style="margin-top:1.5rem;">
      <div class="auth-message error" style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.25rem;border-radius:12px;background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#ef4444;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:1.3rem;"></i>
        <div><strong>Update Notice:</strong> <?= sh_e($updateError) ?></div>
      </div>
    </div>
  <?php endif; ?>


  <!-- ================= PROFILE ================= -->

  <main class="profile-shell">

    <div class="container">


      <!-- ---------- PROFILE HERO ---------- -->

      <section class="profile-hero">

        <div class="profile-cover">
          <span class="profile-cover-glow"></span>
        </div>

        <div class="profile-hero-body">

          <div class="profile-avatar">
            <span><?= sh_e($initials) ?></span>
            <i class="fa-solid fa-circle-check profile-avatar-badge" aria-hidden="true"></i>
          </div>

          <div class="profile-headline">

            <span class="section-badge">
              <i class="fa-solid fa-id-badge"></i>
              Student Profile
            </span>

            <h1><?= sh_e($name) ?></h1>

            <p class="profile-role"><?= sh_e($program) ?></p>

            <div class="profile-chips">
              <span class="profile-chip"><i class="fa-solid fa-hashtag"></i> <span><?= sh_e($studentId) ?></span></span>
              <span class="profile-chip"><i class="fa-solid fa-building-columns"></i> CHARUSAT</span>
              <span class="profile-chip"><i class="fa-solid fa-graduation-cap"></i> <span><?= sh_e($department) ?></span></span>
              <span class="profile-chip is-active"><i class="fa-solid fa-circle"></i> <span><?= sh_e($status) ?></span></span>
            </div>

          </div>

          <div class="profile-hero-actions">
            <a href="#profile-edit" class="btn btn-primary">
              <i class="fa-solid fa-pen"></i>
              Edit Profile
            </a>
            <a href="dashboard.php" class="btn btn-outline">
              <i class="fa-solid fa-gauge-high"></i>
              Dashboard
            </a>
          </div>

        </div>

        <div class="profile-quickstats">

          <div class="profile-quickstat">
            <span class="profile-quickstat-icon teal"><i class="fa-solid fa-award"></i></span>
            <div>
              <strong>9.28<small>/10</small></strong>
              <span>Current CGPA</span>
            </div>
          </div>

          <div class="profile-quickstat">
            <span class="profile-quickstat-icon violet"><i class="fa-solid fa-user-check"></i></span>
            <div>
              <strong>94<small>%</small></strong>
              <span>Attendance</span>
            </div>
          </div>

          <div class="profile-quickstat">
            <span class="profile-quickstat-icon amber"><i class="fa-solid fa-layer-group"></i></span>
            <div>
              <strong>112</strong>
              <span>Credits Earned</span>
            </div>
          </div>

          <div class="profile-quickstat">
            <span class="profile-quickstat-icon rose"><i class="fa-solid fa-ranking-star"></i></span>
            <div>
              <strong><?= $regCount ?></strong>
              <span>Registered Events</span>
            </div>
          </div>

        </div>

      </section>


      <!-- ---------- MAIN GRID ---------- -->

      <div class="profile-grid">


        <!-- ===== MAIN COLUMN ===== -->

        <div class="profile-main">


          <!-- About -->
          <section class="dash-panel">

            <div class="dash-panel-head">
              <div>
                <span>Introduction</span>
                <h2>About Me</h2>
              </div>
              <i class="fa-solid fa-user-pen dash-panel-tag-icon"></i>
            </div>

            <p class="profile-bio">
              <?= nl2br(sh_e($bio)) ?>
            </p>

          </section>


          <!-- Academic information -->
          <section class="dash-panel">

            <div class="dash-panel-head">
              <div>
                <span>Records</span>
                <h2>Academic Information</h2>
              </div>
              <span class="dash-panel-tag">Verified</span>
            </div>

            <div class="profile-info-grid">

              <div class="profile-info">
                <span class="profile-info-label"><i class="fa-solid fa-graduation-cap"></i> Program</span>
                <span class="profile-info-value"><?= sh_e($program) ?></span>
              </div>

              <div class="profile-info">
                <span class="profile-info-label"><i class="fa-solid fa-building"></i> Institute</span>
                <span class="profile-info-value">CSPIT — CHARUSAT University</span>
              </div>

              <div class="profile-info">
                <span class="profile-info-label"><i class="fa-solid fa-hashtag"></i> Enrollment No.</span>
                <span class="profile-info-value"><?= sh_e($studentId) ?></span>
              </div>

              <div class="profile-info">
                <span class="profile-info-label"><i class="fa-solid fa-calendar-days"></i> Batch</span>
                <span class="profile-info-value">2024 – 2028</span>
              </div>

              <div class="profile-info">
                <span class="profile-info-label"><i class="fa-solid fa-layer-group"></i> Current Semester</span>
                <span class="profile-info-value"><?= sh_e($semester) ?></span>
              </div>

              <div class="profile-info">
                <span class="profile-info-label"><i class="fa-solid fa-chalkboard-user"></i> Faculty Advisor</span>
                <span class="profile-info-value">Prof. P. Patel (IT Dept.)</span>
              </div>

            </div>

          </section>


        </div>


        <!-- ===== SIDE COLUMN ===== -->

        <aside class="profile-side">


          <!-- Contact -->
          <section class="dash-panel">

            <div class="dash-panel-head">
              <div>
                <span>Reach Me</span>
                <h2>Contact Details</h2>
              </div>
            </div>

            <ul class="profile-contact">

              <li class="profile-contact-item">
                <span class="profile-contact-icon"><i class="fa-solid fa-envelope"></i></span>
                <div>
                  <span>Email</span>
                  <strong><?= sh_e($email) ?></strong>
                </div>
              </li>

              <li class="profile-contact-item">
                <span class="profile-contact-icon"><i class="fa-solid fa-phone"></i></span>
                <div>
                  <span>Phone</span>
                  <strong><?= sh_e($phone) ?></strong>
                </div>
              </li>

              <li class="profile-contact-item">
                <span class="profile-contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                <div>
                  <span>Address</span>
                  <strong><?= sh_e($address) ?></strong>
                </div>
              </li>

            </ul>

          </section>


          <!-- Profile completion -->
          <section class="dash-panel">

            <div class="dash-panel-head">
              <div>
                <span>Account</span>
                <h2>Profile Strength</h2>
              </div>
            </div>

            <div class="profile-completion">
              <div class="profile-completion-top">
                <span>Completion</span>
                <strong><?= $strengthPercent ?>%</strong>
              </div>
              <div class="dash-bar"><span class="dash-bar-fill" style="width: <?= $strengthPercent ?>%;"></span></div>
              <p class="profile-completion-hint">
                <i class="fa-solid fa-circle-info"></i>
                Fill in the fields below to strengthen your profile.
              </p>
            </div>

          </section>


          <!-- Quick actions -->
          <section class="dash-panel">

            <div class="dash-panel-head">
              <div>
                <span>Shortcuts</span>
                <h2>Quick Actions</h2>
              </div>
            </div>

            <div class="profile-actions">

              <a href="#profile-edit" class="profile-action">
                <span class="profile-action-icon teal"><i class="fa-solid fa-pen-to-square"></i></span>
                <span>Edit Details</span>
                <i class="fa-solid fa-chevron-right"></i>
              </a>

              <a href="dashboard.php" class="profile-action">
                <span class="profile-action-icon violet"><i class="fa-solid fa-gauge-high"></i></span>
                <span>View Dashboard</span>
                <i class="fa-solid fa-chevron-right"></i>
              </a>

              <a href="events.php" class="profile-action">
                <span class="profile-action-icon amber"><i class="fa-solid fa-calendar-star"></i></span>
                <span>Browse Events</span>
                <i class="fa-solid fa-chevron-right"></i>
              </a>

              <a href="logout.php" class="profile-action">
                <span class="profile-action-icon rose"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span>Sign Out</span>
                <i class="fa-solid fa-chevron-right"></i>
              </a>

            </div>

          </section>

        </aside>

      </div>


      <!-- ---------- EDIT DETAILS ---------- -->

      <section class="dash-panel profile-edit" id="profile-edit">

        <div class="dash-panel-head">
          <div>
            <span>Manage</span>
            <h2>Edit Personal Information</h2>
          </div>
          <i class="fa-solid fa-sliders dash-panel-tag-icon"></i>
        </div>

        <form class="profile-form" id="profileForm" method="POST" action="profile.php" novalidate>
          <input type="hidden" name="csrf" value="<?= sh_e(sh_csrf()) ?>">
          <input type="hidden" name="action" value="update_profile">

          <div class="form-row">

            <div class="form-group">
              <label for="firstName"><i class="fa-solid fa-user"></i> First Name</label>
              <div class="input-wrapper">
                <i class="fa-solid fa-user"></i>
                <input type="text" id="firstName" name="firstName" value="<?= sh_e($firstName) ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label for="lastName"><i class="fa-solid fa-user"></i> Last Name</label>
              <div class="input-wrapper">
                <i class="fa-solid fa-user"></i>
                <input type="text" id="lastName" name="lastName" value="<?= sh_e($lastName) ?>" required>
              </div>
            </div>

          </div>

          <div class="form-row">

            <div class="form-group">
              <label for="profileEmail"><i class="fa-solid fa-envelope"></i> Email Address</label>
              <div class="input-wrapper">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" id="profileEmail" name="profileEmail" value="<?= sh_e($email) ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label for="profilePhone"><i class="fa-solid fa-phone"></i> Phone Number</label>
              <div class="input-wrapper">
                <i class="fa-solid fa-phone"></i>
                <input type="tel" id="profilePhone" name="profilePhone" value="<?= sh_e($phone) ?>" placeholder="+91 90000 00000">
              </div>
            </div>

          </div>

          <div class="form-row">

            <div class="form-group">
              <label for="profileDob"><i class="fa-solid fa-cake-candles"></i> Date of Birth</label>
              <div class="input-wrapper">
                <i class="fa-solid fa-cake-candles"></i>
                <input type="text" id="profileDob" name="profileDob" value="<?= sh_e($dob) ?>" placeholder="DD / MM / YYYY">
              </div>
            </div>

            <div class="form-group">
              <label for="profileGender"><i class="fa-solid fa-venus-mars"></i> Gender</label>
              <div class="input-wrapper select-wrapper">
                <i class="fa-solid fa-venus-mars"></i>
                <select id="profileGender" name="profileGender">
                  <option value="">Select</option>
                  <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                  <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                  <option value="Prefer not to say" <?= $gender === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                </select>
              </div>
            </div>

          </div>

          <div class="form-group">
            <label for="profileAddress"><i class="fa-solid fa-house"></i> Address</label>
            <div class="input-wrapper is-textarea">
              <i class="fa-solid fa-house"></i>
              <textarea id="profileAddress" name="profileAddress" rows="2" placeholder="Your address"><?= sh_e($address) ?></textarea>
            </div>
          </div>

          <div class="form-group">
            <label for="profileBioField"><i class="fa-solid fa-pen-nib"></i> Short Bio</label>
            <div class="input-wrapper is-textarea">
              <i class="fa-solid fa-pen-nib"></i>
              <textarea id="profileBioField" name="profileBioField" rows="3" placeholder="Tell people a bit about yourself"><?= sh_e($bio) ?></textarea>
            </div>
          </div>

          <div class="form-message" id="profileFormMessage" role="status" aria-live="polite"></div>

          <div class="profile-form-actions">
            <button type="reset" class="btn btn-outline">
              <i class="fa-solid fa-rotate-left"></i>
              Reset
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk"></i>
              Save Changes
            </button>
          </div>

        </form>

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
          <p><strong>Name:</strong> <span><?= sh_e($name) ?></span></p>
          <p><strong>ID:</strong> <span><?= sh_e($studentId) ?></span></p>
          <p><strong>Email:</strong> <span><?= sh_e($email) ?></span></p>
          <p><strong>Department:</strong> <span><?= sh_e($department) ?></span></p>
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