<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/price_data.php';

$allPriceData = getPriceTrendData();

/*
|--------------------------------------------------------------------------
| Prototype crop names
|--------------------------------------------------------------------------
*/

$cropLabels = [
    'Aman Rice' => 'Rice',
    'Wheat'     => 'Wheat',
    'Lentil'    => 'Lentil',
    'Maize'     => 'Maize',
    'Potato'    => 'Potato'
];

$months = [
    'Sep 2024',
    'Oct 2024',
    'Nov 2024',
    'Dec 2024',
    'Jan 2025',
    'Feb 2025',
    'Mar 2025'
];

/*
|--------------------------------------------------------------------------
| Default chart
|--------------------------------------------------------------------------
|
| Prototype screenshot initially compares:
| Rice + Wheat + Lentil
|
*/

$defaultSelected = [
    'Aman Rice',
    'Wheat',
    'Lentil'
];

$requestedCrop = trim($_GET['crop'] ?? '');

if (
    $requestedCrop !== '' &&
    isset($allPriceData[$requestedCrop])
) {
    $selectedCrops = [$requestedCrop];
} else {
    $selectedCrops = $defaultSelected;
}

$pageTitle = 'Market Price Trends';
$pageSubtitle = 'Historical crop prices — ৳ per ton';

require_once __DIR__ . '/includes/header.php';


/*
|--------------------------------------------------------------------------
| Fixed prototype chart scale
|--------------------------------------------------------------------------
|
| 0k - 100k
|
*/

$chartWidth = 1100;
$chartHeight = 330;

$left = 70;
$right = 25;
$top = 25;
$bottom = 50;

$plotWidth = $chartWidth - $left - $right;
$plotHeight = $chartHeight - $top - $bottom;

$chartMax = 100000;


/*
|--------------------------------------------------------------------------
| Chart point helper
|--------------------------------------------------------------------------
*/

function getChartPoint(
    $index,
    $value,
    $count,
    $left,
    $top,
    $plotWidth,
    $plotHeight,
    $chartMax
) {
    $x = $count > 1
        ? $left + ($index / ($count - 1)) * $plotWidth
        : $left;

    $y =
        $top +
        (1 - ($value / $chartMax)) *
        $plotHeight;

    return [
        'x' => round($x, 2),
        'y' => round($y, 2)
    ];
}

?>

<style>

/* =========================================================
   PRICE TRENDS — PROTOTYPE MATCH
   ========================================================= */

.prototype-price-page {
    width: 100%;
}

.prototype-price-tabs {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}

.prototype-price-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 66px;
    height: 38px;
    padding: 0 18px;
    border: 1px solid #dfe5df;
    border-radius: 9px;
    background: #ffffff;
    color: #4f5d53;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.2s ease;
}

.prototype-price-tab:hover {
    border-color: #168a45;
    color: #168a45;
}

.prototype-price-tab.active {
    background: #148a43;
    border-color: #148a43;
    color: #ffffff;
}

.prototype-price-chart-card {
    background: #ffffff;
    border: 1px solid #e5e9e6;
    border-radius: 18px;
    overflow: hidden;
}

.prototype-chart-title {
    padding: 20px 22px 8px;
}

.prototype-chart-title h3 {
    margin: 0;
    color: #26352b;
    font-size: 17px;
    font-weight: 700;
}

.prototype-chart-area {
    position: relative;
    width: 100%;
    padding: 4px 20px 18px;
    box-sizing: border-box;
}

.prototype-chart-svg {
    display: block;
    width: 100%;
    height: 390px;
    overflow: visible;
}

.prototype-chart-grid {
    stroke: #e6ebe7;
    stroke-width: 1;
}

.prototype-chart-axis {
    stroke: #9aa79d;
    stroke-width: 1;
}

.prototype-chart-label {
    fill: #68766c;
    font-size: 12px;
}

.prototype-chart-line {
    fill: none;
    stroke-width: 2.4;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.prototype-chart-line.line-1,
.prototype-chart-point.line-1 {
    stroke: #168a45;
}

.prototype-chart-line.line-2,
.prototype-chart-point.line-2 {
    stroke: #2788b7;
}

.prototype-chart-line.line-3,
.prototype-chart-point.line-3 {
    stroke: #7765c5;
}

.prototype-chart-line.line-4,
.prototype-chart-point.line-4 {
    stroke: #d3942e;
}

.prototype-chart-line.line-5,
.prototype-chart-point.line-5 {
    stroke: #cf5f5f;
}

.prototype-chart-point {
    fill: #ffffff;
    stroke-width: 2.3;
    cursor: pointer;
    transition: r 0.15s ease;
}

.prototype-chart-point:hover {
    r: 6;
}

.prototype-chart-legend {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 22px;
    flex-wrap: wrap;
    margin-top: -10px;
    padding-bottom: 10px;
}

.prototype-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #68766c;
    font-size: 12px;
}

