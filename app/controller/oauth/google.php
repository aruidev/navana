<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../../model/session.php';
require_once __DIR__ . '/../../model/services/UserService.php';
require_once __DIR__ . '/../../model/services/GoogleOAuthService.php';

startSession();

$userService = new UserService();
$googleService = new GoogleOAuthService();

if (!$googleService->isConfigured()) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Google OAuth is not configured'];
    header('Location: ../../view/login.php');
    exit;
}

if (isset($_GET['start'])) {
    $mode = ($_GET['mode'] ?? 'login') === 'link' ? 'link' : 'login';

    if ($mode === 'link' && !isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        header('Location: ../../view/login.php');
        exit;
    }

    // Generate a unique state parameter for CSRF protection and store it in the session
    $state = bin2hex(random_bytes(32));
    $_SESSION['google_oauth_state'] = $state;
    $_SESSION['google_oauth_mode'] = $mode;
    $_SESSION['google_oauth_user_id'] = (int) ($_SESSION['user_id'] ?? 0);

    $authUrl = $googleService->getAuthorizationUrl($state);
    header('Location: ' . $authUrl);
    exit;
}

if (isset($_GET['unlink'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        header('Location: ../../view/login.php');
        exit;
    }

    $userId = (int) $_SESSION['user_id'];
    $unlinked = $userService->unlinkGoogleAccount($userId);

    if ($unlinked) {
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Google account unlinked'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not unlink Google account'];
    }

    header('Location: ../../view/account-settings.php');
    exit;
}

if (!isset($_GET['code'], $_GET['state'])) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid Google OAuth response'];
    header('Location: ../../view/login.php');
    exit;
}

$state = (string) $_GET['state'];
$expectedState = (string) ($_SESSION['google_oauth_state'] ?? '');
$mode = (string) ($_SESSION['google_oauth_mode'] ?? 'login');
$oauthUserId = (int) ($_SESSION['google_oauth_user_id'] ?? 0);

unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_mode'], $_SESSION['google_oauth_user_id']);

if ($expectedState === '' || !hash_equals($expectedState, $state)) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid OAuth state'];
    header('Location: ../../view/login.php');
    exit;
}

$googleProfile = $googleService->getUserProfileFromCode((string) $_GET['code']);
if ($googleProfile === null) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Google authentication failed'];
    header('Location: ../../view/login.php');
    exit;
}

if ($mode === 'link') {
    if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_id'] !== $oauthUserId) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Session expired. Please try again.'];
        header('Location: ../../view/account-settings.php');
        exit;
    }

    $linked = $userService->linkGoogleAccount((int) $_SESSION['user_id'], $googleProfile);
    if ($linked) {
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Google account linked'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not link Google account'];
    }

    header('Location: ../../view/account-settings.php');
    exit;
}

$user = $userService->loginWithGoogle($googleProfile);
if ($user === null) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not sign in with Google'];
    header('Location: ../../view/login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user->getId();
$_SESSION['username'] = (string) $user->getUsername();
$_SESSION['email'] = (string) $user->getEmail();
$_SESSION['is_admin'] = (bool) $user->isAdmin();
resetLoginAttempts();
$_SESSION['flash'] = ['type' => 'success', 'text' => 'Login successful'];

header('Location: ../../view/library.php');
exit;
