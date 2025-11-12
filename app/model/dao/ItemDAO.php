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
        $this->conn = Connexio::getConnection();
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
            $items[] = new Item($row['id'], $row['title'], $row['description'], $row['link']);
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
            return new Item($row['id'], $row['title'], $row['description'], $row['link']);
        }
        return null;
    }

    /**
     * Insert a new item.
     * @param string $title Item title.
     * @param string $description Item description.
     * @param string $link Item link.
     * @return void
     */
    public function insert($title, $description, $link) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO items (title, description, link) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description, $link]);
        } catch (PDOException $e) {
            echo "Error inserting item: " . $e->getMessage();
        }
    }

    /**
     * Update an existing item.
     * @param int $id ID of the item to update.
     * @param string $title New item title.
     * @param string $description New item description.
     * @param string $link New item link.
     * @return void
     */
    public function update($id, $title, $description, $link) {
        try {
            $stmt = $this->conn->prepare("UPDATE items SET title=?, description=?, link=? WHERE id=?");
            $stmt->execute([$title, $description, $link, $id]);
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
            $stmt = $this->conn->prepare("INSERT INTO items (title, description, link) VALUES (?, ?, ?)");
            $stmt->execute([$item->getTitle(), $item->getDescription(), $item->getLink()]);
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
            $stmt = $this->conn->prepare("UPDATE items SET title=?, description=?, link=? WHERE id=?");
            $stmt->execute([$item->getTitle(), $item->getDescription(), $item->getLink(), $item->getId()]);
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
    
    /**
     * Search items by title term.
     * @param string $term Search term.
     * @return array List of items that match the term.
     */
    public function search($term) {
        $stmt = $this->conn->prepare("SELECT * FROM items WHERE title LIKE ?");
        $stmt->execute(['%' . $term . '%']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        foreach ($rows as $row) {
            $items[] = new Item($row['id'], $row['title'], $row['description'], $row['link']);
        }
        return $items;
    }

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
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS cnt FROM items WHERE title LIKE ?");
            $stmt->execute(['%' . $term . '%']);
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
     * @return array List of paginated items.
     */
    public function getPaginated($limit, $offset, $term = '', $order = 'ASC') {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        if ($term === '') {
            $sql = "SELECT * FROM items ORDER BY id $order LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "SELECT * FROM items WHERE title LIKE :term ORDER BY id $order LIMIT :limit OFFSET :offset";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        foreach ($rows as $row) {
            $items[] = new Item($row['id'], $row['title'], $row['description'], $row['link']);
        }
        return $items;
    }
}
?>
