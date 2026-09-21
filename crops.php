<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/crop_data.php';
require_once __DIR__ . '/includes/farmer_data.php';

$crops = getAllCrops();
$farmers = getAllFarmers();

$pageTitle = 'Crop Management';
$pageSubtitle = count($crops) . ' registered crops';

require_once __DIR__ . '/includes/header.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

?>

<?php if ($success === 'added'): ?>
    <div class="flash-message flash-success">
        Crop registered successfully.
    </div>
<?php endif; ?>

<?php if ($success === 'updated'): ?>
    <div class="flash-message flash-success">
        Crop updated successfully.
    </div>
<?php endif; ?>

<?php if ($success === 'deleted'): ?>
    <div class="flash-message flash-success">
        Crop deleted successfully.
    </div>
<?php endif; ?>

<?php if ($error === 'missing'): ?>
    <div class="flash-message flash-error">
        Please complete all required fields correctly.
    </div>
<?php endif; ?>

<?php if ($error === 'type'): ?>
    <div class="flash-message flash-error">
        Please select a valid crop type.
    </div>
<?php endif; ?>

<?php if ($error === 'date'): ?>
    <div class="flash-message flash-error">
        Harvest date cannot be earlier than planting date.
    </div>
<?php endif; ?>

<?php if ($error === 'database'): ?>
    <div class="flash-message flash-error">
        Unable to register the crop. Please try again.
    </div>
<?php endif; ?>

<?php if ($error === 'delete'): ?>
    <div class="flash-message flash-error">
        This crop cannot be deleted because it is linked to historical records.
    </div>
<?php endif; ?>


<div class="page-toolbar crop-toolbar">

    <div class="table-search">
        <span>⌕</span>

        <input
            type="text"
            id="cropSearch"
            placeholder="Search by name or ID"
        >
    </div>

    <div class="toolbar-right">

        <div class="filter-buttons">

            <button
                type="button"
                class="filter-btn active"
                data-status="all"
            >
                All
            </button>

            <button
                type="button"
                class="filter-btn"
                data-status="growing"
            >
                Growing
            </button>

            <button
                type="button"
                class="filter-btn"
                data-status="harvested"
            >
                Harvested
            </button>

            <button
                type="button"
                class="filter-btn"
                data-status="pending"
            >
                Pending
            </button>

        </div>

        <button
            type="button"
            class="btn btn-primary"
            data-modal-open="registerCropModal"
        >
            + Register Crop
        </button>

    </div>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table
            class="app-table"
            id="cropsTable"
        >

            <thead>
            <tr>
                <th>Crop ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Farmer</th>
                <th>Area</th>
                <th>Planting Date</th>
                <th>Harvest Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>

            <?php if (empty($crops)): ?>

                <tr>
                    <td
                        colspan="9"
                        style="text-align:center;"
                    >
                        No crops found.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($crops as $crop): ?>

                    <tr
                        data-status="<?= strtolower(
                            htmlspecialchars($crop['status'])
                        ) ?>"
                    >

                        <td>
                            <strong>
                                <?= htmlspecialchars($crop['id']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($crop['name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($crop['type']) ?>
                        </td>

                        <td>

                            <a
                                href="farmer_details.php?id=<?= urlencode(
                                    $crop['farmer_id']
                                ) ?>"
                                class="table-id-link"
                            >
                                <?= htmlspecialchars($crop['farmer']) ?>
                            </a>

                        </td>

                        <td>
                            <?= htmlspecialchars($crop['area']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $crop['planting_date']
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $crop['harvest_date']
                            ) ?>
                        </td>

                        <td>

                            <?php

                            $statusClass = 'status-warning';

                            if ($crop['status'] === 'Growing') {
                                $statusClass = 'status-success';
                            } elseif ($crop['status'] === 'Harvested') {
                                $statusClass = 'status-info';
                            }

                            ?>

                            <span
                                class="status-badge <?= $statusClass ?>"
                            >
                                <?= htmlspecialchars($crop['status']) ?>
                            </span>

                        </td>

                        <td>

                            <div class="action-buttons">

                                <a
                                    href="crop_details.php?id=<?= urlencode(
                                        $crop['id']
                                    ) ?>"
                                    class="action-link view"
                                >
                                    View
                                </a>

                                <a
                                    href="crop_edit.php?id=<?= urlencode(
                                        $crop['id']
                                    ) ?>"
                                    class="action-link edit"
                                >
                                    Edit
                                </a>

                                <a
                                    href="crop_delete.php?id=<?= urlencode(
                                        $crop['id']
                                    ) ?>"
                                    class="action-link delete"
                                    data-confirm="Delete <?= htmlspecialchars(
                                        $crop['name'],
                                        ENT_QUOTES
                                    ) ?>?"
                                >
                                    Delete
                                </a>

                                <a
                                    href="harvest_batches.php?crop=<?= urlencode(
                                        $crop['id']
                                    ) ?>"
                                    class="action-link batches"
                                >
                                    Batches
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- REGISTER CROP MODAL -->

