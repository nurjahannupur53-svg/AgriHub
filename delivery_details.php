<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/delivery_data.php';

$id = trim($_GET['id'] ?? '');

$delivery = findDeliveryById($id);

if (!$delivery) {
    header('Location: deliveries.php');
    exit;
}

$pageTitle = 'Delivery Details';
$pageSubtitle =
    $delivery['id'] . ' — ' . $delivery['buyer'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="deliveries.php"
        class="btn btn-light"
    >
        ← Back to Deliveries
    </a>


    <div class="detail-action-group">

        <?php if ($delivery['status'] !== 'Delivered'): ?>

            <a
                href="delivery_update.php?id=<?= urlencode($delivery['id']) ?>"
                class="btn btn-primary"
            >
                Update Delivery
            </a>

        <?php endif; ?>

        <a
            href="vehicles.php?id=<?= urlencode($delivery['vehicle_id']) ?>"
            class="btn btn-light"
        >
            View Vehicle
        </a>

        <a
            href="iot_telematics.php?vehicle=<?= urlencode($delivery['vehicle_id']) ?>"
            class="btn btn-light"
        >
            IoT Tracking
        </a>

    </div>

</div>


<div class="app-card details-section">

    <div class="card-header">
        <h3>Delivery Information</h3>
    </div>


    <div class="card-body">

        <div class="info-grid">

            <div class="info-item">
                <span>Delivery ID</span>
                <strong><?= htmlspecialchars($delivery['id']) ?></strong>
            </div>

            <div class="info-item">

                <span>Market Order</span>

                <a
                    href="market_order_details.php?id=<?= urlencode($delivery['order_id']) ?>"
                    class="detail-value-link"
                >
                    <?= htmlspecialchars($delivery['order_id']) ?>
                </a>

            </div>

            <div class="info-item">
                <span>Destination / Buyer</span>
                <strong><?= htmlspecialchars($delivery['buyer']) ?></strong>
            </div>

            <div class="info-item">
                <span>Driver</span>
                <strong><?= htmlspecialchars($delivery['driver']) ?></strong>
            </div>

            <div class="info-item">
                <span>Vehicle</span>

                <strong>
                    <?= htmlspecialchars($delivery['vehicle']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Delivery Date</span>

                <strong>
                    <?= htmlspecialchars($delivery['delivery_date']) ?>
                </strong>
            </div>

            <div class="info-item">

                <span>Status</span>

                <span class="status-badge <?= $delivery['status'] === 'Delivered' ? 'status-success' : 'status-info' ?>">
                    <?= htmlspecialchars($delivery['status']) ?>
                </span>

            </div>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?> 