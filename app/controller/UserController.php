<?php
require_once __DIR__ . '/../model/services/UserService.php';
require_once __DIR__ . '/../model/services/RememberMeService.php';
require_once __DIR__ . '/../model/session.php';
startSession();

$config = require __DIR__ . '/../../environments/env.php';

$service = new UserService();

// LOGIN
if (isset($_GET['login'])) {
    $user = $service->login($_POST['identifier'], $_POST['password']);
    if ($user) {
        startSession();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();

        $rememberMe = !empty($_POST['remember_me']);

        // Remember username
        if ($rememberMe) {
            $rememberService = new RememberMeService();
            [$selector, $validator, $expiresAt] = $rememberService->issueToken($user->getId());
            $cookieSecure = $config['cookie_secure'] ?? false;
            setcookie('remember_me', $selector . ':' . $validator, [
                'expires'  => strtotime($expiresAt),
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => $cookieSecure,
            ]);
        } else {
            setcookie('remember_me', '', time() - 3600, '/');
        }

        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Login successful'];
        header('Location: ../view/dashboard.php');
        exit;
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid credentials'];
        header('Location: ../view/login.php?error=invalid_credentials');
        exit;
    }
}

// REGISTER
if (isset($_GET['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'] ?? '';

    $_SESSION['errors'] = [];

    // Validate registration data
    $errors = $service->validateRegister([
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'password2' => $password2
    ]);

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        // Preserve register form data
        $_SESSION['old'] = [
            'username' => $username,
            'email' => $email
        ];
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid registration data'];
        header('Location: ../view/register.php');
        exit;
    }

    $success = $service->register($username, $email, $password);
    if ($success) {
        unset($_SESSION['errors']);
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Registration successful'];
        header('Location: ../view/login.php?message=registration_successful');
        exit;
    }
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Registration failed'];
    $_SESSION['errors'][] = "Registration failed. Please try again.";
    header('Location: ../view/register.php');
    exit;
}

// LOGOUT
if (isset($_GET['logout'])) {
    startSession();
    session_unset();
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Logged out successfully'];
    $rememberService = new RememberMeService();
    if (isset($_SESSION['user_id'])) {
        $rememberService->clearUserTokens((int)$_SESSION['user_id']);
    }
    setcookie('remember_me', '', time() - 3600, '/');
    session_destroy();
    header('Location: ../view/login.php?message=logged_out');
    exit;
}
