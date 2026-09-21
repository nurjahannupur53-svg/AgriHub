<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/equipment_data.php';
require_once __DIR__ . '/includes/farmer_data.php';

$bookings = getAllEquipmentBookings();
$farmers = getAllFarmers();

$success = trim($_GET['success'] ?? '');

$pageTitle = 'Equipment Booking';
$pageSubtitle = count($bookings) . ' equipment bookings';

require_once __DIR__ . '/includes/header.php';

?>

<?php if ($success === 'added'): ?>

    <div class="flash-message flash-success">
        Equipment booking created successfully.
    </div>

<?php elseif ($success === 'updated'): ?>

    <div class="flash-message flash-success">
        Equipment booking updated successfully.
    </div>

<?php elseif ($success === 'cancelled'): ?>

    <div class="flash-message flash-success">
        Equipment booking cancelled successfully.
    </div>

<?php endif; ?>


<div class="page-toolbar">

    <div class="table-search">

        <span>⌕</span>

        <input
            type="text"
            id="equipmentSearch"
            placeholder="Search equipment, farmer or purpose"
        >

    </div>


    <button
        type="button"
        class="btn btn-primary"
        data-modal-open="equipmentBookingModal"
    >
        + New Booking
    </button>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table
            class="app-table"
            id="equipmentTable"
        >

            <thead>

            <tr>
                <th>Booking ID</th>
                <th>Equipment</th>
                <th>Purpose</th>
                <th>Farmer</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            </thead>


            <tbody>

            <?php foreach ($bookings as $booking): ?>

                <tr>

                    <td>
                        <strong>
                            <?= htmlspecialchars($booking['id']) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($booking['equipment']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($booking['purpose']) ?>
                    </td>

                    <td>

                        <a
                            href="farmer_details.php?id=<?= urlencode($booking['farmer_id']) ?>"
                            class="table-id-link"
                        >
                            <?= htmlspecialchars($booking['farmer']) ?>
                        </a>

                    </td>

                    <td>
                        <?= htmlspecialchars($booking['start_date']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($booking['end_date']) ?>
                    </td>

                    <td>

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

                    </td>

                    <td>

                        <div class="action-buttons">

                            <a
                                href="equipment_details.php?id=<?= urlencode($booking['id']) ?>"
                                class="action-link view"
                            >
                                View
                            </a>


                            <?php if (
                                !in_array(
                                    $booking['status'],
                                    ['Returned', 'Cancelled'],
                                    true
                                )
                            ): ?>

                                <a
                                    href="equipment_update.php?id=<?= urlencode($booking['id']) ?>"
                                    class="action-link edit"
                                >
                                    Update
                                </a>

                                <a
                                    href="equipment_cancel.php?id=<?= urlencode($booking['id']) ?>"
                                    class="action-link delete"
                                    data-confirm="Cancel booking <?= htmlspecialchars($booking['id'], ENT_QUOTES) ?>?"
                                >
                                    Cancel
                                </a>

                            <?php endif; ?>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- NEW BOOKING MODAL -->

<div
    class="modal-overlay"
    id="equipmentBookingModal"
>

    <div class="app-modal">

        <div class="modal-header">

            <div>
                <h2>New Equipment Booking</h2>
                <p>Assign agricultural equipment to a farmer.</p>
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
            action="equipment_add.php"
            method="POST"
            class="modal-body"
        >

            <div class="app-form-group">

                <label>Equipment</label>

                <select
                    name="equipment"
                    class="app-select"
                    required
                >
                    <option value="">Select equipment</option>
                    <option>Combine Harvester</option>
                    <option>Tractor (MF 240)</option>
                    <option>Drip Irrigation Set</option>
                    <option>Sprayer Machine</option>
                    <option>Power Tiller</option>
                    <option>Seed Drill Machine</option>
                </select>

            </div>


            <div class="app-form-group">

                <label>Purpose</label>

                <select
                    name="purpose"
                    class="app-select"
                    required
                >
                    <option value="">Select purpose</option>
                    <option>Harvesting</option>
                    <option>Tillage</option>
                    <option>Irrigation</option>
                    <option>Pest Control</option>
                    <option>Planting</option>
                    <option>Land Preparation</option>
                </select>

            </div>


            <div class="app-form-group">

                <label>Farmer</label>

                <select
                    name="farmer_id"
                    class="app-select"
                    required
                >

                    <option value="">Select farmer</option>

                    <?php foreach ($farmers as $farmer): ?>

                        <option value="<?= htmlspecialchars($farmer['id']) ?>">
                            <?= htmlspecialchars($farmer['id']) ?>
                            —
                            <?= htmlspecialchars($farmer['name']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Start Date</label>

                    <input
                        type="date"
                        name="start_date"
                        class="app-input"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>End Date</label>

                    <input
                        type="date"
                        name="end_date"
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
                    Create Booking
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const input =
        document.getElementById("equipmentSearch");

    const rows =
        document.querySelectorAll(
            "#equipmentTable tbody tr"
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