<?php

require_once __DIR__ . '/includes/auth_check.php';

require_once __DIR__ . '/includes/farmer_data.php';
require_once __DIR__ . '/includes/crop_data.php';
require_once __DIR__ . '/includes/batch_data.php';
require_once __DIR__ . '/includes/inventory_data.php';
require_once __DIR__ . '/includes/order_data.php';
require_once __DIR__ . '/includes/super_shop_data.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];


/* =========================================================
   SEARCH HELPER
========================================================= */

function searchMatch($value, $query)
{
    return mb_stripos(
        (string) $value,
        $query
    ) !== false;
}


/* =========================================================
   MAIN PAGES / MODULES
========================================================= */

$pages = [

    [
        'title' => 'Dashboard',
        'keywords' => 'dashboard home overview',
        'icon' => '▦',
        'url' => 'dashboard.php'
    ],

    [
        'title' => 'Farmers',
        'keywords' => 'farmer farmers',
        'icon' => '👨‍🌾',
        'url' => 'farmers.php'
    ],

    [
        'title' => 'Crops',
        'keywords' => 'crop crops agriculture',
        'icon' => '🌾',
        'url' => 'crops.php'
    ],

    [
        'title' => 'Harvest Batches',
        'keywords' => 'harvest batch batches',
        'icon' => '📦',
        'url' => 'harvest_batches.php'
    ],

    [
        'title' => 'Quality Checks',
        'keywords' => 'quality check checks qc',
        'icon' => '✅',
        'url' => 'quality_checks.php'
    ],

    [
        'title' => 'Inventory',
        'keywords' => 'inventory stock warehouse',
        'icon' => '🏪',
        'url' => 'inventory.php'
    ],

    [
        'title' => 'Demand Forecast',
        'keywords' => 'demand forecast prediction',
        'icon' => '📈',
        'url' => 'demand_forecast.php'
    ],

    [
        'title' => 'Price Trends',
        'keywords' => 'price prices trend trends',
        'icon' => '💰',
        'url' => 'price_trends.php'
    ],

    [
        'title' => 'Market Orders',
        'keywords' => 'market order orders buyer',
        'icon' => '🛒',
        'url' => 'market_orders.php'
    ],

    [
        'title' => 'Super Shop Orders',
        'keywords' => 'super shop orders store',
        'icon' => '🏬',
        'url' => 'super_shop_orders.php'
    ],

    [
        'title' => 'Equipment Booking',
        'keywords' => 'equipment booking tractor machine',
        'icon' => '⚙️',
        'url' => 'equipment_booking.php'
    ],

    [
        'title' => 'Consultation',
        'keywords' => 'consultation consultant advice',
        'icon' => '☎️',
        'url' => 'consultation.php'
    ],

    [
        'title' => 'Deliveries',
        'keywords' => 'delivery deliveries transport shipment',
        'icon' => '🚚',
        'url' => 'deliveries.php'
    ],

    [
        'title' => 'Vehicles',
        'keywords' => 'vehicle vehicles truck van',
        'icon' => '🚛',
        'url' => 'vehicles.php'
    ],

    [
        'title' => 'IoT Telematics',
        'keywords' => 'iot telematics gps tracking sensor',
        'icon' => '📡',
        'url' => 'iot_telematics.php'
    ],

    [
        'title' => 'Notifications',
        'keywords' => 'notification notifications alerts',
        'icon' => '🔔',
        'url' => 'notification.php'
    ],

    [
        'title' => 'Profile / Settings',
        'keywords' => 'profile settings account',
        'icon' => '👤',
        'url' => 'profile.php'
    ]

];

foreach ($pages as $page) {

    if (
        searchMatch($page['title'], $query) ||
        searchMatch($page['keywords'], $query)
    ) {

        $results[] = [
            'title' => $page['title'],
            'meta' => 'AgriHub Page',
            'icon' => $page['icon'],
            'url' => $page['url']
        ];
    }
}


/* =========================================================
   FARMERS
========================================================= */

foreach (getAllFarmers() as $farmer) {

    if (
        searchMatch($farmer['id'] ?? '', $query) ||
        searchMatch($farmer['name'] ?? '', $query) ||
        searchMatch($farmer['phone'] ?? '', $query) ||
        searchMatch($farmer['location'] ?? '', $query)
    ) {

        $results[] = [
            'title' =>
                ($farmer['name'] ?? 'Farmer') .
                ' (' . ($farmer['id'] ?? '') . ')',

            'meta' =>
                'Farmer • ' .
                ($farmer['location'] ?? ''),

            'icon' => '👨‍🌾',

            'url' =>
                'farmer_details.php?id=' .
                urlencode($farmer['id'])
        ];
    }
}


