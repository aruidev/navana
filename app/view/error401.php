<?php
$title = '401 Not Authorized';
include __DIR__ . '/layout/header.php';
?>
<div class="container">
    <header class="page-header center">
        <h1>401 - Not Authorized</h1>
    </header>
    <div class="page-section">
        <div class="form-wrapper border card-like" style="text-align:center;">
            <h2 style="font-size:2.5rem; margin-bottom:0.5em;">🔒</h2>
            <p style="font-size:1.2rem; color:var(--color-text-muted);">You do not have permission to access this page.</p>
            <div class="form-actions" style="justify-content:center;">
                <a class="primary-btn ghost-btn" href="home.php">Go to Home</a>
                <a class="ghost-btn" href="login.php">Log In</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/layout/footer.php'; ?>
