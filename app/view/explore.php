<?php
// Requerim i instanciem el servei d'items
require_once __DIR__ . '/../services/ItemService.php';
require_once __DIR__ . '/../services/Pagination.php';
require_once __DIR__ . '/../services/SavedItemService.php';
require_once __DIR__ . '/../services/LogoDevService.php';
require_once __DIR__ . '/../model/dao/UserDAO.php';
require_once __DIR__ . '/../helpers/date_format.php';
require_once __DIR__ . '/../helpers/route_helpers.php';
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
$pagination = new Pagination($page, $perPage, $total, $term, $order, buildRouteUrl('explore'));

// Page title
$title = 'Explore';
?>

<?php
// Include the header
include __DIR__ . '/layout/header.php';
?>

<?php
$currentUserId = $_SESSION['user_id'] ?? null;
$redirect = $_SERVER['REQUEST_URI'] ?? 'explore.php';
$savedItemLookup = [];
if ($currentUserId && !empty($items)) {
    $savedService = new SavedItemService();
    $itemIds = array_map(static function ($item) {
        return (int) $item->getId();
    }, $items);
    $savedIds = $savedService->getSavedItemIdsForUserAndItemIds((int) $currentUserId, $itemIds);
    $savedItemLookup = array_fill_keys($savedIds, true);
}

$logoService = new LogoDevService();
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

// Include the footer
include __DIR__ . '/layout/footer.php';
?>