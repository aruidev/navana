<?php

declare(strict_types=1);

/**
 * Transitional front-controller.
 *
 * During migration, this keeps legacy file-based execution and provides a
 * centralized route entry point via ?route=... rewrites.
 */
$route = isset($_GET['route']) ? trim((string) $_GET['route']) : '';
$route = trim($route, "/ \t\n\r\0\x0B");

$viewRoutes = [
	'' => 'app/view/home.php',
	'home' => 'app/view/home.php',
	'inici' => 'app/view/home.php',
	'explore' => 'app/view/explore.php',
	'library' => 'app/view/library.php',
	'saved' => 'app/view/saved.php',
	'add' => 'app/view/form_insert.php',
	'item' => 'app/view/form_view.php',
	'item/edit' => 'app/view/form_update.php',
	'login' => 'app/view/login.php',
	'register' => 'app/view/register.php',
	'account' => 'app/view/account-settings.php',
	'terms' => 'app/view/terms.php',
	'reset' => 'app/view/reset.php',
	'reset/confirm' => 'app/view/reset_confirm.php',
	'error401' => 'app/view/error401.php',
	'error404' => 'app/view/error404.php',
];

$controllerRoutes = [
	'auth/google' => 'app/controller/auth/google.php',
	'auth/github' => 'app/controller/auth/github.php',
	'user' => 'app/controller/UserController.php',
	'item-action' => 'app/controller/ItemController.php',
	'saved-action' => 'app/controller/SavedController.php',
];

if (isset($viewRoutes[$route])) {
	$target = $viewRoutes[$route];
	$GLOBALS['navana_route'] = $route;
	unset($_GET['route']);
	require __DIR__ . '/' . $target;
	exit;
}

if (isset($controllerRoutes[$route])) {
	$target = $controllerRoutes[$route];
	$query = $_GET;
	unset($query['route']);
	$queryString = http_build_query($query);

	// Preserve method/body for forms if controller routes are used.
	header('Location: ' . $target . ($queryString !== '' ? '?' . $queryString : ''), true, 307);
	exit;
}

http_response_code(404);
require __DIR__ . '/app/view/error404.php';
