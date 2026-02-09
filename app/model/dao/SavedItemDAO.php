<?php
declare(strict_types=1);

require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../entities/Item.php';

class SavedItemDAO {
    private PDO $conn;

    public function __construct() {
        $this->conn = Connection::getConnection();
    }

    public function save(int $userId, int $itemId): void {
        try {
            $stmt = $this->conn->prepare(
                'INSERT IGNORE INTO saved_items (user_id, item_id) VALUES (?, ?)'
            );
            $stmt->execute([$userId, $itemId]);
        } catch (PDOException $e) {
            echo 'Error saving item: ' . $e->getMessage();
        }
    }

    public function unsave(int $userId, int $itemId): void {
        try {
            $stmt = $this->conn->prepare('DELETE FROM saved_items WHERE user_id = ? AND item_id = ?');
            $stmt->execute([$userId, $itemId]);
        } catch (PDOException $e) {
            echo 'Error unsaving item: ' . $e->getMessage();
        }
    }

    public function isSaved(int $userId, int $itemId): bool {
        $stmt = $this->conn->prepare(
            'SELECT 1 FROM saved_items WHERE user_id = ? AND item_id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $itemId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * @param int $userId
     * @param int[] $itemIds
     * @return int[]
     */
    public function getSavedItemIdsForUserAndItemIds(int $userId, array $itemIds): array {
        if (empty($itemIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $sql = "SELECT item_id FROM saved_items WHERE user_id = ? AND item_id IN ($placeholders)";
        $stmt = $this->conn->prepare($sql);
        $params = array_merge([$userId], array_map('intval', $itemIds));
        $stmt->execute($params);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * @return Item[]
     */
    public function getSavedPaginated(int $userId, int $limit, int $offset, string $term = '', string $order = 'DESC'): array {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        if ($term === '') {
            $sql = "SELECT i.* FROM items i
                    INNER JOIN saved_items s ON s.item_id = i.id
                    WHERE s.user_id = :userId
                    ORDER BY i.updated_at $order
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "SELECT i.* FROM items i
                    INNER JOIN saved_items s ON s.item_id = i.id
                    WHERE s.user_id = :userId
                      AND (i.title LIKE :title OR i.tag LIKE :tag OR i.link LIKE :link)
                    ORDER BY i.updated_at $order
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $like = '%' . $term . '%';
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':title', $like, PDO::PARAM_STR);
            $stmt->bindValue(':tag', $like, PDO::PARAM_STR);
            $stmt->bindValue(':link', $like, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        foreach ($rows as $row) {
            $items[] = new Item(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['link'],
                isset($row['tag']) ? $row['tag'] : '',
                isset($row['created_at']) ? $row['created_at'] : '',
                isset($row['updated_at']) ? $row['updated_at'] : '',
                isset($row['user_id']) ? $row['user_id'] : null
            );
        }
        return $items;
    }

    public function countSavedByUser(int $userId, string $term = ''): int {
        if ($term === '') {
            $stmt = $this->conn->prepare(
                'SELECT COUNT(*) AS cnt FROM saved_items WHERE user_id = ?'
            );
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->conn->prepare(
                'SELECT COUNT(*) AS cnt FROM items i
                 INNER JOIN saved_items s ON s.item_id = i.id
                 WHERE s.user_id = ?
                   AND (i.title LIKE ? OR i.tag LIKE ? OR i.link LIKE ?)'
            );
            $like = '%' . $term . '%';
            $stmt->execute([$userId, $like, $like, $like]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['cnt'];
    }
}
?>