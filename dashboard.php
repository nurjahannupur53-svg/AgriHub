<?php

require_once __DIR__ . '/includes/auth_check.php';

require_once __DIR__ . '/includes/farmer_data.php';
require_once __DIR__ . '/includes/crop_data.php';
require_once __DIR__ . '/includes/batch_data.php';
require_once __DIR__ . '/includes/qc_data.php';
require_once __DIR__ . '/includes/inventory_data.php';
require_once __DIR__ . '/includes/delivery_data.php';
require_once __DIR__ . '/includes/order_data.php';
require_once __DIR__ . '/includes/notification_data.php';
require_once __DIR__ . '/includes/price_data.php';


$currentMonthYear = date('F Y');

$pageTitle = 'Dashboard';

$pageSubtitle =
    'Agriculture Supply Chain Overview — ' .
    $currentMonthYear;


/*
|--------------------------------------------------------------------------
| Live Dashboard Data
|--------------------------------------------------------------------------
*/

$farmers = getAllFarmers();
$crops = getAllCrops();
$batches = getAllBatches();
$qualityChecks = getAllQualityChecks();
$inventoryItems = getAllInventory();
$deliveries = getAllDeliveries();
$marketOrders = getAllMarketOrders();
$allNotifications = getAllNotifications();
$priceTrendData = getPriceTrendData();


/*
|--------------------------------------------------------------------------
| Dashboard Totals
|--------------------------------------------------------------------------
*/

$totalFarmers = count($farmers);

$totalCrops = count($crops);

$totalBatches = count($batches);

$totalQualityChecks = count($qualityChecks);


/*
|--------------------------------------------------------------------------
| Inventory Totals
|--------------------------------------------------------------------------
*/

$totalInventoryStock = 0;
$totalAvailable = 0;
$totalReserved = 0;
$totalOriginalStock = 0;

$lowStockItems = [];


foreach ($inventoryItems as $item) {

    $total =
        (float) ($item['total'] ?? 0);

    $available =
        (float) ($item['available'] ?? 0);

    $reserved =
        (float) ($item['reserved'] ?? 0);

    $threshold =
        (float) ($item['threshold'] ?? 0);


    $totalOriginalStock += $total;

    $totalAvailable += $available;

    $totalReserved += $reserved;


    /*
    |--------------------------------------------------------------------------
    | Current Physical Stock
    |--------------------------------------------------------------------------
    */

    $totalInventoryStock +=
        $available + $reserved;


    /*
    |--------------------------------------------------------------------------
    | Low Stock
    |--------------------------------------------------------------------------
    */

    if (
        $available > 0 &&
        $available <= $threshold
    ) {

        $lowStockItems[] = $item;
    }
}


/*
|--------------------------------------------------------------------------
| Sold / Out Stock
|--------------------------------------------------------------------------
*/

$totalSoldStock = max(
    0,
    $totalOriginalStock -
    $totalAvailable -
    $totalReserved
);


/*
|--------------------------------------------------------------------------
| Inventory Percentages
|--------------------------------------------------------------------------
*/

$inventoryBase =
    $totalAvailable +
    $totalReserved +
    $totalSoldStock;


if ($inventoryBase > 0) {

    $availablePercent = round(
        ($totalAvailable / $inventoryBase) * 100
    );

    $reservedPercent = round(
        ($totalReserved / $inventoryBase) * 100
    );

    $soldPercent = max(
        0,
        100 -
        $availablePercent -
        $reservedPercent
    );

} else {

    $availablePercent = 0;
    $reservedPercent = 0;
    $soldPercent = 0;
}


/*
|--------------------------------------------------------------------------
| Active Deliveries
|--------------------------------------------------------------------------
*/

$activeDeliveries = 0;


foreach ($deliveries as $delivery) {

    $status = strtolower(
        trim($delivery['status'] ?? '')
    );

    if (
        $status !== 'delivered' &&
        $status !== 'cancelled'
    ) {

        $activeDeliveries++;
    }
}


/*
|--------------------------------------------------------------------------
| Dashboard Cards
|--------------------------------------------------------------------------
*/

