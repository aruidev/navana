<?php

if (session_status() == PHP_SESSION_NONE) {  //XMM_2024
    session_start();
}

require_once __DIR__ . '/../../../bootstrap.php';

require_once "../model/pdo-users.php";
require_once "../controller/session.php";
require_once "../controller/social-auth-common.php";
require_once '../vendor/autoload.php';  //XMM_2024

if (isset($_SESSION['userId'])) {
    header('Location: login.php');
    return;
}

// init configuration
$appConfig = config();
$clientID = (string) ($appConfig['oauth_google_client_id'] ?? '');
$clientSecret = (string) ($appConfig['oauth_google_client_secret'] ?? '');
$redirectUri = (string) ($appConfig['oauth_google_redirect_uri'] ?? '');

/***
 * 	// create Client Request to access Google API
	$client = new Google_Client();
	$client->setClientId($clientID);
	$client->setClientSecret($clientSecret);
	$client->setRedirectUri($redirectUri);
	$client->addScope("email");
	$client->addScope("profile");
 * 
 */

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// authenticate code from Google OAuth Flow

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    // get profile info
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $email =  $google_account_info->email;
    $name =  $google_account_info->name;

    $_SESSION['usuari'] = [
        "name" => $name,
        "email" => $email,
        "accessType" => "Google"
    ];
    header('Location: contingut.php');
    die();
}
