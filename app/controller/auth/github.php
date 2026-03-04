<?php

/*

if (session_status() == PHP_SESSION_NONE) {  //XMM_2024
    session_start();
}

require_once __DIR__ . '/../../../bootstrap.php';

if (isset($_SESSION['userId'])) {
    header('Location: login.php');
    return;
}

// Hybridauth autoloader
require_once '../lib/hybridauth/src/autoload.php';
require_once '../controller/social-auth-common.php';

// Configuració pel nostre provider

$appConfig = config();

$config = [
    'callback' => (string) ($appConfig['hybrid_auth_github_callback_uri'] ?? ''),
    'keys' => [
        'id' => (string) ($appConfig['hybrid_auth_github_client_id'] ?? ''),
        'secret' => (string) ($appConfig['hybrid_auth_github_client_secret'] ?? ''),
    ]
];

try {
    $github = new HybridauthProviderGitHub($config);

    // Demanem a l'usuari que s'autentiqui amb GitHub
    $github->authenticate();

    // Obtenim el token d'accés
    $accessToken = $github->getAccessToken();

    $userProfile = $github->getUserProfile(); // obtenim l'user profile, que conté diversa informació de l'usuari
    $displayName = $userProfile->displayName; // obtenim el nickname
    $email = $userProfile->email; // i el correu

    // Registrem l'usuari si no ho està i el loguem
    loginSocialProviderUser($email, $displayName, "GitHub");

    // Tanquem el popup i actualitzem la finestra mare
    closeWindowAndReloadParent();
} catch (Exception $e) {
    echo $e->getMessage();
}

*/
