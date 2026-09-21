<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/order_data.php';

$id = trim($_GET['id'] ?? '');

$order = findMarketOrderById($id);

if (!$order) {
    header('Location: market_orders.php');
    exit;
}

$pageTitle = 'Market Order Details';
$pageSubtitle = 'Order ' . $order['id'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="page-toolbar">

    <a href="market_orders.php" class="btn btn-light">
        ← Back to Orders
    </a>

    <div class="action-buttons">

        <?php if ($order['status'] !== 'Cancelled'): ?>

            <a
                href="deliveries.php?order=<?= urlencode($order['id']) ?>"
                class="btn btn-primary"
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
                class="btn btn-danger"
                data-confirm="Cancel order <?= htmlspecialchars($order['id'], ENT_QUOTES) ?>?"
            >
                Cancel Order
            </a>

        <?php endif; ?>

    </div>

</div>


<div class="app-card">

    <div class="card-header">

        <div>
            <h2>
                <?= htmlspecialchars($order['id']) ?>
            </h2>

            <p>
                Market order information
            </p>
        </div>


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

    </div>


    <div class="detail-grid">

        <div class="detail-item">
            <span class="detail-label">Order ID</span>

            <strong>
                <?= htmlspecialchars($order['id']) ?>
            </strong>
        </div>


        <div class="detail-item">
            <span class="detail-label">Buyer / Market</span>

            <strong>
                <?= htmlspecialchars($order['buyer']) ?>
            </strong>
        </div>


        <div class="detail-item">
            <span class="detail-label">Crop</span>

            <strong>
                <?= htmlspecialchars($order['crop']) ?>
            </strong>
        </div>


        <div class="detail-item">
            <span class="detail-label">Quantity</span>

            <strong>
                <?= number_format((float) $order['quantity'], 1) ?>
                ton
            </strong>
        </div>


        <div class="detail-item">
            <span class="detail-label">Total Amount</span>

            <strong>
                ৳<?= number_format((float) $order['amount']) ?>
            </strong>
        </div>


        <div class="detail-item">
            <span class="detail-label">Payment Status</span>

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
        </div>


        <div class="detail-item">
            <span class="detail-label">Order Date</span>

            <strong>
                <?= htmlspecialchars($order['order_date']) ?>
            </strong>
        </div>


        <div class="detail-item">
            <span class="detail-label">Delivery Date</span>

            <strong>
                <?= htmlspecialchars($order['delivery_date']) ?>
            </strong>
        </div>


        <div class="detail-item">
            <span class="detail-label">Inventory</span>

            <a
                href="inventory_details.php?id=<?= urlencode($order['inventory_id']) ?>"
                class="detail-value-link"
            >
                <?= htmlspecialchars($order['inventory_id']) ?>
            </a>
        </div>


        <div class="detail-item">
            <span class="detail-label">Harvest Batch</span>

            <a
                href="batch_details.php?id=<?= urlencode($order['batch_id']) ?>"
                class="detail-value-link"
            >
                <?= htmlspecialchars($order['batch_id']) ?>
            </a>
        </div>


        <div class="detail-item">
            <span class="detail-label">Order Status</span>

            <span class="status-badge <?= $statusClass ?>">
                <?= htmlspecialchars($order['status']) ?>
            </span>
        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>