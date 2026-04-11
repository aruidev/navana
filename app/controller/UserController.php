<?php

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../services/RememberMeService.php';
require_once __DIR__ . '/../services/PasswordResetService.php';
require_once __DIR__ . '/../services/RecaptchaService.php';
require_once __DIR__ . '/../helpers/base_path.php';
require_once __DIR__ . '/../helpers/route_helpers.php';
require_once __DIR__ . '/../model/session.php';
startSession();

$config = config();

$service = new UserService();

// CHANGE USERNAME
if (isset($_POST['change_username'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        redirectToView('login.php');
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
        redirectToView('account-settings.php');
    }

    if ($service->usernameExists($newUsername)) {
        $_SESSION['errors'][] = 'Username already taken.';
        $_SESSION['old_username'] = $newUsername;
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Username already taken'];
        redirectToView('account-settings.php');
    }

    $updated = $service->changeUsername((int) $_SESSION['user_id'], $newUsername);

    if ($updated) {
        unset($_SESSION['errors'], $_SESSION['old_username']);
        $_SESSION['username'] = $newUsername;
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Username updated'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not update username'];
    }

    redirectToView('account-settings.php');
}

// CHANGE EMAIL
if (isset($_POST['change_email'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        redirectToView('login.php');
    }

    $newEmail = trim($_POST['new_email'] ?? '');
    $_SESSION['email_errors'] = [];

    $currentUser = $service->getUserById((int) $_SESSION['user_id']);
    if ($currentUser === null) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'User not found'];
        redirectToView('account-settings.php');
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
        redirectToView('account-settings.php');
    }

    if ($service->emailExists($newEmail)) {
        $_SESSION['email_errors'][] = 'Email already registered.';
        $_SESSION['old_email'] = $newEmail;
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Email already registered'];
        redirectToView('account-settings.php');
    }

    $updated = $service->changeEmail((int) $_SESSION['user_id'], $newEmail);

    if ($updated) {
        unset($_SESSION['email_errors'], $_SESSION['old_email']);
        $_SESSION['email'] = $newEmail;
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Email updated'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not update email'];
    }

    redirectToView('account-settings.php');
}

// CHANGE PASSWORD
if (isset($_POST['change_password'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        redirectToView('login.php');
    }

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $_SESSION['password_errors'] = [];

    $currentUser = $service->getUserById((int) $_SESSION['user_id']);
    if ($currentUser === null) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'User not found'];
        redirectToView('account-settings.php');
    }

    $hasLocalPassword = trim((string) $currentUser->getPasswordHash()) !== '';

    if ($hasLocalPassword && $currentPassword === '') {
        $_SESSION['password_errors'][] = 'Current password is required.';
    }
    if ($newPassword === '') {
        $_SESSION['password_errors'][] = 'New password is required.';
    }
    if ($confirmPassword === '') {
        $_SESSION['password_errors'][] = 'Please confirm the new password.';
    }

    if ($hasLocalPassword && $currentPassword !== '' && !password_verify($currentPassword, $currentUser->getPasswordHash())) {
        $_SESSION['password_errors'][] = 'Current password is incorrect.';
    }

    $_SESSION['password_errors'] = array_merge(
        $_SESSION['password_errors'],
        $service->validatePasswordRules($newPassword, $confirmPassword),
    );

    if (!empty($_SESSION['password_errors'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid password data'];
        redirectToView('account-settings.php');
    }

    $updated = $service->changePassword((int) $_SESSION['user_id'], $newPassword);

    if ($updated) {
        unset($_SESSION['password_errors']);
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Password updated'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not update password'];
    }

    redirectToView('account-settings.php');
}

// ADMIN/SELF: DELETE USER
if (isset($_POST['delete_user'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Login required'];
        redirectToView('login.php');
    }

    $targetUserId = (int) ($_POST['user_id'] ?? 0);
    $actorUserId = (int) $_SESSION['user_id'];
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
            redirectToView('login.php');
        }

        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not delete account'];
        redirectToView('account-settings.php');
    }

    if (empty($_SESSION['is_admin'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Admin access required'];
        redirectToView('account-settings.php');
    }

    if ($targetUserId === $actorUserId) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'You cannot delete your own account'];
        redirectToView('account-settings.php');
    }

    $deleted = $service->deleteUser($targetUserId, $actorUserId);

    if ($deleted) {
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'User deleted'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not delete user'];
    }

    redirectToView('account-settings.php');
}

