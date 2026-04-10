<?php
// Requerim i instanciem el servei d'items
require_once __DIR__ . '/../services/ItemService.php';
require_once __DIR__ . '/../services/Pagination.php';
require_once __DIR__ . '/../services/SavedItemService.php';
require_once __DIR__ . '/../model/dao/UserDAO.php';
require_once __DIR__ . '/../helpers/date_format.php';
$service = new ItemService();
$userDao = new UserDAO();

// Search term
$term = isset($_GET['term']) ? trim($_GET['term']) : '';

// Pagination parameters
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

// Items per page options
$allowedPerPage = [3, 6, 12];
$perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 6;
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 6;
}

// Order parameter
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Get paginated items and total count
$paginated = $service->getItemsPaginated($page, $perPage, $term, $order);
$items = $paginated['items'];
$total = $paginated['total'];

// Pagination object
$pagination = new Pagination($page, $perPage, $total, $term, $order, 'explore.php');

// Page title
$title = 'Explore';
?>

<?php
// Include the header
include __DIR__ . '/layout/header.php';
?>

<?php
$currentUserId = $_SESSION['user_id'] ?? null;
$redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'explore.php');
$savedItemLookup = [];
if ($currentUserId && !empty($items)) {
    $savedService = new SavedItemService();
    $itemIds = array_map(static function ($item) {
        return (int) $item->getId();
    }, $items);
    $savedIds = $savedService->getSavedItemIdsForUserAndItemIds((int) $currentUserId, $itemIds);
    $savedItemLookup = array_fill_keys($savedIds, true);
}
?>

<div class="container">

    <header class="list-header">
        <h1>Explore</h1>
        <a class="primary-btn ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('add')) ?>">➕ Add bookmark</a>
    </header>


    <div>
        <form method="get" action="<?= htmlspecialchars(buildRouteUrl('explore')) ?>" class="search-container">
            <input type="text" id="search-input" name="term" placeholder="Search..."
                value="<?=
                        // Store the search term in the input
                        htmlspecialchars($term)
?>">
            <input type="hidden" name="perPage" value="<?= $perPage ?>">
            <div class="search-actions">
                <?php
                // Show clear button only if there is a search term
                if ($term !== ''): ?>
                    <a class="secondary-btn ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('explore', ['perPage' => $perPage])) ?>">🗑️ Clear</a>
                <?php endif; ?>
                <button type="submit" class="secondary-btn ghost-btn">🔎 Search</button>
                <button class="secondary-btn ghost-btn" type="submit" name="order" title="Sort by date"
                    value="<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">
                    <?= $order === 'ASC' ? '⬆️ Oldest first' : '⬇️ Most recent' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Item grid -->
    <div class="card-grid">
        <?php foreach ($items as $item): ?>
            <?php $author = $item->getUserId() ? $userDao->findById($item->getUserId()) : null; ?>
            <article class="card">

                <h2>
                    <a class="truncate card-title-link" href="<?= htmlspecialchars(buildRouteUrl('item', ['id' => $item->getId()])) ?>" title="<?= htmlspecialchars($item->getTitle()) ?>"><?= htmlspecialchars($item->getTitle()) ?></a>
                </h2>

                <div class="row meta">
                    <span class="badge"><?= $item->getTag() !== '' ? '🏷️ ' . htmlspecialchars($item->getTag()) : '🏷️ -' ?></span>
                    <span class="badge"><?= $author ? '👤 ' . htmlspecialchars($author->getUsername()) : '👤 Unknown' ?></span>
                    <span class="badge">📅 <?= htmlspecialchars(formatDateOnly($item->getUpdatedAt())) ?></span>
                </div>

                <p class="desc truncate">
                    <?= htmlspecialchars($item->getDescription()) ?>
                </p>

                <?php if ($item->getLink()): ?>
                    <a class="link truncate"
                        href="<?= htmlspecialchars($item->getLink()) ?>"
                        target="_blank" rel="noopener">
                        <?= htmlspecialchars($item->getLink()) ?>
                    </a>
                <?php endif; ?>

                <div class="actions">
                    <?php if (isset($_SESSION['user_id']) && $item->getUserId() === $_SESSION['user_id']): ?>
                        <a class="danger ghost-btn"
                            href="<?= htmlspecialchars(buildControllerUrl('ItemController.php', ['delete' => $item->getId()])) ?>"
                            onclick="return confirm('Are you sure you want to delete this item?')">🗑️ Delete</a>
                        <a class="ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('item/edit', ['id' => $item->getId()])) ?>">✏️ Edit</a>
                    <?php endif; ?>
                    <?php if ($currentUserId): ?>
                        <?php $isSaved = isset($savedItemLookup[(int) $item->getId()]); ?>
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

            </article>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <p>No items found.</p>
        <?php endif; ?>
    </div>
</div>


<?php
// Include the pagination component
include_once __DIR__ . '/components/pagination.php';

// Include the footer
include __DIR__ . '/layout/footer.php';
?>