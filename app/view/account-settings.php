<?php
require_once __DIR__ . '/../model/services/UserService.php';

$title = 'Account Settings';
include __DIR__ . '/layout/header.php';

$currentUsername = $_SESSION['username'] ?? '';
$pendingUsername = $_SESSION['old_username'] ?? $currentUsername;
$errors = $_SESSION['errors'] ?? [];
$isAdmin = !empty($_SESSION['is_admin']);
$users = [];

if ($isAdmin) {
    $userService = new UserService();
    $users = $userService->getAllUsers();
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    $users = array_filter($users, function ($user) use ($currentUserId) {
        return $user->getId() !== $currentUserId;
    });
}

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

            <header class="page-header">
                <h2><span style="font-weight: 400; ">User: </span><?= htmlspecialchars($currentUsername) ?></h2>
            </header>

            <label for="new_username">New username:</label>
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

    <?php if ($isAdmin): ?>
        <div class="page-section">
            <div class="form-wrapper border">
                <header class="page-header">
                    <h2><span style="font-weight: 400; ">Admin: </span>Manage users</h2>
                </header>

                <?php if (empty($users)): ?>
                    <p>No other users to manage.</p>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <div class="row space-between" style="align-items: center; margin-bottom: 0.75rem;">
                            <div>
                                <p>
                                    <strong><?= htmlspecialchars($user->getUsername()) ?></strong>
                                    <span style="color: #666;">(<?= htmlspecialchars($user->getEmail()) ?>)</span>
                                    <?= $user->isAdmin() ? '<span class="badge">Admin</span>' : '' ?>
                                </p>
                            </div>
                            <form method="POST" action="../controller/UserController.php" onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="delete_user" value="1">
                                <input type="hidden" name="user_id" value="<?= $user->getId() ?>">
                                <button class="danger secondary-btn ghost-btn" type="submit">🗑️ Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$isAdmin): ?>
        <div class="page-section">
            <div class="form-wrapper border">
                <header class="page-header">
                    <h2>Delete account</h2>
                </header>
                <p style="color: red;">Warning: This action is irreversible. All your data will be permanently deleted, and your posts will no longer be linked to your account.</p>
                <form method="POST" action="../controller/UserController.php" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                    <input type="hidden" name="delete_user" value="1">
                    <input type="hidden" name="user_id" value="<?= (int)($_SESSION['user_id'] ?? 0) ?>">
                    <div class="form-actions">
                        <div class="actions actions-left">
                            <a class="ghost-btn" href="dashboard.php">⬅️ Back</a>
                        </div>
                        <div class="actions actions-right">
                            <button class="danger secondary-btn ghost-btn" type="submit">🗑️ Delete my account</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>