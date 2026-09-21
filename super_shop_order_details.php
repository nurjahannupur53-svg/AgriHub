<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/super_shop_data.php';

$id = trim($_GET['id'] ?? '');

$order = findSuperShopOrderById($id);

if (!$order) {
    header('Location: super_shop_orders.php');
    exit;
}

$pageTitle = 'Super Shop Order';
$pageSubtitle = $order['id'] . ' — ' . $order['shop'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="super_shop_orders.php"
        class="btn btn-light"
    >
        ← Back to Orders
    </a>


    <?php if (
        !in_array(
            $order['status'],
            ['Delivered', 'Cancelled'],
            true
        )
    ): ?>

        <div class="detail-action-group">

            <a
                href="super_shop_order_update.php?id=<?= urlencode($order['id']) ?>"
                class="btn btn-primary"
            >
                Update Status
            </a>

            <a
                href="super_shop_order_cancel.php?id=<?= urlencode($order['id']) ?>"
                class="btn btn-light"
                data-confirm="Cancel this super shop order?"
            >
                Cancel Order
            </a>

        </div>

    <?php endif; ?>

</div>


<div class="app-card details-section">

    <div class="card-header">
        <h3>Order Information</h3>
    </div>


    <div class="card-body">

        <div class="info-grid">

            <div class="info-item">
                <span>Order ID</span>
                <strong><?= htmlspecialchars($order['id']) ?></strong>
            </div>

            <div class="info-item">
                <span>Super Shop</span>
                <strong><?= htmlspecialchars($order['shop']) ?></strong>
            </div>

            <div class="info-item">
                <span>Product</span>
                <strong><?= htmlspecialchars($order['product']) ?></strong>
            </div>

            <div class="info-item">
                <span>Crop</span>
                <strong><?= htmlspecialchars($order['crop']) ?></strong>
            </div>

            <div class="info-item">
                <span>Quantity</span>

                <strong>
                    <?= number_format((float) $order['quantity']) ?>
                    <?= htmlspecialchars($order['unit']) ?>
                </strong>
            </div>

            <div class="info-item">
                <span>Order Date</span>
                <strong><?= htmlspecialchars($order['order_date']) ?></strong>
            </div>

            <div class="info-item">
                <span>Delivery Date</span>
                <strong><?= htmlspecialchars($order['delivery_date']) ?></strong>
            </div>

            <div class="info-item">
                <span>Status</span>

                <?php
                $statusClass = 'status-warning';

                if ($order['status'] === 'Delivered') {
                    $statusClass = 'status-success';
                } elseif ($order['status'] === 'Processing') {
                    $statusClass = 'status-info';
                } elseif ($order['status'] === 'Cancelled') {
                    $statusClass = 'status-danger';
                }
                ?>

                <span class="status-badge <?= $statusClass ?>">
                    <?= htmlspecialchars($order['status']) ?>
                </span>

            </div>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>