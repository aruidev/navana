<?php
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
?>