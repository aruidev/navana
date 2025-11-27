<?php
require_once __DIR__ . '/../dao/UserDAO.php';
require_once __DIR__ . '/../entities/User.php';

class UserService {
    private $dao;

    /**
     * Constructor to initialize the DAO
     * @return void
     */
    public function __construct() {
        $this->dao = new UserDAO();
    }

    /**
     * Register a new user
     * @param string $username Username of the user
     * @param string $email Email of the user
     * @param string $password Plain text password of the user
     * @return bool Returns true on successful registration, false otherwise
     */
    public function register($username, $email, $password) {
        // Hash the password before storing
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $user = new User(null, $username, $email, $password_hash);
        return $this->dao->create($user);
    }

    /**
     * Login a user
     * @param string $usernameOrEmail Username or email of the user
     * @param string $password Plain text password of the user
     * @return User|null Returns the User object if credentials are valid, null otherwise
     */
    public function login($usernameOrEmail, $password) {
        return $this->dao->verifyCredentials($usernameOrEmail, $password);
    }

    /**
     * Check if a username already exists
     * @param string $username Username to check
     * @return bool Returns true if username exists, false otherwise
     */
    public function usernameExists($username) {
        return $this->dao->existsByUsername($username);    
    }

    /**
     * Check if an email already exists
     * @param string $email Email to check
     * @return bool Returns true if email exists, false otherwise
     */
    public function emailExists($email) {
        return $this->dao->existsByEmail($email);
    }
}