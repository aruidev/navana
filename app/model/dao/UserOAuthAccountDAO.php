<?php

declare(strict_types=1);

require_once __DIR__ . '/../connection.php';

class UserOAuthAccountDAO {
    private PDO $conn;

    public function __construct() {
        $this->conn = Connection::getConnection();
    }

    /**
     * Find a linked OAuth account by provider and provider user ID.
     * @param string $provider The OAuth provider (e.g., 'google')
     * @param string $providerUserId The user ID from the OAuth provider
     * @return array|null Associative array of the linked account data or null if not found
     */
    public function findByProviderUserId(string $provider, string $providerUserId): ?array {
        $stmt = $this->conn->prepare(
            'SELECT id, user_id, provider, provider_user_id, provider_email, linked_at
             FROM user_oauth_accounts
             WHERE provider = ? AND provider_user_id = ?
             LIMIT 1',
        );
        $stmt->execute([$provider, $providerUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Find a linked OAuth account by user ID and provider.
     * @param int $userId The local user ID
     * @param string $provider The OAuth provider (e.g., 'google')
     * @return array|null Associative array of the linked account data or null if not found
     */
    public function findByUserAndProvider(int $userId, string $provider): ?array {
        $stmt = $this->conn->prepare(
            'SELECT id, user_id, provider, provider_user_id, provider_email, linked_at
             FROM user_oauth_accounts
             WHERE user_id = ? AND provider = ?
             LIMIT 1',
        );
        $stmt->execute([$userId, $provider]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Create a new linked OAuth account for a user.
     * @param int $userId The local user ID
     * @param string $provider The OAuth provider (e.g., 'google')
     * @param string $providerUserId The user ID from the OAuth provider
     * @param string|null $providerEmail The email from the OAuth provider (optional)
     * @return bool True on success, false on failure
     */
    public function create(int $userId, string $provider, string $providerUserId, ?string $providerEmail): bool {
        $stmt = $this->conn->prepare(
            'INSERT INTO user_oauth_accounts (user_id, provider, provider_user_id, provider_email)
             VALUES (?, ?, ?, ?)',
        );

        return $stmt->execute([$userId, $provider, $providerUserId, $providerEmail]);
    }

    /**
     * Update the linked OAuth account data (e.g., if the provider user ID or email changes).
     * @param int $id The ID of the linked OAuth account to update
     * @param string $providerUserId The new user ID from the OAuth provider
     * @param string|null $providerEmail The new email from the OAuth provider (optional)
     * @return bool True on success, false on failure
     */
    public function updateLinkData(int $id, string $providerUserId, ?string $providerEmail): bool {
        $stmt = $this->conn->prepare(
            'UPDATE user_oauth_accounts
             SET provider_user_id = ?, provider_email = ?, linked_at = CURRENT_TIMESTAMP
             WHERE id = ?',
        );

        return $stmt->execute([$providerUserId, $providerEmail, $id]);
    }

    /**
     * Delete a linked OAuth account for a user and provider.
     * @param int $userId The local user ID
     * @param string $provider The OAuth provider (e.g., 'google')
     * @return bool True if a record was deleted, false otherwise
     */
    public function deleteByUserAndProvider(int $userId, string $provider): bool {
        $stmt = $this->conn->prepare('DELETE FROM user_oauth_accounts WHERE user_id = ? AND provider = ?');
        $stmt->execute([$userId, $provider]);

        return $stmt->rowCount() > 0;
    }
}
