<?php

declare(strict_types=1);
require_once __DIR__ . '/app/helpers/routes.php';

/**
 * Application front-controller entry point.
 *
 * Resolves the route from the request and dispatches to the mapped
 * view/controller file declared in app/helpers/routes.php.
 */
$route = trim((string) ($_GET['route'] ?? ''), "/ \t\n\r\0\x0B");

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
