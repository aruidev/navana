<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add item</title>
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
            <div class="error">You must be logged in to add an item.</div>
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
        <h1>Add item</h1>
        <form class="border" action="../controller/ItemController.php" method="POST">
            <?php if (isset($_SESSION['user_id'])): ?>
                <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
            <?php endif; ?>

            <label>Title:</label><br>
            <input class="input-field" type="text" name="title" required><br><br>
                <label for="category">Category:</label><br>
                <input class="input-field" type="text" id="category" name="category"><br><br>
            <label>Description:</label><br>
            <textarea class="input-field" name="description" rows="5" cols="40" required></textarea><br><br>
            <label>Link:</label><br>
            <input class="input-field" type="url" name="link" required><br><br>
            <button type="submit" name="insert">Save</button>
        </form>
        <br>
        <a class="ghost-btn" href="list.php">⬅️ Back</a>
    </div>

    <?php
        // Include the footer
        include __DIR__ . '/layout/footer.php';
    ?>

</body>
</html>
