<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
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

    <div class="container">
        <h1>Add item</h1>
        <form class="border" action="../controller/ItemController.php" method="POST">
            <label>Title:</label><br>
            <input class="input-field" type="text" name="title" required><br><br>
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
