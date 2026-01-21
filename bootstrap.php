<?php
declare(strict_types=1);

if (!defined('ENV_NAME')) {
    define('ENV_NAME', 'env.local.php');
}

function config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $path = __DIR__ . '/environments/' . ENV_NAME;
    if (!is_file($path)) {
        throw new RuntimeException('Config file not found: ' . $path);
    }

    $loaded = require $path;
    if (!is_array($loaded)) {
        throw new RuntimeException('Config file must return an array: ' . $path);
    }

    $config = $loaded;
    return $config;
}
