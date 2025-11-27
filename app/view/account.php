<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account - Navana</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles.css">
</head>
<style>
.username {
    font-weight: bold;
    font-size: 1.2rem;
}
</style>
<body>
    <?php include __DIR__ . '/layout/header.php'; ?>

    <div class="container">
        <h1>My account</h1>
        <div>
            <label for="username">Username:</label>
            <span id="username" class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>
    </div>

    <?php include __DIR__ . '/layout/footer.php'; ?>
</body>
</html>