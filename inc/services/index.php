<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/index.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    xds_json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$type = strtolower(trim((string)($_GET['type'] ?? 'all')));

$hydratePartners = static function (array $partners): array {
    return array_map(static function (array $partner): array {
        $file = (string)($partner['logo_file'] ?? '');
        return [
            'name'      => $partner['name'] ?? '',
            'sector'    => $partner['sector'] ?? null,
            'logo_file' => $file,
            'logo_url'  => $file !== '' ? xds_image_asset($file) : null,
        ];
    }, $partners);
};

$payload = [];

if ($type === 'all' || $type === 'credit') {
    $credit = xds_fetch_single_table('page_credit_services', 'page_credit_services');
    $payload['credit'] = [
        'title'      => $credit['page_title'],
        'overview'   => $credit['overview_copy'],
        'banner_url' => xds_image_asset($credit['banner_image_file']),
        'offerings'  => xds_decode_json($credit['offerings_json'] ?? null),
        'partners'   => $hydratePartners(xds_decode_json($credit['partners_json'] ?? null)),
        'url'        => xds_url('products/credit-information/'),
    ];
}

if ($type === 'all' || $type === 'agricultural') {
    $agri = xds_fetch_single_table('page_agricultural_services', 'page_agricultural_services');
    $payload['agricultural'] = [
        'title'       => $agri['page_title'],
        'intro'       => $agri['intro_paragraph'],
        'banner_url'  => xds_image_asset($agri['banner_image_file']),
        'diagram_url' => xds_image_asset($agri['diagram_image_file']),
        'features'    => xds_decode_json($agri['features_json'] ?? null),
        'pillars'     => xds_decode_json($agri['pillars_json'] ?? null),
        'url'         => xds_url('products/agricultural-services/'),
    ];
}

if ($type === 'all' || $type === 'authentication') {
    $auth = xds_fetch_single_table('page_authentication_services', 'page_authentication_services');
    $payload['authentication'] = [
        'title'            => $auth['page_title'],
        'overview'         => $auth['overview_copy'],
        'banner_url'       => xds_image_asset($auth['banner_image_file']),
        'echeck_badge_url' => xds_image_asset($auth['echeck_badge_file']),
        'checks'           => xds_decode_json($auth['checks_json'] ?? null),
        'partners'         => $hydratePartners(xds_decode_json($auth['partners_json'] ?? null)),
        'url'              => xds_url('products/authentication-services/'),
    ];
}

if (empty($payload)) {
    xds_json_response([
        'ok'      => false,
        'message' => 'Unknown service type. Expected all, credit, agricultural, or authentication.',
    ], 400);
}

xds_json_response([
    'ok'       => true,
    'services' => $payload,
]);