.prototype-legend-symbol {
    position: relative;
    width: 18px;
    height: 8px;
}

.prototype-legend-symbol::before {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: 3px;
    height: 2px;
    background: currentColor;
}

.prototype-legend-symbol::after {
    content: "";
    position: absolute;
    width: 5px;
    height: 5px;
    left: 6px;
    top: 1px;
    border: 2px solid currentColor;
    background: #ffffff;
    border-radius: 50%;
}

.prototype-legend-symbol.line-1 {
    color: #168a45;
}

.prototype-legend-symbol.line-2 {
    color: #2788b7;
}

.prototype-legend-symbol.line-3 {
    color: #7765c5;
}

.prototype-legend-symbol.line-4 {
    color: #d3942e;
}

.prototype-legend-symbol.line-5 {
    color: #cf5f5f;
}


/* Tooltip */

.price-chart-tooltip {
    position: fixed;
    z-index: 9999;
    min-width: 150px;
    padding: 11px 13px;
    background: rgba(255, 255, 255, 0.98);
    border: 1px solid #dce3dd;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    pointer-events: none;
    display: none;
}

.price-chart-tooltip strong {
    display: block;
    margin-bottom: 5px;
    color: #29372d;
    font-size: 13px;
}

.price-chart-tooltip span {
    display: block;
    color: #657269;
    font-size: 13px;
}


/* Responsive */

@media (max-width: 900px) {

    .prototype-chart-svg {
        min-width: 850px;
    }

    .prototype-chart-area {
        overflow-x: auto;
    }

}

</style>


