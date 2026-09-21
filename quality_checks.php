<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/qc_data.php';
require_once __DIR__ . '/includes/batch_data.php';

$pageTitle = 'Quality Checks';
$pageSubtitle = 'Quality inspection and batch approval';

require_once __DIR__ . '/includes/header.php';

$checks = getAllQualityChecks();
$allBatches = getAllBatches();

$batchFilter = trim($_GET['batch'] ?? '');
$success = $_GET['success'] ?? '';

$selectedBatch = null;
$selectedCheck = null;

if ($batchFilter !== '') {
    $selectedBatch = findBatchById($batchFilter);
    $selectedCheck = findQualityCheckByBatch($batchFilter);
}

?>

<?php if ($success === 'added'): ?>
    <div class="flash-message flash-success">
        Quality check saved successfully.
    </div>
<?php endif; ?>


<?php if ($selectedBatch): ?>

    <div class="selected-batch-card">

        <div>
            <span>Selected Batch</span>

            <strong>
                <?= htmlspecialchars($selectedBatch['id']) ?>
                —
                <?= htmlspecialchars($selectedBatch['crop']) ?>
            </strong>

            <small>
                <?= htmlspecialchars($selectedBatch['farmer']) ?>
                •
                <?= htmlspecialchars($selectedBatch['quantity']) ?>
            </small>
        </div>

        <div class="selected-batch-actions">

            <a
                href="batch_details.php?id=<?= urlencode($selectedBatch['id']) ?>"
                class="btn btn-light"
            >
                View Batch
            </a>

            <?php if (!$selectedCheck): ?>

                <button
                    type="button"
                    class="btn btn-primary"
                    data-modal-open="performQcModal"
                >
                    Perform QC
                </button>

            <?php endif; ?>

        </div>

    </div>

<?php endif; ?>


<div class="page-toolbar">

    <div class="table-search">

        <span>⌕</span>

        <input
            type="text"
            id="qcSearch"
            placeholder="Search QC, batch or inspector"
        >

    </div>

    <?php if ($batchFilter !== ''): ?>

        <a
            href="quality_checks.php"
            class="btn btn-light"
        >
            Clear Batch Filter
        </a>

    <?php endif; ?>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table class="app-table" id="qcTable">

            <thead>
            <tr>
                <th>QC ID</th>
                <th>Batch</th>
                <th>Crop</th>
                <th>Inspector</th>
                <th>Check Date</th>
                <th>Grade</th>
                <th>Moisture</th>
                <th>Result</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>

            <?php foreach ($checks as $check): ?>

                <?php
                if (
                    $batchFilter !== '' &&
                    $check['batch_id'] !== $batchFilter
                ) {
                    continue;
                }
                ?>

                <tr>

                    <td>
                        <strong>
                            <?= htmlspecialchars($check['id']) ?>
                        </strong>
                    </td>

                    <td>

                        <a
                            href="batch_details.php?id=<?= urlencode($check['batch_id']) ?>"
                            class="table-id-link"
                        >
                            <?= htmlspecialchars($check['batch_id']) ?>
                        </a>

                    </td>

                    <td>
                        <?= htmlspecialchars($check['crop']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($check['inspector']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($check['date']) ?>
                    </td>

                    <td>
                        <strong>
                            <?= htmlspecialchars($check['grade']) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($check['moisture']) ?>
                    </td>

                    <td>

                        <span class="status-badge <?= $check['result'] === 'Approved' ? 'status-success' : 'status-danger' ?>">
                            <?= htmlspecialchars($check['result']) ?>
                        </span>

                    </td>

                    <td>

                        <div class="action-buttons">

                            <?php if ($check['result'] === 'Approved'): ?>

                                <a
                                    href="inventory.php?batch=<?= urlencode($check['batch_id']) ?>"
                                    class="action-link inventory"
                                >
                                    Inventory
                                </a>

                            <?php endif; ?>

                            <a
                                href="batch_details.php?id=<?= urlencode($check['batch_id']) ?>"
                                class="action-link view"
                            >
                                Batch
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =====================================================
     PERFORM QUALITY CHECK MODAL
===================================================== -->

<?php if ($selectedBatch && !$selectedCheck): ?>

<div
    class="modal-overlay"
    id="performQcModal"
>

    <div class="app-modal">

        <div class="modal-header">

            <div>

                <h2>Perform Quality Check</h2>

                <p>
                    <?= htmlspecialchars($selectedBatch['id']) ?>
                    —
                    <?= htmlspecialchars($selectedBatch['crop']) ?>
                </p>

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
            action="qc_add.php"
            method="POST"
            class="modal-body"
        >

            <input
                type="hidden"
                name="batch_id"
                value="<?= htmlspecialchars($selectedBatch['id']) ?>"
            >


            <div class="app-form-group">

                <label>Inspector</label>

                <input
                    type="text"
                    name="inspector"
                    class="app-input"
                    placeholder="Inspector name"
                    required
                >

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Check Date</label>

                    <input
                        type="date"
                        name="date"
                        class="app-input"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Grade</label>

                    <select
                        name="grade"
                        class="app-select"
                        required
                    >
                        <option value="">Select grade</option>
                        <option>A+</option>
                        <option>A</option>
                        <option>B+</option>
                        <option>B</option>
                        <option>C</option>
                    </select>

                </div>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Moisture (%)</label>

                    <input
                        type="number"
                        name="moisture"
                        class="app-input"
                        min="0"
                        step="0.1"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Result</label>

                    <select
                        name="result"
                        class="app-select"
                        required
                    >
                        <option value="Approved">
                            Approved
                        </option>

                        <option value="Rejected">
                            Rejected
                        </option>
                    </select>

                </div>

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
                    Save Quality Check
                </button>

            </div>

        </form>

    </div>

</div>

<?php endif; ?>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const input =
        document.getElementById("qcSearch");

    const rows =
        document.querySelectorAll("#qcTable tbody tr");

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