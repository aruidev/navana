<?php
require_once __DIR__ . '/../model/services/ItemService.php';
$service = new ItemService();
$item = $service->getItemById($_GET['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
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
            <hr><hr>
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
        <form class="border" action="../controller/ItemController.php" method="POST">
            <input type="hidden" name="id" value="<?= $item->getId() ?>">
            <label>Title:</label><br>
            <input type="text" name="title" value="<?= htmlspecialchars($item->getTitle()) ?>" required><br><br>
            <label for="category">Category</label><br>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($item->getCategory()) ?>"><br><br>
            <label>Description:</label><br>
            <textarea name="description" rows="5" cols="40" required><?= htmlspecialchars($item->getDescription()) ?></textarea><br><br>
            <label>Link:</label><br>
            <textarea name="link" rows="5" cols="40" required><?= htmlspecialchars($item->getLink()) ?></textarea><br><br>
            <button type="submit" name="update">Update</button>
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
