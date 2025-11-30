<?php
require_once __DIR__ . '/../model/services/ItemService.php';
$service = new ItemService();
$item = $service->getItemById($_GET['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update item</title>
<link rel="stylesheet" href="../../styles.css">
</head>
<style>
    .border {
        max-width: 400px;
    }
</style>
<body>

    <?php
    // Include the header
    include __DIR__ . '/layout/header.php';
    ?>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="container">
            <div class="error">You must be logged in to edit an item.</div>
            <br>
            <div class="actions">
                <a class="ghost-btn" href="list.php">⬅️ Back</a>
                <a class="primary-btn ghost-btn" href="login.php">🔐 Login</a>
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
        <h1>Edit item</h1>
        <form class="border item-form" action="../controller/ItemController.php" method="POST">
            <input type="hidden" name="id" value="<?= $item->getId() ?>">
            <label>Title:</label><br>
            <input class="input-field" type="text" name="title" value="<?= htmlspecialchars($item->getTitle()) ?>" required><br><br>
            <label for="tag">Tag:</label><br>
            <input class="input-field" type="text" id="tag" name="tag" value="<?= htmlspecialchars($item->getTag()) ?>"><br><br>
            <label>Description:</label><br>
            <textarea class="input-field" name="description" rows="5" cols="40" required><?= htmlspecialchars($item->getDescription()) ?></textarea><br><br>
            <label>Link:</label><br>
            <input class="input-field" type="url" name="link" value="<?= htmlspecialchars($item->getLink()) ?>" required><br><br>
            <div class="actions">
                <button type="submit" name="update">Update</button>
            </div>
        </form>
        <br>
        <div class="actions">
            <a class="ghost-btn" href="form_view.php?id=<?= $item->getId() ?>">⬅️ Back</a>
            <a class="ghost-btn" href="list.php">🏠 Home</a>
        </div>
    </div>

    <?php
        // Include the footer
        include __DIR__ . '/layout/footer.php';
    ?>

</body>
</html>
