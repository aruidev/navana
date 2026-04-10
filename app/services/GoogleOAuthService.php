<?php

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../vendor/autoload.php';

class GoogleOAuthService {
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct() {
        $appConfig = config();
        $this->clientId = (string) ($appConfig['oauth_google_client_id'] ?? '');
        $this->clientSecret = (string) ($appConfig['oauth_google_client_secret'] ?? '');
        $this->redirectUri = (string) ($appConfig['oauth_google_redirect_uri'] ?? '');
    }

    /**
     * Check if the Google OAuth service is properly configured.
     * @return bool True if configured, false otherwise
     */
    public function isConfigured(): bool {
        return $this->clientId !== ''
            && $this->clientSecret !== ''
            && $this->redirectUri !== '';
    }

    /**
     * Get the Google OAuth authorization URL to redirect the user for login/registration.
     * @param string $state A unique state parameter to prevent CSRF attacks
     */
    public function getAuthorizationUrl(string $state): string {
        $client = $this->buildClient();
        $client->setState($state);

        return $client->createAuthUrl();
    }

    /**
     * Exchange the authorization code for an access token and retrieve the user's profile information.
     * @param string $code The authorization code received from Google after user consent
     */
    public function getUserProfileFromCode(string $code): ?array {
        $client = $this->buildClient();

        $token = $client->fetchAccessTokenWithAuthCode($code);
        if (!is_array($token) || isset($token['error']) || empty($token['access_token'])) {
            return null;
        }

        $client->setAccessToken($token['access_token']);
        $googleOauth = new Google_Service_Oauth2($client);
        $googleAccount = $googleOauth->userinfo->get();

        $sub = trim((string) ($googleAccount->id ?? ''));
        $email = strtolower(trim((string) ($googleAccount->email ?? '')));
        $name = trim((string) ($googleAccount->name ?? ''));

        if ($sub === '' || $email === '') {
            return null;
        }

        if ($name === '') {
            $name = strstr($email, '@', true) ?: 'user';
        }

        return [
            'provider' => 'google',
            'provider_user_id' => $sub,
            'email' => $email,
            'name' => $name,
        ];
    }

    /**
     * Build and configure the Google_Client instance for OAuth operations.
     * @return Google_Client Configured Google_Client instance
     */
    private function buildClient(): Google_Client {
        $client = new Google_Client();
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri($this->redirectUri);
        $client->setAccessType('online');
        $client->setPrompt('select_account');
        $client->addScope('openid');
        $client->addScope('email');
        $client->addScope('profile');

        return $client;
    }
}
