<?php
require_once __DIR__ . '/../model/services/ItemService.php';
require_once __DIR__ . '/../model/session.php';
startSession();

$service = new ItemService();

// INSERT
if (isset($_POST['insert'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../view/login.php');
        exit;
    }
    $newId = $service->insertItem($_POST['title'], $_POST['description'], $_POST['link'], $_SESSION['user_id'], $_POST['tag'] ?? null);
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item created'];
    if ($newId) {
        header('Location: ../view/form_view.php?id=' . $newId);
    } else {
        header('Location: ../view/library.php');
    }
    exit;
}

// UPDATE
if (isset($_POST['update'])) {
    $service->updateItem($_POST['id'], $_POST['title'], $_POST['description'], $_POST['link'], $_POST['tag'] ?? null);
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item updated'];
    header('Location: ../view/form_view.php?id=' . (int)$_POST['id']);
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $service->deleteItem($_GET['delete']);
    $_SESSION['flash'] = ['type' => 'success', 'text' => 'Item deleted'];
    header('Location: ../view/library.php');
    exit;
}
?>
