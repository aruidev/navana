<?php
require_once __DIR__ . '/../../helpers/base_path.php';
require_once __DIR__ . '/../../model/session.php';
startSession();

$basePath = getBasePath();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!-- Viewport Meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Page Title -->
    <title><?= htmlspecialchars($title) ?> - Navana</title>
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../../styles.css">
    <!-- Google Fonts Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <nav class="header">
            <div class="header-inner">
                <a href="list.php"><img src="<?= $basePath ?>navana.svg" alt="Navana logo" width="130"></a>
                <div class="nav-items">
                    <ul>
                        <li><a class="nav-item ghost-btn" href="list.php" rel="noopener noreferrer">Home</a></li>
                        <li><a class="nav-item ghost-btn" href="my_items.php" rel="noopener noreferrer">My items</a></li>
                        <li><a class="nav-item ghost-btn" href="../../docs/index.html" rel="noopener noreferrer" target="_blank">Docs</a></li>
                    </ul>
                    <ul>
                        <?php if (!isset($_SESSION['username'])): ?>
                            <li><a class="nav-item auth-btn secondary-btn ghost-btn" href="login.php">Login</a></li>
                            <li><a class="nav-item auth-btn primary-btn ghost-btn" href="register.php">Register</a></li>
                        <?php else: ?>
                            <li><a class="nav-item avatar border" href="account-settings.php">👤 <?= htmlspecialchars($_SESSION['username']) ?></a></li>
                            <li><a class="nav-item auth-btn logout secondary-btn ghost-btn" onclick="return confirm('Are you sure you want to logout?')" href="../controller/UserController.php?logout=1">Logout</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <?php include __DIR__ . '/../components/toast.php'; ?>