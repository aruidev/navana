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
        $this->conn = Connection::getConnection();
    }

    /**
     * Create a new user.
     * @param User $user User object containing user details.
     * @return bool True on success, false on failure.
     */
    public function create($user) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password_hash, is_admin) VALUES (?, ?, ?, ?)");
            return $stmt->execute([
                $user->getUsername(),
                $user->getEmail(),
                $user->getPasswordHash(),
                $user->isAdmin()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Check if a username already exists.
     * @param string $username Username to check.
     * @return bool Returns true if username exists, false otherwise.
     */
    public function existsByUsername($username) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE username=?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if an email already exists.
     * @param string $email Email to check.
     * @return bool Returns true if email exists, false otherwise.
     */
    public function existsByEmail($email) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email=?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
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
            return new User($row['id'], $row['username'], $row['email'], $row['password_hash'], $row['is_admin'] ?? false);
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

    /**
     * Find a user by ID.
     * @param int $id User ID.
     * @return User|null User object if found, null otherwise.
     */
    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new User($row['id'], $row['username'], $row['email'], $row['password_hash'], $row['is_admin'] ?? false);
        }
        return null;
    }
    
    /**
     * Update the username of a user.
     * @param int $userId ID of the user to update.
     * @param string $newUsername New username to set.
     * @return bool True on success, false on failure.
     */
    public function updateUsername($userId, $newUsername) {
        if ($this->existsByUsername($newUsername)) {
            return false; // Username already taken
        }
        try {
            $stmt = $this->conn->prepare("UPDATE users SET username=? WHERE id=?");
            return $stmt->execute([$newUsername, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findAll() {
        $stmt = $this->conn->query("SELECT * FROM users ORDER BY username ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $users[] = new User($row['id'], $row['username'], $row['email'], $row['password_hash'], $row['is_admin'] ?? false);
        }
        return $users;
    }

    public function deleteById($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}