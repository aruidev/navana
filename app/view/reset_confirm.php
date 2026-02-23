<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../model/session.php';
startSession();

$selector = $_GET['selector'] ?? '';
$validator = $_GET['validator'] ?? '';

$title = 'Set new password';
include __DIR__ . '/layout/header.php';
?>
<div class="container">

  <header class="page-header center">
    <h2>Set new password</h2>
  </header>

  <?php if ($selector === '' || $validator === ''): ?>
    <div class="form-messages">
      <div class="error">Invalid reset link. Please request a new one.</div>
    </div>
    <div class="page-section">
      <a href="reset.php" class="primary-btn">Request new link</a>
    </div>
  <?php else: ?>
    <div class="page-section">
      <form class="form-wrapper border" method="POST" action="../controller/UserController.php?reset=1">
        <input type="hidden" name="selector" value="<?php echo htmlspecialchars($selector); ?>">
        <input type="hidden" name="validator" value="<?php echo htmlspecialchars($validator); ?>">
        <label for="new_password">New password:</label>
        <input type="password" id="new_password" name="new_password" placeholder="At least 6 characters" required>
        <label for="confirm_password">Repeat password:</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
        <div class="form-actions">
          <div class="actions actions-left">
            <a href="login.php">Login instead</a>
          </div>
          <div class="actions actions-right">
            <button class="primary-btn" type="submit">Update password</button>
          </div>
        </div>
      </form>
    </div>

    <?php if (!empty($_SESSION['reset_errors'])): ?>
      <div class="form-messages">
        <div class="error"><?php foreach ($_SESSION['reset_errors'] as $e) echo '<p>' . htmlspecialchars($e) . '</p>';
                            unset($_SESSION['reset_errors']); ?></div>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>
<?php include __DIR__ . '/layout/footer.php'; ?>