<div
    class="modal-overlay"
    id="registerCropModal"
>

    <div class="app-modal">

        <div class="modal-header">

            <div>
                <h2>Register Crop</h2>
                <p>Add a crop to a registered farmer.</p>
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
            action="crop_add.php"
            method="POST"
            class="modal-body"
        >

            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Crop Name</label>

                    <input
                        type="text"
                        name="name"
                        class="app-input"
                        placeholder="e.g. Aman Rice"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Crop Type</label>

                    <select
                        name="type"
                        class="app-select"
                        required
                    >

                        <option value="">
                            Select type
                        </option>

                        <option value="Grain">
                            Grain / Cereal
                        </option>

                        <option value="Vegetable">
                            Vegetable
                        </option>

                        <option value="Fruit">
                            Fruit
                        </option>

                        <option value="Legume">
                            Pulse / Legume
                        </option>

                        <option value="Oilseed">
                            Oilseed
                        </option>

                        <option value="Fiber">
                            Fiber
                        </option>

                        <option value="Spice">
                            Spice
                        </option>

                        <option value="Tuber">
                            Tuber / Root Crop
                        </option>

                        <option value="Cash Crop">
                            Cash Crop
                        </option>

                        <option value="Fodder">
                            Fodder / Feed Crop
                        </option>

                        <option value="Plantation">
                            Plantation Crop
                        </option>

                        <option value="Other">
                            Other
                        </option>

                    </select>

                </div>

            </div>


            <div class="app-form-group">

                <label>Farmer</label>

                <select
                    name="farmer_id"
                    class="app-select"
                    required
                >

                    <option value="">
                        Select farmer
                    </option>

                    <?php foreach ($farmers as $farmer): ?>

                        <option
                            value="<?= htmlspecialchars(
                                $farmer['id']
                            ) ?>"
                        >
                            <?= htmlspecialchars($farmer['id']) ?>
                            —
                            <?= htmlspecialchars($farmer['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Area (acres)</label>

                    <input
                        type="number"
                        name="area"
                        class="app-input"
                        min="0.1"
                        step="0.1"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Status</label>

                    <select
                        name="status"
                        class="app-select"
                        required
                    >

                        <option value="Growing">
                            Growing
                        </option>

                        <option value="Pending">
                            Pending
                        </option>

                        <option value="Harvested">
                            Harvested
                        </option>

                    </select>

                </div>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Planting Date</label>

                    <input
                        type="date"
                        name="planting_date"
                        class="app-input"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Expected Harvest Date</label>

                    <input
                        type="date"
                        name="harvest_date"
                        class="app-input"
                        required
                    >

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
                    Register Crop
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const searchInput =
        document.getElementById("cropSearch");

    const rows =
        document.querySelectorAll(
            "#cropsTable tbody tr[data-status]"
        );

    const filterButtons =
        document.querySelectorAll(".filter-btn");

    let activeStatus = "all";


    function filterCrops() {

        const search = searchInput
            ? searchInput.value.toLowerCase().trim()
            : "";

        rows.forEach(function (row) {

            const text =
                row.innerText.toLowerCase();

            const status =
                row.dataset.status;

            const matchesSearch =
                text.includes(search);

            const matchesStatus =
                activeStatus === "all" ||
                status === activeStatus;

            row.style.display =
                matchesSearch && matchesStatus
                    ? ""
                    : "none";

        });

    }


    if (searchInput) {

        searchInput.addEventListener(
            "input",
            filterCrops
        );

    }


    filterButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                filterButtons.forEach(
                    function (item) {
                        item.classList.remove("active");
                    }
                );

                button.classList.add("active");

                activeStatus =
                    button.dataset.status;

                filterCrops();

            }
        );

    });

});
</script>


<?php
require_once __DIR__ . '/includes/footer.php';
?>