<?php

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/order_data.php';
require_once __DIR__ . '/includes/delivery_data.php';


/*
|--------------------------------------------------------------------------
| Only Allow POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: deliveries.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$orderId = trim($_POST['order_id'] ?? '');
$driver = trim($_POST['driver'] ?? '');
$vehicleId = trim($_POST['vehicle_id'] ?? '');
$deliveryDate = trim($_POST['delivery_date'] ?? '');


if (
    $orderId === '' ||
    $driver === '' ||
    $vehicleId === '' ||
    $deliveryDate === ''
) {
    header('Location: deliveries.php?error=missing');
    exit;
}


/*
|--------------------------------------------------------------------------
| Database Transaction
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Lock Market Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            inventory_id,
            buyer,
            quantity,
            order_date,
            delivery_date,
            status
        FROM market_orders
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$orderId]);

    $order = $stmt->fetch();

    if (!$order) {

        $pdo->rollBack();

        header(
            'Location: deliveries.php?error=order'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Cannot Deliver Cancelled / Delivered Orders
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $order['status'],
            ['Delivered', 'Cancelled'],
            true
        )
    ) {

        $pdo->rollBack();

        header(
            'Location: deliveries.php?error=orderstatus'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Delivery Date
    |--------------------------------------------------------------------------
    */

    if ($deliveryDate < $order['order_date']) {

        $pdo->rollBack();

        header(
            'Location: deliveries.php?error=date'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing Delivery
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM deliveries
        WHERE order_id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$orderId]);

    if ($stmt->fetch()) {

        $pdo->rollBack();

        header(
            'Location: deliveries.php?error=duplicate'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Lock Vehicle
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            registration,
            capacity,
            driver,
            status
        FROM vehicles
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$vehicleId]);

    $vehicle = $stmt->fetch();

    if (!$vehicle) {

        $pdo->rollBack();

        header(
            'Location: deliveries.php?error=vehicle'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Vehicle Availability
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $vehicle['status'],
            ['In Transit', 'Maintenance'],
            true
        )
    ) {

        $pdo->rollBack();

        header(
            'Location: deliveries.php?error=vehiclebusy'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Vehicle Capacity Check
    |--------------------------------------------------------------------------
    */

    if (
        (float) $order['quantity'] >
        (float) $vehicle['capacity']
    ) {

        $pdo->rollBack();

        header(
            'Location: deliveries.php?error=capacity'
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Next Delivery ID
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT
            MAX(
                CAST(
                    SUBSTRING(id, 4)
                    AS UNSIGNED
                )
            ) AS max_number
        FROM deliveries
    ");

    $row = $stmt->fetch();

    $nextNumber =
        ((int) ($row['max_number'] ?? 0)) + 1;

    $newId =
        'DEL' .
        str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );


    /*
    |--------------------------------------------------------------------------
    | Insert Delivery
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO deliveries
        (
            id,
            order_id,
            vehicle_id,
            delivery_date,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            'In Transit'
        )
    ");

    $stmt->execute([
        $newId,
        $orderId,
        $vehicleId,
        $deliveryDate
    ]);


    /*
    |--------------------------------------------------------------------------
    | Update Market Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE market_orders
        SET
            status = 'In Transit',
            delivery_date = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $deliveryDate,
        $orderId
    ]);


    /*
    |--------------------------------------------------------------------------
    | Update Vehicle
    |--------------------------------------------------------------------------
    |
    | Driver entered in the delivery form becomes the assigned driver.
    |
    */

    $stmt = $pdo->prepare("
        UPDATE vehicles
        SET
            driver = ?,
            status = 'In Transit'
        WHERE id = ?
    ");

    $stmt->execute([
        $driver,
        $vehicleId
    ]);


    /*
    |--------------------------------------------------------------------------
    | Commit
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    header(
        'Location: deliveries.php?success=added'
    );

    exit;


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header(
        'Location: deliveries.php?error=database'
    );

    exit;
}