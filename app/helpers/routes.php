<?php

declare(strict_types=1);

/**
 * Return the canonical route table.
 *
 * @return array{
 *   view: array<string, string>,
 *   controller: array<string, string>
 * }
 */
function navanaRoutes(): array {
    return [
        'view' => [
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
        ],
        'controller' => [
            'auth/google' => 'app/controller/auth/google.php',
            'auth/github' => 'app/controller/auth/github.php',
            'user' => 'app/controller/UserController.php',
            'item-action' => 'app/controller/ItemController.php',
            'saved-action' => 'app/controller/SavedController.php',
        ],
    ];
}

/**
 * @return array<string, string>
 */
function navanaViewFileToRouteMap(): array {
    return [
        'home.php' => 'home',
        'explore.php' => 'explore',
        'library.php' => 'library',
        'saved.php' => 'saved',
        'form_insert.php' => 'add',
        'form_view.php' => 'item',
        'form_update.php' => 'item/edit',
        'login.php' => 'login',
        'register.php' => 'register',
        'account-settings.php' => 'account',
        'terms.php' => 'terms',
        'reset.php' => 'reset',
        'reset_confirm.php' => 'reset/confirm',
        'error401.php' => 'error401',
        'error404.php' => 'error404',
    ];
}

/**
 * @return array<string, string>
 */
function navanaControllerFileToRouteMap(): array {
    return [
        'UserController.php' => 'user',
        'ItemController.php' => 'item-action',
        'SavedController.php' => 'saved-action',
        'auth/google.php' => 'auth/google',
        'auth/github.php' => 'auth/github',
    ];
}

/**
 * @return array<string, string>
 */
function navanaPrimaryNavRoutes(): array {
    return [
        'home' => 'Home',
        'explore' => 'Explore',
        'library' => 'Library',
        'saved' => 'Saved',
    ];
}

function navanaCurrentRouteFromScript(string $scriptName): string {
    $file = basename($scriptName);
    $map = navanaViewFileToRouteMap();
    return $map[$file] ?? '';
}
