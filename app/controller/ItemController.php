<?php
require_once __DIR__ . '/../model/services/ItemService.php';

$service = new ItemService();

// INSERT
if (isset($_POST['insert'])) {
    $service->insertItem($_POST['title'], $_POST['description'], $_POST['link']);
    header('Location: ../view/list.php');
    exit;
}

// UPDATE
if (isset($_POST['update'])) {
    $service->updateItem($_POST['id'], $_POST['title'], $_POST['description'], $_POST['link']);
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
