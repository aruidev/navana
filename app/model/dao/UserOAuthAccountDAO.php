<?php
declare(strict_types=1);

require_once __DIR__ . '/../connection.php';

class UserOAuthAccountDAO
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Connection::getConnection();
    }

    public function findByProviderUserId(string $provider, string $providerUserId): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id, user_id, provider, provider_user_id, provider_email, linked_at
             FROM user_oauth_accounts
             WHERE provider = ? AND provider_user_id = ?
             LIMIT 1'
        );
        $stmt->execute([$provider, $providerUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findByUserAndProvider(int $userId, string $provider): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id, user_id, provider, provider_user_id, provider_email, linked_at
             FROM user_oauth_accounts
             WHERE user_id = ? AND provider = ?
             LIMIT 1'
        );
        $stmt->execute([$userId, $provider]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(int $userId, string $provider, string $providerUserId, ?string $providerEmail): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO user_oauth_accounts (user_id, provider, provider_user_id, provider_email)
             VALUES (?, ?, ?, ?)' 
        );

        return $stmt->execute([$userId, $provider, $providerUserId, $providerEmail]);
    }

    public function updateLinkData(int $id, string $providerUserId, ?string $providerEmail): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE user_oauth_accounts
             SET provider_user_id = ?, provider_email = ?, linked_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );

        return $stmt->execute([$providerUserId, $providerEmail, $id]);
    }

    public function deleteByUserAndProvider(int $userId, string $provider): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM user_oauth_accounts WHERE user_id = ? AND provider = ?');
        $stmt->execute([$userId, $provider]);

        return $stmt->rowCount() > 0;
    }
}
