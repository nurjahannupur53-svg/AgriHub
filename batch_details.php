<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/batch_data.php';

$id = trim($_GET['id'] ?? '');

$batch = findBatchById($id);

if (!$batch) {
    header('Location: harvest_batches.php');
    exit;
}

$pageTitle = 'Harvest Batch';
$pageSubtitle = $batch['id'] . ' — ' . $batch['crop'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="harvest_batches.php"
        class="btn btn-light"
    >
        ← Back to Batches
    </a>

    <div class="detail-action-group">

        <a
            href="quality_checks.php?batch=<?= urlencode($batch['id']) ?>"
            class="btn btn-light"
        >
            Quality Check
        </a>

        <?php if ($batch['qc_status'] === 'Approved'): ?>

            <a
                href="inventory.php?batch=<?= urlencode($batch['id']) ?>"
                class="btn btn-primary"
            >
                View Inventory
            </a>

        <?php endif; ?>

    </div>

</div>


<div class="app-card details-section">

    <div class="card-header">
        <h3>Batch Information</h3>
    </div>

    <div class="card-body">

        <div class="info-grid">

            <div class="info-item">
                <span>Batch ID</span>
                <strong>
                    <?= htmlspecialchars($batch['id']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Crop</span>

                <a
                    href="crop_details.php?id=<?= urlencode($batch['crop_id']) ?>"
                    class="detail-value-link"
                >
                    <?= htmlspecialchars($batch['crop']) ?>
                </a>
            </div>

            <div class="info-item">
                <span>Farmer</span>

                <a
                    href="farmer_details.php?id=<?= urlencode($batch['farmer_id']) ?>"
                    class="detail-value-link"
                >
                    <?= htmlspecialchars($batch['farmer']) ?>
                </a>
            </div>

            <div class="info-item">
                <span>Harvest Date</span>
                <strong>
                    <?= htmlspecialchars($batch['harvest_date']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Quantity</span>
                <strong>
                    <?= htmlspecialchars($batch['quantity']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Current Location</span>
                <strong>
                    <?= htmlspecialchars($batch['location']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Quality Check</span>

                <?php
                $qcClass = 'status-warning';

                if ($batch['qc_status'] === 'Approved') {
                    $qcClass = 'status-success';
                } elseif ($batch['qc_status'] === 'Rejected') {
                    $qcClass = 'status-danger';
                }
                ?>

                <span class="status-badge <?= $qcClass ?>">
                    <?= htmlspecialchars($batch['qc_status']) ?>
                </span>
            </div>

            <div class="info-item">
                <span>Inventory Status</span>

                <?php
                $inventoryClass = 'status-warning';

                if ($batch['inventory_status'] === 'In Inventory') {
                    $inventoryClass = 'status-success';
                } elseif ($batch['inventory_status'] === 'Sold') {
                    $inventoryClass = 'status-info';
                } elseif ($batch['inventory_status'] === 'Rejected') {
                    $inventoryClass = 'status-danger';
                }
                ?>

                <span class="status-badge <?= $inventoryClass ?>">
                    <?= htmlspecialchars($batch['inventory_status']) ?>
                </span>
            </div>

        </div>

    </div>

</div>


<div class="details-two-column">

    <div class="app-card">

        <div class="card-header">
            <h3>Quality Control</h3>
        </div>

        <div class="card-body">

            <p class="detail-description">
                Current quality-check status:
                <strong>
                    <?= htmlspecialchars($batch['qc_status']) ?>
                </strong>.
            </p>

            <a
                href="quality_checks.php?batch=<?= urlencode($batch['id']) ?>"
                class="btn btn-light"
            >
                Open Quality Checks
            </a>

        </div>

    </div>


    <div class="app-card">

        <div class="card-header">
            <h3>Inventory</h3>
        </div>

        <div class="card-body">

            <p class="detail-description">
                Current inventory status:
                <strong>
                    <?= htmlspecialchars($batch['inventory_status']) ?>
                </strong>.
            </p>

            <?php if ($batch['qc_status'] === 'Approved'): ?>

                <a
                    href="inventory.php?batch=<?= urlencode($batch['id']) ?>"
                    class="btn btn-primary"
                >
                    Open Inventory
                </a>

            <?php else: ?>

                <button
                    type="button"
                    class="btn btn-light"
                    disabled
                >
                    QC Approval Required
                </button>

            <?php endif; ?>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>