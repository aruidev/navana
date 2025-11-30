<?php
require_once __DIR__ . '/../model/services/ItemService.php';
require_once __DIR__ . '/../model/dao/UserDAO.php';
$service = new ItemService();
$userDao = new UserDAO();

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Items - Navana</title>
    <link rel="stylesheet" href="../../styles.css">
</head>

<body>
    <?php include __DIR__ . '/layout/header.php'; ?>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="container">
            <div class="error">You must be logged in to access this page.</div>
            <br>
            <div class="actions">
                <a class="ghost-btn" href="list.php">⬅️ Back</a>
                <a class="primary-btn ghost-btn" href="login.php">🔐 Login</a>
            </div>
        </div>
        <?php include __DIR__ . '/layout/footer.php'; ?>
</body>

</html>
<?php exit(); ?>
<?php endif; ?>

<?php
$currentUserId = $_SESSION['user_id'];
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$items = $service->getItemsByUser($currentUserId, $term, $order);
?>

<div class="container">
    <header class="list-header">
        <h1>My Items</h1>
        <a class="primary-btn ghost-btn" href="form_insert.php">➕ Add item</a>
    </header>

    <div>
        <form method="get" action="my_items.php" class="search-container">
            <input type="text" id="search-input" name="term" placeholder="Search..."
                value="<?=
                        // Store the search term in the input
                        htmlspecialchars($term)
                        ?>">
            <div class="search-actions">
                <?php
                // Show clear button only if there is a search term
                if ($term !== ''): ?>
                    <a class="secondary-btn ghost-btn" href="my_items.php">🗑️ Clear</a>
                <?php endif; ?>
                <button type="submit" class="secondary-btn ghost-btn">🔎 Search</button>
                <button class="secondary-btn ghost-btn" type="submit" name="order" title="Change order"
                    value="<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">
                    <?= $order === 'ASC' ? '⬆️ Sort' : '⬇️ Sort' ?>
                </button>
            </div>
        </form>
    </div>

    <div class="card-grid">
        <?php foreach ($items as $item): ?>
            <?php $author = $item->getUserId() ? $userDao->findById($item->getUserId()) : null; ?>
            <article class="card">
                <div class="row meta">
                    <span><?= $item->getTag() !== '' ? '🏷️ ' . htmlspecialchars($item->getTag()) : '🏷️ -' ?></span>
                    <span><?= $author ? '👤 ' . htmlspecialchars($author->getUsername()) : '👤 Unknown' ?></span>
                </div>
                <h3><span class="truncate" title="<?= htmlspecialchars($item->getTitle()) ?>">
                        <?= htmlspecialchars($item->getTitle()) ?></span></h3>
                <p class="desc truncate"><?= htmlspecialchars($item->getDescription()) ?></p>
                <?php if ($item->getLink()): ?>
                    <p><a class="truncate" href="<?= htmlspecialchars($item->getLink()) ?>"
                            target="_blank" rel="noopener"><?= htmlspecialchars($item->getLink()) ?></a></p>
                <?php endif; ?>
                <div class="actions">
                    <?php if (isset($_SESSION['user_id']) && $item->getUserId() === $_SESSION['user_id']): ?>
                        <a class="ghost-btn"
                            href="../controller/ItemController.php?delete=<?= $item->getId() ?>"
                            onclick="return confirm('Are you sure you want to delete this item?')">🗑️ Delete</a>
                        <a class="ghost-btn" href="form_update.php?id=<?= $item->getId() ?>">✏️ Edit</a>
                    <?php endif; ?>

                    <a class="ghost-btn" href="form_view.php?id=<?= $item->getId() ?>">➡️ View</a>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <p>No items found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
</body>

</html>