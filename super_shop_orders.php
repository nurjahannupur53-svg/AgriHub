<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/super_shop_data.php';

$orders = getAllSuperShopOrders();

$success = trim($_GET['success'] ?? '');

$pageTitle = 'Super Shop Orders';
$pageSubtitle = count($orders) . ' retail / super shop orders';

require_once __DIR__ . '/includes/header.php';

?>

<?php if ($success === 'added'): ?>

    <div class="flash-message flash-success">
        Super shop order created successfully.
    </div>

<?php elseif ($success === 'updated'): ?>

    <div class="flash-message flash-success">
        Order status updated successfully.
    </div>

<?php elseif ($success === 'cancelled'): ?>

    <div class="flash-message flash-success">
        Super shop order cancelled successfully.
    </div>

<?php endif; ?>


<div class="page-toolbar">

    <div class="table-search">

        <span>⌕</span>

        <input
            type="text"
            id="superShopSearch"
            placeholder="Search order, shop or product"
        >

    </div>


    <button
        type="button"
        class="btn btn-primary"
        data-modal-open="superShopOrderModal"
    >
        + New Order
    </button>

</div>


<div class="app-card">

    <div class="table-responsive">

        <table
            class="app-table"
            id="superShopTable"
        >

            <thead>

            <tr>
                <th>Order ID</th>
                <th>Super Shop</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Order Date</th>
                <th>Delivery Date</th>
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
                        <?= htmlspecialchars($order['shop']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($order['product']) ?>
                    </td>

                    <td>
                        <?= number_format((float) $order['quantity']) ?>
                        <?= htmlspecialchars($order['unit']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($order['order_date']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($order['delivery_date']) ?>
                    </td>

                    <td>

                        <?php
                        $statusClass = 'status-warning';

                        if ($order['status'] === 'Delivered') {
                            $statusClass = 'status-success';
                        } elseif ($order['status'] === 'Cancelled') {
                            $statusClass = 'status-danger';
                        } elseif ($order['status'] === 'Processing') {
                            $statusClass = 'status-info';
                        }
                        ?>

                        <span class="status-badge <?= $statusClass ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>

                    </td>

                    <td>

                        <div class="action-buttons">

                            <a
                                href="super_shop_order_details.php?id=<?= urlencode($order['id']) ?>"
                                class="action-link view"
                            >
                                View
                            </a>


                            <?php if (
                                !in_array(
                                    $order['status'],
                                    ['Delivered', 'Cancelled'],
                                    true
                                )
                            ): ?>

                                <a
                                    href="super_shop_order_update.php?id=<?= urlencode($order['id']) ?>"
                                    class="action-link edit"
                                >
                                    Update
                                </a>


                                <a
                                    href="super_shop_order_cancel.php?id=<?= urlencode($order['id']) ?>"
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


<!-- NEW ORDER MODAL -->

<div
    class="modal-overlay"
    id="superShopOrderModal"
>

    <div class="app-modal">

        <div class="modal-header">

            <div>
                <h2>Create Super Shop Order</h2>
                <p>Add a new retail order.</p>
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
            action="super_shop_order_add.php"
            method="POST"
            class="modal-body"
        >

            <div class="app-form-group">

                <label>Super Shop</label>

                <input
                    type="text"
                    name="shop"
                    class="app-input"
                    placeholder="e.g. Shwapno Superstore"
                    required
                >

            </div>


            <div class="app-form-group">

                <label>Product</label>

                <input
                    type="text"
                    name="product"
                    class="app-input"
                    placeholder="e.g. Aman Rice (5kg pack)"
                    required
                >

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Crop</label>

                    <select
                        name="crop"
                        class="app-select"
                        required
                    >
                        <option value="">Select crop</option>
                        <option>Aman Rice</option>
                        <option>Wheat</option>
                        <option>Potato</option>
                        <option>Lentil</option>
                        <option>Maize</option>
                        <option>Brinjal</option>
                        <option>Jute</option>
                    </select>

                </div>


                <div class="app-form-group">

                    <label>Quantity (packs)</label>

                    <input
                        type="number"
                        name="quantity"
                        class="app-input"
                        min="1"
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
        document.getElementById("superShopSearch");

    const rows =
        document.querySelectorAll(
            "#superShopTable tbody tr"
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