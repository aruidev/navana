<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../model/session.php';
startSession();

$title = 'Login';
$config = config();
$captchaRequired = isLoginCaptchaRequired();
$recaptchaSiteKey = $config['recaptcha_site_key'] ?? '';
include __DIR__ . '/layout/header.php';
?>
<div class="container">

  <!-- Login Form -->
  <header class="page-header center">
    <h2>Login</h2>
  </header>
  <div class="page-section">
    <form class="form-wrapper border" method="POST" action="../controller/UserController.php?login=1">
      <label for="identifier">Username or email:</label>
      <input type="text" id="identifier" name="identifier" placeholder="name@example.com" required
        value="<?php
                // Retrieve remembered username from cookie
                echo isset($_COOKIE['remembered_user']) ? htmlspecialchars($_COOKIE['remembered_user']) : '';
                ?>">
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" placeholder="Your account password" required>
      <?php if ($captchaRequired && $recaptchaSiteKey !== ''): ?>
        <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey) ?>"></div>
      <?php endif; ?>
      <div class="row space-between">
        <div>
          <input type="checkbox" name="remember_me" id="remember_me">
          <label for="remember_me">Remember me</label>
        </div>
        <div>
          <span><a href="reset.php">Forgot password</a></span>
        </div>
      </div>
      <div class="form-actions">
        <div class="actions actions-left">
          <a href="register.php">Register instead</a>
        </div>
        <div class="actions actions-right">
          <button class="primary-btn" type="submit">Login</button>
        </div>
      </div>
    </form>
  </div>



  <!-- Errors and messages -->
  <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials'): ?>
    <div class="form-messages">
      <div class="error">Invalid username/email or password.</div>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['error']) && $_GET['error'] === 'captcha_required'): ?>
    <div class="form-messages">
      <div class="error">Please complete the CAPTCHA to continue.</div>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['message']) && $_GET['message'] === 'registration_successful'): ?>
    <div class="form-messages">
      <div class="success">Registration successful. You can now log in.</div>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['message']) && $_GET['message'] === 'logged_out'): ?>
    <div class="form-messages">
      <div class="success">You have been logged out successfully.</div>
    </div>
  <?php endif; ?>
  <?php if (isset($_GET['message']) && $_GET['message'] === 'password_reset'): ?>
    <div class="form-messages">
      <div class="success">Your password has been updated. You can now log in.</div>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['errors'])): ?>
    <div class="form-messages">
      <div class="error"><?php foreach ($_SESSION['errors'] as $e) echo '<p>' . htmlspecialchars($e) . '</p>';
                          unset($_SESSION['errors']); ?></div>
    </div>
  <?php endif; ?>

</div>
<?php if ($captchaRequired && $recaptchaSiteKey !== ''): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<?php include __DIR__ . '/layout/footer.php'; ?>