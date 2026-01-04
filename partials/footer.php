  <footer>
    <div class="container">
      <div class="col-lg-12">
        <p id="footer-copyright">Copyright © <span data-current-year></span> Timelord casino review. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Sign In Modal -->
  <div class="modal fade brand-modal" id="signInModal" tabindex="-1" aria-labelledby="signInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="signInModalLabel">Welcome Back</h5>
            <p class="mb-0 small">Sign in to access exclusive casino insights.</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="signInForm">
            <div class="mb-3">
              <label for="signInEmail" class="form-label">Email address</label>
              <input type="email" class="form-control" id="signInEmail" placeholder="name@example.com" required>
            </div>
            <div class="mb-4">
              <label for="signInPassword" class="form-label">Password</label>
              <input type="password" class="form-control" id="signInPassword" placeholder="Enter your password" required>
            </div>
            <div class="d-flex flex-column flex-sm-row gap-3">
              <button type="submit" class="btn btn-brand flex-fill">Sign In</button>
              <button type="button" class="btn btn-accent flex-fill" data-bs-target="#contactAdminModal" data-bs-toggle="modal" data-bs-dismiss="modal">Sign Up</button>
            </div>
            <p class="mt-3 mb-0 small text-muted" aria-live="polite" data-signin-status>Sign in to sync your casino shortlists.</p>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Contact Admin Modal -->
  <div class="modal fade brand-modal" id="contactAdminModal" tabindex="-1" aria-labelledby="contactAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="contactAdminModalLabel">Need an Account?</h5>
            <p class="mb-0 small">We can help you get started.</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-3">Please contact our admin team to get access to the platform.</p>
          <a href="mailto:admin@timelordcasino.com" class="btn btn-accent w-100">Contact Admin</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Subscribe Success Modal -->
  <div class="modal fade brand-modal" id="subscribeSuccessModal" tabindex="-1" aria-labelledby="subscribeSuccessLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="subscribeSuccessLabel">Subscription Confirmed</h5>
            <p class="mb-0 small">You're on the list!</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <div class="subscribe-success-icon">
            <i class="fa fa-check-circle"></i>
          </div>
          <p class="subscribe-success-text">Thank you for subscribing. Expect the latest casino bonuses in your inbox.</p>
        </div>
        <div class="modal-footer d-flex justify-content-center">
          <button type="button" class="btn btn-brand" data-bs-dismiss="modal">Great!</button>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($casinoDirectory ?? [])): ?>
    <script>
      window.__CASINO_DIRECTORY__ = <?= json_encode($casinoDirectory, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
  <?php endif; ?>
  <?php if (isset($database) && $database instanceof PDO): ?>
    <?php $casinoDataPayload = buildCasinoDataPayload($database); ?>
    <?php if (!empty($casinoDataPayload)): ?>
      <script>
        window.__CASINO_DATA__ = <?= json_encode($casinoDataPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      </script>
    <?php endif; ?>
  <?php endif; ?>
  
  <!-- Scripts -->
  <!-- Bootstrap core JavaScript -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/js/isotope.min.js"></script>
  <script src="assets/js/owl-carousel.js"></script>
  <script src="assets/js/counter.js"></script>
  <script src="assets/js/casino-data.js"></script>
  <script src="assets/js/casino-routing.js"></script>
  <script src="assets/js/custom.js"></script>
  <script src="assets/js/platform.js"></script>
  <?php if (!empty($additionalScripts ?? [])) : ?>
    <?php foreach ($additionalScripts as $scriptPath): ?>
      <script src="<?= htmlspecialchars($scriptPath, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>

  </body>
</html>
