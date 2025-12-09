<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add item</title>
<link rel="stylesheet" href="../../styles.css">
</head>
<style>
    /* Removed per-view max-width; using global .form-wrapper */
</style>
<body>

    <?php
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
                    <div class="form-messages"><div class="error">You must be logged in to add an item.</div></div>
                    <div class="form-actions">
                        <div class="actions actions-left">
                            <a class="ghost-btn" href="list.php">⬅️ Back</a>
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
            <h1>Add item</h1>
        </header>
        <div class="page-section">
        <form class="form-wrapper border item-form" action="../controller/ItemController.php" method="POST">
            <?php if (isset($_SESSION['user_id'])): ?>
                <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
            <?php endif; ?>

            <label>Title:</label>
            <input class="input-field" type="text" name="title" required>
            <label for="tag">Tag:</label>
            <input class="input-field" type="text" id="tag" name="tag">
            <label>Description:</label>
            <textarea class="input-field" name="description" rows="5" cols="40" required></textarea>
            <label>Link:</label>
            <input class="input-field" type="url" name="link" required>
            <div class="form-actions">
                <div class="actions actions-left">
                    <a class="ghost-btn" href="list.php">⬅️ Back</a>
                </div>
                <div class="actions actions-right">
                    <button type="submit" name="insert">Save</button>
                </div>
            </div>
            
        </form>
        </div>
        
    </div>

    <?php
        // Include the footer
        include __DIR__ . '/layout/footer.php';
    ?>

</body>
</html>
