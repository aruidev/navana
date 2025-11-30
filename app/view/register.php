<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Navana</title>
  <link rel="stylesheet" href="../../styles.css">
</head>
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
    border: 1px solid #bbb;
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

<body>
  <?php
  include __DIR__ . '/layout/header.php';
  ?>
  <div class="container">

    <!-- Registration Form -->
    <h2>Register</h2>
    <form class="border" method="POST" action="../controller/UserController.php?register=1">
      <input type="hidden" name="action" value="register">
      <input type="text" name="username" placeholder="Username" required
        value="<?php echo isset($_SESSION['old']['username']) ? htmlspecialchars($_SESSION['old']['username']) : ''; ?>">
      <input type="text" name="email" placeholder="Email" required
        value="<?php echo isset($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : ''; ?>">
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="password2" placeholder="Repeat password" required>
      <div>
        <input type="checkbox" name="terms" id="terms" required>
        <label for="terms">I agree to the</label>
        <a href="terms.php">terms and conditions</a>
      </div>
      <button class="primary-btn" type="submit">Create account</button>
      <a href="login.php">Login instead</a>
    </form>

    <!-- Errors and messages -->
    <?php if (!empty($_SESSION['errors'])): ?>
      <div class="error"><?php foreach ($_SESSION['errors'] as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; unset($_SESSION['errors']); ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'registration_failed'): ?>
      <div class="error">Registration failed. Please try again.</div>
    <?php endif; ?>
    <?php if (isset($_GET['message']) && $_GET['message'] === 'registration_successful'): ?>
      <div class="success">Registration successful. You can now log in.</div>
    <?php endif; ?>

  </div>
  <?php include __DIR__ . '/layout/footer.php'; ?>
</body>

</html>