/* =========================================================
   CROPS
========================================================= */

foreach (getAllCrops() as $crop) {

    $cropName =
        $crop['name'] ??
        $crop['crop'] ??
        '';

    if (
        searchMatch($crop['id'] ?? '', $query) ||
        searchMatch($cropName, $query) ||
        searchMatch($crop['type'] ?? '', $query) ||
        searchMatch($crop['farmer_id'] ?? '', $query)
    ) {

        $results[] = [
            'title' =>
                $cropName .
                ' (' . ($crop['id'] ?? '') . ')',

            'meta' =>
                'Crop • ' .
                ($crop['type'] ?? ''),

            'icon' => '🌾',

            'url' =>
                'crop_details.php?id=' .
                urlencode($crop['id'])
        ];
    }
}


/* =========================================================
   HARVEST BATCHES
========================================================= */

foreach (getAllBatches() as $batch) {

    if (
        searchMatch($batch['id'] ?? '', $query) ||
        searchMatch($batch['crop'] ?? '', $query) ||
        searchMatch($batch['farmer'] ?? '', $query) ||
        searchMatch($batch['status'] ?? '', $query)
    ) {

        $results[] = [
            'title' =>
                ($batch['id'] ?? '') .
                ' • ' .
                ($batch['crop'] ?? ''),

            'meta' =>
                'Harvest Batch • ' .
                ($batch['status'] ?? ''),

            'icon' => '📦',

            'url' =>
                'batch_details.php?id=' .
                urlencode($batch['id'])
        ];
    }
}


/* =========================================================
   INVENTORY
========================================================= */

foreach (getAllInventory() as $item) {

    if (
        searchMatch($item['id'] ?? '', $query) ||
        searchMatch($item['batch_id'] ?? '', $query) ||
        searchMatch($item['crop'] ?? '', $query) ||
        searchMatch($item['location'] ?? '', $query) ||
        searchMatch($item['status'] ?? '', $query)
    ) {

        $results[] = [
            'title' =>
                ($item['crop'] ?? 'Inventory') .
                ' (' . ($item['id'] ?? '') . ')',

            'meta' =>
                'Inventory • ' .
                ($item['status'] ?? ''),

            'icon' => '🏪',

            'url' =>
                'inventory_details.php?id=' .
                urlencode($item['id'])
        ];
    }
}


/* =========================================================
   MARKET ORDERS
========================================================= */

foreach (getAllMarketOrders() as $order) {

    if (
        searchMatch($order['id'] ?? '', $query) ||
        searchMatch($order['buyer'] ?? '', $query) ||
        searchMatch($order['crop'] ?? '', $query) ||
        searchMatch($order['status'] ?? '', $query)
    ) {

        $results[] = [
            'title' =>
                ($order['id'] ?? '') .
                ' • ' .
                ($order['buyer'] ?? ''),

            'meta' =>
                'Market Order • ' .
                ($order['crop'] ?? ''),

            'icon' => '🛒',

            'url' =>
                'market_order_details.php?id=' .
                urlencode($order['id'])
        ];
    }
}


/* =========================================================
   SUPER SHOP ORDERS
========================================================= */

foreach (getAllSuperShopOrders() as $order) {

    if (
        searchMatch($order['id'] ?? '', $query) ||
        searchMatch($order['shop'] ?? '', $query) ||
        searchMatch($order['product'] ?? '', $query) ||
        searchMatch($order['crop'] ?? '', $query) ||
        searchMatch($order['status'] ?? '', $query)
    ) {

        $results[] = [
            'title' =>
                ($order['id'] ?? '') .
                ' • ' .
                ($order['shop'] ?? ''),

            'meta' =>
                'Super Shop Order • ' .
                ($order['product'] ?? ''),

            'icon' => '🏬',

            'url' =>
                'super_shop_order_details.php?id=' .
                urlencode($order['id'])
        ];
    }
}


/* =========================================================
   RETURN RESULTS
========================================================= */

$results = array_slice(
    $results,
    0,
    12
);

echo json_encode(
    $results,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

exit;