$stats = [

    [
        'title' => 'Total Farmers',
        'value' => $totalFarmers,
        'icon' => '👨‍🌾',
        'url' => 'farmers.php'
    ],

    [
        'title' => 'Active Crops',
        'value' => $totalCrops,
        'icon' => '🌾',
        'url' => 'crops.php'
    ],

    [
        'title' => 'Harvest Batches',
        'value' => $totalBatches,
        'icon' => '📦',
        'url' => 'harvest_batches.php'
    ],

    [
        'title' => 'Inventory Stock',
        'value' =>
            number_format(
                $totalInventoryStock,
                1
            ) . ' ton',
        'icon' => '🏪',
        'url' => 'inventory.php'
    ],

    [
        'title' => 'Quality Checks',
        'value' => $totalQualityChecks,
        'icon' => '✅',
        'url' => 'quality_checks.php'
    ],

    [
        'title' => 'Active Deliveries',
        'value' => $activeDeliveries,
        'icon' => '🚚',
        'url' => 'deliveries.php'
    ]

];


/*
|--------------------------------------------------------------------------
| Harvest Overview
|--------------------------------------------------------------------------
*/

$harvestCounts = [

    'In Inventory' => 0,
    'Awaiting QC' => 0,
    'Sold' => 0,
    'Rejected' => 0

];


foreach ($batches as $batch) {

    $status = trim(
        $batch['inventory_status'] ?? ''
    );

    if (isset($harvestCounts[$status])) {

        $harvestCounts[$status]++;
    }
}


/*
|--------------------------------------------------------------------------
| Recent Harvest Batches
|--------------------------------------------------------------------------
*/

$recentBatches = $batches;


usort(
    $recentBatches,
    function ($a, $b) {

        $dateCompare = strcmp(
            $b['harvest_date'] ?? '',
            $a['harvest_date'] ?? ''
        );

        if ($dateCompare !== 0) {

            return $dateCompare;
        }

        return strcmp(
            $b['id'] ?? '',
            $a['id'] ?? ''
        );
    }
);


$recentBatches = array_slice(
    $recentBatches,
    0,
    4
);


/*
|--------------------------------------------------------------------------
| Recent Market Orders
|--------------------------------------------------------------------------
*/

$recentOrders = $marketOrders;


usort(
    $recentOrders,
    function ($a, $b) {

        $dateCompare = strcmp(
            $b['order_date'] ?? '',
            $a['order_date'] ?? ''
        );

        if ($dateCompare !== 0) {

            return $dateCompare;
        }

        return strcmp(
            $b['id'] ?? '',
            $a['id'] ?? ''
        );
    }
);


$recentOrders = array_slice(
    $recentOrders,
    0,
    3
);


/*
|--------------------------------------------------------------------------
| Recent Notifications
|--------------------------------------------------------------------------
*/

$recentNotifications = array_slice(
    $allNotifications,
    0,
    3
);


/*
|--------------------------------------------------------------------------
| Status Helper
|--------------------------------------------------------------------------
*/

function dashboardStatusClass($status)
{
    $status = strtolower(
        trim($status)
    );

    switch ($status) {

        case 'approved':
        case 'available':
        case 'in inventory':
        case 'delivered':
        case 'paid':

            return 'status-success';


        case 'rejected':
        case 'cancelled':
        case 'out of stock':

            return 'status-danger';


        case 'processing':
        case 'active':
        case 'sold':

            return 'status-info';


        case 'pending':
        case 'awaiting qc':
        case 'in transit':
        case 'partially reserved':
        case 'partial':
        case 'unpaid':

            return 'status-warning';


        default:

            return 'status-info';
    }
}


/*
|--------------------------------------------------------------------------
| Notification Type Helper
|--------------------------------------------------------------------------
*/

function dashboardNotificationClass($type)
{
    $type = strtolower(
        trim($type)
    );

    switch ($type) {

        case 'quality':

            return 'success';


        case 'inventory':

            return 'danger';


        case 'harvest':
        case 'delivery':
        case 'equipment':
        case 'consultation':

            return 'warning';


        default:

            return 'success';
    }
}


