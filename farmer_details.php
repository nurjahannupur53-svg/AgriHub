<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/farmer_data.php';

$id = trim($_GET['id'] ?? 'F001');

$farmer = findFarmerById($id);

if (!$farmer) {
    header('Location: farmers.php');
    exit;
}

$pageTitle = 'Farmer: ' . $farmer['name'];
$pageSubtitle = 'Farmer ID: ' . $farmer['id'];

require_once __DIR__ . '/includes/header.php';


$crops = [];

$batches = [];
$equipment = [];
$consultations = [];


/*
|--------------------------------------------------------------------------
| Rahim Uddin screenshot data
|--------------------------------------------------------------------------
*/

if ($farmer['id'] === 'F001') {

    $crops = [
        [
            'id' => 'C001',
            'name' => 'Aman Rice',
            'type' => 'Grain',
            'area' => '5.0 acres',
            'status' => 'Growing'
        ],
        [
            'id' => 'C002',
            'name' => 'Mustard',
            'type' => 'Oilseed',
            'area' => '3.5 acres',
            'status' => 'Harvested'
        ]
    ];

    $batches = [
        [
            'id' => 'HB001',
            'crop' => 'Aman Rice',
            'quantity' => '7.2 ton',
            'status' => 'Approved'
        ],
        [
            'id' => 'HB002',
            'crop' => 'Mustard',
            'quantity' => '2 ton',
            'status' => 'Pending'
        ]
    ];

    $equipment = [
        [
            'name' => 'Combine Harvester',
            'dates' => '2024-11-25 → 2024-11-28',
            'status' => 'Returned'
        ]
    ];

    $consultations = [
        [
            'topic' => 'Rice Blast Disease Control',
            'date' => '2024-10-15',
            'consultant' => 'Dr. Farid Ahmed',
            'status' => 'Completed'
        ]
    ];
}

?>

<div class="details-top-actions">

    <a href="farmers.php" class="btn btn-light">
        ← Back to Farmers
    </a>

    <a
        href="farmer_edit.php?id=<?= urlencode($farmer['id']) ?>"
        class="btn btn-primary"
    >
        Edit Farmer
    </a>

</div>


<!-- FARMER INFORMATION -->

<div class="app-card details-section">

    <div class="card-header">
        <h3>Farmer Information</h3>
    </div>

    <div class="card-body">

        <div class="info-grid">

            <div class="info-item">
                <span>Farmer ID</span>
                <strong><?= htmlspecialchars($farmer['id']) ?></strong>
            </div>

            <div class="info-item">
                <span>Name</span>
                <strong><?= htmlspecialchars($farmer['name']) ?></strong>
            </div>

            <div class="info-item">
                <span>Phone</span>
                <strong><?= htmlspecialchars($farmer['phone']) ?></strong>
            </div>

            <div class="info-item">
                <span>Email</span>
                <strong>
                    <?= htmlspecialchars($farmer['email'] ?? 'Not provided') ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Location</span>
                <strong><?= htmlspecialchars($farmer['location']) ?></strong>
            </div>

            <div class="info-item">
                <span>Total Land</span>
                <strong><?= htmlspecialchars($farmer['land']) ?></strong>
            </div>

            <div class="info-item">
                <span>NID</span>
                <strong>
                    <?= htmlspecialchars($farmer['nid'] ?? 'Not provided') ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Joined</span>
                <strong>
                    <?= htmlspecialchars($farmer['joined'] ?? date('Y-m-d')) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Status</span>

                <?php if ($farmer['status'] === 'Active'): ?>
                    <span class="status-badge status-success">
                        Active
                    </span>
                <?php else: ?>
                    <span class="status-badge status-danger">
                        Inactive
                    </span>
                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<!-- REGISTERED CROPS -->

<div class="app-card details-section">

    <div class="card-header">

        <h3>Registered Crops</h3>

        <a href="crops.php" class="small-link">
            View all
        </a>

    </div>

    <?php if ($crops): ?>

        <div class="table-responsive">

            <table class="app-table">

                <thead>
                <tr>
                    <th>Crop ID</th>
                    <th>Crop</th>
                    <th>Type</th>
                    <th>Area</th>
                    <th>Status</th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($crops as $crop): ?>

                    <tr>
                        <td><strong><?= htmlspecialchars($crop['id']) ?></strong></td>
                        <td><?= htmlspecialchars($crop['name']) ?></td>
                        <td><?= htmlspecialchars($crop['type']) ?></td>
                        <td><?= htmlspecialchars($crop['area']) ?></td>

                        <td>
                            <span class="status-badge <?= $crop['status'] === 'Growing' ? 'status-success' : 'status-info' ?>">
                                <?= htmlspecialchars($crop['status']) ?>
                            </span>
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div class="empty-state">
            No registered crops found for this farmer.
        </div>

    <?php endif; ?>

</div>


<!-- HARVEST BATCHES -->

<div class="app-card details-section">

    <div class="card-header">

        <h3>Harvest Batches</h3>

        <a href="harvest_batches.php" class="small-link">
            View all
        </a>

    </div>

    <?php if ($batches): ?>

        <div class="table-responsive">

            <table class="app-table">

                <thead>
                <tr>
                    <th>Batch ID</th>
                    <th>Crop</th>
                    <th>Quantity</th>
                    <th>Status</th>
                </tr>
                </thead>

                <tbody>

                <?php foreach ($batches as $batch): ?>

                    <tr>

                        <td>
                            <a
                                class="table-id-link"
                                href="batch_details.php?id=<?= urlencode($batch['id']) ?>"
                            >
                                <?= htmlspecialchars($batch['id']) ?>
                            </a>
                        </td>

                        <td><?= htmlspecialchars($batch['crop']) ?></td>

                        <td><?= htmlspecialchars($batch['quantity']) ?></td>

                        <td>
                            <span class="status-badge <?= $batch['status'] === 'Approved' ? 'status-success' : 'status-warning' ?>">
                                <?= htmlspecialchars($batch['status']) ?>
                            </span>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <div class="empty-state">
            No harvest batches found for this farmer.
        </div>

    <?php endif; ?>

</div>


<div class="details-two-column">

    <!-- EQUIPMENT -->

    <div class="app-card">

        <div class="card-header">

            <h3>Equipment Bookings</h3>

            <a href="equipment_booking.php" class="small-link">
                View all
            </a>

        </div>

        <?php if ($equipment): ?>

            <div class="simple-record-list">

                <?php foreach ($equipment as $item): ?>

                    <div class="simple-record">

                        <div>
                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                            <span><?= htmlspecialchars($item['dates']) ?></span>
                        </div>

                        <span class="status-badge status-info">
                            <?= htmlspecialchars($item['status']) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">
                No equipment bookings.
            </div>

        <?php endif; ?>

    </div>


    <!-- CONSULTATIONS -->

    <div class="app-card">

        <div class="card-header">

            <h3>Consultations</h3>

            <a href="consultation.php" class="small-link">
                View all
            </a>

        </div>

        <?php if ($consultations): ?>

            <div class="simple-record-list">

                <?php foreach ($consultations as $item): ?>

                    <div class="simple-record consultation-record">

                        <div>

                            <strong>
                                <?= htmlspecialchars($item['topic']) ?>
                            </strong>

                            <span>
                                <?= htmlspecialchars($item['date']) ?>
                                —
                                <?= htmlspecialchars($item['consultant']) ?>
                            </span>

                        </div>

                        <span class="status-badge status-success">
                            <?= htmlspecialchars($item['status']) ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">
                No consultations.
            </div>

        <?php endif; ?>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>