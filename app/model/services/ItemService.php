<?php
require_once __DIR__ . '/../dao/ItemDAO.php';
require_once __DIR__ . '/../entities/Item.php';

class ItemService {
    private $dao;

    /**
     * Constructor to initialize the DAO
     * @return void
     */
    public function __construct() {
        $this->dao = new ItemDAO();
    }

    /**
     * Get all items
     * @return array List of Item objects
     */
    public function getItems() {
        return $this->dao->getAll();
    }

    /**
     * Get all items for a user (optional search).
     * @param int $user_id
     * @param string $term
     * @return array
     */
    public function getItemsByUser($user_id, $term = '', $order = 'DESC') {
        return $this->dao->getAllByUser($user_id, $term, $order);
    }

    /**
     * Get an item by ID
     * @param int $id ID of the item
     * @return Item|null The Item object or null if it doesn't exist
     */
    public function getItemById($id) {
        return $this->dao->getById($id);
    }

    /**
     * Insert a new item
     * @param string $title Item title
     * @param string $description Item description
     * @param string $link Item link
     * @param int $user_id ID of the user who owns the item
     * @param string|null $tag Item tag (optional)
     * @return int Inserted item ID; 0 on failure
     */
    public function insertItem($title, $description, $link, $user_id, $tag = null) {
        return $this->dao->insert($title, $description, $link, $user_id, $tag);
    }

    /**
     * Update an existing item
     * @param int $id ID of the item to update
     * @param string $title New item title
     * @param string $description New item description
     * @param string $link New item link
     * @param string|null $tag New item tag (optional)
     * @return void
     */
    public function updateItem($id, $title, $description, $link, $tag = null) {
        $this->dao->update($id, $title, $description, $link, $tag);
    }

    /**
     * Insert a new item from an Item object
     * @param Item $item Object Item to insert
     * @return Item The inserted Item object
     * @return void
     */
    public function insertItemObject(Item $item) {
        $this->dao->insertItem($item);
        return $item;
    }

    /**
     * Update an item from an Item object
     * @param Item $item Object Item to update
     * @return void
     */
    public function updateItemObject(Item $item) {
        $this->dao->updateItem($item);
    }

    /**
     * Delete an item by ID
     * @param int $id ID of the item to delete
     * @return void
     */
    public function deleteItem($id) {
        $this->dao->delete($id);
    }

    /**
     * Search items by title term
     * @param string $term Search term
     * @return array List of Item objects that match the term
     */
    public function searchItems($term) {
        return $this->dao->search($term);
    }

    /**
     * Get paginated items
     * @param int $page Page number (default 1)
     * @param int $perPage Number of items per page (default 6)
     * @param string $term Search term (default empty)
     * @param string $order Item order (ASC|DESC)(default 'ASC')
     * @return array List of items and total count
     */
    public function getItemsPaginated($page = 1, $perPage = 6, $term = '', $order = 'DESC') {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page - 1) * $perPage;
        $items = $this->dao->getPaginated($perPage, $offset, $term, $order);
        $total = $this->dao->count($term);
        return ['items' => $items, 'total' => $total];
    }

    /**
     * Get paginated items for a user.
     * @param int $userId User ID.
     * @param int $page Page number (default 1).
     * @param int $perPage Number of items per page (default 6).
     * @param string $term Search term (default empty).
     * @param string $order Item order (ASC|DESC)(default 'DESC').
     * @return array List of items and total count
     */
    public function getItemsPaginatedByUser($userId, $page = 1, $perPage = 6, $term = '', $order = 'DESC') {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page - 1) * $perPage;
        $items = $this->dao->getPaginatedByUser($perPage, $offset, $userId, $term, $order);
        $total = $this->dao->countByUser($userId, $term);
        return ['items' => $items, 'total' => $total];
    }
}
?>
