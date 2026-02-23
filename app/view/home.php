<?php
$title = 'Home';
include __DIR__ . '/layout/header.php';
?>

<main class="hero-page">
    <section class="hero-container">
        <figure>
            <img src="<?= htmlspecialchars($basePath . 'navana.svg') ?>" alt="Navana logo" width="350">
        </figure>
        <h2>Save, organize, and explore your favorite sites.</h2>
        <p class="hero-desc">Discover a simple way to manage your bookmarks.</p>
    </section>
    <nav>
        <ul class="cta-row-list">
            <li class="cta-detail">
                <a class="nav-item cta-btn primary-btn ghost-btn" href="explore.php" rel="noopener noreferrer">🧭 Explore</a>
                <small>Browse public collections</small>
            </li>
            <li class="cta-detail">
                <a class="nav-item cta-btn secondary-btn ghost-btn" href="library.php" rel="noopener noreferrer">📕 Library</a>
                <small>Your personal bookmarks</small>
            </li>
            <li class="cta-detail">
                <a class="nav-item cta-btn secondary-btn ghost-btn" href="saved.php" rel="noopener noreferrer">♥️ Saved</a>
                <small>Quick access to favorites</small>
            </li>
        </ul>
    </nav>
</main>

<?php include __DIR__ . '/layout/footer.php'; ?>