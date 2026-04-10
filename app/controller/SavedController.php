<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/SavedItemService.php';
require_once __DIR__ . '/../model/session.php';
require_once __DIR__ . '/../helpers/route_helpers.php';

startSession();

$service = new SavedItemService();

$action = isset($_GET['action']) ? (string) $_GET['action'] : '';
$itemId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$redirect = resolveRedirectUrl(isset($_GET['redirect']) ? (string) $_GET['redirect'] : null, 'explore.php');

if (!isset($_SESSION['user_id'])) {
    redirectToView('login.php');
}

if ($itemId <= 0) {
    redirectToUrl($redirect);
}

if ($action === 'save') {
    $service->saveItem((int) $_SESSION['user_id'], $itemId);
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item saved'];
} elseif ($action === 'unsave') {
    $service->unsaveItem((int) $_SESSION['user_id'], $itemId);
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item removed from saved'];
}

redirectToUrl($redirect);
