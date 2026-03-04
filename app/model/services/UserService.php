<?php

require_once __DIR__ . '/../dao/UserDAO.php';
require_once __DIR__ . '/../dao/UserOAuthAccountDAO.php';
require_once __DIR__ . '/../entities/User.php';

class UserService {
    private $dao;
    private $oauthDao;

    /**
     * Constructor to initialize the DAO
     * @return void
     */
    public function __construct() {
        $this->dao = new UserDAO();
        $this->oauthDao = new UserOAuthAccountDAO();
    }

    /**
     * Register a new user
     * @param string $username Username of the user
     * @param string $email Email of the user
     * @param string $password Plain text password of the user
     * @return bool Returns true on successful registration, false otherwise
     */
    public function register($username, $email, $password) {
        // Hash the password before storing
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $user = new User(null, $username, $email, $password_hash, false);
        return $this->dao->create($user);
    }

    /**
     * Login a user
     * @param string $usernameOrEmail Username or email of the user
     * @param string $password Plain text password of the user
     * @return User|null Returns the User object if credentials are valid, null otherwise
     */
    public function login($usernameOrEmail, $password) {
        return $this->dao->verifyCredentials($usernameOrEmail, $password);
    }

    public function isAdmin($user) {
        return $user !== null && $user->isAdmin();
    }

    /**
     * Check if a username already exists
     * @param string $username Username to check
     * @return bool Returns true if username exists, false otherwise
     */
    public function usernameExists($username) {
        $username = strtolower(trim($username));
        return $this->dao->existsByUsername($username);
    }

    /**
     * Check if an email already exists
     * @param string $email Email to check
     * @return bool Returns true if email exists, false otherwise
     */
    public function emailExists($email) {
        $email = strtolower(trim($email));
        return $this->dao->existsByEmail($email);
    }

