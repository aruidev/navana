<?php

require_once __DIR__ . '/../services/ItemService.php';
require_once __DIR__ . '/../model/session.php';
require_once __DIR__ . '/../helpers/route_helpers.php';
startSession();

$service = new ItemService();

// INSERT
if (isset($_POST['insert'])) {
    if (!isset($_SESSION['user_id'])) {
        redirectToView('login.php');
    }
    $result = $service->insertItemWithSafetyCheck(
        $_POST['title'],
        $_POST['description'],
        $_POST['link'],
        $_SESSION['user_id'],
        $_POST['tag'] ?? null,
    );
    if (!$result['success']) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => $result['message']];
        redirectToView('form_insert.php');
    }

    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item created'];
    if ($result['id']) {
        redirectToView('form_view.php', ['id' => (int) $result['id']]);
    } else {
        redirectToView('library.php');
    }
}

// UPDATE
if (isset($_POST['update'])) {
    $result = $service->updateItemWithSafetyCheck(
        $_POST['id'],
        $_POST['title'],
        $_POST['description'],
        $_POST['link'],
        $_POST['tag'] ?? null,
    );
    if (!$result['success']) {
        $_SESSION['flash'] = ['type' => 'error', 'text' => $result['message']];
        redirectToView('form_update.php', ['id' => (int) $_POST['id']]);
    }

    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item updated'];
    redirectToView('form_view.php', ['id' => (int) $_POST['id']]);
}

// DELETE
if (isset($_GET['delete'])) {
    $service->deleteItem($_GET['delete']);
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item deleted'];
    redirectToView('library.php');
}
