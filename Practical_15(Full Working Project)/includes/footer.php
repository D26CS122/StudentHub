
  <!-- ================= FOOTER ================= -->

<?php if ($authPage ?? false): ?>
  <footer class="auth-footer">
    <div class="container">
      <p>&copy; <span data-year></span> StudentHub &mdash; CHARUSAT. All rights reserved.</p>
    </div>
  </footer>
<?php else: ?>
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">

        <div class="footer-brand">
          <a href="<?= sh_e($basePath ?? '') ?>index.php" class="logo">
            <div class="logo-box">
              <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="logo-text">
              <h2>StudentHub</h2>
              <span>CHARUSAT Student Portal</span>
            </div>
          </a>
          <p>
            The digital campus companion for
            CHARUSAT students to manage academics,
            events, profiles and campus life.
          </p>
        </div>

        <div>
          <h4>Quick Links</h4>
          <ul>
            <li><a href="<?= sh_e($basePath ?? '') ?>about.php">About</a></li>
            <li><a href="<?= sh_e($basePath ?? '') ?>events.php">Events</a></li>
            <li><a href="<?= sh_e($basePath ?? '') ?>dashboard.php">Dashboard</a></li>
            <li><a href="<?= sh_e($basePath ?? '') ?>profile.php">Profile</a></li>
          </ul>
        </div>

        <div>
          <h4>Support</h4>
          <ul>
            <li><a href="<?= sh_e($basePath ?? '') ?>faq.php">FAQ</a></li>
            <li><a href="<?= sh_e($basePath ?? '') ?>feedback.php">Feedback</a></li>
            <li><a href="<?= sh_e($basePath ?? '') ?>contact.php">Contact</a></li>
            <li><a href="<?= sh_e($basePath ?? '') ?>admin_dashboard.php">Admin</a></li>
          </ul>
        </div>

        <div>
          <h4>Student Information</h4>
<?php if (!empty($_SESSION['user_id'])): ?>
          <p><strong>Name:</strong> <?= sh_e($_SESSION['user_name'] ?? '') ?></p>
          <p><strong>Email:</strong> <?= sh_e($_SESSION['user_email'] ?? '') ?></p>
          <p><strong>Role:</strong> <?= sh_e(ucfirst($_SESSION['user_role'] ?? 'student')) ?></p>
<?php else: ?>
          <p><strong>Name:</strong> Tirth Ladani</p>
          <p><strong>ID:</strong> 25IT049</p>
          <p><strong>Email:</strong> 25it049@charusat.edu.in</p>
          <p><strong>Department:</strong> Information Technology</p>
<?php endif; ?>
        </div>

      </div>

      <div class="footer-bottom">
        <p>&copy; <span data-year></span> StudentHub &mdash; CHARUSAT. Designed for academic excellence.</p>
      </div>
    </div>
  </footer>
<?php endif; ?>

  <!-- JS -->
  <script src="<?= sh_e($basePath ?? '') ?>js/script.js"></script>
</body>
</html>
