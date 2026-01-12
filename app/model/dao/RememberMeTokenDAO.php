<?php
declare(strict_types=1);
require_once __DIR__ . '/../connection.php';

class RememberMeTokenDAO {
    private PDO $conn;

    public function __construct() {
        $this->conn = Connection::getConnection();
    }

    /**
     * Create a new remember me token record.
     * Returns true on success, false on failure.
     */
    public function create(int $userId, string $selector, string $validatorHash, string $expiresAt): bool {
        $stmt = $this->conn->prepare(
            'INSERT INTO remember_me_tokens (user_id, selector, validator_hash, expires_at) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$userId, $selector, $validatorHash, $expiresAt]);
    }

    /**
     * Find a remember me token record by selector.
     * Returns associative array with keys or null if not found.
     */
    public function findBySelector(string $selector): ?array {
        $stmt = $this->conn->prepare(
            'SELECT id, user_id, selector, validator_hash, expires_at FROM remember_me_tokens WHERE selector = ? LIMIT 1'
        );
        $stmt->execute([$selector]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Delete a remember me token by selector.
     */
    public function deleteBySelector(string $selector): void {
        $stmt = $this->conn->prepare('DELETE FROM remember_me_tokens WHERE selector = ?');
        $stmt->execute([$selector]);
    }

    /**
     * Delete all tokens for a given user ID.
     */
    public function deleteByUser(int $userId): void {
        $stmt = $this->conn->prepare('DELETE FROM remember_me_tokens WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    /**
     * Delete expired tokens.
     */
    public function deleteExpired(): void {
        $stmt = $this->conn->prepare('DELETE FROM remember_me_tokens WHERE expires_at < NOW()');
        $stmt->execute();
    }
}