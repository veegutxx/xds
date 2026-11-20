<?php
/**
 * XDS Zimbabwe — Articles & Insights AJAX Endpoint
 * Module: inc/articles/index.php
 * Method: GET
 * Params:
 *   ?slug={slug}                          -> single article details
 *   ?category={cat}&q={search}&page={n}&per_page={m} -> paginated list
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/index.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    xds_json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$slug = trim((string)($_GET['slug'] ?? ''));

if ($slug !== '') {
    $article = xds_get_article_by_slug($slug);
    if ($article === null) {
        xds_json_response([
            'ok'      => false,
            'message' => 'Article not found.',
        ], 404);
    }

    xds_json_response([
        'ok'      => true,
        'article' => [
            'id'                => (int)($article['id'] ?? 0),
            'slug'              => $article['slug'],
            'title'             => $article['title'],
            'summary'           => $article['summary'],
            'body_content'      => $article['body_content'],
            'image_file'        => $article['image_file'],
            'image_url'         => xds_article_asset($article['slug'], $article['image_file']),
            'published_date'    => $article['published_date'],
            'formatted_date'    => date('d M Y', strtotime((string)$article['published_date'])),
            'author'            => $article['author'],
            'category'          => $article['category'],
            'read_time_minutes' => (int)($article['read_time_minutes'] ?? 4),
            'canonical_url'     => xds_canonical('resources/articles/' . $article['slug'] . '/'),
        ],
    ]);
}

$category = isset($_GET['category']) ? trim((string)$_GET['category']) : null;
$search   = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
$page     = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage  = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 6;

$result = xds_get_articles($category, $search, $page, $perPage);

$formatted = array_map(static function (array $row): array {
    return [
        'id'                => (int)($row['id'] ?? 0),
        'slug'              => $row['slug'],
        'title'             => $row['title'],
        'summary'           => $row['summary'],
        'image_file'        => $row['image_file'],
        'image_url'         => xds_article_asset($row['slug'], $row['image_file']),
        'published_date'    => $row['published_date'],
        'formatted_date'    => date('d M Y', strtotime((string)$row['published_date'])),
        'author'            => $row['author'],
        'category'          => $row['category'],
        'read_time_minutes' => (int)($row['read_time_minutes'] ?? 4),
        'url'               => xds_url('resources/articles/view/?slug=' . rawurlencode((string)$row['slug'])),
        'canonical_url'     => xds_canonical('resources/articles/' . $row['slug'] . '/'),
    ];
}, $result['items']);

xds_json_response([
    'ok'          => true,
    'items'       => $formatted,
    'total'       => $result['total'],
    'page'        => $result['page'],
    'per_page'    => $result['per_page'],
    'total_pages' => $result['total_pages'],
    'categories'  => $result['categories'],
]);
