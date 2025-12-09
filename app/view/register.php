<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Navana</title>
  <link rel="stylesheet" href="../../styles.css">
</head>
<style>
  /* Removed inline form layout; using global wrappers */
</style>

<body>
  <?php
  include __DIR__ . '/layout/header.php';
  ?>
  <div class="container">

    <!-- Registration Form -->
    <header class="page-header center">
      <h2>Register</h2>
    </header>
    <div class="page-section">
    <form class="form-wrapper border" method="POST" action="../controller/UserController.php?register=1">
      <input type="hidden" name="action" value="register">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" placeholder="Your public name" required
        value="<?php echo isset($_SESSION['old']['username']) ? htmlspecialchars($_SESSION['old']['username']) : ''; ?>">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" placeholder="name@example.com" required
        value="<?php echo isset($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : ''; ?>">
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
      <label for="password2">Repeat password:</label>
      <input type="password" id="password2" name="password2" placeholder="Repeat password" required>
      <div>
        <input type="checkbox" name="terms" id="terms" required>
        <label for="terms">I agree to the <a href="terms.php">terms and conditions</a></label>
      </div>
      <div class="form-actions">
        <div class="actions actions-left">
          <a href="login.php">Login instead</a>
        </div>
        <div class="actions actions-right">
          <button class="primary-btn" type="submit">Create account</button>
        </div>
      </div>
    </form>
    </div>

    <!-- Errors and messages -->
    <?php if (!empty($_SESSION['errors'])): ?>
      <div class="form-messages"><div class="error"><?php foreach ($_SESSION['errors'] as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; unset($_SESSION['errors']); ?></div></div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'registration_failed'): ?>
      <div class="form-messages"><div class="error">Registration failed. Please try again.</div></div>
    <?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'registration_successful'): ?>
      <div class="form-messages"><div class="success">Registration successful. You can now log in.</div></div>
    <?php endif; ?>

  </div>
  <?php include __DIR__ . '/layout/footer.php'; ?>
</body>

</html>