<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><title>Login - Navana</title>
  <link rel="stylesheet" href="../../styles.css">
</head>
<body>
<style>
  form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 18px 20px;
    max-width: 400px;
  }
  form input[type="text"],
  form input[type="password"] {
    padding: 8px;
    border: 1px solid #bbb;
    border-radius: 3px;
    font-size: 1em;
  }
  form input[type="checkbox"] {
    margin-right: 6px;
  }
  form label {
    font-size: 0.98em;
    color: #333;
    user-select: none;
  }
  form button {
    transition: background 0.3s ease;
    width: 100%;
    text-decoration: none;
    color: inherit;
  }
  form a {
    color: #1976d2;
    text-decoration: none;
  }
  form a:hover {
    text-decoration: underline;
  }
</style>
<?php 
  include __DIR__ . '/layout/header.php';
  session_start();
?>
<div class="container">
  <?php if (!empty($_SESSION['errors'])): ?>
    <div class="error"><?php foreach ($_SESSION['errors'] as $e) echo '<p>'.htmlspecialchars($e).'</p>'; unset($_SESSION['errors']); ?></div>
  <?php endif; ?>

  <h2>Login</h2>
  <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials'): ?>
    <div class="error">Invalid username/email or password.</div>
  <?php endif; ?>
  <?php if (isset($_GET['message']) && $_GET['message'] === 'registration_successful'): ?>
    <div class="success">Registration successful. You can now log in.</div>
  <?php endif; ?>
  <?php if (isset($_GET['message']) && $_GET['message'] === 'logged_out'): ?>
    <div class="success">You have been logged out successfully.</div>
  <?php endif; ?>
  <form class="border" method="POST" action="../controller/UserController.php?login=1">
    <input type="text" name="identifier" placeholder="Username or email" required>
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
    <button class="primary-btn" type="submit">Login</button>
    <a href="register.php">Register instead</a>
  </form>

</div>
<?php include __DIR__ . '/layout/footer.php'; ?>
</body>
</html>