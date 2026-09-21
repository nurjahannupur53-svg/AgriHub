<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/delivery_data.php';


$id = trim($_GET['id'] ?? $_POST['id'] ?? '');

$delivery = findDeliveryById($id);

if (!$delivery) {
    header('Location: deliveries.php');
    exit;
}

if ($delivery['status'] === 'Delivered') {
    header(
        'Location: delivery_details.php?id=' .
        urlencode($delivery['id'])
    );
    exit;
}

$error = '';


/*
|--------------------------------------------------------------------------
| Update Delivery
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $driver = trim($_POST['driver'] ?? '');
    $deliveryDate = trim($_POST['delivery_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $allowedStatuses = [
        'In Transit',
        'Delivered'
    ];


    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if (
        $driver === '' ||
        $deliveryDate === '' ||
        !in_array($status, $allowedStatuses, true)
    ) {

        $error = 'Please complete all fields correctly.';

    } else {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Lock Delivery
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    order_id,
                    vehicle_id,
                    delivery_date,
                    status
                FROM deliveries
                WHERE id = ?
                LIMIT 1
                FOR UPDATE
            ");

            $stmt->execute([$id]);

            $lockedDelivery = $stmt->fetch();

            if (!$lockedDelivery) {
                throw new Exception('Delivery not found.');
            }


            /*
            |--------------------------------------------------------------------------
            | Prevent Double Finalization
            |--------------------------------------------------------------------------
            */

            if ($lockedDelivery['status'] === 'Delivered') {

                $pdo->rollBack();

                header(
                    'Location: delivery_details.php?id=' .
                    urlencode($id)
                );

                exit;
            }


            /*
            |--------------------------------------------------------------------------
            | Lock Market Order
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    inventory_id,
                    quantity,
                    order_date,
                    status
                FROM market_orders
                WHERE id = ?
                LIMIT 1
                FOR UPDATE
            ");

            $stmt->execute([
                $lockedDelivery['order_id']
            ]);

            $order = $stmt->fetch();

            if (!$order) {
                throw new Exception('Market order not found.');
            }


            /*
            |--------------------------------------------------------------------------
            | Validate Delivery Date
            |--------------------------------------------------------------------------
            */

            if ($deliveryDate < $order['order_date']) {

                $pdo->rollBack();

                $error =
                    'Delivery date cannot be earlier than the order date.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Lock Vehicle
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    SELECT
                        id,
                        status
                    FROM vehicles
                    WHERE id = ?
                    LIMIT 1
                    FOR UPDATE
                ");

                $stmt->execute([
                    $lockedDelivery['vehicle_id']
                ]);

                $vehicle = $stmt->fetch();

                if (!$vehicle) {
                    throw new Exception('Vehicle not found.');
                }


                /*
                |--------------------------------------------------------------------------
                | Update Driver on Vehicle
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    UPDATE vehicles
                    SET driver = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $driver,
                    $lockedDelivery['vehicle_id']
                ]);


                /*
                |--------------------------------------------------------------------------
                | If Delivery remains In Transit
                |--------------------------------------------------------------------------
                */

                if ($status === 'In Transit') {

                    $stmt = $pdo->prepare("
                        UPDATE deliveries
                        SET
                            delivery_date = ?,
                            status = 'In Transit'
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $deliveryDate,
                        $id
                    ]);


                    $stmt = $pdo->prepare("
                        UPDATE market_orders
                        SET
                            delivery_date = ?,
                            status = 'In Transit'
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $deliveryDate,
                        $order['id']
                    ]);


                    $stmt = $pdo->prepare("
                        UPDATE vehicles
                        SET status = 'In Transit'
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $lockedDelivery['vehicle_id']
                    ]);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | DELIVERY COMPLETED
                    |--------------------------------------------------------------------------
                    |
                    | The stock has already been removed from AVAILABLE when
                    | the order was created.
                    |
                    | Therefore, on delivery:
                    |
                    | AVAILABLE stays unchanged
                    | RESERVED decreases
                    | TOTAL decreases
                    |
                    | Example:
                    |
                    | Before order:
                    | Total 3.1
                    | Available 2.5
                    | Reserved 0.6
                    |
                    | After delivery:
                    | Total 2.5
                    | Available 2.5
                    | Reserved 0.0
                    |
                    */


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Inventory
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        SELECT
                            id,
                            total,
                            available,
                            reserved
                        FROM inventory
                        WHERE id = ?
                        LIMIT 1
                        FOR UPDATE
                    ");

                    $stmt->execute([
                        $order['inventory_id']
                    ]);

                    $inventory = $stmt->fetch();

                    if (!$inventory) {
                        throw new Exception('Inventory not found.');
                    }


                    $orderQuantity =
                        (float) $order['quantity'];

                    $currentTotal =
                        (float) $inventory['total'];

                    $currentAvailable =
                        (float) $inventory['available'];

                    $currentReserved =
                        (float) $inventory['reserved'];


                    /*
                    |--------------------------------------------------------------------------
                    | Reserved Stock Safety Check
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $currentReserved + 0.00001 <
                        $orderQuantity
                    ) {
                        throw new Exception(
                            'Reserved inventory is lower than order quantity.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Finalize Inventory
                    |--------------------------------------------------------------------------
                    */

                    $newReserved =
                        $currentReserved - $orderQuantity;

                    $newTotal =
                        $currentTotal - $orderQuantity;


                    if (
                        $newReserved < 0 ||
                        $newTotal < 0 ||
                        $currentAvailable + $newReserved >
                            $newTotal + 0.00001
                    ) {
                        throw new Exception(
                            'Invalid inventory balance.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Recalculate Inventory Status
                    |--------------------------------------------------------------------------
                    */

                    if ($currentAvailable <= 0) {

                        $inventoryStatus = 'Out of Stock';

                    } elseif ($newReserved > 0) {

                        $inventoryStatus = 'Partially Reserved';

                    } else {

                        $inventoryStatus = 'Available';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Update Inventory
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        UPDATE inventory
                        SET
                            total = ?,
                            reserved = ?,
                            status = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $newTotal,
                        $newReserved,
                        $inventoryStatus,
                        $inventory['id']
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Update Delivery
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        UPDATE deliveries
                        SET
                            delivery_date = ?,
                            status = 'Delivered'
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $deliveryDate,
                        $id
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Update Market Order
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        UPDATE market_orders
                        SET
                            delivery_date = ?,
                            status = 'Delivered'
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $deliveryDate,
                        $order['id']
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Release Vehicle
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $pdo->prepare("
                        UPDATE vehicles
                        SET status = 'Available'
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $lockedDelivery['vehicle_id']
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Commit
                |--------------------------------------------------------------------------
                */

                $pdo->commit();


                header(
                    'Location: deliveries.php?success=updated'
                );

                exit;
            }


        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error =
                'Unable to update the delivery. Please check the related inventory and try again.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Keep Submitted Values After Validation Error
    |--------------------------------------------------------------------------
    */

    $delivery['driver'] = $driver;
    $delivery['delivery_date'] = $deliveryDate;
    $delivery['status'] = $status;
}


$pageTitle = 'Update Delivery';

$pageSubtitle =
    $delivery['id'] . ' — ' . $delivery['buyer'];

require_once __DIR__ . '/includes/header.php';

?>

<div class="details-top-actions">

    <a
        href="delivery_details.php?id=<?= urlencode($delivery['id']) ?>"
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
        <h3>Update Delivery</h3>
    </div>


    <div class="card-body">

        <form method="POST">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($delivery['id']) ?>"
            >


            <div class="app-form-group">

                <label>Driver</label>

                <input
                    type="text"
                    name="driver"
                    class="app-input"
                    value="<?= htmlspecialchars($delivery['driver']) ?>"
                    required
                >

            </div>


            <div class="form-grid-2">

                <div class="app-form-group">

                    <label>Delivery Date</label>

                    <input
                        type="date"
                        name="delivery_date"
                        class="app-input"
                        value="<?= htmlspecialchars($delivery['delivery_date']) ?>"
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

                        <option
                            value="In Transit"
                            <?= $delivery['status'] === 'In Transit'
                                ? 'selected'
                                : '' ?>
                        >
                            In Transit
                        </option>

                        <option
                            value="Delivered"
                            <?= $delivery['status'] === 'Delivered'
                                ? 'selected'
                                : '' ?>
                        >
                            Delivered
                        </option>

                    </select>

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="delivery_details.php?id=<?= urlencode($delivery['id']) ?>"
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