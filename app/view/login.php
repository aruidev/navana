<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../model/session.php';
startSession();

$title = 'Login';
$config = config();
$captchaRequired = isLoginCaptchaRequired();
$recaptchaSiteKey = $config['recaptcha_site_key'] ?? '';
if (isset($_GET['reason']) && $_GET['reason'] === 'save') {
    $_SESSION['flash'] = ['type' => 'info', 'text' => 'Log in to save items'];
}
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
      <div class="form-actions">
        <div style="width: 100%;">
          <a class="secondary-btn ghost-btn" style="width: 100%; text-align: center; display: inline-flex; gap: 6px;" href="../controller/auth/google.php?start=1&amp;mode=login">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 40 40" fill="none">
              <g width="19" height="20">
                <rect x="0.5" y="0.5" width="39" height="39" rx="19.5" fill="white" />
                <g clip-path="url(#clip0_710_6217)">
                  <path d="M29.6 20.2273C29.6 19.5182 29.5364 18.8364 29.4182 18.1818H20V22.05H25.3818C25.15 23.3 24.4455 24.3591 23.3864 25.0682V27.5773H26.6182C28.5091 25.8364 29.6 23.2727 29.6 20.2273Z" fill="#4285F4" />
                  <path d="M20 30C22.7 30 24.9636 29.1045 26.6181 27.5773L23.3863 25.0682C22.4909 25.6682 21.3454 26.0227 20 26.0227C17.3954 26.0227 15.1909 24.2636 14.4045 21.9H11.0636V24.4909C12.7091 27.7591 16.0909 30 20 30Z" fill="#34A853" />
                  <path d="M14.4045 21.9C14.2045 21.3 14.0909 20.6591 14.0909 20C14.0909 19.3409 14.2045 18.7 14.4045 18.1V15.5091H11.0636C10.3864 16.8591 10 18.3864 10 20C10 21.6136 10.3864 23.1409 11.0636 24.4909L14.4045 21.9Z" fill="#FBBC04" />
                  <path d="M20 13.9773C21.4681 13.9773 22.7863 14.4818 23.8227 15.4727L26.6909 12.6045C24.9591 10.9909 22.6954 10 20 10C16.0909 10 12.7091 12.2409 11.0636 15.5091L14.4045 18.1C15.1909 15.7364 17.3954 13.9773 20 13.9773Z" fill="#E94235" />
                </g>
                <rect x="0.5" y="0.5" width="39" height="39" rx="19.5" stroke="#747775" />
                <defs>
                  <clipPath id="clip0_710_6217">
                    <rect width="20" height="20" fill="white" transform="translate(10 10)" />
                  </clipPath>
                </defs>
              </g>
            </svg>

            Continue with Google</a>
        </div>
      </div>
      <div class="form-actions">
        <div style="width: 100%;">
          <a class="secondary-btn ghost-btn" style="width: 100%; text-align: center; display: inline-flex; gap: 6px;" href="../controller/auth/github.php?start=1&amp;mode=login">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-brand-github"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3.5c0 -1 .1 -1.4 -.5 -2c2.8 -.3 5.5 -1.4 5.5 -6a4.6 4.6 0 0 0 -1.3 -3.2a4.2 4.2 0 0 0 -.1 -3.2s-1.1 -.3 -3.5 1.3a12.3 12.3 0 0 0 -6.2 0c-2.4 -1.6 -3.5 -1.3 -3.5 -1.3a4.2 4.2 0 0 0 -.1 3.2a4.6 4.6 0 0 0 -1.3 3.2c0 4.6 2.7 5.7 5.5 6c-.6 .6 -.6 1.2 -.5 2v3.5" /></svg>  
          
          Continue with GitHub</a>
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
      <div class="error"><?php foreach ($_SESSION['errors'] as $e) {
          echo '<p>' . htmlspecialchars($e) . '</p>';
      }
      unset($_SESSION['errors']); ?></div>
    </div>
  <?php endif; ?>

</div>
<?php if ($captchaRequired && $recaptchaSiteKey !== ''): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<?php include __DIR__ . '/layout/footer.php'; ?>