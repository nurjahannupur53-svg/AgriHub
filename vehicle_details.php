<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/vehicle_data.php';
require_once __DIR__ . '/includes/delivery_data.php';

$id = trim($_GET['id'] ?? '');

$vehicle = findVehicleById($id);

if (!$vehicle) {
    header('Location: vehicles.php');
    exit;
}

$relatedDeliveries = [];

foreach (getAllDeliveries() as $delivery) {

    if ($delivery['vehicle_id'] === $vehicle['id']) {
        $relatedDeliveries[] = $delivery;
    }
}

$pageTitle = 'Vehicle Details';
$pageSubtitle =
    $vehicle['id'] . ' — ' . $vehicle['registration'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="vehicles.php"
        class="btn btn-light"
    >
        ← Back to Vehicles
    </a>


    <div class="detail-action-group">

        <a
            href="vehicle_edit.php?id=<?= urlencode($vehicle['id']) ?>"
            class="btn btn-primary"
        >
            Edit Vehicle
        </a>

        <a
            href="iot_telematics.php?vehicle=<?= urlencode($vehicle['id']) ?>"
            class="btn btn-light"
        >
            IoT Telematics
        </a>

    </div>

</div>


<div class="app-card details-section">

    <div class="card-header">
        <h3>Vehicle Information</h3>
    </div>


    <div class="card-body">

        <div class="info-grid">

            <div class="info-item">
                <span>Vehicle ID</span>
                <strong><?= htmlspecialchars($vehicle['id']) ?></strong>
            </div>

            <div class="info-item">
                <span>Registration</span>
                <strong><?= htmlspecialchars($vehicle['registration']) ?></strong>
            </div>

            <div class="info-item">
                <span>Vehicle Type</span>
                <strong><?= htmlspecialchars($vehicle['type']) ?></strong>
            </div>

            <div class="info-item">
                <span>Capacity</span>
                <strong><?= htmlspecialchars($vehicle['capacity']) ?></strong>
            </div>

            <div class="info-item">
                <span>Driver</span>
                <strong><?= htmlspecialchars($vehicle['driver']) ?></strong>
            </div>

            <div class="info-item">
                <span>Driver Phone</span>

                <strong>
                    <?= $vehicle['phone'] !== ''
                        ? htmlspecialchars($vehicle['phone'])
                        : '—' ?>
                </strong>
            </div>

            <div class="info-item">

                <span>Status</span>

                <?php
                $statusClass = 'status-warning';

                if (
                    in_array(
                        $vehicle['status'],
                        ['Active', 'Available'],
                        true
                    )
                ) {
                    $statusClass = 'status-success';
                } elseif ($vehicle['status'] === 'In Transit') {
                    $statusClass = 'status-info';
                } elseif ($vehicle['status'] === 'Maintenance') {
                    $statusClass = 'status-danger';
                }
                ?>

                <span class="status-badge <?= $statusClass ?>">
                    <?= htmlspecialchars($vehicle['status']) ?>
                </span>

            </div>

        </div>

    </div>

</div>


<div class="app-card details-section">

    <div class="card-header">

        <div>
            <h3>Delivery History</h3>

            <span class="card-subtitle">
                <?= count($relatedDeliveries) ?> linked deliveries
            </span>
        </div>

    </div>


    <?php if (!empty($relatedDeliveries)): ?>

        <div class="table-responsive">

            <table class="app-table">

                <thead>

                <tr>
                    <th>Delivery</th>
                    <th>Order</th>
                    <th>Buyer</th>
                    <th>Driver</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                </thead>

                <tbody>

                <?php foreach ($relatedDeliveries as $delivery): ?>

                    <tr>

                        <td>
                            <strong>
                                <?= htmlspecialchars($delivery['id']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($delivery['order_id']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($delivery['buyer']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($delivery['driver']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($delivery['delivery_date']) ?>
                        </td>

                        <td>

                            <span class="status-badge <?= $delivery['status'] === 'Delivered' ? 'status-success' : 'status-info' ?>">
                                <?= htmlspecialchars($delivery['status']) ?>
                            </span>

                        </td>

                        <td>

                            <a
                                href="delivery_details.php?id=<?= urlencode($delivery['id']) ?>"
                                class="action-link view"
                            >
                                View
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div class="empty-detail-state">
            No delivery history found for this vehicle.
        </div>

    <?php endif; ?>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>