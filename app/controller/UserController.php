<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../model/services/UserService.php';
require_once __DIR__ . '/../model/services/RememberMeService.php';
require_once __DIR__ . '/../model/services/RecaptchaService.php';
require_once __DIR__ . '/../model/session.php';
startSession();

$config = config();

$service = new UserService();

// CHANGE USERNAME
if (isset($_POST['change_username'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        header('Location: ../view/login.php');
        exit;
    }

    $newUsername = trim($_POST['new_username'] ?? '');
    $_SESSION['errors'] = [];

    if ($newUsername === '') {
        $_SESSION['errors'][] = 'Username cannot be empty.';
    }

    if (!empty($_SESSION['errors'])) {
        if (empty($newUsername)) {
            $_SESSION['errors'] = ['type' => 'error', 'text' => 'Username cannot be empty'];
        }
        if ($service->usernameExists($newUsername)) {
            $_SESSION['errors'][] = 'Username already taken.';
        }
        $_SESSION['old_username'] = $newUsername;
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid username data'];
        header('Location: ../view/account-settings.php');
        exit;
    }

    if ($service->usernameExists($newUsername)) {
        $_SESSION['errors'][] = 'Username already taken.';
        $_SESSION['old_username'] = $newUsername;
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Username already taken'];
        header('Location: ../view/account-settings.php');
        exit;
    }

    $updated = $service->changeUsername((int)$_SESSION['user_id'], $newUsername);

    if ($updated) {
        unset($_SESSION['errors'], $_SESSION['old_username']);
        $_SESSION['username'] = $newUsername;
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Username updated'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not update username'];
    }

    header('Location: ../view/account-settings.php');
    exit;
}

// CHANGE EMAIL
if (isset($_POST['change_email'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        header('Location: ../view/login.php');
        exit;
    }

    $newEmail = trim($_POST['new_email'] ?? '');
    $_SESSION['email_errors'] = [];

    $currentUser = $service->getUserById((int)$_SESSION['user_id']);
    if ($currentUser === null) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'User not found'];
        header('Location: ../view/account-settings.php');
        exit;
    }

    $currentEmail = $currentUser->getEmail();

    if ($newEmail === '') {
        $_SESSION['email_errors'][] = 'Email cannot be empty.';
    }

    if ($newEmail !== '' && !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['email_errors'][] = 'Invalid email format.';
    }

    if ($newEmail !== '' && $newEmail === $currentEmail) {
        $_SESSION['email_errors'][] = 'Email is unchanged.';
    }

    if (!empty($_SESSION['email_errors'])) {
        if ($service->emailExists($newEmail)) {
            $_SESSION['email_errors'][] = 'Email already registered.';
        }
        $_SESSION['old_email'] = $newEmail;
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid email data'];
        header('Location: ../view/account-settings.php');
        exit;
    }

    if ($service->emailExists($newEmail)) {
        $_SESSION['email_errors'][] = 'Email already registered.';
        $_SESSION['old_email'] = $newEmail;
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Email already registered'];
        header('Location: ../view/account-settings.php');
        exit;
    }

    $updated = $service->changeEmail((int)$_SESSION['user_id'], $newEmail);

    if ($updated) {
        unset($_SESSION['email_errors'], $_SESSION['old_email']);
        $_SESSION['email'] = $newEmail;
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Email updated'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not update email'];
    }

    header('Location: ../view/account-settings.php');
    exit;
}