<div class="prototype-price-page">

    <!-- Crop buttons -->

    <div class="prototype-price-tabs">

        <?php foreach ($cropLabels as $cropKey => $label): ?>

            <?php
            $isActive = in_array(
                $cropKey,
                $selectedCrops,
                true
            );
            ?>

            <a
                href="price_trends.php?crop=<?= urlencode($cropKey) ?>"
                class="prototype-price-tab <?= $isActive ? 'active' : '' ?>"
            >
                <?= htmlspecialchars($label) ?>
            </a>

        <?php endforeach; ?>


        <?php if ($requestedCrop !== ''): ?>

            <a
                href="price_trends.php"
                class="prototype-price-tab"
            >
                Compare
            </a>

        <?php endif; ?>

    </div>


    <!-- Chart -->

    <div class="prototype-price-chart-card">

        <div class="prototype-chart-title">

            <h3>
                Price History (Sep 2024 – Mar 2025)
            </h3>

        </div>


        <div class="prototype-chart-area">

            <svg
                class="prototype-chart-svg"
                viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight ?>"
                preserveAspectRatio="xMidYMid meet"
                aria-label="Market crop price history"
            >

                <?php

                $yTicks = [
                    100000,
                    75000,
                    50000,
                    25000,
                    0
                ];

                ?>

                <!-- Horizontal grid / Y axis -->

                <?php foreach ($yTicks as $tick): ?>

                    <?php
                    $y =
                        $top +
                        (1 - ($tick / $chartMax)) *
                        $plotHeight;
                    ?>

                    <line
                        x1="<?= $left ?>"
                        y1="<?= $y ?>"
                        x2="<?= $chartWidth - $right ?>"
                        y2="<?= $y ?>"
                        class="prototype-chart-grid"
                    />

                    <text
                        x="<?= $left - 10 ?>"
                        y="<?= $y + 4 ?>"
                        text-anchor="end"
                        class="prototype-chart-label"
                    >
                        <?php if ($tick === 0): ?>
                            ৳0k
                        <?php else: ?>
                            ৳<?= (int) ($tick / 1000) ?>k
                        <?php endif; ?>
                    </text>

                <?php endforeach; ?>


                <!-- Y axis -->

                <line
                    x1="<?= $left ?>"
                    y1="<?= $top ?>"
                    x2="<?= $left ?>"
                    y2="<?= $top + $plotHeight ?>"
                    class="prototype-chart-axis"
                />


                <!-- X axis -->

                <line
                    x1="<?= $left ?>"
                    y1="<?= $top + $plotHeight ?>"
                    x2="<?= $chartWidth - $right ?>"
                    y2="<?= $top + $plotHeight ?>"
                    class="prototype-chart-axis"
                />


                <!-- Month labels -->

                <?php foreach ($months as $index => $month): ?>

                    <?php

                    $point = getChartPoint(
                        $index,
                        0,
                        count($months),
                        $left,
                        $top,
                        $plotWidth,
                        $plotHeight,
                        $chartMax
                    );

                    $shortMonth = substr($month, 0, 3);

                    ?>

                    <text
                        x="<?= $point['x'] ?>"
                        y="<?= $top + $plotHeight + 25 ?>"
                        text-anchor="middle"
                        class="prototype-chart-label"
                    >
                        <?= htmlspecialchars($shortMonth) ?>
                    </text>

                <?php endforeach; ?>


                <!-- Price lines -->

                <?php foreach ($selectedCrops as $seriesIndex => $crop): ?>

                    <?php

                    if (!isset($allPriceData[$crop])) {
                        continue;
                    }

                    $points = [];
                    $pointData = [];

                    foreach ($months as $index => $month) {

                        if (
                            !isset(
                                $allPriceData[$crop]['prices'][$month]
                            )
                        ) {
                            continue;
                        }

                        /*
                         * price_data.php stores ৳ / kg.
                         * Prototype displays ৳ / ton.
                         */
                        $pricePerTon =
                            (float) $allPriceData[$crop]['prices'][$month]
                            * 1000;

                        $point = getChartPoint(
                            $index,
                            $pricePerTon,
                            count($months),
                            $left,
                            $top,
                            $plotWidth,
                            $plotHeight,
                            $chartMax
                        );

                        $points[] =
                            $point['x'] . ',' . $point['y'];

                        $pointData[] = [
                            'x' => $point['x'],
                            'y' => $point['y'],
                            'month' => $month,
                            'price' => $pricePerTon
                        ];
                    }

                    $lineClass =
                        'line-' . ($seriesIndex + 1);

                    ?>


                    <polyline
                        points="<?= htmlspecialchars(implode(' ', $points)) ?>"
                        class="prototype-chart-line <?= $lineClass ?>"
                    />


                    <?php foreach ($pointData as $point): ?>

                        <circle
                            cx="<?= $point['x'] ?>"
                            cy="<?= $point['y'] ?>"
                            r="4"
                            class="prototype-chart-point <?= $lineClass ?>"
                            data-crop="<?= htmlspecialchars($cropLabels[$crop] ?? $crop, ENT_QUOTES) ?>"
                            data-month="<?= htmlspecialchars($point['month'], ENT_QUOTES) ?>"
                            data-price="<?= number_format($point['price'], 0, '.', '') ?>"
                        />

                    <?php endforeach; ?>

                <?php endforeach; ?>

            </svg>


            <!-- Legend -->

            <div class="prototype-chart-legend">

                <?php foreach ($selectedCrops as $index => $crop): ?>

                    <span class="prototype-legend-item">

                        <i
                            class="prototype-legend-symbol line-<?= $index + 1 ?>"
                        ></i>

                        <?= htmlspecialchars(
                            $cropLabels[$crop] ?? $crop
                        ) ?>

                    </span>

                <?php endforeach; ?>

            </div>

        </div>

    </div>

</div>


<!-- Tooltip -->

<div
    class="price-chart-tooltip"
    id="priceChartTooltip"
></div>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const tooltip =
            document.getElementById(
                "priceChartTooltip"
            );

        const points =
            document.querySelectorAll(
                ".prototype-chart-point"
            );


        points.forEach(function (point) {

            point.addEventListener(
                "mouseenter",
                function () {

                    const crop =
                        point.dataset.crop;

                    const month =
                        point.dataset.month;

                    const price =
                        Number(
                            point.dataset.price
                        ).toLocaleString();


                    tooltip.innerHTML =
                        "<strong>" +
                        month +
                        "</strong>" +
                        "<span>" +
                        crop +
                        ": ৳" +
                        price +
                        "</span>";


                    tooltip.style.display =
                        "block";

                }
            );


            point.addEventListener(
                "mousemove",
                function (event) {

                    tooltip.style.left =
                        event.clientX +
                        15 +
                        "px";

                    tooltip.style.top =
                        event.clientY -
                        20 +
                        "px";

                }
            );


            point.addEventListener(
                "mouseleave",
                function () {

                    tooltip.style.display =
                        "none";

                }
            );

        });

    }
);

</script>


<?php
require_once __DIR__ . '/includes/footer.php';
?>