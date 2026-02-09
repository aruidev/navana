<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../model/session.php';
startSession();

$title = 'Reset password';
include __DIR__ . '/layout/header.php';
?>
<div class="container">

    <header class="page-header center">
        <h2>Reset password</h2>
    </header>

    <div class="page-section">
        <form class="form-wrapper border" method="POST" action="../controller/UserController.php?forgot=1">
            <p>Enter your email to receive a reset link.</p>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="name@example.com" required
                value="<?php echo isset($_SESSION['reset_old_email']) ? htmlspecialchars($_SESSION['reset_old_email']) : ''; ?>">
            <div class="form-actions">
                <div class="actions actions-left">
                    <a href="login.php">Login instead</a>
                </div>
                <div class="actions actions-right">
                    <button class="primary-btn" type="submit">Send reset link</button>
                </div>
            </div>
        </form>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_token'): ?>
        <div class="form-messages">
            <div class="error">The reset link is invalid or has expired. Please request a new one.</div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'reset_sent'): ?>
        <div class="form-messages">
            <div class="success">If the email exists, a reset link has been sent.</div>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['reset_errors'])): ?>
        <div class="form-messages">
            <div class="error"><?php foreach ($_SESSION['reset_errors'] as $e) echo '<p>' . htmlspecialchars($e) . '</p>';
                                unset($_SESSION['reset_errors'], $_SESSION['reset_old_email']); ?></div>
        </div>
    <?php endif; ?>

</div>
<?php include __DIR__ . '/layout/footer.php'; ?>