// ADMIN/SELF: DELETE USER
if (isset($_POST['delete_user'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        header('Location: ../view/login.php');
        exit;
    }

    $targetUserId = (int)($_POST['user_id'] ?? 0);
    $actorUserId = (int)$_SESSION['user_id'];
    $isSelfDelete = $targetUserId === $actorUserId;

    if ($isSelfDelete) {
        $deleted = $service->deleteOwnAccount($actorUserId);

        if ($deleted) {
            $rememberService = new RememberMeService();
            $rememberService->clearUserTokens($actorUserId);
            setcookie('remember_me', '', time() - 3600, '/');
            setcookie('remembered_user', '', time() - 3600, '/');
            unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['is_admin']);
            $_SESSION['flash'] = ['type' => 'success', 'text' => 'Account deleted'];
            header('Location: ../view/login.php');
            exit;
        }

        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not delete account'];
        header('Location: ../view/account-settings.php');
        exit;
    }

    if (empty($_SESSION['is_admin'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Admin access required'];
        header('Location: ../view/account-settings.php');
        exit;
    }

    if ($targetUserId === $actorUserId) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'You cannot delete your own account'];
        header('Location: ../view/account-settings.php');
        exit;
    }

    $deleted = $service->deleteUser($targetUserId, $actorUserId);

    if ($deleted) {
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'User deleted'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not delete user'];
    }

    header('Location: ../view/account-settings.php');
    exit;
}

// LOGIN
if (isset($_GET['login'])) {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $captchaRequired = isLoginCaptchaRequired();

    if ($captchaRequired) {
        $captchaToken = $_POST['g-recaptcha-response'] ?? '';
        $recaptcha = new RecaptchaService($config['recaptcha_secret_key'] ?? '');
        $remoteIp = $_SERVER['REMOTE_ADDR'] ?? null;

        if (!$recaptcha->isConfigured()) {
            incrementLoginAttempts();
            $_SESSION['flash'] = ['type' => 'error', 'text' => 'CAPTCHA not configured. Please contact the administrator.'];
            header('Location: ../view/login.php?error=captcha_required');
            exit;
        }

        if (!$recaptcha->verify($captchaToken, $remoteIp)) {
            incrementLoginAttempts();
            $_SESSION['flash'] = ['type' => 'error', 'text' => 'Complete the CAPTCHA to proceed'];
            header('Location: ../view/login.php?error=captcha_required');
            exit;
        }
    }

    $user = $service->login($identifier, $password);
    if ($user) {
        resetLoginAttempts();
        startSession();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['is_admin'] = $user->isAdmin();

        $rememberMe = !empty($_POST['remember_me']);

        // Remember username and autologin token
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
            setcookie('remembered_user', $user->getUsername(), [
                'expires'  => strtotime($expiresAt),
                'path'     => '/',
                'httponly' => false,
                'samesite' => 'Lax',
                'secure'   => $cookieSecure,
            ]);
        } else {
            setcookie('remember_me', '', time() - 3600, '/');
            setcookie('remembered_user', '', time() - 3600, '/');
        }

        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Login successful'];
        header('Location: ../view/dashboard.php');
        exit;
    }

    incrementLoginAttempts();
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid credentials'];
    header('Location: ../view/login.php?error=invalid_credentials');
    exit;
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
    setcookie('remembered_user', '', time() - 3600, '/');
    session_destroy();
    header('Location: ../view/login.php?message=logged_out');
    exit;
}

// MAKE/REVOKE ADMIN
if (isset($_POST['make_admin']) || isset($_POST['revoke_admin'])) {
    if (empty($_SESSION['is_admin'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Admin access required'];
        header('Location: ../view/account-settings.php');
        exit;
    }
    $targetUserId = (int)($_POST['user_id'] ?? 0);
    if ($targetUserId === (int)($_SESSION['user_id'] ?? 0)) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'You cannot change your own admin status'];
        header('Location: ../view/account-settings.php');
        exit;
    }
    $makeAdmin = isset($_POST['make_admin']);
    $ok = $service->setAdmin($targetUserId, $makeAdmin);
    if ($ok) {
        $_SESSION['flash'] = ['type' => 'success', 'text' => $makeAdmin ? 'User is now admin' : 'Admin rights revoked'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not update admin status'];
    }
    header('Location: ../view/account-settings.php');
    exit;
}
