<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/index.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    xds_json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$category = isset($_GET['category']) ? trim((string)$_GET['category']) : null;
$search   = isset($_GET['q']) ? trim((string)$_GET['q']) : null;

$result = xds_get_faqs($category, $search);

$items = array_map(static function (array $row): array {
    return [
        'id'       => (int)($row['id'] ?? 0),
        'question' => $row['question'],
        'answer'   => $row['answer'],
        'category' => $row['category'],
        'icon_key' => $row['icon_key'] ?? 'help-circle',
    ];
}, $result['items']);

xds_json_response([
    'ok'         => true,
    'items'      => $items,
    'categories' => $result['categories'],
    'total'      => $result['total'],
]);
