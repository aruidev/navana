<?php
class User {
    // Attributes
    private $id;
    private $username;
    private $email;
    private $password_hash;
    private $is_admin;

    /**
     * Constructor of the User class.
     * @param int|null $id ID of the user (null for new users).
     * @param string $username Username of the user.
     * @param string $email Email of the user.
     * @param string $password_hash Hashed password of the user.
     * @return void
     */
    public function __construct($id = null, $username = '', $email = '', $password_hash = '', $is_admin = false) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password_hash = $password_hash;
        $this->is_admin = (bool)$is_admin;
    }

    // GETTERS
    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPasswordHash() {
        return $this->password_hash;
    }

    public function isAdmin() {
        return (bool)$this->is_admin;
    }

    // SETTERS
    public function setId($id) {
        $this->id = $id;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPasswordHash($password_hash) {
        $this->password_hash = $password_hash;
    }

    public function setIsAdmin($is_admin) {
        $this->is_admin = (bool)$is_admin;
    }
}