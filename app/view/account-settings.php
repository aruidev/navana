<?php
$title = 'Account Settings';
include __DIR__ . '/layout/header.php'; 
?>

<div class="container">
    <h1>Account Settings</h1>
    <div>
        <label for="username">Username:</label>
        <span id="username" class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>