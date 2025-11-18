<?php
require_once __DIR__ . '/../model/services/UserService.php';

$service = new UserService();

// LOGIN
if (isset($_GET['login'])) {
    $user = $service->login($_POST['identifier'], $_POST['password']);
    if ($user) {
        session_start();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        header('Location: ../view/list.php');
        exit;
    } else {
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

    // Basic validation
    if ($password !== $password2) {
        header('Location: ../view/register.php?error=registration_failed');
        exit;
    }
    if ($username === '' || $email === '' || $password === '') {
        header('Location: ../view/register.php?error=registration_failed');
        exit;
    }

    $success = $service->register($username, $email, $password);
    if ($success) {
        header('Location: ../view/login.php?message=registration_successful');
        exit;
    }
    header('Location: ../view/register.php?error=registration_failed');
    exit;
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../view/login.php?message=logged_out');
    exit;
}
