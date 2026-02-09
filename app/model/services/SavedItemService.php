<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/SavedItemDAO.php';
require_once __DIR__ . '/../entities/Item.php';

class SavedItemService {
    private SavedItemDAO $dao;

    public function __construct() {
        $this->dao = new SavedItemDAO();
    }

    public function saveItem(int $userId, int $itemId): void {
        $this->dao->save($userId, $itemId);
    }

    public function unsaveItem(int $userId, int $itemId): void {
        $this->dao->unsave($userId, $itemId);
    }

    public function isSaved(int $userId, int $itemId): bool {
        return $this->dao->isSaved($userId, $itemId);
    }

    /**
     * @param int[] $itemIds
     * @return int[]
     */
    public function getSavedItemIdsForUserAndItemIds(int $userId, array $itemIds): array {
        return $this->dao->getSavedItemIdsForUserAndItemIds($userId, $itemIds);
    }

    /**
     * @return array{items: Item[], total: int}
     */
    public function getSavedItemsPaginated(int $userId, int $page = 1, int $perPage = 6, string $term = '', string $order = 'DESC'): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;
        $items = $this->dao->getSavedPaginated($userId, $perPage, $offset, $term, $order);
        $total = $this->dao->countSavedByUser($userId, $term);
        return ['items' => $items, 'total' => $total];
    }
}
?>