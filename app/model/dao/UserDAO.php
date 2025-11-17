<?php
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../entities/User.php';

class UserDAO {
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
     * Create a new user.
     * @param User $user User object containing user details.
     * @return void
     */
    public function create($user) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$user->getUsername(), $user->getEmail(), $user->getPasswordHash()]);
        } catch (PDOException $e) {
            echo "Error creating user: " . $e->getMessage();
        }
    }
    
    /**
     * Find a user by username or email.
     * @param string $usernameOrEmail Username or email to search for.
     * @return User|null Returns a User object if found, null otherwise.
     */
    public function findByUsernameOrEmail($usernameOrEmail) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new User($row['id'], $row['username'], $row['email'], $row['password_hash']);
        }
        return null;
    }

    /**
     * Verify user credentials.
     * @param string $usernameOrEmail Username or email.
     * @param string $password Plain text password.
     * @return User|null Returns the User object if credentials are valid, null otherwise.
     */
    public function verifyCredentials($usernameOrEmail, $password) {
        $user = $this->findByUsernameOrEmail($usernameOrEmail);
        if ($user && password_verify($password, $user->getPasswordHash())) {
            return $user;
        }
        return null;
    }
}