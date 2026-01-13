<?php
$title = 'Account Settings';
include __DIR__ . '/layout/header.php';

$currentUsername = $_SESSION['username'] ?? '';
$pendingUsername = $_SESSION['old_username'] ?? $currentUsername;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_username']);
?>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="container">
        <header class="page-header center">
            <h2>Login required</h2>
        </header>
        <div class="page-section">
            <div class="form-wrapper border">
                <div class="form-messages">
                    <div class="error">You must be logged in to access this page.</div>
                </div>
                <div class="form-actions">
                    <div class="actions actions-left">
                        <a class="ghost-btn" href="home.php">⬅️ Back</a>
                    </div>
                    <div class="actions actions-right">
                        <a class="primary-btn ghost-btn" href="login.php">🔐 Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/layout/footer.php'; ?>
    </body>

    </html>
    <?php exit(); ?>
<?php endif; ?>

<div class="container">
    <header class="page-header center">
        <h1>Account Settings</h1>
    </header>

    <div class="page-section">
        <form class="form-wrapper border" method="POST" action="../controller/UserController.php">
            <input type="hidden" name="change_username" value="1">

            <div class="row space-between">
                <div>
                    <p><strong>Current username:</strong> <?= htmlspecialchars($currentUsername) ?></p>
                </div>
            </div>

            <label for="new_username">New username</label>
            <input type="text" id="new_username" name="new_username" required
                value="<?= htmlspecialchars($pendingUsername) ?>">

            <div class="form-actions">
                <div class="actions actions-left">
                    <a class="ghost-btn" href="dashboard.php">⬅️ Back</a>
                </div>
                <div class="actions actions-right">
                    <button class="primary-btn" type="submit">Update username</button>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="form-messages">
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>