// FORGOT PASSWORD
if (isset($_GET['forgot'])) {
    if (isset($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'You are already logged in'];
        redirectToView('account-settings.php');
    }

    $email = trim($_POST['email'] ?? '');
    $_SESSION['reset_errors'] = [];

    if ($email === '') {
        $_SESSION['reset_errors'][] = 'Email is required.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_errors'][] = 'Invalid email format.';
    }

    if (!empty($_SESSION['reset_errors'])) {
        $_SESSION['reset_old_email'] = $email;
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid reset request'];
        redirectToView('reset.php');
    }

    $resetService = new PasswordResetService(30);
    $resetService->requestReset($email, getAppUrl());

    unset($_SESSION['reset_errors'], $_SESSION['reset_old_email']);
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'If the email exists, a reset link has been sent.'];
    redirectToView('reset.php', ['message' => 'reset_sent']);
}

// RESET PASSWORD
if (isset($_GET['reset'])) {
    $selector = trim($_POST['selector'] ?? '');
    $validator = trim($_POST['validator'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($selector === '' || $validator === '') {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid or expired reset link'];
        redirectToView('reset.php', ['error' => 'invalid_token']);
    }

    $_SESSION['reset_errors'] = [];
    if ($newPassword === '') {
        $_SESSION['reset_errors'][] = 'New password is required.';
    }
    if ($confirmPassword === '') {
        $_SESSION['reset_errors'][] = 'Please confirm the new password.';
    }

    $_SESSION['reset_errors'] = array_merge(
        $_SESSION['reset_errors'],
        $service->validatePasswordRules($newPassword, $confirmPassword),
    );

    if (!empty($_SESSION['reset_errors'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid password data'];
        redirectToView('reset_confirm.php', ['selector' => $selector, 'validator' => $validator]);
    }

    $resetService = new PasswordResetService(30);
    $resetService->clearExpired();
    $userId = $resetService->consumeToken($selector, $validator);

    if ($userId === null) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid or expired reset link'];
        redirectToView('reset.php', ['error' => 'invalid_token']);
    }

    $updated = $service->changePassword($userId, $newPassword);
    $resetService->clearUserTokens($userId);

    if ($updated) {
        unset($_SESSION['reset_errors']);
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Password updated'];
        redirectToView('login.php', ['message' => 'password_reset']);
    }

    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not update password'];
    redirectToView('reset_confirm.php', ['selector' => $selector, 'validator' => $validator]);
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
            redirectToView('login.php', ['error' => 'captcha_required']);
        }

        if (!$recaptcha->verify($captchaToken, $remoteIp)) {
            incrementLoginAttempts();
            $_SESSION['flash'] = ['type' => 'error', 'text' => 'Complete the CAPTCHA to proceed'];
            redirectToView('login.php', ['error' => 'captcha_required']);
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
        redirectToView('library.php');
    }

    incrementLoginAttempts();
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid credentials'];
    redirectToView('login.php', ['error' => 'invalid_credentials']);
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
        'password2' => $password2,
    ]);

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        // Preserve register form data
        $_SESSION['old'] = [
            'username' => $username,
            'email' => $email,
        ];
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Invalid registration data'];
        redirectToView('register.php');
    }

    $success = $service->register($username, $email, $password);
    if ($success) {
        unset($_SESSION['errors']);
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Registration successful'];
        redirectToView('login.php', ['message' => 'registration_successful']);
    }
    $_SESSION['flash'] = ['type' => 'error', 'text' => 'Registration failed'];
    $_SESSION['errors'][] = 'Registration failed. Please try again.';
    redirectToView('register.php');
}

// LOGOUT
if (isset($_GET['logout'])) {
    startSession();
    session_unset();
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Logged out successfully'];
    $rememberService = new RememberMeService();
    if (isset($_SESSION['user_id'])) {
        $rememberService->clearUserTokens((int) $_SESSION['user_id']);
    }
    setcookie('remember_me', '', time() - 3600, '/');
    setcookie('remembered_user', '', time() - 3600, '/');
    session_destroy();
    redirectToView('login.php', ['message' => 'logged_out']);
}

// MAKE/REVOKE ADMIN
if (isset($_POST['make_admin']) || isset($_POST['revoke_admin'])) {
    if (empty($_SESSION['is_admin'])) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Admin access required'];
        redirectToView('account-settings.php');
    }
    $targetUserId = (int) ($_POST['user_id'] ?? 0);
    if ($targetUserId === (int) ($_SESSION['user_id'] ?? 0)) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'You cannot change your own admin status'];
        redirectToView('account-settings.php');
    }
    $makeAdmin = isset($_POST['make_admin']);
    $ok = $service->setAdmin($targetUserId, $makeAdmin);
    if ($ok) {
        $_SESSION['flash'] = ['type' => 'success', 'text' => $makeAdmin ? 'User is now admin' : 'Admin rights revoked'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'text' => 'Could not update admin status'];
    }
    redirectToView('account-settings.php');
}
