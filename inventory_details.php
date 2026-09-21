<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/inventory_data.php';

$id = trim($_GET['id'] ?? '');

$item = findInventoryById($id);

if (!$item) {
    header('Location: inventory.php');
    exit;
}

$pageTitle = 'Inventory Details';
$pageSubtitle = $item['id'] . ' — ' . $item['crop'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="inventory.php"
        class="btn btn-light"
    >
        ← Back to Inventory
    </a>

    <a
        href="inventory_update.php?id=<?= urlencode($item['id']) ?>"
        class="btn btn-primary"
    >
        Update Inventory
    </a>

</div>


<div class="app-card details-section">

    <div class="card-header">
        <h3>Stock Information</h3>
    </div>

    <div class="card-body">

        <div class="info-grid">

            <div class="info-item">
                <span>Inventory ID</span>

                <strong>
                    <?= htmlspecialchars($item['id']) ?>
                </strong>
            </div>


            <div class="info-item">
                <span>Harvest Batch</span>

                <a
                    href="batch_details.php?id=<?= urlencode($item['batch_id']) ?>"
                    class="detail-value-link"
                >
                    <?= htmlspecialchars($item['batch_id']) ?>
                </a>
            </div>


            <div class="info-item">
                <span>Crop</span>

                <strong>
                    <?= htmlspecialchars($item['crop']) ?>
                </strong>
            </div>


            <div class="info-item">
                <span>Total Stock</span>

                <strong>
                    <?= number_format((float) $item['total'], 1) ?>
                    ton
                </strong>
            </div>


            <div class="info-item">
                <span>Available Stock</span>

                <strong>
                    <?= number_format((float) $item['available'], 1) ?>
                    ton
                </strong>
            </div>


            <div class="info-item">
                <span>Reserved Stock</span>

                <strong>
                    <?= number_format((float) $item['reserved'], 1) ?>
                    ton
                </strong>
            </div>


            <div class="info-item">
                <span>Warehouse</span>

                <strong>
                    <?= htmlspecialchars($item['location']) ?>
                </strong>
            </div>


            <div class="info-item">
                <span>Low Stock Threshold</span>

                <strong>
                    <?= number_format((float) $item['threshold'], 1) ?>
                    ton
                </strong>
            </div>


            <div class="info-item">
                <span>Status</span>

                <?php
                $statusClass = 'status-success';

                if ($item['status'] === 'Partially Reserved') {
                    $statusClass = 'status-warning';
                } elseif ($item['status'] === 'Out of Stock') {
                    $statusClass = 'status-danger';
                }
                ?>

                <span class="status-badge <?= $statusClass ?>">
                    <?= htmlspecialchars($item['status']) ?>
                </span>

            </div>

        </div>

    </div>

</div>


<div class="details-two-column">

    <div class="app-card">

        <div class="card-header">
            <h3>Demand Forecast</h3>
        </div>

        <div class="card-body">

            <p class="detail-description">
                Review predicted demand for
                <?= htmlspecialchars($item['crop']) ?>
                before allocating stock.
            </p>

            <a
                href="demand_forecast.php?crop=<?= urlencode($item['crop']) ?>"
                class="btn btn-light"
            >
                View Forecast
            </a>

        </div>

    </div>


    <div class="app-card">

        <div class="card-header">
            <h3>Market Order</h3>
        </div>

        <div class="card-body">

            <p class="detail-description">
                Available stock:
                <strong>
                    <?= number_format((float) $item['available'], 1) ?>
                    ton
                </strong>.
            </p>

            <?php if ((float) $item['available'] > 0): ?>

                <a
                    href="market_orders.php?inventory=<?= urlencode($item['id']) ?>"
                    class="btn btn-primary"
                >
                    Create Market Order
                </a>

            <?php else: ?>

                <button
                    type="button"
                    class="btn btn-light"
                    disabled
                >
                    Out of Stock
                </button>

            <?php endif; ?>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>