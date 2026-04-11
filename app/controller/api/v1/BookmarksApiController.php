<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../helpers/route_helpers.php';
require_once __DIR__ . '/../../../services/ItemService.php';
require_once __DIR__ . '/../../../model/entities/Item.php';

$route = (string) ($GLOBALS['navana_route'] ?? '');
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

if ($method !== 'GET') {
    sendJsonError('method_not_allowed', 'Only GET is allowed for this endpoint.', 405);
}

$itemService = new ItemService();

if ($route === 'api/v1/bookmarks/show') {
    $itemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($itemId === false || $itemId === null || $itemId <= 0) {
        sendJsonError('invalid_id', 'The bookmark id must be a positive integer.', 400);
    }

    $item = $itemService->getItemById($itemId);
    if ($item === null) {
        sendJsonError('not_found', 'Bookmark not found.', 404);
    }

    sendJson(['data' => itemToApiArray($item)]);
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$allowedPerPage = [6, 12, 24];
$perPage = (int) ($_GET['perPage'] ?? 12);
if (!in_array($perPage, $allowedPerPage, true)) {
    $perPage = 12;
}

$term = trim((string) ($_GET['term'] ?? ''));
if (strlen($term) > 100) {
    $term = substr($term, 0, 100);
}

$order = strtoupper((string) ($_GET['order'] ?? 'DESC'));
$order = $order === 'ASC' ? 'ASC' : 'DESC';

$result = $itemService->getItemsPaginated($page, $perPage, $term, $order);
$items = array_map(
    static fn (Item $item): array => itemToApiArray($item),
    $result['items'],
);
$total = (int) $result['total'];
$totalPages = (int) ceil($total / max(1, $perPage));

sendJson([
    'data' => $items,
    'meta' => [
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
        ],
        'filters' => [
            'term' => $term,
            'order' => $order,
        ],
    ],
]);

/**
 * @return array<string, int|string|null>
 */
function itemToApiArray(Item $item): array {
    return [
        'id' => (int) $item->getId(),
        'title' => (string) $item->getTitle(),
        'description' => (string) $item->getDescription(),
        'link' => (string) $item->getLink(),
        'tag' => (string) $item->getTag(),
        'createdAt' => (string) $item->getCreatedAt(),
        'updatedAt' => (string) $item->getUpdatedAt(),
        'ownerUserId' => $item->getUserId() !== null ? (int) $item->getUserId() : null,
    ];
}
