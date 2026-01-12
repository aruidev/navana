<?php
$title = 'Terms and Conditions';
include __DIR__ . '/layout/header.php';
?>

<div class="hero-page">
    <div class="hero-container">
        <figure>
            <a href="home.php"><img src="<?= $basePath ?>navana.svg" alt="Navana logo" width="350"></a>
        </figure>
        <div>
            <p class="hero-desc">Save, organize, and explore your favorite sites.</p>
        </div>
    </div>
    <ul class="cta-row-list">
        <li><a class="nav-item cta-btn secondary-btn ghost-btn" href="explore.php" rel="noopener noreferrer">Explore</a></li>
        <li><a class="nav-item cta-btn primary-btn ghost-btn" href="dashboard.php" rel="noopener noreferrer">Dashboard</a></li>
    </ul>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>