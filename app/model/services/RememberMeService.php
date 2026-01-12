<?php
declare(strict_types=1);
require_once __DIR__ . '/../dao/RememberMeTokenDAO.php';

class RememberMeService {
    private RememberMeTokenDAO $dao;

    public function __construct() {
        $this->dao = new RememberMeTokenDAO();
    }

    /**
     * Issue a new remember me token for the given user ID.
     * Returns an array with [selector, validator, expiresAt].
     */
    public function issueToken(int $userId, int $days = 30): array {
        $selector  = bin2hex(random_bytes(12));          // 24 hex chars
        $validator = bin2hex(random_bytes(32));          // 64 hex chars
        $hash      = hash('sha256', $validator);
        $expiresAt = (new DateTimeImmutable("+{$days} days"))->format('Y-m-d H:i:s');
        $this->dao->create($userId, $selector, $hash, $expiresAt);
        return [$selector, $validator, $expiresAt];
    }

    /**
     * Consume a remember me token.
     * Returns the user ID if valid, null otherwise.
     */
    public function consumeToken(string $selector, string $validator): ?int {
        $record = $this->dao->findBySelector($selector);
        if (!$record) {
            return null;
        }
        if ($record['expires_at'] < date('Y-m-d H:i:s')) {
            $this->dao->deleteBySelector($selector);
            return null;
        }
        $hash = hash('sha256', $validator);
        if (!hash_equals($record['validator_hash'], $hash)) {
            $this->dao->deleteBySelector($selector); // invalidate on mismatch
            return null;
        }
        // rotate: remove old; caller may issue a fresh token
        $this->dao->deleteBySelector($selector);
        return (int)$record['user_id'];
    }

    /**
     * Clear all tokens for a given user ID.
     */
    public function clearUserTokens(int $userId): void {
        $this->dao->deleteByUser($userId);
    }

    /**
     * Clear expired tokens.
     */
    public function clearExpired(): void {
        $this->dao->deleteExpired();
    }
}