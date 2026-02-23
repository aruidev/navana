<?php

if (session_status() == PHP_SESSION_NONE) {  //XMM_2024
    session_start();
}
if (isset($_SESSION['userId'])) {
    header('Location: login.php');
    return;
}

// Hybridauth autoloader
require_once '../lib/hybridauth/src/autoload.php';
require_once '../controller/social-auth-common.php';

// Configuració pel nostre provider


$config = [
    //'callback' => 'http://localhost/.../oauth/github.php',
    'callback' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/oauth/github.php', //XMM_2024: ruta dinàmica per al callback per quan funcioni correctament el OAuth
    'keys' => [
        'id' => 'Iv23liMag0QY2GVm9oiM',
        'secret' => '69c2007827e514bf85dbd18e913ece49afa950f4',
    ]
];
echo 'Callback URL: ' . $config['callback'];

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