/*
|--------------------------------------------------------------------------
| SVG Chart Helper
|--------------------------------------------------------------------------
| Converts values into SVG polyline points.
|--------------------------------------------------------------------------
*/

function dashboardChartPoints(
    array $values,
    float $maxValue,
    float $left = 55,
    float $right = 590,
    float $top = 25,
    float $bottom = 225
) {

    $count = count($values);

    if ($count === 0) {

        return '';
    }


    if ($maxValue <= 0) {

        $maxValue = 1;
    }


    $width =
        $right - $left;

    $height =
        $bottom - $top;


    $stepX =
        $count > 1
            ? $width / ($count - 1)
            : 0;


    $points = [];


    foreach ($values as $index => $value) {

        $value = max(
            0,
            (float) $value
        );


        $x =
            $left +
            ($index * $stepX);


        $ratio =
            min(
                1,
                $value / $maxValue
            );


        $y =
            $bottom -
            ($ratio * $height);


        $points[] =
            round($x, 2) .
            ',' .
            round($y, 2);
    }


    return implode(
        ' ',
        $points
    );
}


/*
|--------------------------------------------------------------------------
| Nice Chart Maximum Helper
|--------------------------------------------------------------------------
*/

function dashboardNiceMax(
    float $value,
    float $minimum = 1
) {

    $value = max(
        $value,
        $minimum
    );


    $magnitude =
        pow(
            10,
            floor(log10($value))
        );


    $normalized =
        $value / $magnitude;


    if ($normalized <= 1) {

        $nice = 1;

    } elseif ($normalized <= 2) {

        $nice = 2;

    } elseif ($normalized <= 5) {

        $nice = 5;

    } else {

        $nice = 10;
    }


    return $nice * $magnitude;
}


/*
|--------------------------------------------------------------------------
| Dynamic Crop Production Chart
|--------------------------------------------------------------------------
| Uses harvest batch quantity grouped by crop + month.
|--------------------------------------------------------------------------
*/

$productionMonthKeys = [];

$productionMonths = [];

$productionCropTotals = [];


/*
|--------------------------------------------------------------------------
| Find Months Existing In Harvest Data
|--------------------------------------------------------------------------
*/

