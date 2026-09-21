<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/forecast_data.php';

$forecasts = getDemandForecasts();

$selectedCrop = trim($_GET['crop'] ?? 'All');

$validCrops = [
    'All',
    'Aman Rice',
    'Wheat',
    'Potato',
    'Lentil',
    'Maize'
];

if (!in_array($selectedCrop, $validCrops, true)) {
    $selectedCrop = 'All';
}

$pageTitle = 'Demand Forecast';
$pageSubtitle = 'AI-based crop demand prediction';

require_once __DIR__ . '/includes/header.php';


$displayForecasts = $forecasts;

if ($selectedCrop !== 'All') {

    $displayForecasts = array_values(
        array_filter(
            $forecasts,
            function ($forecast) use ($selectedCrop) {
                return $forecast['crop'] === $selectedCrop;
            }
        )
    );
}


/*
|--------------------------------------------------------------------------
| Chart maximum
|--------------------------------------------------------------------------
*/

$maxDemand = 1;

foreach ($forecasts as $forecast) {

    $maxDemand = max(
        $maxDemand,
        $forecast['current'],
        $forecast['predicted']
    );
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalCurrent = 0;
$totalPredicted = 0;
$totalConfidence = 0;

foreach ($forecasts as $forecast) {

    $totalCurrent += $forecast['current'];
    $totalPredicted += $forecast['predicted'];
    $totalConfidence += $forecast['confidence'];
}

$averageConfidence =
    count($forecasts) > 0
        ? round($totalConfidence / count($forecasts))
        : 0;

$growth =
    $totalCurrent > 0
        ? (($totalPredicted - $totalCurrent) / $totalCurrent) * 100
        : 0;

?>


<!-- SUMMARY -->

<div class="forecast-summary-grid">

    <div class="forecast-summary-card">

        <span>Current Demand</span>

        <strong>
            <?= number_format($totalCurrent) ?> ton
        </strong>

        <small>
            Across 5 major crops
        </small>

    </div>


    <div class="forecast-summary-card">

        <span>Predicted Demand</span>

        <strong>
            <?= number_format($totalPredicted) ?> ton
        </strong>

        <small>
            Next forecast period
        </small>

    </div>


    <div class="forecast-summary-card">

        <span>Expected Growth</span>

        <strong>
            +<?= number_format($growth, 1) ?>%
        </strong>

        <small>
            Compared with current demand
        </small>

    </div>


    <div class="forecast-summary-card">

        <span>Avg. Confidence</span>

        <strong>
            <?= $averageConfidence ?>%
        </strong>

        <small>
            Forecast confidence
        </small>

    </div>

</div>


<!-- FILTERS -->

<div class="forecast-toolbar">

    <div class="filter-buttons forecast-filters">

        <?php foreach ($validCrops as $crop): ?>

            <a
                href="demand_forecast.php?crop=<?= urlencode($crop) ?>"
                class="filter-btn <?= $selectedCrop === $crop ? 'active' : '' ?>"
            >
                <?= htmlspecialchars($crop) ?>
            </a>

        <?php endforeach; ?>

    </div>


    <div class="forecast-toolbar-actions">

        <a
            href="price_trends.php<?= $selectedCrop !== 'All' ? '?crop=' . urlencode($selectedCrop) : '' ?>"
            class="btn btn-light"
        >
            View Market Prices
        </a>

        <a
            href="market_orders.php<?= $selectedCrop !== 'All' ? '?crop=' . urlencode($selectedCrop) : '' ?>"
            class="btn btn-primary"
        >
            + Create Market Order
        </a>

    </div>

</div>


<!-- CHART -->

<div class="app-card forecast-chart-card">

    <div class="card-header">

        <div>
            <h3>Demand Comparison</h3>

            <span class="card-subtitle">
                Current vs predicted demand in metric tons
            </span>
        </div>


        <div class="chart-legend">

            <span>
                <i class="legend-dot current"></i>
                Current
            </span>

            <span>
                <i class="legend-dot predicted"></i>
                Predicted
            </span>

        </div>

    </div>


    <div class="card-body">

        <div class="forecast-chart">

            <?php foreach ($displayForecasts as $forecast): ?>

                <?php

                $currentHeight =
                    ($forecast['current'] / $maxDemand) * 100;

                $predictedHeight =
                    ($forecast['predicted'] / $maxDemand) * 100;

                ?>

                <div class="forecast-chart-group">

                    <div class="forecast-bars">

                        <div class="forecast-bar-wrap">

                            <span class="forecast-bar-value">
                                <?= number_format($forecast['current']) ?>
                            </span>

                            <div
                                class="forecast-bar current"
                                style="height: <?= $currentHeight ?>%;"
                            ></div>

                        </div>


                        <div class="forecast-bar-wrap">

                            <span class="forecast-bar-value">
                                <?= number_format($forecast['predicted']) ?>
                            </span>

                            <div
                                class="forecast-bar predicted"
                                style="height: <?= $predictedHeight ?>%;"
                            ></div>

                        </div>

                    </div>


                    <span class="forecast-chart-label">
                        <?= htmlspecialchars($forecast['crop']) ?>
                    </span>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>


<!-- FORECAST TABLE -->

<div class="app-card">

    <div class="card-header">

        <div>
            <h3>Forecast Details</h3>

            <span class="card-subtitle">
                Regional crop demand predictions
            </span>
        </div>

    </div>


    <div class="table-responsive">

        <table class="app-table">

            <thead>

            <tr>
                <th>Crop</th>
                <th>Current Demand</th>
                <th>Predicted Demand</th>
                <th>Change</th>
                <th>Confidence</th>
                <th>Primary Market</th>
                <th>Actions</th>
            </tr>

            </thead>


            <tbody>

            <?php foreach ($displayForecasts as $forecast): ?>

                <?php

                $change =
                    $forecast['predicted'] -
                    $forecast['current'];

                $changePercent =
                    $forecast['current'] > 0
                        ? ($change / $forecast['current']) * 100
                        : 0;

                ?>

                <tr>

                    <td>
                        <strong>
                            <?= htmlspecialchars($forecast['crop']) ?>
                        </strong>
                    </td>

                    <td>
                        <?= number_format($forecast['current']) ?>
                        ton
                    </td>

                    <td>
                        <?= number_format($forecast['predicted']) ?>
                        ton
                    </td>

                    <td>

                        <span class="forecast-growth">
                            ↑
                            <?= number_format($changePercent, 1) ?>%
                        </span>

                    </td>

                    <td>

                        <div class="confidence-cell">

                            <strong>
                                <?= $forecast['confidence'] ?>%
                            </strong>

                            <div class="confidence-track">

                                <div
                                    class="confidence-fill"
                                    style="width: <?= $forecast['confidence'] ?>%;"
                                ></div>

                            </div>

                        </div>

                    </td>

                    <td>
                        <?= htmlspecialchars($forecast['region']) ?>
                    </td>

                    <td>

                        <div class="action-buttons">

                            <a
                                href="price_trends.php?crop=<?= urlencode($forecast['crop']) ?>"
                                class="action-link view"
                            >
                                Prices
                            </a>

                            <a
                                href="market_orders.php?crop=<?= urlencode($forecast['crop']) ?>"
                                class="action-link order"
                            >
                                Order
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>