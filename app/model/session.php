<?php
declare(strict_types=1);

require_once __DIR__ . '/services/RememberMeService.php';

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