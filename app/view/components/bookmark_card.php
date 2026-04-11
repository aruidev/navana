<?php

/**
 * Expected variables:
 * - $item
 * - $author
 * - $currentUserId
 * - $redirect
 * - $isSaved
 * - $logoService
 * - $alwaysShowSavedAction (optional)
 */

$alwaysShowSavedAction = $alwaysShowSavedAction ?? false;
$logoUrl = null;

if (isset($logoService) && $item->getLink()) {
    $logoUrl = $logoService->getLogoUrlFromLink((string) $item->getLink());
}
?>
<article class="card">

    <h2 class="card-title-with-logo">
        <a class="truncate card-title-link" href="<?= htmlspecialchars(buildRouteUrl('item', ['id' => $item->getId()])) ?>" title="<?= htmlspecialchars($item->getTitle()) ?>"><?= htmlspecialchars($item->getTitle()) ?></a>
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
            <a class="danger ghost-btn"
                href="<?= htmlspecialchars(buildControllerUrl('ItemController.php', ['delete' => $item->getId()])) ?>"
                onclick="return confirm('Are you sure you want to delete this item?')">🗑️ Delete</a>
            <a class="ghost-btn" href="<?= htmlspecialchars(buildRouteUrl('item/edit', ['id' => $item->getId()])) ?>">✏️ Edit</a>
        <?php endif; ?>

        <?php if ($alwaysShowSavedAction): ?>
            <a class="ghost-btn"
                href="<?= htmlspecialchars(buildControllerUrl('SavedController.php', ['action' => 'unsave', 'id' => $item->getId(), 'redirect' => $redirect])) ?>">♥️ Saved</a>
        <?php elseif ($currentUserId): ?>
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