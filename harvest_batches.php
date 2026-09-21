<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/batch_data.php';
require_once __DIR__ . '/includes/crop_data.php';

$pageTitle = 'Harvest Batches';
$pageSubtitle = '8 harvest batches';

require_once __DIR__ . '/includes/header.php';

$batches = getAllBatches();
$crops = getAllCrops();

$cropFilter = trim($_GET['crop'] ?? '');
$success = $_GET['success'] ?? '';

if ($cropFilter !== '') {
    $batches = array_values(
        array_filter(
            $batches,
            function ($batch) use ($cropFilter) {
                return $batch['crop_id'] === $cropFilter;
            }
        )
    );
}

?>

<?php if ($success === 'added'): ?>
    <div class="flash-message flash-success">
        Harvest batch registered successfully.
    </div>
<?php endif; ?>


<div class="page-toolbar">

    <div class="table-search">
        <span>⌕</span>

        <input
            type="text"
            id="batchSearch"
            placeholder="Search batch, crop or farmer"
        >
    </div>


    <div class="toolbar-right">

        <?php if ($cropFilter !== ''): ?>

            <a href="harvest_batches.php" class="btn btn-light">
                Clear Crop Filter
            </a>

        <?php endif; ?>

        <button
            type="button"
            class="btn btn-primary"
            data-modal-open="addBatchModal"
        >
            + Add Harvest Batch
        </button>

    </div>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table class="app-table" id="batchTable">

            <thead>
            <tr>
                <th>Batch ID</th>
                <th>Crop</th>
                <th>Farmer</th>
                <th>Harvest Date</th>
                <th>Quantity</th>
                <th>QC Status</th>
                <th>Location</th>
                <th>Inventory</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>

            <?php if ($batches): ?>

                <?php foreach ($batches as $batch): ?>

                    <tr>

                        <td>
                            <strong>
                                <?= htmlspecialchars($batch['id']) ?>
                            </strong>
                        </td>

                        <td>
                            <a
                                href="crop_details.php?id=<?= urlencode($batch['crop_id']) ?>"
                                class="table-id-link"
                            >
                                <?= htmlspecialchars($batch['crop']) ?>
                            </a>
                        </td>

                        <td>
                            <a
                                href="farmer_details.php?id=<?= urlencode($batch['farmer_id']) ?>"
                                class="table-id-link"
                            >
                                <?= htmlspecialchars($batch['farmer']) ?>
                            </a>
                        </td>

                        <td>
                            <?= htmlspecialchars($batch['harvest_date']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($batch['quantity']) ?>
                        </td>

                        <td>

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

                        </td>

                        <td>
                            <?= htmlspecialchars($batch['location']) ?>
                        </td>

                        <td>

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

                        </td>

                        <td>

                            <div class="action-buttons">

                                <a
                                    href="batch_details.php?id=<?= urlencode($batch['id']) ?>"
                                    class="action-link view"
                                >
                                    View
                                </a>

                                <a
                                    href="quality_checks.php?batch=<?= urlencode($batch['id']) ?>"
                                    class="action-link qc"
                                >
                                    QC
                                </a>

                                <?php if ($batch['qc_status'] === 'Approved'): ?>

                                    <a
                                        href="inventory.php?batch=<?= urlencode($batch['id']) ?>"
                                        class="action-link inventory"
                                    >
                                        Inv.
                                    </a>

                                <?php endif; ?>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            No harvest batches found.
                        </div>
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- ADD HARVEST BATCH MODAL -->

<div class="modal-overlay" id="addBatchModal">

    <div class="app-modal">

        <div class="modal-header">

            <div>
                <h2>Add Harvest Batch</h2>
                <p>Register a harvested crop batch.</p>
            </div>

            <button
                type="button"
                class="modal-close"
                data-modal-close
            >
                ×
            </button>

        </div>


        <form
            action="batch_add.php"
            method="POST"
            class="modal-body"
        >

            <div class="app-form-group">

                <label>Crop</label>

                <select
                    name="crop_id"
                    class="app-select"
                    required
                >

                    <option value="">
                        Select crop
                    </option>

                    <?php foreach ($crops as $crop): ?>

                        <option
                            value="<?= htmlspecialchars($crop['id']) ?>"
                            <?= $cropFilter === $crop['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($crop['id']) ?>
                            —
                            <?= htmlspecialchars($crop['name']) ?>
                            —
                            <?= htmlspecialchars($crop['farmer']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Harvest Date</label>

                    <input
                        type="date"
                        name="harvest_date"
                        class="app-input"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Quantity (ton)</label>

                    <input
                        type="number"
                        name="quantity"
                        class="app-input"
                        min="0.1"
                        step="0.1"
                        placeholder="0.0"
                        required
                    >

                </div>

            </div>


            <div class="app-form-group">

                <label>Current Location</label>

                <input
                    type="text"
                    name="location"
                    class="app-input"
                    value="Field"
                    required
                >

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-modal-close
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Batch
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("batchSearch");

    const rows = document.querySelectorAll(
        "#batchTable tbody tr"
    );

    if (!input) {
        return;
    }

    input.addEventListener("input", function () {

        const search =
            input.value.toLowerCase().trim();

        rows.forEach(function (row) {

            const text =
                row.innerText.toLowerCase();

            row.style.display =
                text.includes(search)
                    ? ""
                    : "none";

        });

    });

});
</script>


<?php
require_once __DIR__ . '/includes/footer.php';
?>