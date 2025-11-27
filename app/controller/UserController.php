<?php
require_once __DIR__ . '/../model/services/UserService.php';
require_once __DIR__ . '/../model/session.php';
startSession();

$service = new UserService();

// LOGIN
if (isset($_GET['login'])) {
    $user = $service->login($_POST['identifier'], $_POST['password']);
    if ($user) {
        startSession();
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

    $_SESSION['errors'] = [];

    // Check if username or email already exists
    if ($service->usernameExists($username)) {
        $_SESSION['errors'][] = "Username already taken.";
    }
    if ($service->emailExists($email)) {
        $_SESSION['errors'][] = "Email already registered.";
    }

    // Validations
    if ($password !== $password2) {
        $_SESSION['errors'][] = "Passwords don't match.";
    }

    if (strlen($password) < 6) {
        $_SESSION['errors'][] = "Password must be at least 6 characters long.";
    }

    if (preg_match('/^[a-zA-Z0-9]+$/', $password)) {
        $_SESSION['errors'][] = "Password must include at least one lowercase letter, one uppercase letter, and one number.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['errors'][] = "Invalid email format.";
    }

    if ($username === '' || $email === '' || $password === '') {
        $_SESSION['errors'][] = "All fields are required.";
    }

    if (!empty($_SESSION['errors'])) {
        header('Location: ../view/register.php');
        exit;
    }

    $success = $service->register($username, $email, $password);
    if ($success) {
        unset($_SESSION['errors']);
        header('Location: ../view/login.php?message=registration_successful');
        exit;
    }
    $_SESSION['errors'][] = "Registration failed. Please try again.";
    header('Location: ../view/register.php');
    exit;
}

// LOGOUT
if (isset($_GET['logout'])) {
    startSession();
    session_unset();
    session_destroy();
    header('Location: ../view/login.php?message=logged_out');
    exit;
}
