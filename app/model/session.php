<?php
declare(strict_types=1);

require_once __DIR__ . '/services/RememberMeService.php';

const LOGIN_ATTEMPT_THRESHOLD = 3;
const LOGIN_ATTEMPT_TTL = 900; // 15 minutes

    /**
     * Starts a session if none exists, with a lifetime of a number of seconds.
     * @param int $seconds The lifetime of the session in seconds (default: 2400 seconds / 40 min).
     */
    function startSession($seconds = 2400) {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', $seconds);
            session_set_cookie_params($seconds);
            session_start();
        }
    }

    /**
     * Retrieve the current login attempts, resetting them if expired.
     */
    function getLoginAttempts(): array {
        startSession();
        $now = time();
        $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'last' => 0];

        if (($now - (int)($attempts['last'] ?? 0)) > LOGIN_ATTEMPT_TTL) {
            $attempts = ['count' => 0, 'last' => 0];
            $_SESSION['login_attempts'] = $attempts;
        }

        return $attempts;
    }

    /**
     * Increment the login attempt counter and update the timestamp.
     */
    function incrementLoginAttempts(): int {
        startSession();
        $now = time();
        $attempts = $_SESSION['login_attempts'] ?? ['count' => 0, 'last' => 0];

        if (($now - (int)($attempts['last'] ?? 0)) > LOGIN_ATTEMPT_TTL) {
            $attempts = ['count' => 0, 'last' => $now];
        }

        $attempts['count'] = (int)($attempts['count'] ?? 0) + 1;
        $attempts['last'] = $now;
        $_SESSION['login_attempts'] = $attempts;

        return $attempts['count'];
    }

    /**
     * Reset the login attempt counter.
     */
    function resetLoginAttempts(): void {
        startSession();
        unset($_SESSION['login_attempts']);
    }

    /**
     * Determine if the login flow should require a CAPTCHA challenge.
     */
    function isLoginCaptchaRequired(): bool {
        startSession();
        $attempts = $_SESSION['login_attempts'] ?? null;

        if ($attempts === null) {
            return false;
        }

        $now = time();
        if (($now - (int)($attempts['last'] ?? 0)) > LOGIN_ATTEMPT_TTL) {
            unset($_SESSION['login_attempts']);
            return false;
        }

        return ($attempts['count'] ?? 0) >= LOGIN_ATTEMPT_THRESHOLD;
    }

    // Auto-login via "Remember Me" cookie
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
        [$selector, $validator] = explode(':', $_COOKIE['remember_me'], 2) + [null, null];
        if ($selector && $validator) {
            $rememberService = new RememberMeService();
            // Clear expired tokens
            $rememberService->clearExpired();
            $userId = $rememberService->consumeToken($selector, $validator);
            if ($userId) {
                $_SESSION['user_id'] = $userId;
                // issue fresh token (rotation)
                [$newSelector, $newValidator, $expiresAt] = $rememberService->issueToken($userId);
                setcookie('remember_me', $newSelector . ':' . $newValidator, [
                    'expires'  => strtotime($expiresAt),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                    'secure'   => false
                ]);
            } else {
                setcookie('remember_me', '', time() - 3600, '/');
            }
        }
    }
?>