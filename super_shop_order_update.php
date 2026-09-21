<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/super_shop_data.php';

$id = trim($_GET['id'] ?? $_POST['id'] ?? '');

$order = findSuperShopOrderById($id);

if (!$order) {
    header('Location: super_shop_orders.php');
    exit;
}

if ($order['status'] === 'Cancelled') {
    header('Location: super_shop_orders.php');
    exit;
}

$error = '';


/*
|--------------------------------------------------------------------------
| Update Order Status
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = trim($_POST['status'] ?? '');

    $allowedStatuses = [
        'Pending',
        'Processing',
        'Delivered'
    ];


    /*
    |--------------------------------------------------------------------------
    | Validate Status
    |--------------------------------------------------------------------------
    */

    if (!in_array($status, $allowedStatuses, true)) {

        $error = 'Please select a valid order status.';

    } else {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Lock Order
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    status
                FROM super_shop_orders
                WHERE id = ?
                LIMIT 1
                FOR UPDATE
            ");

            $stmt->execute([$id]);

            $lockedOrder = $stmt->fetch();

            if (!$lockedOrder) {
                throw new Exception('Order not found.');
            }


            /*
            |--------------------------------------------------------------------------
            | Do Not Update Cancelled Order
            |--------------------------------------------------------------------------
            */

            if ($lockedOrder['status'] === 'Cancelled') {

                $pdo->rollBack();

                header(
                    'Location: super_shop_orders.php?error=cancelled'
                );

                exit;
            }


            /*
            |--------------------------------------------------------------------------
            | Update MySQL
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE super_shop_orders
                SET status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $status,
                $id
            ]);


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            $pdo->commit();


            header(
                'Location: super_shop_orders.php?success=updated'
            );

            exit;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error =
                'Unable to update the order. Please try again.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Keep Selected Status After Error
    |--------------------------------------------------------------------------
    */

    $order['status'] = $status;
}


$pageTitle = 'Update Super Shop Order';

$pageSubtitle =
    $order['id'] . ' — ' . $order['shop'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="super_shop_order_details.php?id=<?= urlencode($order['id']) ?>"
        class="btn btn-light"
    >
        ← Back
    </a>

</div>


<?php if ($error !== ''): ?>

    <div class="flash-message flash-error">
        <?= htmlspecialchars($error) ?>
    </div>

<?php endif; ?>


<div class="app-card edit-form-card">

    <div class="card-header">

        <div>

            <h3>Update Order Status</h3>

            <span class="card-subtitle">
                <?= htmlspecialchars($order['product']) ?>
            </span>

        </div>

    </div>


    <div class="card-body">

        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($order['id']) ?>"
            >


            <div class="app-form-group">

                <label>Status</label>

                <select
                    name="status"
                    class="app-select"
                    required
                >

                    <?php
                    $statuses = [
                        'Pending',
                        'Processing',
                        'Delivered'
                    ];
                    ?>

                    <?php foreach ($statuses as $status): ?>

                        <option
                            value="<?= htmlspecialchars($status) ?>"
                            <?= $order['status'] === $status
                                ? 'selected'
                                : '' ?>
                        >
                            <?= htmlspecialchars($status) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-actions">

                <a
                    href="super_shop_order_details.php?id=<?= urlencode($order['id']) ?>"
                    class="btn btn-light"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>