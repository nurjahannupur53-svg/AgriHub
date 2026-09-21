<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/equipment_data.php';

$id = trim($_GET['id'] ?? '');

$booking = findEquipmentBookingById($id);

if (!$booking) {
    header('Location: equipment_booking.php');
    exit;
}

$pageTitle = 'Equipment Booking';
$pageSubtitle =
    $booking['id'] . ' — ' . $booking['equipment'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="equipment_booking.php"
        class="btn btn-light"
    >
        ← Back to Equipment
    </a>


    <?php if (
        !in_array(
            $booking['status'],
            ['Returned', 'Cancelled'],
            true
        )
    ): ?>

        <div class="detail-action-group">

            <a
                href="equipment_update.php?id=<?= urlencode($booking['id']) ?>"
                class="btn btn-primary"
            >
                Update Booking
            </a>

            <a
                href="equipment_cancel.php?id=<?= urlencode($booking['id']) ?>"
                class="btn btn-light"
                data-confirm="Cancel this equipment booking?"
            >
                Cancel Booking
            </a>

        </div>

    <?php endif; ?>

</div>


<div class="app-card details-section">

    <div class="card-header">
        <h3>Booking Information</h3>
    </div>

    <div class="card-body">

        <div class="info-grid">

            <div class="info-item">
                <span>Booking ID</span>
                <strong><?= htmlspecialchars($booking['id']) ?></strong>
            </div>

            <div class="info-item">
                <span>Equipment</span>
                <strong><?= htmlspecialchars($booking['equipment']) ?></strong>
            </div>

            <div class="info-item">
                <span>Purpose</span>
                <strong><?= htmlspecialchars($booking['purpose']) ?></strong>
            </div>

            <div class="info-item">

                <span>Farmer</span>

                <a
                    href="farmer_details.php?id=<?= urlencode($booking['farmer_id']) ?>"
                    class="detail-value-link"
                >
                    <?= htmlspecialchars($booking['farmer']) ?>
                </a>

            </div>

            <div class="info-item">
                <span>Start Date</span>
                <strong><?= htmlspecialchars($booking['start_date']) ?></strong>
            </div>

            <div class="info-item">
                <span>End Date</span>
                <strong><?= htmlspecialchars($booking['end_date']) ?></strong>
            </div>

            <div class="info-item">

                <span>Status</span>

                <?php
                $statusClass = 'status-warning';

                if ($booking['status'] === 'Active') {
                    $statusClass = 'status-success';
                } elseif ($booking['status'] === 'Returned') {
                    $statusClass = 'status-info';
                } elseif ($booking['status'] === 'Cancelled') {
                    $statusClass = 'status-danger';
                }
                ?>

                <span class="status-badge <?= $statusClass ?>">
                    <?= htmlspecialchars($booking['status']) ?>
                </span>

            </div>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>