<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/crop_data.php';

$id = trim($_GET['id'] ?? '');

$crop = findCropById($id);

if (!$crop) {
    header('Location: crops.php');
    exit;
}

$pageTitle = 'Crop: ' . $crop['name'];
$pageSubtitle = 'Crop ID: ' . $crop['id'];

require_once __DIR__ . '/includes/header.php';

$updated = isset($_GET['updated']);
?>

<?php if ($updated): ?>
    <div class="flash-message flash-success">
        Crop updated successfully.
    </div>
<?php endif; ?>


<div class="details-top-actions">

    <a href="crops.php" class="btn btn-light">
        ← Back to Crops
    </a>

    <a
        href="crop_edit.php?id=<?= urlencode($crop['id']) ?>"
        class="btn btn-primary"
    >
        Edit Crop
    </a>

</div>


<div class="app-card details-section">

    <div class="card-header">
        <h3>Crop Information</h3>
    </div>

    <div class="card-body">

        <div class="info-grid">

            <div class="info-item">
                <span>Crop ID</span>
                <strong>
                    <?= htmlspecialchars($crop['id']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Crop Name</span>
                <strong>
                    <?= htmlspecialchars($crop['name']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Crop Type</span>
                <strong>
                    <?= htmlspecialchars($crop['type']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Farmer</span>

                <a
                    class="detail-value-link"
                    href="farmer_details.php?id=<?= urlencode($crop['farmer_id']) ?>"
                >
                    <?= htmlspecialchars($crop['farmer']) ?>
                </a>
            </div>

            <div class="info-item">
                <span>Cultivation Area</span>
                <strong>
                    <?= htmlspecialchars($crop['area']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Status</span>

                <?php
                $statusClass = 'status-warning';

                if ($crop['status'] === 'Growing') {
                    $statusClass = 'status-success';
                } elseif ($crop['status'] === 'Harvested') {
                    $statusClass = 'status-info';
                }
                ?>

                <span class="status-badge <?= $statusClass ?>">
                    <?= htmlspecialchars($crop['status']) ?>
                </span>
            </div>

            <div class="info-item">
                <span>Planting Date</span>
                <strong>
                    <?= htmlspecialchars($crop['planting_date']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Expected Harvest</span>
                <strong>
                    <?= htmlspecialchars($crop['harvest_date']) ?>
                </strong>
            </div>

        </div>

    </div>

</div>


<div class="details-two-column">

    <div class="app-card">

        <div class="card-header">
            <h3>Farmer Information</h3>
        </div>

        <div class="card-body">

            <p class="detail-description">
                This crop is registered under
                <strong><?= htmlspecialchars($crop['farmer']) ?></strong>.
                Open the farmer profile to see land, contact,
                equipment booking and consultation information.
            </p>

            <a
                href="farmer_details.php?id=<?= urlencode($crop['farmer_id']) ?>"
                class="btn btn-light"
            >
                View Farmer
            </a>

        </div>

    </div>


    <div class="app-card">

        <div class="card-header">
            <h3>Harvest Batches</h3>
        </div>

        <div class="card-body">

            <p class="detail-description">
                View all harvest batches associated with
                <?= htmlspecialchars($crop['name']) ?>.
            </p>

            <a
                href="harvest_batches.php?crop=<?= urlencode($crop['id']) ?>"
                class="btn btn-primary"
            >
                View Batches
            </a>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>