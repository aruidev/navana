<?php
function getBasePath() {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $navanaPos = strpos($scriptDir, '/navana');
    if ($navanaPos !== false) {
        return substr($scriptDir, 0, $navanaPos + strlen('/navana')) . '/assets/';
    } else {
        $depth = substr_count($scriptDir, '/');
        return str_repeat('../', $depth) . 'assets/';
    }
}

function getAppUrl(): string
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');

    $navanaPos = strpos($scriptDir, '/navana');
    if ($navanaPos !== false) {
        $basePath = substr($scriptDir, 0, $navanaPos + strlen('/navana'));
    } else {
        $basePath = rtrim($scriptDir, '/');
    }

    return $scheme . '://' . $host . $basePath;
}