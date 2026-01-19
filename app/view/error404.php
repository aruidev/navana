<?php
$title = '404 Not Found';
include __DIR__ . '/layout/header.php';
?>
<div class="container">
    <header class="page-header center">
        <h1>404 - Page Not Found</h1>
    </header>
    <div class="page-section">
        <div class="form-wrapper border card-like" style="text-align:center;">
            <h2 style="font-size:2.5rem; margin-bottom:0.5em;">😕</h2>
            <p style="font-size:1.2rem; color:var(--color-text-muted);">The page you are looking for does not exist or has been moved.</p>
            <div class="form-actions" style="justify-content:center;">
                <a class="primary-btn ghost-btn" href="/index.php">Go to Home</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/layout/footer.php'; ?>