    /**
     * Validate registration data
     * @param array $data Associative array containing 'username', 'email', 'password', 'password2'
     * @return array Returns an array of error messages, empty if no errors
     */
    public function validateRegister($data) {
        $errors = [];

        // Check for empty fields
        if ($data['username'] === '' || $data['email'] === '' || $data['password'] === '') {
            $errors[] = 'All fields are required.';
        }

        // Check if username or email already exists
        if ($this->usernameExists($data['username'])) {
            $errors[] = 'Username already taken.';
        }
        if ($this->emailExists($data['email'])) {
            $errors[] = 'Email already registered.';
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        // Password validations
        $errors = array_merge($errors, $this->validatePasswordRules($data['password'], $data['password2']));

        return $errors;
    }

    /**
     * Validate password rules reused by register and change password.
     * @param string $password New password
     * @param string $password2 Confirmation password
     * @return array List of validation error messages
     */
    public function validatePasswordRules($password, $password2) {
        $errors = [];

        if ($password !== $password2) {
            $errors[] = "Passwords don't match.";
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        if (preg_match('/^[a-zA-Z0-9]+$/', $password)) {
            $errors[] = 'Password must include at least one lowercase letter, one uppercase letter, and one number.';
        }

        return $errors;
    }

    /**
     * Change username for a user
     * @param int $userId ID of the user
     * @param string $newUsername New username to set
     * @return bool Returns true on success, false on failure
     */
    public function changeUsername($userId, $newUsername) {
        $newUsername = trim($newUsername);
        if ($this->usernameExists($newUsername)) {
            return false; // Username already taken
        }
        return $this->dao->updateUsername($userId, $newUsername);
    }

    /**
     * Change email for a user
     * @param int $userId ID of the user
     * @param string $newEmail New email to set
     * @return bool Returns true on success, false on failure
     */
    public function changeEmail($userId, $newEmail) {
        $newEmail = trim($newEmail);
        if ($this->emailExists($newEmail)) {
            return false; // Email already registered
        }
        return $this->dao->updateEmail($userId, $newEmail);
    }

    public function getUserById($userId) {
        return $this->dao->findById($userId);
    }

    public function getAllUsers() {
        return $this->dao->findAll();
    }

    /**
     * Delete a user by ID
     * @param int $targetUserId ID of the user to delete
     * @param int $actorUserId ID of the user performing the deletion
     * @return bool True on success, false on failure
     */
    public function deleteUser($targetUserId, $actorUserId) {
        $actor = $this->dao->findById($actorUserId);
        if (!$this->isAdmin($actor)) {
            return false;
        }

        if ($targetUserId === $actorUserId) {
            return false; // Prevent self-delete
        }

        $target = $this->dao->findById($targetUserId);
        if ($target === null) {
            return false;
        }

        return $this->dao->deleteById($targetUserId);
    }

    /**
     * Delete own account
     * @param int $userId ID of the user to delete
     * @return bool True on success, false on failure
     */
    public function deleteOwnAccount(int $userId): bool {
        $user = $this->dao->findById($userId);
        if ($user === null) {
            return false;
        }

        return $this->dao->deleteById($userId);
    }

    /**
     * Change password for a user.
     * @param int $userId ID of the user
     * @param string $newPassword New password in plain text
     * @return bool True on success, false on failure
     */
    public function changePassword(int $userId, string $newPassword): bool {
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->dao->updatePassword($userId, $passwordHash);
    }

    /**
     * Set or unset admin rights for a user
     * @param int $userId ID of the user to modify
     * @param bool $isAdmin Whether to set or unset admin rights
     * @return bool True on success, false on failure
     */
    public function setAdmin($userId, $isAdmin) {
        return $this->dao->setAdmin($userId, $isAdmin);
    }

    /**
     * Login or register a user using Google profile data.
     * @param array $googleProfile ['provider_user_id' => string, 'email' => string, 'name' => string]
     * @return User|null
     */
    public function loginWithGoogle(array $googleProfile) {
        $providerUserId = trim((string) ($googleProfile['provider_user_id'] ?? ''));
        $email = strtolower(trim((string) ($googleProfile['email'] ?? '')));
        $name = trim((string) ($googleProfile['name'] ?? ''));

        if ($providerUserId === '' || $email === '') {
            return null;
        }

        $existingLink = $this->oauthDao->findByProviderUserId('google', $providerUserId);
        if ($existingLink !== null) {
            return $this->dao->findById((int) $existingLink['user_id']);
        }

        $user = $this->dao->findByEmail($email);
        if ($user !== null) {
            $linked = $this->linkGoogleAccount((int) $user->getId(), $googleProfile);
            return $linked ? $user : null;
        }

        $baseName = $name !== '' ? $name : (strstr($email, '@', true) ?: 'user');
        $username = $this->generateUniqueUsername($baseName);

        $created = $this->dao->create(new User(null, $username, $email, '', false));
        if (!$created) {
            return null;
        }

        $newUser = $this->dao->findByEmail($email);
        if ($newUser === null) {
            return null;
        }

        $linked = $this->linkGoogleAccount((int) $newUser->getId(), $googleProfile);
        return $linked ? $newUser : null;
    }

    /**
     * Link current account with a Google identity.
     */
    public function linkGoogleAccount(int $userId, array $googleProfile): bool {
        $providerUserId = trim((string) ($googleProfile['provider_user_id'] ?? ''));
        $email = strtolower(trim((string) ($googleProfile['email'] ?? '')));

        if ($providerUserId === '') {
            return false;
        }

        $existingIdentity = $this->oauthDao->findByProviderUserId('google', $providerUserId);
        if ($existingIdentity !== null && (int) $existingIdentity['user_id'] !== $userId) {
            return false;
        }

        $existingUserLink = $this->oauthDao->findByUserAndProvider($userId, 'google');
        if ($existingUserLink !== null) {
            return $this->oauthDao->updateLinkData((int) $existingUserLink['id'], $providerUserId, $email !== '' ? $email : null);
        }

        return $this->oauthDao->create($userId, 'google', $providerUserId, $email !== '' ? $email : null);
    }

    /**
     * Unlink Google from the account. Only allowed when local password exists.
     */
    public function unlinkGoogleAccount(int $userId): bool {
        if (!$this->hasGoogleLinked($userId)) {
            return false;
        }

        if (!$this->hasLocalPassword($userId)) {
            return false;
        }

        return $this->oauthDao->deleteByUserAndProvider($userId, 'google');
    }

    public function hasGoogleLinked(int $userId): bool {
        return $this->oauthDao->findByUserAndProvider($userId, 'google') !== null;
    }

    public function canUnlinkGoogle(int $userId): bool {
        return $this->hasGoogleLinked($userId) && $this->hasLocalPassword($userId);
    }

    private function hasLocalPassword(int $userId): bool {
        $user = $this->dao->findById($userId);
        if ($user === null) {
            return false;
        }

        return trim((string) $user->getPasswordHash()) !== '';
    }

    private function generateUniqueUsername(string $rawName): string {
        $base = strtolower(trim($rawName));
        $base = preg_replace('/[^a-z0-9_]+/i', '_', $base);
        $base = trim((string) $base, '_');

        if ($base === '') {
            $base = 'user';
        }

        $candidate = $base;
        $suffix = 1;
        while ($this->usernameExists($candidate)) {
            $candidate = $base . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
