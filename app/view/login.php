<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Navana</title>
  <link rel="stylesheet" href="../../styles.css">
</head>
<body>
<style>
  /* Removed inline form layout; using global wrappers */
</style>
<?php 
  include __DIR__ . '/layout/header.php';
?>
<div class="container">

  <!-- Login Form -->
  <header class="page-header center">
    <h2>Login</h2>
  </header>
  <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials'): ?>
    <div class="error">Invalid username/email or password.</div>
  <?php endif; ?>
  <?php if (isset($_GET['message']) && $_GET['message'] === 'registration_successful'): ?>
    <div class="success">Registration successful. You can now log in.</div>
  <?php endif; ?>
  <?php if (isset($_GET['message']) && $_GET['message'] === 'logged_out'): ?>
    <div class="success">You have been logged out successfully.</div>
  <?php endif; ?>
  <div class="page-section">
  <form class="form-wrapper border" method="POST" action="../controller/UserController.php?login=1">
    <input type="text" name="identifier" placeholder="Username or email" required
      value="<?php
        // Retrieve remembered username from cookie
        echo isset($_COOKIE['remembered_user']) ? htmlspecialchars($_COOKIE['remembered_user']) : ''; 
      ?>">
    <input type="password" name="password" placeholder="Password" required>
    <div class="row">
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
  <?php if (!empty($_SESSION['errors'])): ?>
    <div class="error"><?php foreach ($_SESSION['errors'] as $e) echo '<p>'.htmlspecialchars($e).'</p>'; unset($_SESSION['errors']); ?></div>
  <?php endif; ?>

</div>
<?php include __DIR__ . '/layout/footer.php'; ?>
</body>
</html>