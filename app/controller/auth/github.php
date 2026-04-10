<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../../model/session.php';
require_once __DIR__ . '/../../services/UserService.php';
require_once __DIR__ . '/../../services/GithubAuthService.php';

startSession();

$userService = new UserService();
$githubService = new GithubAuthService();
$isStartFlow = false;

if (!$githubService->isConfigured()) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'GitHub authentication is not configured'];
    header('Location: ../../view/login.php');
    exit;
}

if (isset($_GET['start'])) {
    $isStartFlow = true;
    $mode = ($_GET['mode'] ?? 'login') === 'link' ? 'link' : 'login';

    if ($mode === 'link' && !isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        header('Location: ../../view/login.php');
        exit;
    }

    $_SESSION['github_auth_mode'] = $mode;
    $_SESSION['github_auth_user_id'] = (int) ($_SESSION['user_id'] ?? 0);
}

if (isset($_GET['unlink'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        header('Location: ../../view/login.php');
        exit;
    }

    $userId = (int) $_SESSION['user_id'];
    $unlinked = $userService->unlinkGithubAccount($userId);

    if ($unlinked) {
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'GitHub account unlinked'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not unlink GitHub account'];
    }

    header('Location: ../../view/account-settings.php');
    exit;
}

if (!$isStartFlow && !isset($_GET['code'])) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid GitHub authentication response'];
    header('Location: ../../view/login.php');
    exit;
}

$mode = (string) ($_SESSION['github_auth_mode'] ?? 'login');
$authUserId = (int) ($_SESSION['github_auth_user_id'] ?? 0);

unset($_SESSION['github_auth_mode'], $_SESSION['github_auth_user_id']);

try {
    $githubProfile = $githubService->getUserProfileFromCallback();
} catch (Throwable $exception) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => $isStartFlow ? 'Could not start GitHub authentication' : 'GitHub authentication failed'];
    $redirect = $mode === 'link' ? '../../view/account-settings.php' : '../../view/login.php';
    header('Location: ' . $redirect);
    exit;
}

if ($githubProfile === null || trim((string) ($githubProfile['provider_user_id'] ?? '')) === '') {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'GitHub authentication failed'];
    header('Location: ../../view/login.php');
    exit;
}

if (trim((string) ($githubProfile['email'] ?? '')) === '') {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'GitHub account must provide a valid email'];
    $redirect = $mode === 'link' ? '../../view/account-settings.php' : '../../view/login.php';
    header('Location: ' . $redirect);
    exit;
}

if ($mode === 'link') {
    if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_id'] !== $authUserId) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Session expired. Please try again.'];
        header('Location: ../../view/account-settings.php');
        exit;
    }

    $linked = $userService->linkGithubAccount((int) $_SESSION['user_id'], $githubProfile);
    if ($linked) {
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'GitHub account linked'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not link GitHub account'];
    }

    header('Location: ../../view/account-settings.php');
    exit;
}

$user = $userService->loginWithGithub($githubProfile);
if ($user === null) {
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not sign in with GitHub'];
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
