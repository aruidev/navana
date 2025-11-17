<?php
require_once __DIR__ . '/../model/services/ItemService.php';
require_once __DIR__ . '/../model/dao/UserDAO.php';
$service = new ItemService();
$item = $service->getItemById($_GET['id']);
$userDao = new UserDAO();
$author = $item && $item->getUserId() ? $userDao->findById($item->getUserId()) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View item</title>
<link rel="stylesheet" href="../../styles.css">
</head>
<style>
    label {
        font-weight: bold;
        color: gray;
        margin: 0;
        padding: 0;
    }

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
        <h1>Item</h1>
        <div class="border">
            <h2><?= htmlspecialchars($item->getTitle()) ?></h2>
            <p class="meta">
                <?= $item->getCategory() !== '' ? '📁 '.htmlspecialchars($item->getCategory()).' · ' : '' ?>
                <?= $author ? '👤 '.htmlspecialchars($author->getUsername()) : '👤 Unknown' ?>
            </p>
            <p><?= htmlspecialchars($item->getDescription()) ?></p>
            <a href="<?= htmlspecialchars($item->getLink()) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($item->getLink()) ?></a>
        </div>
        <br>
        <div class="actions">
            <a class="ghost-btn" href="list.php">⬅️ Back</a>
            <?php if (isset($_SESSION['user_id']) && $item->getUserId() === $_SESSION['user_id']): ?>
            <a class="ghost-btn" href="form_update.php?id=<?= $item->getId() ?>">✏️ Edit</a>
            <a class="ghost-btn"
                href="../controller/ItemController.php?delete=<?= $item->getId() ?>"
                onclick="return confirm('Are you sure you want to delete this item?')">🗑️ Delete</a>
            <?php endif; ?>
        </div>
    </div>

    <?php
        // Include the footer
        include __DIR__ . '/layout/footer.php';
    ?>

</body>
</html>
