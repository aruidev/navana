<?php
require_once __DIR__ . '/../services/ItemService.php';
require_once __DIR__ . '/../services/Pagination.php';
require_once __DIR__ . '/../services/SavedItemService.php';
require_once __DIR__ . '/../services/LogoDevService.php';
require_once __DIR__ . '/../model/dao/UserDAO.php';
require_once __DIR__ . '/../helpers/date_format.php';
$service = new ItemService();
$userDao = new UserDAO();

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$allowedPerPage = [3, 6, 12];
$perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 6;
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 6;
}
$title = 'Library';
include __DIR__ . '/layout/header.php';
?>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="container">
        <header class="page-header center">
            <h2>Login required</h2>
        </header>
        <div class="page-section">
            <div class="form-wrapper border">
                <div class="form-messages">
                    <div class="error">You must be logged in to access this page.</div>
                </div>
                <div class="form-actions">
                    <div class="actions actions-left">
                        <a class="ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('explore')) ?>">⬅️ Back</a>
                    </div>
                    <div class="actions actions-right">
                        <a class="primary-btn ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('login')) ?>">🔐 Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/layout/footer.php'; ?>
    </body>

    </html>
    <?php exit(); ?>
<?php endif; ?>

<?php
$currentUserId = $_SESSION['user_id'];
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$paginated = $service->getItemsPaginatedByUser($currentUserId, $page, $perPage, $term, $order);
$items = $paginated['items'];
$total = $paginated['total'];

$redirect = $_SERVER['REQUEST_URI'] ?? 'library.php';
$savedItemLookup = [];
if (!empty($items)) {
    $savedService = new SavedItemService();
    $itemIds = array_map(static function ($item) {
        return (int) $item->getId();
    }, $items);
    $savedIds = $savedService->getSavedItemIdsForUserAndItemIds((int) $currentUserId, $itemIds);
    $savedItemLookup = array_fill_keys($savedIds, true);
}

$logoService = new LogoDevService();

// Pagination object
$pagination = new Pagination($page, $perPage, $total, $term, $order, buildRouteUrl('library'));
?>

<div class="container">
    <header class="list-header">
        <h1>Library</h1>
        <a class="primary-btn ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('add')) ?>">➕ Add bookmark</a>
    </header>

    <div>
        <form method="get" action="<?= htmlspecialchars(buildRouteUrl('library')) ?>" class="search-container">
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
                    <a class="secondary-btn ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('library')) ?>">🗑️ Clear</a>
                <?php endif; ?>
                <button type="submit" class="secondary-btn ghost-btn">🔎 Search</button>
                <button class="secondary-btn ghost-btn" type="submit" name="order" title="Sort by date"
                    value="<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">
                    <?= $order === 'ASC' ? '⬆️ Oldest first' : '⬇️ Most recent' ?>
                </button>
            </div>
        </form>
    </div>

    <div class="card-grid">
        <?php foreach ($items as $item): ?>
            <?php
            // Bookmark card variables
            $author = $item->getUserId() ? $userDao->findById($item->getUserId()) : null;
            $isSaved = isset($savedItemLookup[(int) $item->getId()]);
            include __DIR__ . '/components/bookmark_card.php';
            ?>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <p>No items found.</p>
        <?php endif; ?>
    </div>
</div>

<?php
// Include the pagination component
include_once __DIR__ . '/components/pagination.php';

include __DIR__ . '/layout/footer.php'; ?>