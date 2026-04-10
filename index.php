<?php

declare(strict_types=1);
require_once __DIR__ . '/app/helpers/routes.php';

/**
 * Transitional front-controller.
 *
 * During migration, this keeps legacy file-based execution and provides a
 * centralized route entry point via ?route=... rewrites.
 */
$route = isset($_GET['route']) ? trim((string) $_GET['route']) : '';
$route = trim($route, "/ \t\n\r\0\x0B");

$routes = navanaRoutes();
$viewRoutes = $routes['view'];
$controllerRoutes = $routes['controller'];

if (isset($viewRoutes[$route])) {
	$target = $viewRoutes[$route];
	$GLOBALS['navana_route'] = $route;
	unset($_GET['route']);
	require __DIR__ . '/' . $target;
	exit;
}

if (isset($controllerRoutes[$route])) {
	$target = $controllerRoutes[$route];
	$GLOBALS['navana_route'] = $route;
	unset($_GET['route']);
	require __DIR__ . '/' . $target;
	exit;
}

http_response_code(404);
require __DIR__ . '/app/view/error404.php';
