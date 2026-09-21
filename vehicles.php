<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/vehicle_data.php';

$vehicles = getAllVehicles();

$selectedVehicle = trim($_GET['id'] ?? '');
$success = trim($_GET['success'] ?? '');

$pageTitle = 'Vehicles';
$pageSubtitle = count($vehicles) . ' registered vehicles';

require_once __DIR__ . '/includes/header.php';

?>

<?php if ($success === 'added'): ?>

    <div class="flash-message flash-success">
        Vehicle added successfully.
    </div>

<?php elseif ($success === 'updated'): ?>

    <div class="flash-message flash-success">
        Vehicle updated successfully.
    </div>

<?php endif; ?>


<div class="vehicle-summary-grid">

    <?php

    $activeCount = 0;
    $availableCount = 0;
    $transitCount = 0;
    $maintenanceCount = 0;

    foreach ($vehicles as $vehicle) {

        if ($vehicle['status'] === 'Active') {
            $activeCount++;
        }

        if ($vehicle['status'] === 'Available') {
            $availableCount++;
        }

        if ($vehicle['status'] === 'In Transit') {
            $transitCount++;
        }

        if ($vehicle['status'] === 'Maintenance') {
            $maintenanceCount++;
        }
    }

    ?>

    <div class="vehicle-summary-card">
        <span>Active</span>
        <strong><?= $activeCount ?></strong>
        <small>Assigned vehicles</small>
    </div>

    <div class="vehicle-summary-card">
        <span>Available</span>
        <strong><?= $availableCount ?></strong>
        <small>Ready for assignment</small>
    </div>

    <div class="vehicle-summary-card">
        <span>In Transit</span>
        <strong><?= $transitCount ?></strong>
        <small>Currently delivering</small>
    </div>

    <div class="vehicle-summary-card">
        <span>Maintenance</span>
        <strong><?= $maintenanceCount ?></strong>
        <small>Temporarily unavailable</small>
    </div>

</div>


<div class="page-toolbar">

    <div class="table-search">

        <span>⌕</span>

        <input
            type="text"
            id="vehicleSearch"
            placeholder="Search vehicle, registration or driver"
        >

    </div>


    <button
        type="button"
        class="btn btn-primary"
        data-modal-open="vehicleModal"
    >
        + Add Vehicle
    </button>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table
            class="app-table"
            id="vehicleTable"
        >

            <thead>

            <tr>
                <th>Vehicle ID</th>
                <th>Registration</th>
                <th>Vehicle Type</th>
                <th>Capacity</th>
                <th>Driver</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            </thead>


            <tbody>

            <?php foreach ($vehicles as $vehicle): ?>

                <tr
                    class="<?= $selectedVehicle === $vehicle['id'] ? 'selected-table-row' : '' ?>"
                >

                    <td>
                        <strong>
                            <?= htmlspecialchars($vehicle['id']) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($vehicle['registration']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($vehicle['type']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($vehicle['capacity']) ?>
                    </td>

                    <td>

                        <?php if ($vehicle['driver'] === 'Unassigned'): ?>

                            <span class="muted-text">
                                Unassigned
                            </span>

                        <?php else: ?>

                            <strong>
                                <?= htmlspecialchars($vehicle['driver']) ?>
                            </strong>

                            <?php if ($vehicle['phone'] !== ''): ?>

                                <small class="vehicle-driver-phone">
                                    <?= htmlspecialchars($vehicle['phone']) ?>
                                </small>

                            <?php endif; ?>

                        <?php endif; ?>

                    </td>

                    <td>

                        <?php
                        $statusClass = 'status-warning';

                        if ($vehicle['status'] === 'Active') {
                            $statusClass = 'status-success';
                        } elseif ($vehicle['status'] === 'Available') {
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

                    </td>

                    <td>

                        <div class="action-buttons">

                            <a
                                href="vehicle_details.php?id=<?= urlencode($vehicle['id']) ?>"
                                class="action-link view"
                            >
                                View
                            </a>

                            <a
                                href="vehicle_edit.php?id=<?= urlencode($vehicle['id']) ?>"
                                class="action-link edit"
                            >
                                Edit
                            </a>

                            <a
                                href="iot_telematics.php?vehicle=<?= urlencode($vehicle['id']) ?>"
                                class="action-link iot"
                            >
                                IoT
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- ADD VEHICLE MODAL -->

<div
    class="modal-overlay"
    id="vehicleModal"
>

    <div class="app-modal">

        <div class="modal-header">

            <div>
                <h2>Add Vehicle</h2>
                <p>Register a new delivery vehicle.</p>
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
            action="vehicle_add.php"
            method="POST"
            class="modal-body"
        >

            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Registration Number</label>

                    <input
                        type="text"
                        name="registration"
                        class="app-input"
                        placeholder="e.g. DHA-GA-7890"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Vehicle Type</label>

                    <select
                        name="type"
                        class="app-select"
                        required
                    >
                        <option value="">Select type</option>
                        <option>Refrigerated Truck</option>
                        <option>Cargo Van</option>
                        <option>Flatbed Truck</option>
                        <option>Pickup Truck</option>
                        <option>Mini Truck</option>
                    </select>

                </div>

            </div>


            <div class="app-form-group">

                <label>Capacity</label>

                <input
                    type="text"
                    name="capacity"
                    class="app-input"
                    placeholder="e.g. 5 ton"
                    required
                >

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Driver Name</label>

                    <input
                        type="text"
                        name="driver"
                        class="app-input"
                        placeholder="Leave blank if unassigned"
                    >

                </div>


                <div class="app-form-group">

                    <label>Driver Phone</label>

                    <input
                        type="text"
                        name="phone"
                        class="app-input"
                        placeholder="01XXXXXXXXX"
                    >

                </div>

            </div>


            <div class="app-form-group">

                <label>Status</label>

                <select
                    name="status"
                    class="app-select"
                    required
                >
                    <option value="Available">
                        Available
                    </option>

                    <option value="Active">
                        Active
                    </option>

                    <option value="Maintenance">
                        Maintenance
                    </option>
                </select>

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
                    Add Vehicle
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const input =
        document.getElementById("vehicleSearch");

    const rows =
        document.querySelectorAll(
            "#vehicleTable tbody tr"
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