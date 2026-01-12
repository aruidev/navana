<?php
/**
 * Pagination component.
 * Expects the following variables to be defined:
 * @param int $page Current page number.
 * @param int $totalPages Total number of pages.
 * @param string $term Search term (default empty).
 * @param int $perPage Number of items per page.
 */
if (!isset($page) || !isset($totalPages)) {
    return;
}

/**
 * Generate the URL for a specific page.
 * Maintains the current page, search, and items per page parameters.
 * @param int $pageNumber Page number.
 * @param string $term Search term.
 * @param int|null $perPage Number of items per page (default null).
 * @param string|null $order Order of items (ASC|DESC)(default 'ASC').
 * @return string Generated URL.
 */
function pageUrl($pageNumber, $term = '', $perPage = null, $order = null) {
    $queryParams = [];
    if ($term !== '') $queryParams['term'] = $term;
    $queryParams['page'] = $pageNumber;
    if ($perPage !== null) $queryParams['perPage'] = $perPage;
    if ($order !== null) $queryParams['order'] = $order;
    // Return the URL with the parameters.
    return 'explore.php?' . http_build_query($queryParams);
}
?>
<div class="pagination" aria-label="Pagination Navigation">
     <!-- Pagination links -->
    <?php if ($page > 1): ?>
        <a class="page-link ghost-btn" href="<?= pageUrl($page - 1, $term, $perPage, $order) ?>">&lt; Prev</a>
    <?php else: ?>
        <a disabled class="page-link ghost-btn disabled">&lt; Prev</a>
    <?php endif; ?>

    <?php if ($totalPages > 7): // If there are more than 7 pages ?>
        <?php if ($page > 2): // If the current page is greater than 2, show the first page ?>
            <a class="page-link ghost-btn" href="<?= pageUrl(1, $term, $perPage, $order) ?>">1</a>
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
               href="<?= pageUrl($i, $term, $perPage, $order) ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php
        // Show the last page if it's not in the range
        if ($end < $totalPages):
            // If there is more than 1 page between the range and the last, show the ellipsis
            if ($end < $totalPages - 1): ?>
                <span class="ellipsis">...</span>
            <?php endif; ?>
            <a class="page-link ghost-btn" href="<?= pageUrl($totalPages, $term, $perPage, $order) ?>"><?= $totalPages ?></a>
        <?php endif; ?>

    <?php else: // If there are less than 7 pages, show all ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="page-link ghost-btn <?= $i === $page ? 'active' : '' ?>"
            href="<?= pageUrl($i, $term, $perPage, $order) ?>"><?= $i ?></a>
        <?php endfor; ?>
    <?php endif; ?>

    <?php if ($page < $totalPages): ?>
        <a class="page-link ghost-btn" href="<?= pageUrl($page + 1, $term, $perPage, $order) ?>">Next &gt;</a>
    <?php else: ?>
        <a disabled class="page-link ghost-btn disabled">Next &gt;</a>
    <?php endif; ?>
</div>

<form action="explore.php" class="per-page-container">
    <!-- Selection of items per page -->
    <label for="perPage" class="perpage-select">Show:</label>
    <select id="perPage" name="perPage" onchange="this.form.submit()">
        <option value="3" <?= $perPage === 3 ? 'selected' : '' ?>>3</option>
        <option value="6" <?= $perPage === 6 ? 'selected' : '' ?>>6</option>
        <option value="12" <?= $perPage === 12 ? 'selected' : '' ?>>12</option>
    </select>
</form>