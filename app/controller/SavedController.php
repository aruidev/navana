<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/services/SavedItemService.php';
require_once __DIR__ . '/../model/session.php';

startSession();

$service = new SavedItemService();

$action = isset($_GET['action']) ? (string)$_GET['action'] : '';
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$redirect = isset($_GET['redirect']) ? urldecode((string)$_GET['redirect']) : '../view/explore.php';
$redirect = trim($redirect);
if ($redirect === '' || strpos($redirect, '://') !== false || strpos($redirect, "\n") !== false || strpos($redirect, "\r") !== false) {
    $redirect = '../view/explore.php';
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../view/login.php');
    exit;
}

if ($itemId <= 0) {
    header('Location: ' . $redirect);
    exit;
}

if ($action === 'save') {
    $service->saveItem((int)$_SESSION['user_id'], $itemId);
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item saved'];
} elseif ($action === 'unsave') {
    $service->unsaveItem((int)$_SESSION['user_id'], $itemId);
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item removed from saved'];
}

header('Location: ' . $redirect);
exit;
?>