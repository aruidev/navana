<?php

declare(strict_types=1);

require_once __DIR__ . '/base_path.php';

/**
 * Build an absolute URL to a view file under app/view.
 *
 * @param array<string, scalar|null> $query
 */
function buildViewUrl(string $viewFile, array $query = []): string {
    $base = rtrim(getAppUrl(), '/');
    $view = ltrim($viewFile, '/');
    $url = $base . '/app/view/' . $view;

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
 * Resolve a legacy redirect parameter into a safe internal URL.
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

    if ($path === '' || strpos($path, '/app/view/') === 0) {
        $base = rtrim(getAppUrl(), '/');
        return $base . $path . ($query !== '' ? '?' . $query : '');
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
