<?php
declare(strict_types=1);

require_once __DIR__ . '/../connection.php';

class PasswordResetTokenDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Connection::getConnection();
    }

    /**
     * Create a new password reset token record.
     */
    public function create(int $userId, string $selector, string $validatorHash, string $expiresAt): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO password_reset_tokens (user_id, selector, validator_hash, expires_at) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$userId, $selector, $validatorHash, $expiresAt]);
    }

    /**
     * Find a reset token record by selector.
     */
    public function findBySelector(string $selector): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id, user_id, selector, validator_hash, expires_at FROM password_reset_tokens WHERE selector = ? LIMIT 1'
        );
        $stmt->execute([$selector]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Delete a reset token by selector.
     */
    public function deleteBySelector(string $selector): void
    {
        $stmt = $this->conn->prepare('DELETE FROM password_reset_tokens WHERE selector = ?');
        $stmt->execute([$selector]);
    }

    /**
     * Delete all tokens for a given user ID.
     */
    public function deleteByUser(int $userId): void
    {
        $stmt = $this->conn->prepare('DELETE FROM password_reset_tokens WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    /**
     * Delete expired tokens.
     */
    public function deleteExpired(): void
    {
        $stmt = $this->conn->prepare('DELETE FROM password_reset_tokens WHERE expires_at < NOW()');
        $stmt->execute();
    }
}
