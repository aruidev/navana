<?php
require_once __DIR__ . '/../services/ItemService.php';
require_once __DIR__ . '/../services/SavedItemService.php';
require_once __DIR__ . '/../services/LogoDevService.php';
require_once __DIR__ . '/../model/session.php';
require_once __DIR__ . '/../model/dao/UserDAO.php';
require_once __DIR__ . '/../helpers/date_format.php';
startSession();
$service = new ItemService();
$item = $service->getItemById($_GET['id']);
$userDao = new UserDAO();
$author = $item && $item->getUserId() ? $userDao->findById($item->getUserId()) : null;
$title = $item ? $item->getTitle() : 'Item';

$redirect = $_SERVER['REQUEST_URI'] ?? 'library.php';
$currentUserId = $_SESSION['user_id'] ?? null;
$isSaved = false;
if ($currentUserId && $item) {
    $savedService = new SavedItemService();
    $isSaved = $savedService->isSaved((int) $currentUserId, (int) $item->getId());
}

$logoService = new LogoDevService();
$logoUrl = $item ? $logoService->getLogoUrlFromLink((string) $item->getLink()) : null;
?>

<?php
// Include the header
include __DIR__ . '/layout/header.php';
?>

<div class="container">
    <div class="page-section">
        <div class="form-wrapper">
            <article class="card">

                <header class="page-header">
                    <h1 class="card-title-with-logo">
                        <?= htmlspecialchars($title) ?>

                        <?php if ($logoUrl !== null): ?>
                            <div class="card-logo-block">
                                <img
                                    class="card-logo"
                                    src="<?= htmlspecialchars($logoUrl) ?>"
                                    alt="<?= htmlspecialchars($item->getTitle()) ?> logo"
                                    width="32"
                                    height="32"
                                    loading="lazy"
                                    decoding="async">
                            </div>
                        <?php endif; ?>
                    </h1>
                </header>

                <div class="row meta">
                    <span class="badge"><?= $item->getTag() !== '' ? '🏷️ ' . htmlspecialchars($item->getTag()) : '🏷️ -' ?></span>
                    <span class="badge"><?= $author ? '👤 ' . htmlspecialchars($author->getUsername()) : '👤 Unknown' ?></span>
                    <span class="badge">📅 <?= htmlspecialchars(formatDateOnly($item->getUpdatedAt())) ?></span>
                </div>

                <p class="desc"><?= htmlspecialchars($item->getDescription()) ?></p>

                <a class="link" href="<?= htmlspecialchars($item->getLink()) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($item->getLink()) ?></a>

                <div class="form-actions">
                    <div class="actions actions-left">
                        <a class="ghost-btn" href="#" onclick="window.history.back(); return false;">⬅️ Back</a>
                    </div>
                    <div class="actions actions-right">
                        <?php if (isset($_SESSION['user_id']) && $item->getUserId() === $_SESSION['user_id']): ?>
                            <a class="danger ghost-btn"
                                href="<?= htmlspecialchars(buildControllerUrl('ItemController.php', ['delete' => $item->getId()])) ?>"
                                onclick="return confirm('Are you sure you want to delete this item?')">🗑️ Delete</a>
                            <a class="ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('item/edit', ['id' => $item->getId()])) ?>">✏️ Edit</a>
                        <?php endif; ?>
                        <?php if ($currentUserId): ?>
                            <?php if ($isSaved): ?>
                                <a class="ghost-btn"
                                    href="<?= htmlspecialchars(buildControllerUrl('SavedController.php', ['action' => 'unsave', 'id' => $item->getId(), 'redirect' => $redirect])) ?>">♥️ Saved</a>
                            <?php else: ?>
                                <a class="ghost-btn"
                                    href="<?= htmlspecialchars(buildControllerUrl('SavedController.php', ['action' => 'save', 'id' => $item->getId(), 'redirect' => $redirect])) ?>">💔 Save</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a class="ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('login', ['reason' => 'save'])) ?>">💔 Save</a>
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