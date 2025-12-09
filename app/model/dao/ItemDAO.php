<?php
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../entities/Item.php';

class ItemDAO {
    private $conn;

    /**
     * Constructor to initialize the database connection.
     * Creates a new ItemDAO instance and establishes a database connection.
     * @throws Exception if the connection fails.
     */
    public function __construct() {
        $this->conn = Connection::getConnection();
    }

    /**
     * Get all items.
     * @return array List of Item objects.
     * @throws Exception if the query fails.
     */
    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM items");
        $stmt->execute();
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

    /**
     * Get all items for a specific user (optional search by title, tag or link).
     * @param int $user_id ID of the user
     * @param string $term Search term
     * @param string $order Order of the items by title (default 'ASC')
     * @return Item[]
     */
    public function getAllByUser($user_id, $term = '', $order = 'ASC') {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        if ($term === '') {
            $stmt = $this->conn->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY title $order");
            $stmt->execute([(int)$user_id]);
        } else {
            $sql = "SELECT * FROM items
                    WHERE user_id = ?
                      AND (title LIKE ? OR tag LIKE ? OR link LIKE ?)
                    ORDER BY title $order";
            $like = '%' . $term . '%';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([(int)$user_id, $like, $like, $like]);
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

    /**
     * Get an item by ID.
     * @param int $id ID of the item.
     * @return Item|null The Item object or null if it doesn't exist.
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM items WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Item(
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
        return null;
    }

    /**
     * Insert a new item.
     * @param string $title Item title.
     * @param string $description Item description.
     * @param string $link Item link.
     * @param int $user_id ID of the user who owns the item.
     * @param string|null $tag Tag of the item.
     * @return int Inserted item ID; 0 on failure.
     */
    public function insert($title, $description, $link, $user_id, $tag = null) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO items (title, description, link, tag, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $link, $tag, $user_id]);
            return (int)$this->conn->lastInsertId();
        } catch (PDOException $e) {
            echo "Error inserting item: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * Update an existing item.
     * @param int $id ID of the item to update.
     * @param string $title New item title.
     * @param string $description New item description.
     * @param string $link New item link.
     * @param string|null $tag New item tag.
     * @return void
     */
    public function update($id, $title, $description, $link, $tag = null) {
        try {
            $stmt = $this->conn->prepare("UPDATE items SET title=?, description=?, link=?, tag=? WHERE id=?");
            $stmt->execute([$title, $description, $link, $tag, $id]);
        } catch (PDOException $e) {
            echo "Error updating item: " . $e->getMessage();
        }
    }

    /**
     * Insert a new item from an Item object.
     * @param Item $item Item object to insert.
     * @return void
     */
    public function insertItem(Item $item) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO items (title, description, link, tag, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$item->getTitle(), $item->getDescription(), $item->getLink(), $item->getTag(), $item->getUserId()]);
            $item->setId($this->conn->lastInsertId());
        } catch (PDOException $e) {
            echo "Error inserting item: " . $e->getMessage();
        }
    }

    /**
     * Update an item from an Item object.
     * @param Item $item Item object with updated data.
     * @return void
     */
    public function updateItem(Item $item) {
        try {
            $stmt = $this->conn->prepare("UPDATE items SET title=?, description=?, link=?, tag=? WHERE id=?");
            $stmt->execute([$item->getTitle(), $item->getDescription(), $item->getLink(), $item->getTag(), $item->getId()]);
        } catch (PDOException $e) {
            echo "Error updating item: " . $e->getMessage();
        }
    }

    /**
     * Delete an item by ID.
     * @param int $id ID of the item to delete.
     * @return void
     */
    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM items WHERE id=?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            echo "Error deleting item: " . $e->getMessage();
        }
    }

    /* SEARCH */
    
    /**
     * Search items by term across title, tag or link.
     * @param string $term Search term.
     * @return array List of items that match the term.
     */
    public function search($term) {
        $sql = "SELECT * FROM items
                WHERE title LIKE :title
                   OR tag   LIKE :tag
                   OR link  LIKE :link";
        $stmt = $this->conn->prepare($sql);
        $like = '%' . $term . '%';
        $stmt->bindValue(':title', $like, PDO::PARAM_STR);
        $stmt->bindValue(':tag',   $like, PDO::PARAM_STR);
        $stmt->bindValue(':link',  $like, PDO::PARAM_STR);
        $stmt->execute();

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

    /* PAGINATION */

    // Used for pagination totalPages calculation.
    /**
     * Count the total number of items or items that match a term.
     * @param string $term Search term (default empty).
     * @return int Number of items.
     */
    public function count($term = '') {
        if ($term === '') {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS cnt FROM items");
            $stmt->execute();
        } else {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) AS cnt FROM items
                 WHERE title LIKE ? OR tag LIKE ? OR link LIKE ?"
            );
            $like = '%' . $term . '%';
            $stmt->execute([$like, $like, $like]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['cnt'];
    }

    /**
     * Get paginated items.
     * @param int $limit Maximum number of items per page.
     * @param int $offset Offset for the current page.
     * @param string $order Item order (ASC|DESC)(default 'ASC').
     * @param string $term Search term (default empty).
     * @return Item[]
     */
    public function getPaginated($limit, $offset, $term = '', $order = 'ASC') {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        if ($term === '') {
            $sql = "SELECT * FROM items ORDER BY title $order LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "SELECT * FROM items
                    WHERE title LIKE :title OR tag LIKE :tag OR link LIKE :link
                    ORDER BY title $order
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $like = '%' . $term . '%';
            $stmt->bindValue(':title', $like, PDO::PARAM_STR);
            $stmt->bindValue(':tag',   $like, PDO::PARAM_STR);
            $stmt->bindValue(':link',  $like, PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
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
}
?>