foreach ($batches as $batch) {

    $harvestDate =
        $batch['harvest_date'] ?? '';

    if ($harvestDate === '') {

        continue;
    }


    $timestamp =
        strtotime($harvestDate);

    if ($timestamp === false) {

        continue;
    }


    $monthKey =
        date(
            'Y-m',
            $timestamp
        );


    $productionMonthKeys[$monthKey] =
        $monthKey;


    $cropName =
        trim(
            $batch['crop'] ?? ''
        );


    if ($cropName === '') {

        continue;
    }


    if (!isset($productionCropTotals[$cropName])) {

        $productionCropTotals[$cropName] = 0;
    }


    $productionCropTotals[$cropName] +=
        (float) ($batch['quantity'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| Sort Months Chronologically
|--------------------------------------------------------------------------
*/

ksort($productionMonthKeys);

$productionMonthKeys =
    array_values(
        $productionMonthKeys
    );


/*
|--------------------------------------------------------------------------
| Show Latest 9 Available Months
|--------------------------------------------------------------------------
*/

$productionMonthKeys =
    array_slice(
        $productionMonthKeys,
        -9
    );


foreach ($productionMonthKeys as $monthKey) {

    $productionMonths[] =
        date(
            'M',
            strtotime($monthKey . '-01')
        );
}


/*
|--------------------------------------------------------------------------
| Choose Top 3 Crops By Production
|--------------------------------------------------------------------------
*/

arsort($productionCropTotals);

$productionCropNames =
    array_slice(
        array_keys($productionCropTotals),
        0,
        3
    );


/*
|--------------------------------------------------------------------------
| Fallback When Database Has No Production
|--------------------------------------------------------------------------
*/

if (empty($productionCropNames)) {

    $productionCropNames = [
        'Potato',
        'Aman Rice',
        'Wheat'
    ];
}


if (empty($productionMonthKeys)) {

    $productionMonthKeys = [
        date('Y-m')
    ];

    $productionMonths = [
        date('M')
    ];
}


/*
|--------------------------------------------------------------------------
| Build Monthly Production Series
|--------------------------------------------------------------------------
*/

$productionSeries = [];


foreach ($productionCropNames as $cropName) {

    $productionSeries[$cropName] =
        array_fill(
            0,
            count($productionMonthKeys),
            0
        );
}


foreach ($batches as $batch) {

    $cropName =
        trim(
            $batch['crop'] ?? ''
        );


    if (
        !isset(
            $productionSeries[$cropName]
        )
    ) {

        continue;
    }


    $harvestDate =
        $batch['harvest_date'] ?? '';

    $timestamp =
        strtotime($harvestDate);

    if ($timestamp === false) {

        continue;
    }


    $monthKey =
        date(
            'Y-m',
            $timestamp
        );


    $monthIndex =
        array_search(
            $monthKey,
            $productionMonthKeys,
            true
        );


    if ($monthIndex === false) {

        continue;
    }


    $productionSeries[$cropName][$monthIndex] +=
        (float) ($batch['quantity'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| Production Chart Maximum
|--------------------------------------------------------------------------
*/

$productionRawMax = 0;


foreach ($productionSeries as $values) {

    if (!empty($values)) {

        $seriesMax =
            max($values);

        if ($seriesMax > $productionRawMax) {

            $productionRawMax =
                $seriesMax;
        }
    }
}


$productionChartMax =
    dashboardNiceMax(
        $productionRawMax,
        10
    );


$productionYAxis = [

    $productionChartMax,

    $productionChartMax * 0.75,

    $productionChartMax * 0.50,

    $productionChartMax * 0.25,

    0

];


/*
|--------------------------------------------------------------------------
| Production Chart CSS Classes
|--------------------------------------------------------------------------
*/

$productionClasses = [

    'potato-line',
    'rice-line',
    'wheat-line'

];


$productionLegendClasses = [

    'legend-potato',
    'legend-rice',
    'legend-wheat'

];


/*
|--------------------------------------------------------------------------
| Dynamic Market Price Chart
|--------------------------------------------------------------------------
*/

$priceCropPreferences = [
    'Lentil',
    'Aman Rice',
    'Wheat'
];


$priceCropNames = [];


/*
|--------------------------------------------------------------------------
| Prefer Existing Original 3 Crops
|--------------------------------------------------------------------------
*/

foreach ($priceCropPreferences as $cropName) {

    if (
        isset($priceTrendData[$cropName])
    ) {

        $priceCropNames[] =
            $cropName;
    }
}


/*
|--------------------------------------------------------------------------
| Fill Missing Slots With Other Crops
|--------------------------------------------------------------------------
*/

foreach (
    array_keys($priceTrendData)
    as $cropName
) {

    if (
        count($priceCropNames) >= 3
    ) {

        break;
    }


    if (
        !in_array(
            $cropName,
            $priceCropNames,
            true
        )
    ) {

        $priceCropNames[] =
            $cropName;
    }
}


/*
|--------------------------------------------------------------------------
| Price Month Keys
|--------------------------------------------------------------------------
*/

$priceMonthMap = [];


foreach ($priceCropNames as $cropName) {

    $prices =
        $priceTrendData[$cropName]['prices']
        ?? [];


    foreach ($prices as $monthLabel => $price) {

        $timestamp =
            strtotime(
                '1 ' . $monthLabel
            );

        if ($timestamp === false) {

            continue;
        }


        $monthKey =
            date(
                'Y-m',
                $timestamp
            );


        $priceMonthMap[$monthKey] =
            $monthLabel;
    }
}


ksort($priceMonthMap);


/*
|--------------------------------------------------------------------------
| Latest 7 Price Months
|--------------------------------------------------------------------------
*/

$priceMonthMap =
    array_slice(
        $priceMonthMap,
        -7,
        null,
        true
    );


$priceMonthKeys =
    array_keys(
        $priceMonthMap
    );


$priceMonths = [];


foreach ($priceMonthKeys as $monthKey) {

    $priceMonths[] =
        date(
            'M',
            strtotime($monthKey . '-01')
        );
}


/*
|--------------------------------------------------------------------------
| Build Price Series In ৳ / Ton
|--------------------------------------------------------------------------
*/

$priceSeries = [];


foreach ($priceCropNames as $cropName) {

    $priceSeries[$cropName] = [];


    $cropPrices =
        $priceTrendData[$cropName]['prices']
        ?? [];


    $normalizedPrices = [];


    foreach (
        $cropPrices
        as $monthLabel => $price
    ) {

        $timestamp =
            strtotime(
                '1 ' . $monthLabel
            );

        if ($timestamp === false) {

            continue;
        }


        $monthKey =
            date(
                'Y-m',
                $timestamp
            );


        $normalizedPrices[$monthKey] =
            (float) $price;
    }


    foreach ($priceMonthKeys as $monthKey) {

        /*
        | price_trends is stored as ৳/kg.
        | Dashboard displays ৳/ton.
        */

        $pricePerKg =
            $normalizedPrices[$monthKey]
            ?? 0;


        $priceSeries[$cropName][] =
            $pricePerKg * 1000;
    }
}


/*
|--------------------------------------------------------------------------
| Price Chart Maximum
|--------------------------------------------------------------------------
*/

$priceRawMax = 0;


foreach ($priceSeries as $values) {

    if (!empty($values)) {

        $seriesMax =
            max($values);

        if ($seriesMax > $priceRawMax) {

            $priceRawMax =
                $seriesMax;
        }
    }
}


$priceChartMax =
    dashboardNiceMax(
        $priceRawMax,
        10000
    );


$priceYAxis = [

    $priceChartMax,

    $priceChartMax * 0.75,

    $priceChartMax * 0.50,

    $priceChartMax * 0.25,

    0

];


$priceClasses = [

    'lentil-line',
    'rice-line',
    'wheat-line'

];


$priceLegendClasses = [

    'legend-lentil',
    'legend-rice',
    'legend-wheat'

];


require_once __DIR__ . '/includes/header.php';

?>


<!-- =====================================================
     DASHBOARD STATISTICS
===================================================== -->

<div class="prototype-dashboard-stats">

    <?php foreach ($stats as $stat): ?>

        <a
            href="<?= htmlspecialchars($stat['url']) ?>"
            class="prototype-stat-card"
        >

            <div class="prototype-stat-icon">
                <?= $stat['icon'] ?>
            </div>

            <div class="prototype-stat-info">

                <strong>
                    <?= htmlspecialchars((string) $stat['value']) ?>
                </strong>

                <span>
                    <?= htmlspecialchars($stat['title']) ?>
                </span>

            </div>

        </a>

    <?php endforeach; ?>

</div>


<!-- =====================================================
     DYNAMIC CHARTS
===================================================== -->

<div class="prototype-dashboard-charts">


    <!-- =================================================
         CROP PRODUCTION
    ================================================== -->

    <div class="prototype-chart-card">

        <h3>
            Crop Production (ton/month)
        </h3>


        <div class="prototype-chart-wrap">

            <svg
                class="prototype-chart"
                viewBox="0 0 620 250"
                preserveAspectRatio="none"
            >

                <!-- GRID -->

                <line x1="55" y1="25" x2="600" y2="25" />
                <line x1="55" y1="75" x2="600" y2="75" />
                <line x1="55" y1="125" x2="600" y2="125" />
                <line x1="55" y1="175" x2="600" y2="175" />
                <line x1="55" y1="225" x2="600" y2="225" />


                <?php

                $productionIndex = 0;

                foreach (
                    $productionSeries
                    as $cropName => $values
                ):

                    $lineClass =
                        $productionClasses[
                            $productionIndex % count($productionClasses)
                        ];

                    $points =
                        dashboardChartPoints(
                            $values,
                            $productionChartMax
                        );

                ?>

                    <polyline
                        class="chart-line <?= htmlspecialchars($lineClass) ?>"
                        points="<?= htmlspecialchars($points) ?>"
                    />

                <?php

                    $productionIndex++;

                endforeach;

                ?>

            </svg>


            <div class="prototype-y-labels">

                <?php foreach ($productionYAxis as $label): ?>

                    <span>
                        <?= number_format($label, 0) ?>
                    </span>

                <?php endforeach; ?>

            </div>


            <div class="prototype-x-labels production-labels">

                <?php foreach ($productionMonths as $month): ?>

                    <span>
                        <?= htmlspecialchars($month) ?>
                    </span>

                <?php endforeach; ?>

            </div>

        </div>


        <div class="prototype-chart-legend">

            <?php

            $productionIndex = 0;

            foreach ($productionCropNames as $cropName):

                $legendClass =
                    $productionLegendClasses[
                        $productionIndex % count($productionLegendClasses)
                    ];

            ?>

                <span>

                    <i class="<?= htmlspecialchars($legendClass) ?>"></i>

                    <?= htmlspecialchars($cropName) ?>

                </span>

            <?php

                $productionIndex++;

            endforeach;

            ?>

        </div>

    </div>


    <!-- =================================================
         MARKET PRICE
    ================================================== -->

    <div class="prototype-chart-card">

        <h3>
            Market Price Trends (৳/ton)
        </h3>


        <div class="prototype-chart-wrap">

            <svg
                class="prototype-chart"
                viewBox="0 0 620 250"
                preserveAspectRatio="none"
            >

                <!-- GRID -->

                <line x1="55" y1="25" x2="600" y2="25" />
                <line x1="55" y1="75" x2="600" y2="75" />
                <line x1="55" y1="125" x2="600" y2="125" />
                <line x1="55" y1="175" x2="600" y2="175" />
                <line x1="55" y1="225" x2="600" y2="225" />


                <?php

                $priceIndex = 0;

                foreach (
                    $priceSeries
                    as $cropName => $values
                ):

                    $lineClass =
                        $priceClasses[
                            $priceIndex % count($priceClasses)
                        ];

                    $points =
                        dashboardChartPoints(
                            $values,
                            $priceChartMax
                        );

                ?>

                    <polyline
                        class="chart-line <?= htmlspecialchars($lineClass) ?>"
                        points="<?= htmlspecialchars($points) ?>"
                    />

                <?php

                    $priceIndex++;

                endforeach;

                ?>

            </svg>


            <div class="prototype-y-labels price-labels">

                <?php foreach ($priceYAxis as $label): ?>

                    <span>
                        <?= number_format($label, 0) ?>
                    </span>

                <?php endforeach; ?>

            </div>


            <div class="prototype-x-labels">

                <?php foreach ($priceMonths as $month): ?>

                    <span>
                        <?= htmlspecialchars($month) ?>
                    </span>

                <?php endforeach; ?>

            </div>

        </div>


        <div class="prototype-chart-legend">

            <?php

            $priceIndex = 0;

            foreach ($priceCropNames as $cropName):

                $legendClass =
                    $priceLegendClasses[
                        $priceIndex % count($priceLegendClasses)
                    ];

            ?>

                <span>

                    <i class="<?= htmlspecialchars($legendClass) ?>"></i>

                    <?= htmlspecialchars($cropName) ?>

                </span>

            <?php

                $priceIndex++;

            endforeach;

            ?>

        </div>

    </div>

</div>


<!-- =====================================================
     SECOND ROW
===================================================== -->

<div class="dashboard-grid">


    <!-- =================================================
         HARVEST OVERVIEW
    ================================================== -->

    <div class="app-card">

        <div class="card-header">

            <div>

                <h3>Harvest Overview</h3>

                <span class="card-subtitle">
                    Batch status distribution
                </span>

            </div>


            <a
                href="harvest_batches.php"
                class="small-link"
            >
                View all
            </a>

        </div>


        <div class="card-body">

            <div class="harvest-overview">


                <div class="harvest-chart">

                    <div class="chart-center">

                        <strong>
                            <?= $totalBatches ?>
                        </strong>

                        <span>Total</span>

                    </div>

                </div>


                <div class="chart-legend">


                    <div>

                        <span class="legend-dot approved"></span>

                        <p>

                            <strong>
                                <?= $harvestCounts['In Inventory'] ?>
                            </strong>

                            In Inventory

                        </p>

                    </div>


                    <div>

                        <span class="legend-dot pending"></span>

                        <p>

                            <strong>
                                <?= $harvestCounts['Awaiting QC'] ?>
                            </strong>

                            Awaiting QC

                        </p>

                    </div>


                    <div>

                        <span class="legend-dot sold"></span>

                        <p>

                            <strong>
                                <?= $harvestCounts['Sold'] ?>
                            </strong>

                            Sold

                        </p>

                    </div>


                    <div>

                        <span class="legend-dot rejected"></span>

                        <p>

                            <strong>
                                <?= $harvestCounts['Rejected'] ?>
                            </strong>

                            Rejected

                        </p>

                    </div>


                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         INVENTORY SUMMARY
    ================================================== -->

    <div class="app-card">

        <div class="card-header">

            <div>

                <h3>Inventory Summary</h3>

                <span class="card-subtitle">
                    Current warehouse stock
                </span>

            </div>


            <a
                href="inventory.php"
                class="small-link"
            >
                View inventory
            </a>

        </div>


        <div class="card-body">

            <div class="inventory-summary">


                <!-- AVAILABLE -->

                <div class="inventory-row">

                    <div>

                        <strong>
                            <?= number_format($totalAvailable, 1) ?> ton
                        </strong>

                        <span>Available</span>

                    </div>


                    <span class="inventory-percentage">
                        <?= $availablePercent ?>%
                    </span>

                </div>


                <div class="progress-bar">

                    <div
                        style="width:<?= $availablePercent ?>%"
                    ></div>

                </div>


                <!-- RESERVED -->

                <div class="inventory-row">

                    <div>

                        <strong>
                            <?= number_format($totalReserved, 1) ?> ton
                        </strong>

                        <span>Reserved</span>

                    </div>


                    <span class="inventory-percentage">
                        <?= $reservedPercent ?>%
                    </span>

                </div>


                <div class="progress-bar">

                    <div
                        style="width:<?= $reservedPercent ?>%"
                    ></div>

                </div>


                <!-- SOLD -->

                <div class="inventory-row">

                    <div>

                        <strong>
                            <?= number_format($totalSoldStock, 1) ?> ton
                        </strong>

                        <span>Out / Sold Stock</span>

                    </div>


                    <span class="inventory-percentage">
                        <?= $soldPercent ?>%
                    </span>

                </div>


                <div class="progress-bar">

                    <div
                        style="width:<?= $soldPercent ?>%"
                    ></div>

                </div>

            </div>


            <?php if (!empty($lowStockItems)): ?>

                <div class="dashboard-alert">

                    <span>!</span>

                    <div>

                        <strong>

                            <?= count($lowStockItems) ?>

                            Low Stock Alert<?= count($lowStockItems) !== 1 ? 's' : '' ?>

                        </strong>


                        <p>
                            Some inventory items are at or below their stock threshold.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>


<!-- =====================================================
     RECENT HARVEST BATCHES
===================================================== -->

<div class="app-card dashboard-section">

    <div class="card-header">

        <div>

            <h3>Recent Harvest Batches</h3>

            <span class="card-subtitle">
                Latest harvest activity
            </span>

        </div>


        <a
            href="harvest_batches.php"
            class="small-link"
        >
            View all batches →
        </a>

    </div>


    <div class="table-responsive">

        <table class="app-table">

            <thead>

                <tr>

                    <th>Batch ID</th>
                    <th>Crop</th>
                    <th>Farmer</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php if (empty($recentBatches)): ?>

                <tr>

                    <td colspan="6">
                        No harvest batches found.
                    </td>

                </tr>

            <?php else: ?>


                <?php foreach ($recentBatches as $batch): ?>

                    <?php

                    $batchStatus =
                        $batch['inventory_status']
                        ??
                        $batch['qc_status']
                        ??
                        'Pending';

                    ?>

                    <tr>

                        <td>

                            <strong>
                                <?= htmlspecialchars($batch['id']) ?>
                            </strong>

                        </td>


                        <td>
                            <?= htmlspecialchars($batch['crop'] ?? '') ?>
                        </td>


                        <td>
                            <?= htmlspecialchars($batch['farmer'] ?? '') ?>
                        </td>


                        <td>

                            <?= number_format(
                                (float) ($batch['quantity'] ?? 0),
                                1
                            ) ?> ton

                        </td>


                        <td>

                            <span
                                class="status-badge <?= dashboardStatusClass($batchStatus) ?>"
                            >
                                <?= htmlspecialchars($batchStatus) ?>
                            </span>

                        </td>


                        <td>

                            <a
                                href="batch_details.php?id=<?= urlencode($batch['id']) ?>"
                                class="table-action"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>


            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =====================================================
     ORDERS + NOTIFICATIONS
===================================================== -->

<div class="dashboard-grid dashboard-section">


    <!-- =================================================
         RECENT MARKET ORDERS
    ================================================== -->

    <div class="app-card">

        <div class="card-header">

            <div>

                <h3>Recent Market Orders</h3>

                <span class="card-subtitle">
                    Latest buyer orders
                </span>

            </div>


            <a
                href="market_orders.php"
                class="small-link"
            >
                View all
            </a>

        </div>


        <div class="table-responsive">

            <table class="app-table">

                <thead>

                    <tr>

                        <th>Order</th>
                        <th>Buyer</th>
                        <th>Total</th>
                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (empty($recentOrders)): ?>

                    <tr>

                        <td colspan="4">
                            No market orders found.
                        </td>

                    </tr>

                <?php else: ?>


                    <?php foreach ($recentOrders as $order): ?>

                        <tr>

                            <td>

                                <a
                                    href="market_order_details.php?id=<?= urlencode($order['id']) ?>"
                                    class="table-id-link"
                                >
                                    <?= htmlspecialchars($order['id']) ?>
                                </a>


                                <small class="table-secondary">

                                    <?= htmlspecialchars(
                                        $order['crop'] ?? ''
                                    ) ?>

                                </small>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $order['buyer'] ?? ''
                                ) ?>

                            </td>


                            <td>

                                ৳<?= number_format(
                                    (float) ($order['amount'] ?? 0),
                                    0
                                ) ?>

                            </td>


                            <td>

                                <span
                                    class="status-badge <?= dashboardStatusClass($order['status'] ?? '') ?>"
                                >

                                    <?= htmlspecialchars(
                                        $order['status'] ?? ''
                                    ) ?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- =================================================
         RECENT NOTIFICATIONS
    ================================================== -->

    <div class="app-card">

        <div class="card-header">

            <div>

                <h3>Recent Notifications</h3>

                <span class="card-subtitle">
                    Latest system updates
                </span>

            </div>


            <a
                href="notification.php"
                class="small-link"
            >
                View all
            </a>

        </div>


        <div class="dashboard-notifications">

            <?php if (empty($recentNotifications)): ?>

                <div class="dashboard-notification-item">

                    <div class="notification-content">

                        <strong>
                            No notifications
                        </strong>

                        <p>
                            No recent system updates found.
                        </p>

                    </div>

                </div>

            <?php else: ?>


                <?php foreach ($recentNotifications as $notification): ?>

                    <div class="dashboard-notification-item">


                        <div
                            class="notification-type <?= dashboardNotificationClass($notification['type'] ?? '') ?>"
                        >
                            ●
                        </div>


                        <div class="notification-content">

                            <strong>

                                <?= htmlspecialchars(
                                    $notification['title'] ?? ''
                                ) ?>

                            </strong>


                            <p>

                                <?= htmlspecialchars(
                                    $notification['message'] ?? ''
                                ) ?>

                            </p>


                            <span>

                                <?= htmlspecialchars(
                                    $notification['time'] ?? ''
                                ) ?>

                            </span>

                        </div>

                    </div>

                <?php endforeach; ?>


            <?php endif; ?>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/includes/footer.php';

?>