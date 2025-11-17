<?php
class Item {
    // Attributes
    private $id;
    private $title;
    private $description;
    private $link;
    private $category;
    private $created_at;
    private $updated_at;
    private $user_id;

    /**
     * Constructor of the Item class.
     * @param int|null $id ID of the item (null for new items).
     * @param string $title Title of the item.
     * @param string $description Description of the item.
     * @param string $link Body of the item.
     * @param string $created_at Creation date of the item.
     * @param string $updated_at Update date of the item.
     * @return void
     */
    public function __construct($id = null, $title = '', $description = '', $link = '', $category = '', $created_at = '', $updated_at = '', $user_id = null) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->link = $link;
        $this->category = $category;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->user_id = $user_id;
    }

    // GETTERS
    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getLink() {
        return $this->link;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function getUpdatedAt() {
        return $this->updated_at;
    }

    public function getUserId() {
        return $this->user_id;
    }

    // SETTERS
    public function setId($id) {
        $this->id = $id;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setLink($link) {
        $this->link = $link;
    }

    public function setCategory($category) {
        $this->category = $category;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt($updated_at) {
        $this->updated_at = $updated_at;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }
}
?>
