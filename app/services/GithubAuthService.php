<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Hybridauth\Provider\GitHub;

class GithubAuthService {
    private string $clientId;
    private string $clientSecret;
    private string $callbackUri;

    public function __construct() {
        $appConfig = config();
        $this->clientId = (string) ($appConfig['hybrid_auth_github_client_id'] ?? '');
        $this->clientSecret = (string) ($appConfig['hybrid_auth_github_client_secret'] ?? '');
        $this->callbackUri = (string) ($appConfig['hybrid_auth_github_callback_uri'] ?? '');
    }

    /**
     * Check if the GitHub HybridAuth service is properly configured.
     * @return bool True if configured, false otherwise
     */
    public function isConfigured(): bool {
        return $this->clientId !== ''
            && $this->clientSecret !== ''
            && $this->callbackUri !== '';
    }

    /**
     * Start the GitHub authentication process by redirecting the user to the GitHub authorization page.
     */
    public function startAuthentication(): void {
        $github = $this->buildProvider();
        $github->authenticate();
    }

    /**
     * Handle the GitHub authentication callback, exchange the authorization code for an access token, and retrieve the user's profile information.
     * @return array|null Associative array with 'provider', 'provider_user_id', 'email', and 'name' keys on success, or null on failure
     */
    public function getUserProfileFromCallback(): ?array {
        $github = $this->buildProvider();
        $github->authenticate();

        $profile = $github->getUserProfile();

        $providerUserId = trim((string) ($profile->identifier ?? ''));
        $email = strtolower(trim((string) ($profile->email ?? '')));
        $name = trim((string) ($profile->displayName ?? ''));

        if ($name === '') {
            $name = trim((string) ($profile->firstName ?? ''));
        }

        if ($name === '') {
            $name = trim((string) ($profile->lastName ?? ''));
        }

        if ($name === '') {
            $name = trim((string) ($profile->profileURL ?? ''));
        }

        if ($providerUserId === '') {
            return null;
        }

        return [
            'provider' => 'github',
            'provider_user_id' => $providerUserId,
            'email' => $email,
            'name' => $name,
        ];
    }

    /**
     * Build and return the GitHub provider instance.
     * @return array Associative array of the GitHub provider configuration
     */
    private function buildConfig(): array {
        return [
            'callback' => $this->callbackUri,
            'keys' => [
                'id' => $this->clientId,
                'secret' => $this->clientSecret,
            ],
            'scope' => 'user:email',
        ];
    }

    /**
     * Build and return the GitHub provider instance.
     * @return GitHub Configured GitHub provider instance
     */
    private function buildProvider(): GitHub {
        return new GitHub($this->buildConfig());
    }
}
