<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Register - LinkHub</title>
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

<body>
  <?php include __DIR__ . '/layout/header.php'; ?>
  <div class="container">
    <?php if (!empty($_SESSION['errors'])): ?>
      <div class="error"><?php foreach ($_SESSION['errors'] as $e) echo '<p>' . htmlspecialchars($e) . '</p>';
                          unset($_SESSION['errors']); ?></div>
    <?php endif; ?>

    <h2>Register</h2>
    <form class="border" method="POST" action="../controller/AuthController.php">
      <input type="hidden" name="action" value="register">
      <input type="text" name="username" placeholder="Username" required>
      <input type="text" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="password2" placeholder="Repeat password" required>
      <div>
        <input type="checkbox" name="terms" id="terms" required>
        <label for="terms">I agree to the</label>
        <a href="#">terms and conditions</a>
      </div>
      <button class="primary-btn" type="submit">Create account</button>
      <a href="login.php">Login instead</a>
    </form>

  </div>
  <?php include __DIR__ . '/layout/footer.php'; ?>
</body>

</html>