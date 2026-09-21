<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/order_data.php';
require_once __DIR__ . '/includes/inventory_data.php';

$orders = getAllMarketOrders();
$inventoryItems = getAllInventory();

$selectedInventory = trim($_GET['inventory'] ?? '');
$selectedCrop = trim($_GET['crop'] ?? '');
$success = trim($_GET['success'] ?? '');

$pageTitle = 'Market Orders';
$pageSubtitle = count($orders) . ' market orders';

require_once __DIR__ . '/includes/header.php';

?>

<?php if ($success === 'added'): ?>

    <div class="flash-message flash-success">
        Market order created successfully.
    </div>

<?php elseif ($success === 'cancelled'): ?>

    <div class="flash-message flash-success">
        Market order cancelled successfully.
    </div>

<?php endif; ?>


<div class="page-toolbar">

    <div class="table-search">

        <span>⌕</span>

        <input
            type="text"
            id="orderSearch"
            placeholder="Search order, buyer or crop"
        >

    </div>


    <button
        type="button"
        class="btn btn-primary"
        data-modal-open="createOrderModal"
    >
        + Create Market Order
    </button>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table class="app-table" id="marketOrdersTable">

            <thead>

            <tr>
                <th>Order ID</th>
                <th>Buyer</th>
                <th>Crop</th>
                <th>Quantity</th>
                <th>Total Amount</th>
                <th>Order Date</th>
                <th>Delivery Date</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            </thead>


            <tbody>

            <?php foreach ($orders as $order): ?>

                <tr>

                    <td>
                        <strong>
                            <?= htmlspecialchars($order['id']) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($order['buyer']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($order['crop']) ?>
                    </td>

                    <td>
                        <?= number_format((float) $order['quantity'], 1) ?>
                        ton
                    </td>

                    <td>
                        ৳<?= number_format((float) $order['amount']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($order['order_date']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($order['delivery_date']) ?>
                    </td>

                    <td>

                        <?php
                        $paymentClass = 'status-warning';

                        if ($order['payment'] === 'Paid') {
                            $paymentClass = 'status-success';
                        } elseif ($order['payment'] === 'Unpaid') {
                            $paymentClass = 'status-danger';
                        }
                        ?>

                        <span class="status-badge <?= $paymentClass ?>">
                            <?= htmlspecialchars($order['payment']) ?>
                        </span>

                    </td>

                    <td>

                        <?php
                        $statusClass = 'status-warning';

                        if ($order['status'] === 'Delivered') {
                            $statusClass = 'status-success';
                        } elseif ($order['status'] === 'In Transit') {
                            $statusClass = 'status-info';
                        } elseif ($order['status'] === 'Cancelled') {
                            $statusClass = 'status-danger';
                        }
                        ?>

                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>

                    </td>

                    <td>

                        <div class="action-buttons">

                            <a
                                href="market_order_details.php?id=<?= urlencode($order['id']) ?>"
                                class="action-link view"
                            >
                                View
                            </a>

                            <?php if ($order['status'] !== 'Cancelled'): ?>

                                <a
                                    href="deliveries.php?order=<?= urlencode($order['id']) ?>"
                                    class="action-link delivery"
                                >
                                    Delivery
                                </a>

                            <?php endif; ?>


                            <?php if (
                                !in_array(
                                    $order['status'],
                                    ['Delivered', 'Cancelled'],
                                    true
                                )
                            ): ?>

                                <a
                                    href="market_order_cancel.php?id=<?= urlencode($order['id']) ?>"
                                    class="action-link delete"
                                    data-confirm="Cancel order <?= htmlspecialchars($order['id'], ENT_QUOTES) ?>?"
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


<!-- CREATE ORDER MODAL -->

<div class="modal-overlay" id="createOrderModal">

    <div class="app-modal">

        <div class="modal-header">

            <div>
                <h2>Create Market Order</h2>
                <p>Create an order from available inventory.</p>
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
            action="market_order_add.php"
            method="POST"
            class="modal-body"
        >

            <div class="app-form-group">

                <label>Inventory / Crop</label>

                <select
                    name="inventory_id"
                    class="app-select"
                    required
                >

                    <option value="">
                        Select inventory
                    </option>

                    <?php foreach ($inventoryItems as $item): ?>

                        <?php
                        if ((float) $item['available'] <= 0) {
                            continue;
                        }

                        $isSelected = false;

                        if ($selectedInventory === $item['id']) {
                            $isSelected = true;
                        } elseif (
                            $selectedInventory === '' &&
                            $selectedCrop !== '' &&
                            strtolower($selectedCrop) ===
                            strtolower($item['crop'])
                        ) {
                            $isSelected = true;
                        }
                        ?>

                        <option
                            value="<?= htmlspecialchars($item['id']) ?>"
                            <?= $isSelected ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($item['id']) ?>
                            —
                            <?= htmlspecialchars($item['crop']) ?>
                            —
                            <?= number_format((float) $item['available'], 1) ?>
                            ton available
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="app-form-group">

                <label>Buyer / Market Name</label>

                <input
                    type="text"
                    name="buyer"
                    class="app-input"
                    placeholder="e.g. Dhaka Fresh Market"
                    required
                >

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Quantity (ton)</label>

                    <input
                        type="number"
                        name="quantity"
                        class="app-input"
                        min="0.1"
                        step="0.1"
                        required
                    >

                </div>


                <div class="app-form-group">

                    <label>Total Amount (৳)</label>

                    <input
                        type="number"
                        name="amount"
                        class="app-input"
                        min="0"
                        step="1"
                        required
                    >

                </div>

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Order Date</label>

                    <input
                        type="date"
                        name="order_date"
                        class="app-input"
                        required
                    >

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

            </div>


            <div class="app-form-group">

                <label>Payment Status</label>

                <select
                    name="payment"
                    class="app-select"
                    required
                >
                    <option value="Unpaid">Unpaid</option>
                    <option value="Partial">Partial</option>
                    <option value="Paid">Paid</option>
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
                    Create Order
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const input =
        document.getElementById("orderSearch");

    const rows =
        document.querySelectorAll(
            "#marketOrdersTable tbody tr"
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