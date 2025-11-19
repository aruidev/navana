<?php
require_once __DIR__ . '/../model/services/ItemService.php';
session_start();

$service = new ItemService();

// INSERT
if (isset($_POST['insert'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../view/login.php');
        exit;
    }
    $service->insertItem($_POST['title'], $_POST['description'], $_POST['link'], $_SESSION['user_id'], $_POST['tag'] ?? null);
    header('Location: ../view/list.php');
    exit;
}

// UPDATE
if (isset($_POST['update'])) {
    $service->updateItem($_POST['id'], $_POST['title'], $_POST['description'], $_POST['link'], $_POST['tag'] ?? null);
    header('Location: ../view/list.php');
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $service->deleteItem($_GET['delete']);
    header('Location: ../view/list.php');
    exit;
}
?>
