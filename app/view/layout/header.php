<?php
require_once __DIR__ . '/../../model/session.php';
startSession();
?>
<style>

    .header {
        background-color: inherit;
        overflow: hidden;
        border-bottom: 2px solid lightgray;
        height: 100px;
        display: flex;
        flex-direction: row;
        align-items: center;
        width: 100%;
    }

    .header .header-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        width: 100%;
        margin: 0 auto;
        padding: 0 20px;
    }

    .header ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }

    .header a {
        text-decoration: none;
        color: black;
    }

    .header a span {
        font-weight: 800;
        font-size: 1.6rem;
    }

    .header .nav-items {
        display: flex;
        flex-direction: row;
        gap: 20px;
    }

    .nav-items ul {
        display: flex;
        flex-direction: row;
        gap: 10px;
    }

    .nav-item {
        font-size: medium;
    }

    @media screen and (max-width: 680px) {
        .header {
            height: auto;
            padding: 10px 0;
        }

        .header-inner {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 1rem;
        }
        .header .nav-items {
            flex-direction: column-reverse;
            margin-top: 0.5rem;
            gap: 1rem;
        }
        .nav-items ul {
            flex-direction: row;
            justify-content: center;
            gap: 0.5rem;
        }
    }

    .logout:hover {
        background-color: #e8c3c3ff !important;
    }

</style>
<header>
    <nav class="header">
        <div class="header-inner">
            <a href="list.php"><span>NAVANA</span></a>
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
                        <li><span class="nav-item avatar border">👤 <?= htmlspecialchars($_SESSION['username']) ?></span></li>
                        <li><a class="nav-item auth-btn secondary-btn logout ghost-btn" onclick="return confirm('Are you sure you want to logout?')" href="../controller/UserController.php?logout=1">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>