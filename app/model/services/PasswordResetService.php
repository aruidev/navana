<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/PasswordResetTokenDAO.php';
require_once __DIR__ . '/../dao/UserDAO.php';
require_once __DIR__ . '/MailService.php';

class PasswordResetService
{
    private PasswordResetTokenDAO $dao;
    private UserDAO $userDao;
    private int $ttlMinutes;

    public function __construct(int $ttlMinutes = 30)
    {
        $this->dao = new PasswordResetTokenDAO();
        $this->userDao = new UserDAO();
        $this->ttlMinutes = $ttlMinutes;
    }

    /**
     * Request a password reset for the given email.
     * Always returns true to avoid leaking whether the email exists.
     */
    public function requestReset(string $email, string $appUrl): bool
    {
        $email = trim($email);
        $user = $this->userDao->findByUsernameOrEmail($email);
        if ($user === null) {
            return true;
        }

        $this->dao->deleteByUser($user->getId());
        [$selector, $validator, $expiresAt] = $this->issueToken($user->getId());

        $resetLink = $this->buildResetLink($appUrl, $selector, $validator);
        $this->sendResetEmail($user->getEmail(), $resetLink, $expiresAt);

        return true;
    }

    /**
     * Issue a new reset token and return [selector, validator, expiresAt].
     */
    public function issueToken(int $userId): array
    {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $hash = hash('sha256', $validator);
        $expiresAt = (new DateTimeImmutable("+{$this->ttlMinutes} minutes"))->format('Y-m-d H:i:s');

        $this->dao->create($userId, $selector, $hash, $expiresAt);
        return [$selector, $validator, $expiresAt];
    }

    /**
     * Validate and consume a token. Returns user ID on success, null otherwise.
     */
    public function consumeToken(string $selector, string $validator): ?int
    {
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
            $this->dao->deleteBySelector($selector);
            return null;
        }

        $this->dao->deleteBySelector($selector);
        return (int)$record['user_id'];
    }

    /**
     * Clear all tokens for a user.
     */
    public function clearUserTokens(int $userId): void
    {
        $this->dao->deleteByUser($userId);
    }

    /**
     * Clear expired tokens.
     */
    public function clearExpired(): void
    {
        $this->dao->deleteExpired();
    }

    private function buildResetLink(string $appUrl, string $selector, string $validator): string
    {
        $base = rtrim($appUrl, '/');
        return $base . '/app/view/reset_confirm.php?selector=' . urlencode($selector) . '&validator=' . urlencode($validator);
    }

    /**
     * Send a generic reset email (placeholder for PHPMailer integration).
     */
    private function sendResetEmail(string $email, string $resetLink, string $expiresAt): void
    {
        $subject = 'Password reset request';
        $body = "We received a request to reset your password.\n\n";
        $body .= "Use the link below to set a new password. This link expires at {$expiresAt}.\n\n";
        $body .= $resetLink . "\n\n";
        $body .= "If you did not request this, you can ignore this email.";

        $mailer = new MailService();
        $mailer->send($email, '', $subject, $body);
    }
}
