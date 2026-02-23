<?php
/**
 * Pagination component.
 * Expects a Pagination object in $pagination.
 */
if (!isset($pagination) || !($pagination instanceof Pagination)) {
    return;
}

$totalPages = $pagination->getTotalPages();
$page = $pagination->getPage();
$perPage = $pagination->getPerPage();
$term = $pagination->getTerm();
$order = $pagination->getOrder();
$basePath = $pagination->getBasePath();
?>

<div class="pagination" aria-label="Pagination Navigation">
    <!-- Pagination links -->
    <?php if ($page > 1): ?>
        <a class="page-link ghost-btn" href="<?= $pagination->urlForPage($page - 1) ?>">&lt; Prev</a>
    <?php else: ?>
        <a disabled class="page-link ghost-btn disabled">&lt; Prev</a>
    <?php endif; ?>

    <?php if ($totalPages > 7): // If there are more than 7 pages ?>
        <?php if ($page > 2): // If the current page is greater than 2, show the first page ?>
            <a class="page-link ghost-btn" href="<?= $pagination->urlForPage(1) ?>">1</a>
            <?php
                // If there are more than 3 pages between the first and the current page, show the ellipsis
                if ($page > 3): ?>
                <span class="ellipsis">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php
        // Calculate the central range of pages to show
        $start = max(1, $page - 1); // one page before (or the first one)
        $end = min($totalPages, $page + 1); // one page after (or the last one)
        for ($i = $start; $i <= $end; $i++): ?>
            <a class="page-link ghost-btn <?= $i === $page ? 'active' : '' ?>"
               href="<?= $pagination->urlForPage($i) ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php
        // Show the last page if it's not in the range
        if ($end < $totalPages):
            // If there is more than 1 page between the range and the last, show the ellipsis
            if ($end < $totalPages - 1): ?>
                <span class="ellipsis">...</span>
            <?php endif; ?>
            <a class="page-link ghost-btn" href="<?= $pagination->urlForPage($totalPages) ?>"><?= $totalPages ?></a>
        <?php endif; ?>

    <?php else: // If there are less than 7 pages, show all ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="page-link ghost-btn <?= $i === $page ? 'active' : '' ?>"
            href="<?= $pagination->urlForPage($i) ?>"><?= $i ?></a>
        <?php endfor; ?>
    <?php endif; ?>

    <?php if ($page < $totalPages): ?>
        <a class="page-link ghost-btn" href="<?= $pagination->urlForPage($page + 1) ?>">Next &gt;</a>
    <?php else: ?>
        <a disabled class="page-link ghost-btn disabled">Next &gt;</a>
    <?php endif; ?>
</div>

<form action="<?= htmlspecialchars($basePath) ?>" method="get" class="per-page-container">
    <!-- Selection of items per page -->
    <?php if ($term !== ''): ?>
        <input type="hidden" name="term" value="<?= htmlspecialchars($term) ?>">
    <?php endif; ?>
    <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
    <input type="hidden" name="page" value="<?= $page ?>">
    <label for="perPage" class="perpage-select">Show:</label>
    <select id="perPage" name="perPage" onchange="this.form.submit()">
        <option value="3" <?= $perPage === 3 ? 'selected' : '' ?>>3</option>
        <option value="6" <?= $perPage === 6 ? 'selected' : '' ?>>6</option>
        <option value="12" <?= $perPage === 12 ? 'selected' : '' ?>>12</option>
    </select>
</form>