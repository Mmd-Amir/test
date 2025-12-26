<?php
if (function_exists('rf_set_module')) {
    rf_set_module('bootstrap/tronado_api.php');
}

if (!defined('TRONADO_API_CONFIGURATION')) {
    $tronadoApiConfiguration = [
        'base_url' => 'https://bot.tronado.cloud',
        'order_token_path' => '/Order/GetOrderToken',
        'versions' => [
            'api/v1',
            'api/v2',
            'api/v3',
            'api',
            null,
        ],
    ];

    define('TRONADO_API_CONFIGURATION', $tronadoApiConfiguration);
    unset($tronadoApiConfiguration);
}

if (!defined('TRONADO_ORDER_TOKEN_ENDPOINTS')) {
    $tronadoConfig = TRONADO_API_CONFIGURATION;
    $baseUrl = rtrim((string) ($tronadoConfig['base_url'] ?? ''), '/');
    $path = '/' . ltrim((string) ($tronadoConfig['order_token_path'] ?? ''), '/');
    $versions = is_array($tronadoConfig['versions'] ?? null) ? $tronadoConfig['versions'] : [];

    $computedEndpoints = [];
    foreach ($versions as $version) {
        if ($baseUrl === '') {
            continue;
        }

        $versionSegment = $version !== null ? '/' . trim((string) $version, '/') : '';
        $computedEndpoints[] = $baseUrl . $versionSegment . $path;
    }

    if (!in_array(null, $versions, true)) {
        $computedEndpoints[] = $baseUrl . $path;
    }

    $computedEndpoints = array_values(array_unique(array_filter($computedEndpoints)));
    define('TRONADO_ORDER_TOKEN_ENDPOINTS', $computedEndpoints);
    unset($computedEndpoints, $baseUrl, $path, $versions, $tronadoConfig);
}
