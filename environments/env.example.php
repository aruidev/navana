<?php

declare(strict_types=1);

return [
    'app_name' => 'APP_NAME',
    // Database configuration
    'db_host' => 'LOCALHOST',
    'db_name' => 'DB_NAME',
    'db_user' => 'DB_USER',
    'db_pass' => 'DB_PASS',
    'db_charset' => 'utf8mb4',
    'db_schema_path' => 'db_schema/Pt05_Alex_Ruiz.sql',
    'db_data_seed_path' => 'db_schema/test_data.sql',
    // Cookie security setting
    'cookie_secure' => false, // dev (HTTP)
    // SMTP (Gmail)
    'smtp_host' => 'SMTP_HOST',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',
    'smtp_user' => 'SMTP_USER',
    'smtp_pass' => 'SMTP_PASS',
    'smtp_from_email' => 'SMTP_FROM_EMAIL',
    'smtp_from_name' => 'SMTP_FROM_NAME',
    // reCAPTCHA v2
    'recaptcha_site_key' => 'RECAPTCHA_SITE_KEY',
    'recaptcha_secret_key' => 'RECAPTCHA_SECRET_KEY',
    // OAuth credentials
    'oauth_google_client_id' => 'GOOGLE_CLIENT_ID',
    'oauth_google_client_secret' => 'GOOGLE_CLIENT_SECRET',
    'oauth_google_redirect_uri' => 'http://localhost/oauth/google.php',
    // Hybrid authentication credentials
    'hybrid_auth_github_client_id' => 'GITHUB_CLIENT_ID',
    'hybrid_auth_github_client_secret' => 'GITHUB_CLIENT_SECRET',
    'hybrid_auth_github_callback_uri' => 'http://localhost/oauth/github.php',
];
