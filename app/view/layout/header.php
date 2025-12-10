<?php
require_once __DIR__ . '/../../helpers/base_path.php';
require_once __DIR__ . '/../../model/session.php';
startSession();

$basePath = getBasePath();
?>
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