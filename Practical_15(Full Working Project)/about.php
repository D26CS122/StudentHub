<?php
/**
 * StudentHub - about.php
 * About page: same theme as about.html, with session-aware header/footer.
 */
declare(strict_types=1);
$pageTitle  = 'About StudentHub | CHARUSAT Student Portal';
$bodyClass  = 'theme-teal about-page';
$activePage = 'about';
require __DIR__ . '/includes/header.php';
?>

  <main class="about-shell">


    <!-- ---------- HERO ---------- -->

    <section class="about-hero">

      <div class="container">

        <div class="about-hero-inner">

          <span class="section-badge">
            <i class="fa-solid fa-people-group"></i>
            About StudentHub
          </span>

          <h1>One portal for your <span>entire campus life</span></h1>

          <p>
            StudentHub brings attendance, results, events, profiles and campus
            announcements together behind a single login — designed by CHARUSAT
            students, for CHARUSAT students. Fast, focused and built to make
            everyday academic life effortless.
          </p>

          <div class="about-hero-actions">
            <a href="register.php" class="btn btn-primary btn-large">
              <i class="fa-solid fa-user-plus"></i>
              Join StudentHub
            </a>
            <a href="contact.php" class="btn btn-outline btn-large">
              <i class="fa-solid fa-envelope"></i>
              Get in Touch
            </a>
          </div>

        </div>

        <div class="about-hero-stats">
          <div class="about-hero-stat">
            <strong>1200<small>+</small></strong>
            <span>Students on board</span>
          </div>
          <div class="about-hero-stat">
            <strong>85<small>+</small></strong>
            <span>Faculty members</span>
          </div>
          <div class="about-hero-stat">
            <strong>50<small>+</small></strong>
            <span>Events hosted</span>
          </div>
          <div class="about-hero-stat">
            <strong>24<small>/7</small></strong>
            <span>Portal access</span>
          </div>
        </div>

      </div>

    </section>


    <!-- ---------- MISSION + VISION ---------- -->

    <section class="section about-purpose">

      <div class="container">

        <div class="section-header">
          <span class="section-badge">
            <i class="fa-solid fa-compass"></i>
            Why we built it
          </span>
          <h2>One portal, because four was three too many</h2>
          <p>
            Attendance in one place, results in another, event sign-ups on a
            notice board and everything else in a group chat. StudentHub replaced
            all of it with a single, modern portal.
          </p>
        </div>

        <div class="about-pillars">

          <article class="dash-panel about-pillar">
            <span class="about-pillar-icon teal">
              <i class="fa-solid fa-bullseye"></i>
            </span>
            <h3>Our mission</h3>
            <p>
              Cut the time a student spends chasing information down to nothing.
              Every record, form, notice and event lives behind one login — clear,
              current and always a click away, on any device.
            </p>
          </article>

          <article class="dash-panel about-pillar">
            <span class="about-pillar-icon violet">
              <i class="fa-solid fa-eye"></i>
            </span>
            <h3>Our vision</h3>
            <p>
              A campus where nobody misses a deadline for reading the wrong board.
              Where academics, events and student services feel as simple and
              polished as the apps students already love to use.
            </p>
          </article>

        </div>

      </div>

    </section>


    <!-- ---------- WHAT IT DOES ---------- -->

    <section class="section about-features">

      <div class="container">

        <div class="section-header">
          <span class="section-badge">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            What StudentHub does
          </span>
          <h2>Everything, in one clean place</h2>
        </div>

        <div class="features-grid">

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
              <h3>One login, every service</h3>
              <p>
                Attendance, CGPA, assignments, fees, events and announcements —
                all published to a single dashboard that's always up to date.
              </p>
            </div>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-bell"></i>
            </div>
            <div>
              <h3>Attendance that warns you early</h3>
              <p>
                Subject-wise percentages refresh the same day and flag you before
                shortfall becomes a problem — not after the list goes up.
              </p>
            </div>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-folder-open"></i>
            </div>
            <div>
              <h3>Records that stay with you</h3>
              <p>
                Marksheets, receipts, certificates and event history remain
                downloadable long after the semester ends.
              </p>
            </div>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <i class="fa-solid fa-palette"></i>
            </div>
            <div>
              <h3>Built the way you like it</h3>
              <p>
                Switch between the Campus Teal and Midnight Blue themes anytime —
                your choice is remembered on every page you open.
              </p>
            </div>
          </div>

        </div>

      </div>

    </section>


    <!-- ---------- AT A GLANCE ---------- -->

    <section class="section about-glance">

      <div class="container">

        <div class="dash-panel about-glance-panel">

          <div class="section-header">
            <span class="section-badge">
              <i class="fa-solid fa-chart-simple"></i>
              At a glance
            </span>
            <h2>StudentHub in numbers</h2>
          </div>

          <div class="about-glance-grid">
            <div class="about-glance-item">
              <strong>1200+</strong>
              <span>Registered students</span>
            </div>
            <div class="about-glance-item">
              <strong>85+</strong>
              <span>Faculty members</span>
            </div>
            <div class="about-glance-item">
              <strong>320+</strong>
              <span>Projects uploaded</span>
            </div>
            <div class="about-glance-item">
              <strong>150+</strong>
              <span>Placement offers</span>
            </div>
            <div class="about-glance-item">
              <strong>38</strong>
              <span>Departments &amp; centres</span>
            </div>
            <div class="about-glance-item">
              <strong>NAAC A+</strong>
              <span>Accreditation</span>
            </div>
            <div class="about-glance-item">
              <strong>Changa</strong>
              <span>Anand, Gujarat</span>
            </div>
            <div class="about-glance-item">
              <strong>94%</strong>
              <span>Placement success</span>
            </div>
          </div>

        </div>

      </div>

    </section>


    <!-- ---------- TEAM ---------- -->

    <section class="section about-team">

      <div class="container">

        <div class="section-header">
          <span class="section-badge">
            <i class="fa-solid fa-user-gear"></i>
            Who runs it
          </span>
          <h2>The people behind the portal</h2>
          <p>A small team keeps StudentHub running — and answers when you raise a ticket.</p>
        </div>

        <div class="about-team-grid">

          <article class="dash-panel about-member">
            <span class="about-member-avatar grad-a">TU</span>
            <h3>Dr.Trushit Upadhyaya</h3>
            <span class="about-member-role">Principal</span>
            <p>Chairs the academic council and champions a paper-light, student-first campus.</p>
          </article>

          <article class="dash-panel about-member">
            <span class="about-member-avatar grad-c">PP</span>
            <h3>Prof.Purvi Prajapati</h3>
            <span class="about-member-role">Head, Information Technology</span>
            <p>Owns the product roadmap and reviews every release before it ships.</p>
          </article>

          <article class="dash-panel about-member">
            <span class="about-member-avatar grad-d">CS</span>
            <h4>Prof.Dhaval Patel</h4>
            <h4>Prof.Rajnik Katariya</h4>
            <h4>Prof.Mikin Patel</h4>
            <span class="about-member-role">Faculties</span>
            <p>Counsellors of IT department</p>
          </article>

        </div>

      </div>

    </section>


    <!-- ---------- TIMELINE ---------- -->

    <section class="section about-journey">

      <div class="container">

        <div class="section-header">
          <span class="section-badge">
            <i class="fa-solid fa-timeline"></i>
            Our journey
          </span>
          <h2>How StudentHub came together</h2>
        </div>

        <ol class="about-timeline">

          <li class="about-timeline-item">
            <span class="about-timeline-dot"><i class="fa-solid fa-pen-ruler"></i></span>
            <div class="about-timeline-body">
              <time>2024 · Q1</time>
              <h3>The idea takes shape</h3>
              <p>
                Frustrated by scattered tools, a group of IT students sketched a
                single portal to hold attendance, results, events and more.
              </p>
            </div>
          </li>

          <li class="about-timeline-item">
            <span class="about-timeline-dot"><i class="fa-solid fa-rocket"></i></span>
            <div class="about-timeline-body">
              <time>2024 · Q3</time>
              <h3>StudentHub goes live</h3>
              <p>
                The first release brought the dashboard, profile and event
                registration online — retiring the old paper and spreadsheet flow.
              </p>
            </div>
          </li>

          <li class="about-timeline-item">
            <span class="about-timeline-dot"><i class="fa-solid fa-palette"></i></span>
            <div class="about-timeline-body">
              <time>2025 · Q2</time>
              <h3>A premium redesign</h3>
              <p>
                Glassmorphism, smooth animations and the dual Campus Teal /
                Midnight Blue themes gave the portal its signature look.
              </p>
            </div>
          </li>

          <li class="about-timeline-item">
            <span class="about-timeline-dot"><i class="fa-solid fa-star"></i></span>
            <div class="about-timeline-body">
              <time>2026 · Today</time>
              <h3>Trusted campus-wide</h3>
              <p>
                Now used by 1200+ students every day for academics, events and
                everything in between — and still improving with your feedback.
              </p>
            </div>
          </li>

        </ol>

      </div>

    </section>


    <!-- ---------- CTA ---------- -->

    <section class="section about-cta-section">

      <div class="container">

        <div class="dash-panel about-cta">
          <div class="about-cta-icon"><i class="fa-solid fa-graduation-cap"></i></div>
          <h2>Ready to make campus life easier?</h2>
          <p>Create your StudentHub account and bring your whole semester into one place.</p>
          <div class="about-cta-actions">
            <a href="register.php" class="btn btn-primary btn-large">
              <i class="fa-solid fa-user-plus"></i>
              Create Account
            </a>
            <a href="faq.php" class="btn btn-outline btn-large">
              <i class="fa-solid fa-circle-question"></i>
              Read the FAQ
            </a>
          </div>
        </div>

      </div>

    </section>

  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
