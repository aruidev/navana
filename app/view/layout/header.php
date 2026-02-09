<?php
require_once __DIR__ . '/../../helpers/base_path.php';
require_once __DIR__ . '/../../model/session.php';
startSession();

$currentPage = basename($_SERVER['PHP_SELF']);

$tabs = [
    'home.php' => 'Home',
    'explore.php' => 'Explore',
    'library.php' => 'Library',
    'saved.php' => 'Saved'
];

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
                <a href="home.php" style="border-radius: 9999px;"><img src="<?= $basePath ?>navana.svg" alt="Navana logo" width="100"></a>
                <div class="nav-items">
                    <ul>
                        <?php foreach ($tabs as $url => $label): ?>
                            <li>
                                <a href="<?= $url ?>" class="<?= $currentPage === $url ? 'active-tab' : '' ?> nav-item ghost-btn" rel="noopener noreferrer">
                                    <?= $label ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><a class="nav-item ghost-btn" href="../../docs/index.html" rel="noopener noreferrer" target="_blank">Docs</a></li>
                    </ul>
                    <ul>
                        <?php if (!isset($_SESSION['username'])): ?>
                            <li><a class="nav-item auth-btn secondary-btn ghost-btn" href="login.php">Login</a></li>
                            <li><a class="nav-item auth-btn primary-btn ghost-btn" href="register.php">Register</a></li>
                        <?php else: ?>
                            <li><a class="nav-item auth-btn avatar border ghost-btn" href="account-settings.php">👤 <?= htmlspecialchars($_SESSION['username']) ?></a></li>
                            <li><a class="nav-item auth-btn danger secondary-btn ghost-btn" onclick="return confirm('Are you sure you want to logout?')" href="../controller/UserController.php?logout=1">Logout</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <?php include __DIR__ . '/../components/toast.php'; ?>