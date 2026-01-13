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
        $username = strtolower(trim($username));
        return $this->dao->existsByUsername($username);    
    }

    /**
     * Check if an email already exists
     * @param string $email Email to check
     * @return bool Returns true if email exists, false otherwise
     */
    public function emailExists($email) {
        $email = strtolower(trim($email));
        return $this->dao->existsByEmail($email);
    }

    /**
     * Validate registration data
     * @param array $data Associative array containing 'username', 'email', 'password', 'password2'
     * @return array Returns an array of error messages, empty if no errors
     */
    public function validateRegister($data) {
        $errors = [];

        // Check for empty fields
        if ($data['username'] === '' || $data['email'] === '' || $data['password'] === '') {
            $errors[] = "All fields are required.";
        }

        // Check if username or email already exists
        if ($this->usernameExists($data['username'])) {
            $errors[] = "Username already taken.";
        }
        if ($this->emailExists($data['email'])) {
            $errors[] = "Email already registered.";
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        // Password validations
        if ($data['password'] !== $data['password2']) {
            $errors[] = "Passwords don't match.";
        }

        if (strlen($data['password']) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }

        if (preg_match('/^[a-zA-Z0-9]+$/', $data['password'])) {
            $errors[] = "Password must include at least one lowercase letter, one uppercase letter, and one number.";
        }

        return $errors;
    }

    public function changeUsername($userId, $newUsername) {
        $newUsername = trim($newUsername);
        if ($this->usernameExists($newUsername)) {
            return false; // Username already taken
        }
        return $this->dao->updateUsername($userId, $newUsername);
    }
}