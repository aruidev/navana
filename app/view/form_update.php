<?php
require_once __DIR__ . '/../model/services/ItemService.php';
$service = new ItemService();
$item = $service->getItemById($_GET['id']);
$title = 'Edit Item';
// Include the header
include __DIR__ . '/layout/header.php';
?>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="container">
        <header class="page-header center">
            <h2>Login required</h2>
        </header>
        <div class="page-section">
            <div class="form-wrapper border">
                <div class="form-messages"><span class="error">You must be logged in to edit an item.</span></div>
                <div class="form-actions">
                    <div class="actions actions-left">
                        <a class="ghost-btn" href="explore.php">⬅️ Back</a>
                    </div>
                    <div class="actions actions-right">
                        <a class="primary-btn ghost-btn" href="login.php">🔐 Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Include the footer
    include __DIR__ . '/layout/footer.php';
    ?>
    </body>

    </html>
<?php
    exit();
endif;
?>

<div class="container">
    <header class="page-header center">
        <h1>Edit item</h1>
    </header>
    <div class="page-section">
        <form class="form-wrapper border item-form" action="../controller/ItemController.php" method="POST">
            <input type="hidden" name="id" value="<?= $item->getId() ?>">
            <label for="title">Title:</label>
            <input class="input-field" type="text" id="title" name="title" placeholder="New Item" value="<?= htmlspecialchars($item->getTitle()) ?>" required>
            <label for="tag">Tag:</label>
            <input class="input-field" type="text" id="tag" name="tag" placeholder="Tag (optional)" value="<?= htmlspecialchars($item->getTag()) ?>">
            <label for="description">Description:</label>
            <textarea class="input-field" id="description" name="description" placeholder="A brief description..." rows="5" cols="40" required><?= htmlspecialchars($item->getDescription()) ?></textarea>
            <label for="link">Link:</label>
            <input class="input-field" type="url" id="link" name="link" placeholder="https://example.com" value="<?= htmlspecialchars($item->getLink()) ?>" required>
            <div class="form-actions">
                <div class="actions actions-left">
                    <a class="ghost-btn" href="dashboard.php">⬅️ Back</a>
                </div>
                <div class="actions actions-right">
                    <button type="submit" name="update">Update</button>
                </div>
            </div>
        </form>
    </div>

</div>

<?php
// Include the footer
include __DIR__ . '/layout/footer.php';
?>