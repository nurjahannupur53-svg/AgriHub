<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/delivery_data.php';
require_once __DIR__ . '/includes/order_data.php';
require_once __DIR__ . '/includes/vehicle_data.php';


$deliveries = getAllDeliveries();
$orders = getAllMarketOrders();
$vehicles = getAllVehicles();

$selectedOrder = trim($_GET['order'] ?? '');
$success = trim($_GET['success'] ?? '');
$error = trim($_GET['error'] ?? '');

$pageTitle = 'Deliveries';
$pageSubtitle = count($deliveries) . ' delivery records';

require_once __DIR__ . '/includes/header.php';

?>


<?php if ($success === 'added'): ?>

    <div class="flash-message flash-success">
        Delivery created successfully.
    </div>

<?php elseif ($success === 'updated'): ?>

    <div class="flash-message flash-success">
        Delivery updated successfully.
    </div>

<?php endif; ?>


<?php if ($error !== ''): ?>

    <div class="flash-message flash-error">

        <?php if ($error === 'missing'): ?>

            Please complete all required delivery fields.

        <?php elseif ($error === 'order'): ?>

            The selected market order could not be found.

        <?php elseif ($error === 'orderstatus'): ?>

            This market order cannot be assigned to a delivery.

        <?php elseif ($error === 'date'): ?>

            Delivery date cannot be earlier than the order date.

        <?php elseif ($error === 'duplicate'): ?>

            A delivery already exists for this market order.

        <?php elseif ($error === 'vehicle'): ?>

            The selected vehicle could not be found.

        <?php elseif ($error === 'vehiclebusy'): ?>

            The selected vehicle is currently unavailable.

        <?php elseif ($error === 'capacity'): ?>

            The selected vehicle does not have enough capacity for this order.

        <?php elseif ($error === 'database'): ?>

            Unable to create the delivery. Please try again.

        <?php else: ?>

            Unable to process the delivery request.

        <?php endif; ?>

    </div>

<?php endif; ?>


<div class="page-toolbar">

    <div class="table-search">

        <span>⌕</span>

        <input
            type="text"
            id="deliverySearch"
            placeholder="Search delivery, order, buyer or driver"
        >

    </div>


    <button
        type="button"
        class="btn btn-primary"
        data-modal-open="deliveryModal"
    >
        + Create Delivery
    </button>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table
            class="app-table"
            id="deliveryTable"
        >

            <thead>

            <tr>
                <th>Delivery ID</th>
                <th>Order</th>
                <th>Destination / Buyer</th>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Delivery Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            </thead>


            <tbody>

            <?php foreach ($deliveries as $delivery): ?>

                <tr>

                    <td>
                        <strong>
                            <?= htmlspecialchars($delivery['id']) ?>
                        </strong>
                    </td>

                    <td>

                        <a
                            href="market_order_details.php?id=<?= urlencode($delivery['order_id']) ?>"
                            class="table-id-link"
                        >
                            <?= htmlspecialchars($delivery['order_id']) ?>
                        </a>

                    </td>

                    <td>
                        <?= htmlspecialchars($delivery['buyer']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($delivery['driver']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($delivery['vehicle']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($delivery['delivery_date']) ?>
                    </td>

                    <td>

                        <?php

                        $statusClass = 'status-warning';

                        if ($delivery['status'] === 'Delivered') {

                            $statusClass = 'status-success';

                        } elseif ($delivery['status'] === 'In Transit') {

                            $statusClass = 'status-info';

                        } elseif ($delivery['status'] === 'Cancelled') {

                            $statusClass = 'status-danger';
                        }

                        ?>

                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars($delivery['status']) ?>
                        </span>

                    </td>

                    <td>

                        <div class="action-buttons">

                            <a
                                href="delivery_details.php?id=<?= urlencode($delivery['id']) ?>"
                                class="action-link view"
                            >
                                View
                            </a>

                            <?php if (
                                !in_array(
                                    $delivery['status'],
                                    ['Delivered', 'Cancelled'],
                                    true
                                )
                            ): ?>

                                <a
                                    href="delivery_update.php?id=<?= urlencode($delivery['id']) ?>"
                                    class="action-link edit"
                                >
                                    Update
                                </a>

                            <?php endif; ?>

                            <a
                                href="vehicles.php?id=<?= urlencode($delivery['vehicle_id']) ?>"
                                class="action-link vehicle"
                            >
                                Vehicle
                            </a>

                            <a
                                href="iot_telematics.php?vehicle=<?= urlencode($delivery['vehicle_id']) ?>"
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


<!-- CREATE DELIVERY MODAL -->

<div
    class="modal-overlay"
    id="deliveryModal"
>

    <div class="app-modal">

        <div class="modal-header">

            <div>
                <h2>Create Delivery</h2>
                <p>Assign a driver and vehicle to a market order.</p>
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
            action="delivery_add.php"
            method="POST"
            class="modal-body"
        >

            <div class="app-form-group">

                <label>Market Order</label>

                <select
                    name="order_id"
                    class="app-select"
                    required
                >

                    <option value="">
                        Select order
                    </option>

                    <?php foreach ($orders as $order): ?>

                        <?php

                        if (
                            in_array(
                                $order['status'],
                                ['Delivered', 'Cancelled'],
                                true
                            )
                        ) {
                            continue;
                        }

                        if (findDeliveryByOrder($order['id'])) {
                            continue;
                        }

                        ?>

                        <option
                            value="<?= htmlspecialchars($order['id']) ?>"
                            <?= $selectedOrder === $order['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= htmlspecialchars($order['id']) ?>
                            —
                            <?= htmlspecialchars($order['buyer']) ?>
                            —
                            <?= htmlspecialchars($order['crop']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Driver</label>

                    <input
                        type="text"
                        name="driver"
                        class="app-input"
                        placeholder="Driver name"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Vehicle</label>

                    <select
                        name="vehicle_id"
                        class="app-select"
                        required
                    >

                        <option value="">
                            Select vehicle
                        </option>

                        <?php foreach ($vehicles as $vehicle): ?>

                            <?php

                            if (
                                in_array(
                                    $vehicle['status'],
                                    ['In Transit', 'Maintenance'],
                                    true
                                )
                            ) {
                                continue;
                            }

                            ?>

                            <option
                                value="<?= htmlspecialchars($vehicle['id']) ?>"
                            >
                                <?= htmlspecialchars($vehicle['id']) ?>
                                —
                                <?= htmlspecialchars($vehicle['registration']) ?>
                                —
                                <?= htmlspecialchars($vehicle['capacity']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>


            <div class="app-form-group">

                <label>Delivery Date</label>

                <input
                    type="date"
                    name="delivery_date"
                    class="app-input"
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
                    Create Delivery
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const input =
        document.getElementById("deliverySearch");

    const rows =
        document.querySelectorAll(
            "#deliveryTable tbody tr"
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