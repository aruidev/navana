<?php
require_once __DIR__ . '/../model/services/ItemService.php';
require_once __DIR__ . '/../model/dao/UserDAO.php';
$service = new ItemService();
$item = $service->getItemById($_GET['id']);
$userDao = new UserDAO();
$author = $item && $item->getUserId() ? $userDao->findById($item->getUserId()) : null;
$title = $item ? $item->getTitle() : 'Item';
?>

<?php
// Include the header
include __DIR__ . '/layout/header.php';
?>

<div class="container">
    <header class="page-header center">
        <h1><?= htmlspecialchars($title) ?></h1>
    </header>
    <div class="page-section">
        <div class="form-wrapper">
            <article class="card">
                <div class="row meta">
                    <span><?= $item->getTag() !== '' ? '🏷️ ' . htmlspecialchars($item->getTag()) : '🏷️ -' ?></span>
                    <span><?= $author ? '👤 ' . htmlspecialchars($author->getUsername()) : '👤 Unknown' ?></span>
                </div>

                <h2><span><?= htmlspecialchars($item->getTitle()) ?></span></h2>

                <p class="desc"><?= htmlspecialchars($item->getDescription()) ?></p>

                <a class="link" href="<?= htmlspecialchars($item->getLink()) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($item->getLink()) ?></a>

                <div class="form-actions">
                    <div class="actions actions-left">
                        <a class="ghost-btn" href="list.php">⬅️ Back</a>
                    </div>
                    <div class="actions actions-right">
                        <?php if (isset($_SESSION['user_id']) && $item->getUserId() === $_SESSION['user_id']): ?>
                            <a class="ghost-btn"
                                href="../controller/ItemController.php?delete=<?= $item->getId() ?>"
                                onclick="return confirm('Are you sure you want to delete this item?')">🗑️ Delete</a>
                            <a class="ghost-btn" href="form_update.php?id=<?= $item->getId() ?>">✏️ Edit</a>
                        <?php endif; ?>
                        <a class="ghost-btn" href="<?= htmlspecialchars($item->getLink()) ?>" target="_blank" rel="noopener noreferrer">↗️ Go</a>
                    </div>
                </div>
            </article>
        </div>
    </div>
    <br>

</div>

<?php
// Include the footer
include __DIR__ . '/layout/footer.php';
?>