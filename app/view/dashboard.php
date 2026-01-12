<?php
require_once __DIR__ . '/../model/services/ItemService.php';
require_once __DIR__ . '/../model/dao/UserDAO.php';
require_once __DIR__ . '/../helpers/date_format.php';
$service = new ItemService();
$userDao = new UserDAO();

$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$title = 'Dashboard';
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
                        <a class="ghost-btn" href="explore.php">⬅️ Back</a>
                    </div>
                    <div class="actions actions-right">
                        <a class="primary-btn ghost-btn" href="login.php">🔐 Login</a>
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
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$items = $service->getItemsByUser($currentUserId, $term, $order);
?>

<div class="container">
    <header class="list-header">
        <h1>Dashboard</h1>
        <a class="primary-btn ghost-btn" href="form_insert.php">➕ Add bookmark</a>
    </header>

    <div>
        <form method="get" action="dashboard.php" class="search-container">
            <input type="text" id="search-input" name="term" placeholder="Search..."
                value="<?=
                        // Store the search term in the input
                        htmlspecialchars($term)
                        ?>">
            <div class="search-actions">
                <?php
                // Show clear button only if there is a search term
                if ($term !== ''): ?>
                    <a class="secondary-btn ghost-btn" href="dashboard.php">🗑️ Clear</a>
                <?php endif; ?>
                <button type="submit" class="secondary-btn ghost-btn">🔎 Search</button>
                <button class="secondary-btn ghost-btn" type="submit" name="order" title="Sort by date"
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

                <h2>
                    <span class="truncate" title="<?= htmlspecialchars($item->getTitle()) ?>"><?= htmlspecialchars($item->getTitle()) ?></span>
                </h2>

                <div class="row meta">
                    <span class="badge"><?= $item->getTag() !== '' ? '🏷️ ' . htmlspecialchars($item->getTag()) : '🏷️ -' ?></span>
                    <span class="badge"><?= $author ? '👤 ' . htmlspecialchars($author->getUsername()) : '👤 Unknown' ?></span>
                    <span class="badge">📅 <?= htmlspecialchars(formatDateOnly($item->getUpdatedAt())) ?></span>
                </div>

                <p class="desc truncate"><?= htmlspecialchars($item->getDescription()) ?></p>

                <?php if ($item->getLink()): ?>
                    <a class="link truncate"
                        href="<?= htmlspecialchars($item->getLink()) ?>"
                        target="_blank" rel="noopener">
                        <?= htmlspecialchars($item->getLink()) ?>
                    </a>
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