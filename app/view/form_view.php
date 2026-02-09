<?php
require_once __DIR__ . '/../model/services/ItemService.php';
require_once __DIR__ . '/../model/services/SavedItemService.php';
require_once __DIR__ . '/../model/session.php';
require_once __DIR__ . '/../model/dao/UserDAO.php';
require_once __DIR__ . '/../helpers/date_format.php';
startSession();
$service = new ItemService();
$item = $service->getItemById($_GET['id']);
$userDao = new UserDAO();
$author = $item && $item->getUserId() ? $userDao->findById($item->getUserId()) : null;
$title = $item ? $item->getTitle() : 'Item';

$redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'library.php');
$currentUserId = $_SESSION['user_id'] ?? null;
$isSaved = false;
if ($currentUserId && $item) {
    $savedService = new SavedItemService();
    $isSaved = $savedService->isSaved((int)$currentUserId, (int)$item->getId());
}
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

                <h2>
                    <span><?= htmlspecialchars($item->getTitle()) ?></span>
                </h2>

                <div class="row meta">
                    <span class="badge"><?= $item->getTag() !== '' ? '🏷️ ' . htmlspecialchars($item->getTag()) : '🏷️ -' ?></span>
                    <span class="badge"><?= $author ? '👤 ' . htmlspecialchars($author->getUsername()) : '👤 Unknown' ?></span>
                    <span class="badge">📅 <?= htmlspecialchars(formatDateOnly($item->getUpdatedAt())) ?></span>
                </div>

                <p class="desc"><?= htmlspecialchars($item->getDescription()) ?></p>

                <a class="link" href="<?= htmlspecialchars($item->getLink()) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($item->getLink()) ?></a>

                <div class="form-actions">
                    <div class="actions actions-left">
                        <?php if (isset($_SESSION['user_id']) && $item->getUserId() === $_SESSION['user_id']): ?>
                            <a class="ghost-btn" href="library.php">⬅️ Back</a>
                        <?php else: ?>
                            <a class="ghost-btn" href="explore.php">⬅️ Back</a>
                        <?php endif; ?>
                    </div>
                    <div class="actions actions-right">
                        <?php if (isset($_SESSION['user_id']) && $item->getUserId() === $_SESSION['user_id']): ?>
                            <a class="danger ghost-btn"
                                href="../controller/ItemController.php?delete=<?= $item->getId() ?>"
                                onclick="return confirm('Are you sure you want to delete this item?')">🗑️ Delete</a>
                            <a class="ghost-btn" href="form_update.php?id=<?= $item->getId() ?>">✏️ Edit</a>
                        <?php endif; ?>
                        <?php if ($currentUserId): ?>
                            <?php if ($isSaved): ?>
                                <a class="ghost-btn"
                                    href="../controller/SavedController.php?action=unsave&id=<?= $item->getId() ?>&redirect=<?= htmlspecialchars($redirect) ?>">♥️ Saved</a>
                            <?php else: ?>
                                <a class="ghost-btn"
                                    href="../controller/SavedController.php?action=save&id=<?= $item->getId() ?>&redirect=<?= htmlspecialchars($redirect) ?>">💔 Save</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a class="ghost-btn" href="login.php">🔐 Login to save</a>
                        <?php endif; ?>
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