<?php

declare(strict_types=1);

require_once __DIR__ . '/base_path.php';
require_once __DIR__ . '/routes.php';

/**
 * Return the application base path.
 */
function getAppBasePath(): string {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $navanaPos = strpos($scriptDir, '/navana');

    if ($navanaPos !== false) {
        return rtrim(substr($scriptDir, 0, $navanaPos + strlen('/navana')), '/');
    }

    return rtrim($scriptDir, '/');
}

/**
 * Build an internal URL path to a front-controller route.
 *
 * @param array<string, scalar|null> $query
 */
function buildRouteUrl(string $route, array $query = []): string {
    $basePath = getAppBasePath();
    $normalizedRoute = trim($route, '/');
    $url = $normalizedRoute === ''
        ? ($basePath === '' ? '/' : $basePath . '/')
        : ($basePath === '' ? '' : $basePath) . '/' . $normalizedRoute;

    if ($query === []) {
        return $url;
    }

    $queryString = http_build_query($query);
    return $queryString === '' ? $url : $url . '?' . $queryString;
}

/**
 * Build an internal URL path to a view file under app/view.
 *
 * @param array<string, scalar|null> $query
 */
function buildViewUrl(string $viewFile, array $query = []): string {
    $view = ltrim($viewFile, '/');
    $viewRouteMap = navanaViewFileToRouteMap();

    if (isset($viewRouteMap[$view])) {
        return buildRouteUrl($viewRouteMap[$view], $query);
    }

    $basePath = getAppBasePath();
    $url = ($basePath === '' ? '' : $basePath) . '/app/view/' . $view;

    if ($query === []) {
        return $url;
    }

    $queryString = http_build_query($query);
    return $queryString === '' ? $url : $url . '?' . $queryString;
}

/**
 * Build an internal URL path to a controller file under app/controller.
 *
 * @param array<string, scalar|null> $query
 */
function buildControllerUrl(string $controllerFile, array $query = []): string {
    $controller = ltrim($controllerFile, '/');
    $controllerRouteMap = navanaControllerFileToRouteMap();

    if (isset($controllerRouteMap[$controller])) {
        return buildRouteUrl($controllerRouteMap[$controller], $query);
    }

    $basePath = getAppBasePath();
    $url = ($basePath === '' ? '' : $basePath) . '/app/controller/' . $controller;

    if ($query === []) {
        return $url;
    }

    $queryString = http_build_query($query);
    return $queryString === '' ? $url : $url . '?' . $queryString;
}

/**
 * Redirect to a view file under app/view.
 *
 * @param array<string, scalar|null> $query
 */
function redirectToView(string $viewFile, array $query = []): void {
    header('Location: ' . buildViewUrl($viewFile, $query));
    exit;
}

/**
 * Redirect to an already built URL.
 */
function redirectToUrl(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Resolve a redirect parameter into a safe internal URL.
 */
function resolveRedirectUrl(?string $redirect, string $fallbackView = 'explore.php'): string {
    $raw = trim((string) $redirect);
    if ($raw === '') {
        return buildViewUrl($fallbackView);
    }

    $decoded = urldecode($raw);
    if (strpos($decoded, "\n") !== false || strpos($decoded, "\r") !== false) {
        return buildViewUrl($fallbackView);
    }

    $parts = parse_url($decoded);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return buildViewUrl($fallbackView);
    }

    $path = (string) ($parts['path'] ?? '');
    $query = (string) ($parts['query'] ?? '');

    $basePath = getAppBasePath();
    $appPath = trim($basePath, '/');
    $routePrefix = $appPath === '' ? '/' : '/' . $appPath . '/';

    if ($path === '' || strpos($path, '/app/view/') === 0) {
        $base = $basePath === '' ? '' : $basePath;
        $internalPath = $path === '' ? ($base === '' ? '/' : $base . '/') : $base . $path;
        return $internalPath . ($query !== '' ? '?' . $query : '');
    }

    if ($path !== '' && strpos($path, $routePrefix) === 0) {
        $route = trim(substr($path, strlen($routePrefix)), '/');
        if ($route !== '' && preg_match('/^[a-zA-Z0-9_\/-]+$/', $route) === 1) {
            return buildRouteUrl($route) . ($query !== '' ? '?' . $query : '');
        }
    }

    if ($path !== '' && strpos($path, '/app/view/') !== 0 && preg_match('/^\/[a-zA-Z0-9_\/-]+$/', $path) === 1) {
        return buildRouteUrl(trim($path, '/')) . ($query !== '' ? '?' . $query : '');
    }

    $normalizedView = '';
    if (strpos($path, '../view/') === 0) {
        $normalizedView = substr($path, strlen('../view/'));
    } elseif (strpos($path, '../../view/') === 0) {
        $normalizedView = substr($path, strlen('../../view/'));
    } elseif (strpos($path, 'app/view/') === 0) {
        $normalizedView = substr($path, strlen('app/view/'));
    } elseif (strpos($path, '/app/view/') === 0) {
        $normalizedView = substr($path, strlen('/app/view/'));
    } elseif (strpos($path, '/') === false && str_ends_with($path, '.php')) {
        $normalizedView = $path;
    }

    if ($normalizedView === '' || strpos($normalizedView, '..') !== false) {
        return buildViewUrl($fallbackView);
    }

    $url = buildViewUrl($normalizedView);
    return $query !== '' ? $url . '?' . $query : $url;
}
